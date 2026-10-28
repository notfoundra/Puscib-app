<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;
use DateTimeImmutable;

/**
 * Laporan perjalanan dinas, dihitung langsung dari task + task_member.
 *
 * - mingguan(): "Daftar Penerimaan Uang Perjalanan Dinas" untuk 1 kegiatan dalam rentang tanggal
 * - bulanan():  "Rekap Penerimaan Perjalanan Dinas", 1 baris per minggu kalender (Senin-Minggu)
 *
 * Pembuat daftar = petugas paling atas (task_member.id terkecil) di task paling awal pada rentang itu.
 */
class ReportService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        helper('laporan');
        $this->db = $db ?? db_connect();
    }

    public function tiers(): array
    {
        return $this->db->table('master_volume')->orderBy('jarak_min', 'ASC')->orderBy('jarak_max', 'ASC')->get()->getResultArray();
    }

    /**
     * @param string   $dari    Y-m-d
     * @param string   $sampai  Y-m-d
     * @param int|null $idDesa  opsional; biasanya dikosongkan karena 1 daftar memuat banyak desa
     */
    public function mingguan(int $idKegiatan, string $dari, string $sampai, ?int $idDesa = null): array
    {
        $q = $this->db->table('task_member tm')
            ->select('t.id AS id_task, t.tanggal, d.nama_desa, d.jarak, p.id AS id_pegawai, p.nama, p.jabatan, p.pangkat, p.nip, k.nama_kegiatan')
            ->join('task t', 't.id = tm.id_task')
            ->join('master_desa d', 'd.id = t.id_desa')
            ->join('master_pegawai p', 'p.id = tm.id_pegawai')
            ->join('master_kegiatan k', 'k.id = t.id_kegiatan')
            ->where('t.id_kegiatan', $idKegiatan)
            ->where('t.tanggal >=', $dari . ' 00:00:00')
            ->where('t.tanggal <=', $sampai . ' 23:59:59')
            ->orderBy('t.tanggal', 'ASC')->orderBy('t.id', 'ASC')->orderBy('tm.id', 'ASC');

        if ($idDesa !== null) {
            $q->where('t.id_desa', $idDesa);
        }

        $rows = $q->get()->getResultArray();

        $hasil = self::susun($rows, $this->tiers());

        $nama = $rows[0]['nama_kegiatan'] ?? ($this->db->table('master_kegiatan')->where('id', $idKegiatan)->get()->getRowArray()['nama_kegiatan'] ?? '');

        return $hasil + ['kegiatan' => $nama, 'dari' => $dari, 'sampai' => $sampai];
    }

    public function bulanan(int $idKegiatan, int $tahun, int $bulan): array
    {
        $awal  = new DateTimeImmutable(sprintf('%04d-%02d-01', $tahun, $bulan));
        $akhir = $awal->modify('last day of this month');
        $kec   = setting('Instansi.kecamatan');

        $items = [];
        $minggu = [];
        $total = 0;
        $kegiatan = '';
        $tidakCocok = [];
        $cur = $awal;

        while ($cur <= $akhir) {
            $akhirMinggu = $cur->modify('+' . (7 - (int) $cur->format('N')) . ' days');
            if ($akhirMinggu > $akhir) {
                $akhirMinggu = $akhir;
            }

            $d = $this->mingguan($idKegiatan, $cur->format('Y-m-d'), $akhirMinggu->format('Y-m-d'));
            $kegiatan = $d['kegiatan'];

            if ($d['rows'] !== []) {
                $items[] = [
                    'uraian'  => self::uraian($d['pembuat']['nama'], $d['jumlah_orang'], $d['kegiatan'], $kec, $d['tanggal_text']),
                    'jumlah'  => $d['grand_total'],
                    'dari'    => $d['dari'],
                    'sampai'  => $d['sampai'],
                ];
                $minggu[]   = $d;
                $total     += $d['grand_total'];
                $tidakCocok = $tidakCocok + $d['tidak_cocok'];
            }

            $cur = $akhirMinggu->modify('+1 day');
        }

        return [
            'kegiatan'    => $kegiatan,
            'periode'     => strtoupper(bulan_indo($bulan)) . ' ' . $tahun,
            'items'       => $items,
            'minggu'      => $minggu,   // laporan mingguan lengkap, urut tanggal
            'total'       => $total,
            'terbilang'   => terbilang_rupiah($total),
            'tidak_cocok' => $tidakCocok,
        ];
    }

    /**
     * Semua kegiatan yang punya surat jalan di bulan itu: rekap bulanan + semua laporan mingguannya.
     * Urut nama kegiatan.
     */
    public function bulananSemua(int $tahun, int $bulan): array
    {
        $awal  = new DateTimeImmutable(sprintf('%04d-%02d-01', $tahun, $bulan));
        $akhir = $awal->modify('last day of this month');

        $ids = $this->db->table('task t')
            ->select('t.id_kegiatan')->distinct()
            ->join('master_kegiatan k', 'k.id = t.id_kegiatan')
            ->where('t.tanggal >=', $awal->format('Y-m-d') . ' 00:00:00')
            ->where('t.tanggal <=', $akhir->format('Y-m-d') . ' 23:59:59')
            ->orderBy('k.nama_kegiatan', 'ASC')
            ->get()->getResultArray();

        $kegiatan   = [];
        $total      = 0;
        $tidakCocok = [];

        foreach ($ids as $r) {
            $b = $this->bulanan((int) $r['id_kegiatan'], $tahun, $bulan);
            if ($b['items'] === []) {
                continue;
            }
            $kegiatan[] = $b;
            $total     += $b['total'];
            $tidakCocok = $tidakCocok + $b['tidak_cocok'];
        }

        return [
            'tahun'       => $tahun,
            'bulan'       => $bulan,
            'periode'     => strtoupper(bulan_indo($bulan)) . ' ' . $tahun,
            'kegiatan'    => $kegiatan,
            'total'       => $total,
            'tidak_cocok' => $tidakCocok,
        ];
    }

    public static function uraian(string $pembuat, int $jumlahOrang, string $kegiatan, string $kecamatan, string $tanggal): string
    {
        $an = $jumlahOrang > 1 ? "a.n. {$pembuat} dkk ({$jumlahOrang} orang)" : "a.n. {$pembuat}";

        return "{$an}. Daftar penerimaan kegiatan {$kegiatan} di wilayah Kecamatan {$kecamatan} pada tanggal {$tanggal}.";
    }

    /**
     * Murni logika (tanpa DB) supaya gampang dites.
     * Tiap baris input = 1 pegawai berangkat ke 1 desa pada 1 task => 1 volume di tier sesuai jarak desa.
     *
     * @param list<array<string,mixed>> $rows urut tanggal, task, task_member
     */
    public static function susun(array $rows, array $tiers): array
    {
        helper('laporan');

        $pegawai = [];
        $tanggal = [];
        $tidakCocok = [];

        foreach ($rows as $r) {
            $tanggal[] = $r['tanggal'];
            $pid       = $r['id_pegawai'];

            if (! isset($pegawai[$pid])) {
                $pegawai[$pid] = [
                    'id_pegawai' => $pid,
                    'nama'       => $r['nama'],
                    'nip'        => $r['nip'],
                    'jabatan'    => $r['jabatan'],
                    'pangkat'    => $r['pangkat'],
                    'tier'       => [],
                    'total'      => 0,
                ];

                foreach ($tiers as $t) {
                    $pegawai[$pid]['tier'][$t['id']] = ['volume' => 0, 'harga' => (int) $t['harga'], 'jumlah' => 0];
                }
            }

            $tier = cari_tarif((float) $r['jarak'], $tiers);

            if ($tier === null) {
                $tidakCocok[$r['nama_desa']] = (int) $r['jarak'];
                continue;
            }

            $pegawai[$pid]['tier'][$tier['id']]['volume']++;
        }

        $grand = 0;
        $no    = 1;

        foreach ($pegawai as &$p) {
            foreach ($p['tier'] as &$t) {
                $t['jumlah'] = $t['volume'] * $t['harga'];
                $p['total'] += $t['jumlah'];
            }
            unset($t);

            $p['no'] = $no++;
            $grand  += $p['total'];
        }
        unset($p);

        $list = array_values($pegawai);

        return [
            'tiers'        => $tiers,
            'rows'         => $list,
            'pembuat'      => $list[0] ?? null,
            'jumlah_orang' => count($list),
            'tanggal_text' => daftar_tanggal_indo($tanggal),
            'tanggal_pertama' => $tanggal === [] ? null : substr((string) min($tanggal), 0, 10),
            'tanggal_terakhir' => $tanggal === [] ? null : substr((string) max($tanggal), 0, 10),
            'grand_total'  => $grand,
            'terbilang'    => terbilang_rupiah($grand),
            'tidak_cocok'  => $tidakCocok,   // desa yang jaraknya tidak masuk tier mana pun
        ];
    }
}
