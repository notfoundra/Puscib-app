<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;
use DateTimeImmutable;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

/**
 * Import task dari Excel (composer require phpoffice/phpspreadsheet).
 *
 * Kolom: tanggal | PETUGAS 1..5 | TUJUAN1 | DESA | Kegiatan
 * - Baris kosong (pemisah blok) dilewati.
 * - Kegiatan belum ada di master  => otomatis dibuat.
 * - Desa / pegawai belum ada      => baris ditandai error (butuh jarak / NIP / jabatan, jadi tidak auto-buat).
 * - Tanggal kosong / tidak valid  => error.
 * - Task yang sama (tanggal+desa+kegiatan+set petugas) sudah ada => duplikat, dilewati.
 */
class TaskImporter
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        helper('laporan');
        $this->db = $db ?? db_connect();
    }

    public function preview(string $path): array
    {
        return self::analisa($this->bacaFile($path), $this->muatMaster(), $this->muatTaskAda());
    }

    public function commit(string $path): array
    {
        $hasil = $this->preview($path);

        $this->db->transStart();

        // 1. Kegiatan baru (hanya yang dipakai baris valid)
        $dipakai = [];
        foreach ($hasil['rows'] as $r) {
            if ($r['status'] === 'ok' && $r['kegiatan_baru']) {
                $dipakai[normalisasi_nama($r['kegiatan'])] = true;
            }
        }

        $idKegiatan = [];
        foreach ($hasil['kegiatan_baru'] as $norm => $nama) {
            if (! isset($dipakai[$norm])) {
                continue;
            }
            $this->db->table('master_kegiatan')->insert(['nama_kegiatan' => $nama]);
            $idKegiatan[$norm] = $this->db->insertID();
        }

        // 2. Task + anggota (urutan petugas dipertahankan: petugas 1 = task_member.id terkecil)
        $masuk = 0;
        foreach ($hasil['rows'] as $r) {
            if ($r['status'] !== 'ok') {
                continue;
            }

            $this->db->table('task')->insert([
                'tanggal'     => $r['tanggal'],
                'tujuan'      => $r['tujuan'],
                'id_desa'     => $r['id_desa'],
                'id_kegiatan' => $r['id_kegiatan'] ?? $idKegiatan[normalisasi_nama($r['kegiatan'])],
            ]);
            $idTask = $this->db->insertID();

            foreach ($r['id_pegawai'] as $idPegawai) {
                $this->db->table('task_member')->insert(['id_task' => $idTask, 'id_pegawai' => $idPegawai]);
            }
            $masuk++;
        }

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new RuntimeException('Import gagal, tidak ada data yang tersimpan.');
        }

        $hasil['ringkasan']['masuk']            = $masuk;
        $hasil['ringkasan']['kegiatan_dibuat'] = count($idKegiatan);

        return $hasil;
    }

    // ------------------------------------------------------------------
    // I/O
    // ------------------------------------------------------------------

    private function bacaFile(string $path): array
    {
        // formatData=false => tanggal tetap berupa angka serial Excel
        return IOFactory::load($path)->getSheet(0)->toArray(null, true, false, false);
    }

    private function muatMaster(): array
    {
        $peta = function (string $tabel, string $kolom): array {
            $out = [];
            foreach ($this->db->table($tabel)->select("id, {$kolom}")->get()->getResultArray() as $r) {
                $out[normalisasi_nama($r[$kolom])] = (int) $r['id'];
            }

            return $out;
        };

        return [
            'desa'     => $peta('master_desa', 'nama_desa'),
            'pegawai'  => $peta('master_pegawai', 'nama'),
            'kegiatan' => $peta('master_kegiatan', 'nama_kegiatan'),
        ];
    }

    /** Set kunci task yang sudah ada di DB, untuk deteksi duplikat. */
    private function muatTaskAda(): array
    {
        $rows = $this->db->query(
            'SELECT DATE(t.tanggal) AS tgl, t.id_desa, t.id_kegiatan, GROUP_CONCAT(tm.id_pegawai) AS pegawai
             FROM task t LEFT JOIN task_member tm ON tm.id_task = t.id
             GROUP BY t.id'
        )->getResultArray();

        $set = [];
        foreach ($rows as $r) {
            $ids = array_map('intval', array_filter(explode(',', (string) $r['pegawai'])));
            $set[self::kunci($r['tgl'], $r['id_desa'], $r['id_kegiatan'], $ids)] = true;
        }

        return $set;
    }

    // ------------------------------------------------------------------
    // Logika murni (tanpa DB / PhpSpreadsheet) => bisa dites langsung
    // ------------------------------------------------------------------

    /**
     * @param list<list<mixed>> $data   baris pertama = header
     * @param array             $master ['desa'=>[norm=>id], 'pegawai'=>[...], 'kegiatan'=>[...]]
     * @param array             $ada    set kunci task yang sudah ada
     */
    public static function analisa(array $data, array $master, array $ada = []): array
    {
        helper('laporan');

        $kol = self::petakanHeader($data[0] ?? []);

        $rows = [];
        $kegiatanBaru = [];
        $ringkasan = ['total' => 0, 'ok' => 0, 'error' => 0, 'duplikat' => 0];
        $sudahDiFile = [];

        foreach (array_slice($data, 1, null, true) as $i => $baris) {
            if (self::kosong($baris)) {
                continue; // pemisah blok
            }

            $pesan   = [];
            $tanggal = self::parseTanggal($baris[$kol['tanggal']] ?? null);
            if ($tanggal === null) {
                $pesan[] = 'Tanggal kosong / tidak valid';
            }

            // Petugas: buang kosong & duplikat, urutan dipertahankan
            $petugas = [];
            $idPegawai = [];
            foreach ($kol['petugas'] as $c) {
                $nama = trim((string) ($baris[$c] ?? ''));
                if ($nama === '') {
                    continue;
                }
                $id = $master['pegawai'][normalisasi_nama($nama)] ?? null;
                if ($id === null) {
                    $pesan[] = "Pegawai tidak ada di master: {$nama}";
                } elseif (! in_array($id, $idPegawai, true)) {
                    $idPegawai[] = $id;
                    $petugas[]   = $nama;
                }
            }
            if ($petugas === [] && $pesan === []) {
                $pesan[] = 'Petugas kosong';
            }

            $desa   = trim((string) ($baris[$kol['desa']] ?? ''));
            $idDesa = $master['desa'][normalisasi_nama($desa)] ?? null;
            if ($idDesa === null) {
                $pesan[] = $desa === '' ? 'Desa kosong' : "Desa tidak ada di master: {$desa}";
            }

            $kegiatan = trim((string) ($baris[$kol['kegiatan']] ?? ''));
            $normKeg  = normalisasi_nama($kegiatan);
            $idKeg    = $master['kegiatan'][$normKeg] ?? null;
            $baru     = false;
            if ($kegiatan === '') {
                $pesan[] = 'Kegiatan kosong';
            } elseif ($idKeg === null) {
                $baru = true;
                $kegiatanBaru[$normKeg] ??= $kegiatan;
            }

            $tujuan = $kol['tujuan'] !== null ? trim((string) ($baris[$kol['tujuan']] ?? '')) : '';
            $tujuan = $tujuan !== '' ? $tujuan : $desa;

            $status = 'ok';
            if ($pesan !== []) {
                $status = 'error';
            } else {
                $k = self::kunci($tanggal, $idDesa, $idKeg ?? 'baru:' . $normKeg, $idPegawai);
                if (isset($ada[$k]) || isset($sudahDiFile[$k])) {
                    $status  = 'duplikat';
                    $pesan[] = isset($ada[$k]) ? 'Sudah ada di database' : 'Kembar dengan baris lain di file';
                }
                $sudahDiFile[$k] = true;
            }

            $ringkasan['total']++;
            $ringkasan[$status]++;

            $rows[] = [
                'baris'         => $i + 1, // nomor baris di Excel
                'tanggal'       => $tanggal,
                'petugas'       => $petugas,
                'id_pegawai'    => $idPegawai,
                'desa'          => $desa,
                'id_desa'       => $idDesa,
                'kegiatan'      => $kegiatan,
                'id_kegiatan'   => $idKeg,
                'kegiatan_baru' => $baru,
                'tujuan'        => $tujuan,
                'status'        => $status,
                'pesan'         => $pesan,
            ];
        }

        return ['rows' => $rows, 'kegiatan_baru' => $kegiatanBaru, 'ringkasan' => $ringkasan];
    }

    public static function kunci(?string $tanggal, int|string|null $idDesa, int|string|null $idKegiatan, array $idPegawai): string
    {
        $ids = array_map('intval', $idPegawai);
        sort($ids);

        return substr((string) $tanggal, 0, 10) . '|' . $idDesa . '|' . $idKegiatan . '|' . implode(',', $ids);
    }

    private static function petakanHeader(array $header): array
    {
        $kol = ['tanggal' => null, 'desa' => null, 'kegiatan' => null, 'tujuan' => null, 'petugas' => []];

        foreach ($header as $c => $h) {
            $h = mb_strtolower(trim((string) $h));
            if ($h === 'tanggal') {
                $kol['tanggal'] = $c;
            } elseif ($h === 'desa') {
                $kol['desa'] = $c;
            } elseif ($h === 'kegiatan') {
                $kol['kegiatan'] = $c;
            } elseif (str_starts_with($h, 'tujuan')) {
                $kol['tujuan'] ??= $c;
            } elseif (preg_match('/^petugas\s*\d*$/', $h)) {
                $kol['petugas'][] = $c;
            }
        }

        foreach (['tanggal', 'desa', 'kegiatan'] as $wajib) {
            if ($kol[$wajib] === null) {
                throw new RuntimeException("Kolom '{$wajib}' tidak ditemukan di baris header.");
            }
        }
        if ($kol['petugas'] === []) {
            throw new RuntimeException("Kolom 'PETUGAS 1' dst tidak ditemukan di baris header.");
        }

        return $kol;
    }

    private static function kosong(array $baris): bool
    {
        foreach ($baris as $v) {
            if ($v !== null && trim((string) $v) !== '') {
                return false;
            }
        }

        return true;
    }

    /** Terima serial Excel (46296), "2026-10-01", "01/10/2026", atau "01-10-2026". Hasil: Y-m-d H:i:s */
    public static function parseTanggal(mixed $v): ?string
    {
        if ($v === null || trim((string) $v) === '') {
            return null;
        }

        if (is_numeric($v)) {
            $serial = (float) $v;
            if ($serial < 1) {
                return null;
            }

            return (new DateTimeImmutable('1899-12-30'))->modify('+' . (int) floor($serial) . ' days')->format('Y-m-d 00:00:00');
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y-m-d H:i:s'] as $fmt) {
            $d = DateTimeImmutable::createFromFormat('!' . $fmt, trim((string) $v));
            if ($d !== false && $d->format($fmt) === trim((string) $v)) {
                return $d->format('Y-m-d 00:00:00');
            }
        }

        return null;
    }
}
