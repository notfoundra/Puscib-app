<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$edit = $row !== null;
$old  = old('petugas');
$sel  = is_array($old) ? $old : $idPetugas;
?>
<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><?= esc($title) ?></h6></div>
            <div class="card-body">
                <form method="post" action="<?= site_url($edit ? "task/{$row['id']}" : 'task') ?>" novalidate>
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label" for="tanggal">Tanggal</label>
                            <input class="form-control" type="date" id="tanggal" name="tanggal" value="<?= esc(old('tanggal', $edit ? substr($row['tanggal'], 0, 10) : date('Y-m-d'), false)) ?>">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label" for="id_kegiatan">Kegiatan</label>
                            <select class="form-select select2" id="id_kegiatan" name="id_kegiatan" data-placeholder="Pilih kegiatan">
                                <option></option>
                                <?php foreach ($kegiatan as $k) : ?><option value="<?= $k['id'] ?>" <?= (string) old('id_kegiatan', $row['id_kegiatan'] ?? '') === (string) $k['id'] ? 'selected' : '' ?>><?= esc($k['nama_kegiatan']) ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="id_desa">Desa tujuan</label>
                            <select class="form-select select2" id="id_desa" name="id_desa" data-placeholder="Pilih desa">
                                <option></option>
                                <?php foreach ($desa as $d) : ?><option value="<?= $d['id'] ?>" data-jarak="<?= (int) $d['jarak'] ?>" data-nama="<?= esc($d['nama_desa'], 'attr') ?>" <?= (string) old('id_desa', $row['id_desa'] ?? '') === (string) $d['id'] ? 'selected' : '' ?>><?= esc($d['nama_desa']) ?> (<?= (int) $d['jarak'] ?> km)</option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="tujuan">Tujuan</label>
                            <input class="form-control" id="tujuan" name="tujuan" maxlength="255" value="<?= esc(old('tujuan', $row['tujuan'] ?? '', false)) ?>" placeholder="Kosongkan untuk pakai nama desa">
                        </div>
                        <div class="col-12"><div class="tarif-info" id="tarifInfo">Pilih desa untuk melihat tarif perjalanan.</div></div>

                        <div class="col-12">
                            <div class="fw-bold mb-1">Petugas</div>
                            <div class="form-text mb-2">Petugas 1 otomatis jadi pembuat daftar di laporan. Maksimal <?= (int) $maks ?> orang.</div>
                            <div class="row g-2">
                                <?php for ($i = 0; $i < $maks; $i++) : ?>
                                    <div class="col-md-6">
                                        <label class="form-label" for="petugas_<?= $i ?>">Petugas <?= $i + 1 ?><?= $i === 0 ? '' : ' (opsional)' ?></label>
                                        <select class="form-select select2" id="petugas_<?= $i ?>" name="petugas[]" data-placeholder="Pilih pegawai">
                                            <option></option>
                                            <?php foreach ($pegawai as $p) : ?><option value="<?= $p['id'] ?>" <?= (string) ($sel[$i] ?? '') === (string) $p['id'] ? 'selected' : '' ?>><?= esc($p['nama']) ?></option><?php endforeach; ?>
                                        </select>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <button class="btn btn-gradient" type="submit">Simpan</button>
                        <a class="btn btn-soft" href="<?= site_url('task') ?>">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    var tiers = <?= json_encode($tiers, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    var info = document.getElementById('tarifInfo'), tujuan = document.getElementById('tujuan');

    function rupiah(n) { return 'Rp ' + Number(n).toLocaleString('id-ID'); }

    // Aturan sama dengan server: jarak > min dan jarak <= max (0 km ikut tier yang mulai dari 0)
    function cari(j) {
        for (var i = 0; i < tiers.length; i++) {
            var min = +tiers[i].jarak_min, max = +tiers[i].jarak_max;
            if ((j > min && j <= max) || (min === 0 && j === 0)) return tiers[i];
        }
        return null;
    }

    function perbarui() {
        var opt = $('#id_desa option:selected');
        if (!opt.val()) { info.textContent = 'Pilih desa untuk melihat tarif perjalanan.'; tujuan.placeholder = 'Kosongkan untuk pakai nama desa'; return; }
        tujuan.placeholder = opt.data('nama');
        var t = cari(+opt.data('jarak'));
        info.textContent = t ? 'Jarak ' + opt.data('jarak') + ' km → tarif ' + t.keterangan + ': ' + rupiah(t.harga) + ' per perjalanan per petugas.'
                             : 'Jarak ' + opt.data('jarak') + ' km tidak masuk tarif mana pun. Tambahkan tarif di menu Tarif perjalanan, kalau tidak desa ini tidak dihitung di laporan.';
    }

    $(function () { $('#id_desa').on('change', perbarui); perbarui(); });
})();
</script>
<?= $this->endSection() ?>
