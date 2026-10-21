<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Laporan') ?></title>
    <style>
        @page { size: A4 landscape; margin: 12mm; }
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #000; margin: 0; background: #eceff3; }
        .toolbar { position: sticky; top: 0; background: #fff; padding: 10px 16px; display: flex; gap: 8px; align-items: center; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        .toolbar button, .toolbar a { font: 600 12px Arial; padding: 7px 14px; border-radius: 6px; border: 1px solid #ccd; background: #fff; color: #222; text-decoration: none; cursor: pointer; }
        .toolbar .primary { background: #cb0c9f; color: #fff; border-color: #cb0c9f; }
        .peringatan { background: #fff3cd; border: 1px solid #ffe69c; padding: 8px 12px; margin: 12px auto; max-width: 1100px; border-radius: 6px; font-size: 12px; }
        .kertas { background: #fff; max-width: 1100px; margin: 14px auto; padding: 14mm; }
        .judul { text-align: center; font-weight: 700; line-height: 1.5; }
        .meta { display: flex; justify-content: space-between; margin: 12px 0 6px; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #000; padding: 4px 6px; vertical-align: middle; }
        table.data th { text-align: center; font-weight: 700; }
        .c { text-align: center; } .r { text-align: right; } .nowrap { white-space: nowrap; }
        .nip { display: block; font-size: 10px; }
        .ttd { display: flex; justify-content: space-between; margin-top: 18px; page-break-inside: avoid; }
        .ttd > div { text-align: center; min-width: 30%; }
        .ttd .ruang { height: 56px; }
        .ttd .nama { font-weight: 700; text-decoration: underline; }
        @media print {
            body { background: #fff; }
            .toolbar, .peringatan { display: none; }
            .kertas { margin: 0; padding: 0; max-width: none; }
        }
    </style>
</head>
<body>
<div class="toolbar">
    <a href="<?= site_url('laporan') ?>">&larr; Kembali</a>
    <button class="primary" onclick="window.print()">Cetak</button>
    <span style="color:#667">Atur kertas ke A4 landscape, margin default.</span>
</div>

<?php if (! empty($lap['tidak_cocok'])) : ?>
    <div class="peringatan">
        <strong>Perhatian:</strong> jarak desa berikut tidak masuk tarif mana pun, jadi tidak dihitung:
        <?php $t = []; foreach ($lap['tidak_cocok'] as $desa => $jarak) { $t[] = esc($desa) . ' (' . (int) $jarak . ' km)'; } echo implode(', ', $t); ?>.
        Tambahkan tarif yang sesuai di menu Tarif perjalanan.
    </div>
<?php endif; ?>

<?= $this->renderSection('content') ?>
</body>
</html>
