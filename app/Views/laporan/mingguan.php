<?= $this->extend('layouts/print') ?>
<?= $this->section('content') ?>
<?php
$puskesmas = strtoupper(preg_replace('/^UPTD\s+/i', '', setting('Instansi.namaPuskesmas')));
$kab       = setting('Instansi.kabupaten');
$jmlTier   = count($lap['tiers']);
$kolSemua  = 4 + (3 * $jmlTier);
$pembuat   = $lap['pembuat'];
?>
<div class="kertas">
    <div class="judul">
        DAFTAR PENERIMAAN UANG PERJALANAN DINAS<br>
        <?= esc(strtoupper(setting('Instansi.programAnggaran'))) ?> DALAM RANGKA KEGIATAN<br>
        <?= esc(strtoupper($lap['kegiatan'])) ?><br>
        PADA <?= esc($puskesmas) ?> KABUPATEN <?= esc(strtoupper($kab)) ?><br>
        PADA TANGGAL <?= esc(strtoupper($lap['tanggal_text'])) ?>
    </div>

    <div class="meta"><div>Kode Rekening : <?= esc($kodeRekening) ?></div><div>No. TB : <?= esc($noTb) ?></div></div>

    <table class="data">
        <thead>
            <tr>
                <th rowspan="3" style="width:28px">NO</th>
                <th rowspan="3">NAMA / NIP</th>
                <th rowspan="3">JABATAN</th>
                <th rowspan="3">PANGKAT/<br>GOLONGAN</th>
                <?php foreach ($lap['tiers'] as $t) : ?><th colspan="3">UANG PERJALANAN DINAS<br><?= esc($t['keterangan']) ?></th><?php endforeach; ?>
                <th rowspan="3">JUMLAH TOTAL<br>Rp.</th>
                <th rowspan="3" colspan="2" style="width:120px">TANDA TANGAN</th>
            </tr>
            <tr><?php foreach ($lap['tiers'] as $t) : ?><th>VOLUME</th><th>UNIT COST</th><th>JUMLAH<br>( Rp )</th><?php endforeach; ?></tr>
            <tr><?php foreach ($lap['tiers'] as $t) : ?><th colspan="3" style="font-weight:400"><?= esc($t['keterangan']) ?></th><?php endforeach; ?></tr>
        </thead>
        <tbody>
        <?php foreach ($lap['rows'] as $p) : ?>
            <tr>
                <td class="c"><?= (int) $p['no'] ?></td>
                <td><?= esc($p['nama']) ?><span class="nip">NIP. <?= esc($p['nip']) ?></span></td>
                <td><?= esc($p['jabatan']) ?></td>
                <td><?= esc($p['pangkat']) ?></td>
                <?php foreach ($lap['tiers'] as $t) : $x = $p['tier'][$t['id']]; ?>
                    <td class="c"><?= $x['volume'] ?: '' ?></td>
                    <td class="r"><?= format_rupiah($x['harga']) ?></td>
                    <td class="r"><?= $x['volume'] ? format_rupiah($x['jumlah']) : '' ?></td>
                <?php endforeach; ?>
                <td class="r"><?= format_rupiah($p['total']) ?></td>
                <td style="width:60px"><?= $p['no'] % 2 === 1 ? $p['no'] . '.' : '' ?></td>
                <td style="width:60px"><?= $p['no'] % 2 === 0 ? $p['no'] . '.' : '' ?></td>
            </tr>
        <?php endforeach; ?>
            <tr>
                <td colspan="<?= $kolSemua ?>"><strong>Jumlah ………………………………………….</strong></td>
                <td class="r"><strong><?= format_rupiah($lap['grand_total']) ?></strong></td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td colspan="2"><strong>Terbilang :</strong></td>
                <td colspan="<?= $kolSemua + 1 ?>"><em><?= esc($lap['terbilang']) ?></em></td>
            </tr>
        </tbody>
    </table>

    <div class="ttd">
        <div>
            Mengetahui/ Setuju Dibayar<br><?= esc(setting('Instansi.pimpinanJabatan')) ?><br><?= esc(setting('Instansi.namaPuskesmas')) ?>
            <div class="ruang"></div>
            <div class="nama"><?= esc(setting('Instansi.pimpinanNama')) ?></div>
            NIP. <?= esc(setting('Instansi.pimpinanNip')) ?>
        </div>
        <div>
            Lunas Dibayar<br><?= esc(setting('Instansi.bendaharaJabatan')) ?><br><?= esc(setting('Instansi.namaPuskesmas')) ?>
            <div class="ruang"></div>
            <div class="nama"><?= esc(setting('Instansi.bendaharaNama')) ?></div>
            NIP. <?= esc(setting('Instansi.bendaharaNip')) ?>
        </div>
        <div>
            <?= esc($kab) ?>, <?= esc(daftar_tanggal_indo([$lap['tanggal_terakhir']])) ?><br>Pembuat Daftar<br>&nbsp;
            <div class="ruang"></div>
            <div class="nama"><?= esc($pembuat['nama']) ?></div>
            NIP. <?= esc($pembuat['nip']) ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
