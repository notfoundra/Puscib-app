<?php

namespace App\Libraries;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Bikin 1 file Excel untuk 1 bulan:
 *   per kegiatan  => sheet "K1 Rekap" (rekap bulanan) + 1 sheet per minggu ("K1 1-4 Okt", ...)
 *   (+ sheet "Perhatian" di depan, hanya kalau ada desa yang jaraknya tidak masuk tarif mana pun)
 *
 * Isinya rumus, bukan angka mati, seperti di contoh klien:
 *   Jumlah = Volume x Unit Cost, Total = SUM, dan Rekap merujuk ke total sheet mingguan.
 * Jadi kalau ada volume yang dikoreksi manual di Excel, rekap ikut berubah.
 * Kecuali terbilang: itu teks hasil hitung saat file dibuat (Excel tidak punya fungsi terbilang bawaan).
 */
class LaporanExcel
{
    private const BARIS_DATA = 12;               // baris pertama pegawai di sheet mingguan
    private const FMT_RP     = '#,##0;-#,##0;';  // nol disembunyikan
    private const FMT_VOL    = '0;-0;';
    private const BULAN_PDK  = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    /** @var array<string,true> */
    private array $namaDipakai = [];

    public function __construct()
    {
        helper('laporan');
    }

    public function buat(array $data): Spreadsheet
    {
        $wb = new Spreadsheet();
        $wb->getProperties()->setTitle('Laporan perjalanan dinas ' . $data['periode'])->setCreator(setting('Instansi.namaPuskesmas'));
        $wb->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
        $wb->removeSheetByIndex(0);

        if ($data['tidak_cocok'] !== []) {
            $this->sheetPerhatian($wb->createSheet(), $this->namaSheet('Perhatian'), $data['tidak_cocok']);
        }

        foreach ($data['kegiatan'] as $i => $kg) {
            $kode = 'K' . ($i + 1);

            // Nama sheet mingguan ditentukan dulu supaya rekap bisa merujuk ke totalnya
            $namaMinggu = [];
            foreach ($kg['minggu'] as $mg) {
                $namaMinggu[] = $this->namaSheet($kode . ' ' . $this->labelTanggal($mg['tanggal_pertama'], $mg['tanggal_terakhir']));
            }

            $this->sheetRekap($wb->createSheet(), $this->namaSheet($kode . ' Rekap'), $kg, $namaMinggu);

            foreach ($kg['minggu'] as $j => $mg) {
                $this->sheetMingguan($wb->createSheet(), $namaMinggu[$j], $mg);
            }
        }

        $wb->setActiveSheetIndex(0);

        return $wb;
    }

    public function biner(Spreadsheet $wb): string
    {
        ob_start();
        (new Xlsx($wb))->save('php://output');

        return (string) ob_get_clean();
    }

    // ------------------------------------------------------------------
    // Sheet mingguan: "Daftar penerimaan uang perjalanan dinas"
    // ------------------------------------------------------------------

    private function sheetMingguan(Worksheet $ws, string $nama, array $lap): void
    {
        $ws->setTitle($nama);

        $tiers    = $lap['tiers'];
        $n        = count($tiers);
        $kolTotal = 5 + 3 * $n;
        $kolTtd1  = $kolTotal + 1;
        $akhir    = $kolTotal + 2;
        $ujung    = $akhir;

        $puskesmas = strtoupper(preg_replace('/^UPTD\s+/i', '', setting('Instansi.namaPuskesmas')));

        $this->judul($ws, $ujung, [
            'DAFTAR PENERIMAAN UANG PERJALANAN DINAS',
            strtoupper(setting('Instansi.programAnggaran')) . ' DALAM RANGKA KEGIATAN',
            strtoupper($lap['kegiatan']),
            'PADA ' . $puskesmas . ' KABUPATEN ' . strtoupper(setting('Instansi.kabupaten')),
            'PADA TANGGAL ' . strtoupper($lap['tanggal_text']),
        ]);

        // Diisi manual di Excel
        $ws->setCellValue('A7', 'Kode Rekening : ');
        $ws->setCellValue($this->a(max(5, $akhir - 3), 7), 'No. TB : ');

        // --- header tabel (baris 9-11) ---
        foreach ([1 => 'NO', 2 => 'NAMA / NIP', 3 => 'JABATAN', 4 => 'PANGKAT/GOLONGAN', $kolTotal => "JUMLAH TOTAL\nRp."] as $c => $t) {
            $ws->mergeCells($this->a($c, 9) . ':' . $this->a($c, 11));
            $ws->setCellValue($this->a($c, 9), $t);
        }
        $ws->mergeCells($this->a($kolTtd1, 9) . ':' . $this->a($akhir, 11));
        $ws->setCellValue($this->a($kolTtd1, 9), 'TANDA TANGAN');

        foreach ($tiers as $i => $t) {
            $c0 = 5 + 3 * $i;
            $ws->mergeCells($this->a($c0, 9) . ':' . $this->a($c0 + 2, 9));
            $ws->setCellValue($this->a($c0, 9), 'UANG PERJALANAN DINAS');
            $ws->mergeCells($this->a($c0, 10) . ':' . $this->a($c0 + 2, 10));
            $ws->setCellValue($this->a($c0, 10), $t['keterangan']);
            $ws->setCellValue($this->a($c0, 11), 'VOLUME');
            $ws->setCellValue($this->a($c0 + 1, 11), 'UNIT COST');
            $ws->setCellValue($this->a($c0 + 2, 11), "JUMLAH\n( Rp )");
        }

        $hdr = 'A9:' . $this->a($akhir, 11);
        $ws->getStyle($hdr)->getFont()->setBold(true);
        $ws->getStyle($hdr)->getAlignment()->setHorizontal('center')->setVertical('center')->setWrapText(true);
        $ws->getStyle($hdr)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E9ECEF');
        $ws->getRowDimension(11)->setRowHeight(28);

        // --- data pegawai ---
        $r = self::BARIS_DATA;
        foreach ($lap['rows'] as $p) {
            $ws->setCellValue("A$r", $p['no']);
            $ws->setCellValue("B$r", $p['nama'] . "\nNIP. " . $p['nip']);
            $ws->setCellValue("C$r", $p['jabatan']);
            $ws->setCellValue("D$r", $p['pangkat']);

            $jml = [];
            foreach ($tiers as $i => $t) {
                $c0 = 5 + 3 * $i;
                $x  = $p['tier'][$t['id']];
                $ws->setCellValue($this->a($c0, $r), $x['volume']);
                $ws->setCellValue($this->a($c0 + 1, $r), $x['harga']);
                $ws->setCellValue($this->a($c0 + 2, $r), '=' . $this->a($c0, $r) . '*' . $this->a($c0 + 1, $r));
                $ws->getStyle($this->a($c0, $r))->getNumberFormat()->setFormatCode(self::FMT_VOL);
                $ws->getStyle($this->a($c0, $r))->getAlignment()->setHorizontal('center');
                $ws->getStyle($this->a($c0 + 1, $r) . ':' . $this->a($c0 + 2, $r))->getNumberFormat()->setFormatCode(self::FMT_RP);
                $jml[] = $this->a($c0 + 2, $r);
            }
            $ws->setCellValue($this->a($kolTotal, $r), '=SUM(' . implode(',', $jml) . ')');
            $ws->getStyle($this->a($kolTotal, $r))->getNumberFormat()->setFormatCode(self::FMT_RP);

            // tanda tangan zig-zag: 1. di kiri, 2. di kanan
            // harus eksplisit teks, kalau tidak "1." dibaca Excel sebagai angka 1
            $ws->setCellValueExplicit($this->a($p['no'] % 2 === 1 ? $kolTtd1 : $akhir, $r), $p['no'] . '.', DataType::TYPE_STRING);

            $ws->getRowDimension($r)->setRowHeight(30);
            $r++;
        }
        $terakhir = $r - 1;

        $ws->getStyle('A' . self::BARIS_DATA . ':A' . $terakhir)->getAlignment()->setHorizontal('center');
        $ws->getStyle($this->a($kolTtd1, self::BARIS_DATA) . ':' . $this->a($akhir, $terakhir))->getAlignment()->setHorizontal('left')->setIndent(1);
        $ws->getStyle('A' . self::BARIS_DATA . ':' . $this->a($akhir, $terakhir))->getAlignment()->setVertical('center')->setWrapText(true);

        // --- jumlah & terbilang ---
        $rj = $r;
        $ws->mergeCells("A$rj:" . $this->a($kolTotal - 1, $rj));
        $ws->setCellValue("A$rj", 'Jumlah   ………………………………………….');
        $ws->setCellValue($this->a($kolTotal, $rj), '=SUM(' . $this->a($kolTotal, self::BARIS_DATA) . ':' . $this->a($kolTotal, $terakhir) . ')');
        $ws->getStyle($this->a($kolTotal, $rj))->getNumberFormat()->setFormatCode(self::FMT_RP);
        $ws->getStyle("A$rj:" . $this->a($akhir, $rj))->getFont()->setBold(true);

        $rt = $rj + 1;
        $ws->mergeCells("A$rt:B$rt");
        $ws->setCellValue("A$rt", 'Terbilang :');
        $ws->mergeCells("C$rt:" . $this->a($akhir, $rt));
        $ws->setCellValue("C$rt", $lap['terbilang']);
        $ws->getStyle("A$rt")->getFont()->setBold(true);
        $ws->getStyle("C$rt")->getFont()->setItalic(true);

        $this->kotak($ws, 'A9:' . $this->a($akhir, $rt));

        // --- tanda tangan ---
        $rs = $rt + 2;
        $this->blokTtd($ws, 2, $rs, ['Mengetahui/ Setuju Dibayar', setting('Instansi.pimpinanJabatan'), setting('Instansi.namaPuskesmas')], setting('Instansi.pimpinanNama'), 'NIP. ' . setting('Instansi.pimpinanNip'));
        $this->blokTtd($ws, 5, $rs, ['Lunas Dibayar', setting('Instansi.bendaharaJabatan'), setting('Instansi.namaPuskesmas')], setting('Instansi.bendaharaNama'), 'NIP. ' . setting('Instansi.bendaharaNip'));
        $this->blokTtd(
            $ws,
            $kolTotal,
            $rs,
            [setting('Instansi.kabupaten') . ', ' . daftar_tanggal_indo([$lap['tanggal_terakhir']]), 'Pembuat Daftar', ''],
            $lap['pembuat']['nama'],
            'NIP. ' . $lap['pembuat']['nip']
        );

        // --- tampilan & cetak ---
        foreach ([1 => 5, 2 => 36, 3 => 26, 4 => 22] as $c => $w) {
            $ws->getColumnDimension($this->col($c))->setWidth($w);
        }
        foreach ($tiers as $i => $t) {
            $c0 = 5 + 3 * $i;
            $ws->getColumnDimension($this->col($c0))->setWidth(9);
            $ws->getColumnDimension($this->col($c0 + 1))->setWidth(12);
            $ws->getColumnDimension($this->col($c0 + 2))->setWidth(14);
        }
        $ws->getColumnDimension($this->col($kolTotal))->setWidth(15);
        $ws->getColumnDimension($this->col($kolTtd1))->setWidth(11);
        $ws->getColumnDimension($this->col($akhir))->setWidth(11);

        $this->cetak($ws, 'A1:' . $this->a($akhir, $rs + 8), PageSetup::ORIENTATION_LANDSCAPE);
    }

    // ------------------------------------------------------------------
    // Sheet rekap bulanan per kegiatan
    // ------------------------------------------------------------------

    private function sheetRekap(Worksheet $ws, string $nama, array $kg, array $namaMinggu): void
    {
        $ws->setTitle($nama);

        $this->judul($ws, 5, [
            'REKAP PENERIMAAN PERJALANAN DINAS',
            'DALAM RANGKA KEGIATAN',
            strtoupper($kg['kegiatan']),
            'BULAN ' . $kg['periode'],
        ]);

        $ws->setCellValue('A7', 'Kode Rekening : ');
        $ws->setCellValue('D7', 'No. TB : ');

        $ws->mergeCells('B9:C9');
        foreach (['A9' => 'NO', 'B9' => 'URAIAN', 'D9' => 'JUMLAH UANG', 'E9' => 'KET.'] as $c => $t) {
            $ws->setCellValue($c, $t);
        }
        $ws->mergeCells('B10:C10');
        foreach (['A10' => 1, 'B10' => 2, 'D10' => 3, 'E10' => 4] as $c => $t) {
            $ws->setCellValue($c, $t);
        }
        $ws->getStyle('A9:E10')->getFont()->setBold(true);
        $ws->getStyle('A9:E10')->getAlignment()->setHorizontal('center')->setVertical('center');
        $ws->getStyle('A9:E9')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E9ECEF');

        $r = 11;
        foreach ($kg['items'] as $i => $it) {
            $mg    = $kg['minggu'][$i];
            $total = $this->a(5 + 3 * count($mg['tiers']), self::BARIS_DATA + count($mg['rows']));

            $ws->setCellValue("A$r", $i + 1);
            $ws->mergeCells("B$r:C$r");
            $ws->setCellValue("B$r", $it['uraian']);
            $ws->setCellValue("D$r", "='" . str_replace("'", "''", $namaMinggu[$i]) . "'!" . $total);
            $ws->getStyle("D$r")->getNumberFormat()->setFormatCode('"Rp" #,##0');
            $ws->getStyle("A$r:E$r")->getAlignment()->setVertical('top');
            $ws->getStyle("A$r")->getAlignment()->setHorizontal('center');
            $ws->getStyle("B$r")->getAlignment()->setWrapText(true);
            $ws->getRowDimension($r)->setRowHeight(15 * max(2, (int) ceil(mb_strlen($it['uraian']) / 70)));
            $r++;
        }
        $terakhir = $r - 1;

        $ws->mergeCells("A$r:C$r");
        $ws->setCellValue("A$r", 'JUMLAH');
        $ws->setCellValue("D$r", "=SUM(D11:D$terakhir)");
        $ws->getStyle("D$r")->getNumberFormat()->setFormatCode('"Rp" #,##0');
        $ws->getStyle("A$r:E$r")->getFont()->setBold(true);
        $ws->getStyle("A$r")->getAlignment()->setHorizontal('center');

        $rt = $r + 1;
        $ws->mergeCells("A$rt:B$rt");
        $ws->setCellValue("A$rt", 'Terbilang :');
        $ws->mergeCells("C$rt:E$rt");
        $ws->setCellValue("C$rt", $kg['terbilang']);
        $ws->getStyle("A$rt")->getFont()->setBold(true);
        $ws->getStyle("C$rt")->getFont()->setItalic(true);

        $this->kotak($ws, "A9:E$rt");

        // Tanggal lunas diisi manual
        $rs = $rt + 2;
        $ws->setCellValue("D$rs", 'Lunas Dibayar');
        $ws->setCellValue('D' . ($rs + 1), 'Tanggal ....................');
        $this->blokTtd($ws, 2, $rs + 2, ['Mengetahui/ Setuju Dibayar', setting('Instansi.pimpinanJabatan'), setting('Instansi.namaPuskesmas')], setting('Instansi.pimpinanNama'), 'NIP. ' . setting('Instansi.pimpinanNip'), 6);
        $ws->mergeCells('D' . ($rs + 2) . ':E' . ($rs + 4));
        $ws->setCellValue('D' . ($rs + 2), setting('Instansi.bendaharaJabatan') . ' ' . setting('Instansi.namaPuskesmas'));
        $ws->getStyle('D' . ($rs + 2))->getAlignment()->setWrapText(true)->setVertical('top');
        $ws->setCellValue('D' . ($rs + 8), setting('Instansi.bendaharaNama'));
        $ws->setCellValue('D' . ($rs + 9), 'NIP. ' . setting('Instansi.bendaharaNip'));
        $ws->getStyle('D' . ($rs + 8))->getFont()->setBold(true);
        $ws->getStyle("D$rs:E" . ($rs + 1))->getAlignment()->setHorizontal('left');

        foreach (['A' => 6, 'B' => 40, 'C' => 36, 'D' => 20, 'E' => 14] as $c => $w) {
            $ws->getColumnDimension($c)->setWidth($w);
        }

        $this->cetak($ws, 'A1:E' . ($rs + 10), PageSetup::ORIENTATION_PORTRAIT);
    }

    private function sheetPerhatian(Worksheet $ws, string $nama, array $tidakCocok): void
    {
        $ws->setTitle($nama);
        $ws->setCellValue('A1', 'PERHATIAN');
        $ws->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $ws->setCellValue('A3', 'Desa berikut jaraknya tidak masuk tarif mana pun, jadi perjalanan ke desa ini TIDAK dihitung di laporan lain.');
        $ws->setCellValue('A4', 'Tambahkan tarif yang sesuai di menu Tarif perjalanan, lalu unduh ulang.');
        $ws->setCellValue('A6', 'Desa');
        $ws->setCellValue('B6', 'Jarak (km)');
        $ws->getStyle('A6:B6')->getFont()->setBold(true);
        $r = 7;
        foreach ($tidakCocok as $desa => $jarak) {
            $ws->setCellValue("A$r", $desa);
            $ws->setCellValue("B$r", $jarak);
            $r++;
        }
        $ws->getColumnDimension('A')->setWidth(34);
        $ws->getColumnDimension('B')->setWidth(14);
        $ws->getTabColor()->setRGB('DC3545');
    }

    // ------------------------------------------------------------------
    // Util
    // ------------------------------------------------------------------

    private function judul(Worksheet $ws, int $kolAkhir, array $baris): void
    {
        foreach ($baris as $i => $t) {
            $r = $i + 1;
            $ws->mergeCells("A$r:" . $this->a($kolAkhir, $r));
            $ws->setCellValue("A$r", $t);
            $ws->getStyle("A$r")->getFont()->setBold(true)->setSize($i === 0 ? 12 : 11);
            $ws->getStyle("A$r")->getAlignment()->setHorizontal('center');
        }
    }

    /** Blok tanda tangan: 3 baris teks, ruang kosong, nama (tebal), NIP. */
    private function blokTtd(Worksheet $ws, int $kol, int $baris, array $teks, string $nama, string $nip, int $jarakNama = 6): void
    {
        foreach ($teks as $i => $t) {
            if ($t !== '') {
                $ws->setCellValue($this->a($kol, $baris + $i), $t);
            }
        }
        $ws->setCellValue($this->a($kol, $baris + $jarakNama), $nama);
        $ws->setCellValue($this->a($kol, $baris + $jarakNama + 1), $nip);
        $ws->getStyle($this->a($kol, $baris + $jarakNama))->getFont()->setBold(true);
    }

    private function kotak(Worksheet $ws, string $range): void
    {
        $ws->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    private function cetak(Worksheet $ws, string $area, string $orientasi): void
    {
        $ps = $ws->getPageSetup();
        $ps->setOrientation($orientasi)->setPaperSize(PageSetup::PAPERSIZE_A4)->setFitToPage(true)->setFitToWidth(1)->setFitToHeight(0);
        $ps->setPrintArea($area)->setHorizontalCentered(true);
        $ws->getPageMargins()->setTop(0.5)->setBottom(0.5)->setLeft(0.4)->setRight(0.4);
    }

    private function labelTanggal(string $dari, string $sampai): string
    {
        [$y1, $m1, $d1] = array_map('intval', explode('-', $dari));
        [, , $d2]       = array_map('intval', explode('-', $sampai));

        return $dari === $sampai ? "$d1 " . self::BULAN_PDK[$m1] : "$d1-$d2 " . self::BULAN_PDK[$m1];
    }

    /** Nama sheet Excel: maks 31 karakter, tanpa \ / ? * [ ] :, dan unik. */
    private function namaSheet(string $nama): string
    {
        $dasar = mb_substr(preg_replace('#[\\\\/?*\[\]:]#', '', $nama), 0, 31);
        $coba  = $dasar;
        $i     = 2;

        while (isset($this->namaDipakai[mb_strtolower($coba)])) {
            $sufiks = " ($i)";
            $coba   = mb_substr($dasar, 0, 31 - mb_strlen($sufiks)) . $sufiks;
            $i++;
        }

        $this->namaDipakai[mb_strtolower($coba)] = true;

        return $coba;
    }

    private function col(int $i): string
    {
        return Coordinate::stringFromColumnIndex($i);
    }

    private function a(int $kolom, int $baris): string
    {
        return Coordinate::stringFromColumnIndex($kolom) . $baris;
    }
}
