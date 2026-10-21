<?= $this->extend('layouts/print') ?>
<?= $this->section('content') ?>
<div class="kertas" style="max-width:900px">
    <div class="judul">
        REKAP PENERIMAAN PERJALANAN DINAS<br>
        DALAM RANGKA KEGIATAN<br>
        <?= esc(strtoupper($lap['kegiatan'])) ?><br>
        BULAN <?= esc($lap['periode']) ?>
    </div>

    <div class="meta"><div>Kode Rekening : <?= esc($kodeRekening) ?></div><div>No. TB : <?= esc($noTb) ?></div></div>

    <table class="data">
        <thead>
            <tr><th style="width:36px">NO</th><th>URAIAN</th><th style="width:130px">JUMLAH UANG</th><th style="width:90px">KET.</th></tr>
            <tr><th style="font-weight:400">1</th><th style="font-weight:400">2</th><th style="font-weight:400">3</th><th style="font-weight:400">4</th></tr>
        </thead>
        <tbody>
        <?php foreach ($lap['items'] as $i => $it) : ?>
            <tr>
                <td class="c"><?= $i + 1 ?></td>
                <td><?= esc($it['uraian']) ?></td>
                <td class="r">Rp <?= format_rupiah($it['jumlah']) ?></td>
                <td></td>
            </tr>
        <?php endforeach; ?>
            <tr><td colspan="2" class="c"><strong>JUMLAH</strong></td><td class="r"><strong>Rp <?= format_rupiah($lap['total']) ?></strong></td><td></td></tr>
            <tr><td colspan="2"><strong>Terbilang :</strong></td><td colspan="2"><em><?= esc($lap['terbilang']) ?></em></td></tr>
        </tbody>
    </table>

    <div class="ttd">
        <div>
            <br>Mengetahui/ Setuju Dibayar<br><?= esc(setting('Instansi.pimpinanJabatan')) ?><br><?= esc(setting('Instansi.namaPuskesmas')) ?>
            <div class="ruang"></div>
            <div class="nama"><?= esc(setting('Instansi.pimpinanNama')) ?></div>
            NIP. <?= esc(setting('Instansi.pimpinanNip')) ?>
        </div>
        <div>
            Lunas Dibayar<br><?= $tglLunas !== '' ? 'Tanggal ' . esc($tglLunas) : '&nbsp;' ?><br><?= esc(setting('Instansi.bendaharaJabatan')) ?> <?= esc(setting('Instansi.namaPuskesmas')) ?>
            <div class="ruang"></div>
            <div class="nama"><?= esc(setting('Instansi.bendaharaNama')) ?></div>
            NIP. <?= esc(setting('Instansi.bendaharaNip')) ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
