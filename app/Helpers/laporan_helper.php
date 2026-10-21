<?php

/**
 * Helper laporan: terbilang, format tanggal Indonesia, normalisasi nama,
 * dan pencocokan tarif berdasarkan jarak.
 * Daftarkan di Config\Autoload::$helpers => ['auth', 'setting', 'laporan']
 */

if (! function_exists('_terbilang_rec')) {
    function _terbilang_rec(int $n): string
    {
        $s = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        return match (true) {
            $n < 12                => $s[$n],
            $n < 20                => $s[$n - 10] . ' belas',
            $n < 100               => $s[intdiv($n, 10)] . ' puluh ' . $s[$n % 10],
            $n < 200               => 'seratus ' . _terbilang_rec($n - 100),
            $n < 1000              => $s[intdiv($n, 100)] . ' ratus ' . _terbilang_rec($n % 100),
            $n < 2000              => 'seribu ' . _terbilang_rec($n - 1000),
            $n < 1_000_000         => _terbilang_rec(intdiv($n, 1000)) . ' ribu ' . _terbilang_rec($n % 1000),
            $n < 1_000_000_000     => _terbilang_rec(intdiv($n, 1_000_000)) . ' juta ' . _terbilang_rec($n % 1_000_000),
            $n < 1_000_000_000_000 => _terbilang_rec(intdiv($n, 1_000_000_000)) . ' miliar ' . _terbilang_rec($n % 1_000_000_000),
            default                => _terbilang_rec(intdiv($n, 1_000_000_000_000)) . ' triliun ' . _terbilang_rec($n % 1_000_000_000_000),
        };
    }
}

if (! function_exists('terbilang')) {
    /** 230000 => "dua ratus tiga puluh ribu" */
    function terbilang(int $angka): string
    {
        $angka = abs($angka);

        return $angka === 0 ? 'nol' : trim(preg_replace('/\s+/', ' ', _terbilang_rec($angka)));
    }
}

if (! function_exists('terbilang_rupiah')) {
    /** 230000 => "Dua ratus tiga puluh ribu rupiah" */
    function terbilang_rupiah(int $angka): string
    {
        return ucfirst(terbilang($angka)) . ' rupiah';
    }
}

if (! function_exists('format_rupiah')) {
    function format_rupiah(int|float $angka): string
    {
        return number_format($angka, 0, ',', '.');
    }
}

if (! function_exists('bulan_indo')) {
    function bulan_indo(int $bulan): string
    {
        return [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'][$bulan] ?? '';
    }
}

if (! function_exists('daftar_tanggal_indo')) {
    /**
     * ['2026-07-27', '2026-07-28 00:00:00'] => "27, 28 Juli 2026"
     * Lintas bulan => "30, 31 Juli 2026 dan 1 Agustus 2026"
     */
    function daftar_tanggal_indo(array $tanggal): string
    {
        $unik = array_unique(array_map(static fn ($d) => substr((string) $d, 0, 10), $tanggal));
        sort($unik);

        $grup = [];

        foreach ($unik as $d) {
            [$y, $m, $h] = array_map('intval', explode('-', $d));
            $grup["$y-$m"]['hari'][] = $h;
            $grup["$y-$m"]['label']  = bulan_indo($m) . ' ' . $y;
        }

        $bagian = array_map(static fn ($g) => implode(', ', $g['hari']) . ' ' . $g['label'], array_values($grup));

        if (count($bagian) > 1) {
            $akhir = array_pop($bagian);

            return implode(', ', $bagian) . ' dan ' . $akhir;
        }

        return $bagian[0] ?? '';
    }
}

if (! function_exists('normalisasi_nama')) {
    /**
     * Supaya "A.Md.Keb." dan "AMd.Keb" dianggap sama, "Bdn" = "Bdn.",
     * dan beda spasi/huruf besar tidak jadi masalah.
     */
    function normalisasi_nama(?string $nama): string
    {
        $s = mb_strtolower(trim((string) $nama));
        $s = str_replace('.', '', $s);
        $s = str_replace(',', ' ', $s);

        return trim(preg_replace('/\s+/u', ' ', $s));
    }
}

if (! function_exists('cari_tarif')) {
    /**
     * Cari tier tarif untuk jarak tertentu.
     * Aturan: jarak > jarak_min DAN jarak <= jarak_max (5 km masuk 0-5, bukan 5-10).
     * Khusus tier yang mulai dari 0, jarak 0 ikut masuk.
     *
     * @param list<array{id:int|string,jarak_min:int|string,jarak_max:int|string,harga:int|string}> $tiers
     */
    function cari_tarif(int|float $jarak, array $tiers): ?array
    {
        foreach ($tiers as $t) {
            $min = (float) $t['jarak_min'];
            $max = (float) $t['jarak_max'];

            if (($jarak > $min && $jarak <= $max) || ($min === 0.0 && $jarak == 0)) {
                return $t;
            }
        }

        return null;
    }
}
