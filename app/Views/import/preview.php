<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $r = $hasil['ringkasan']; ?>
<div class="row g-3 mb-3">
    <?php foreach ([['Total baris', $r['total'], 'text-body'], ['Siap masuk', $r['ok'], 'text-success'], ['Duplikat', $r['duplikat'], 'text-secondary'], ['Error', $r['error'], 'text-danger']] as [$l, $n, $c]) : ?>
        <div class="col-6 col-xl-3"><div class="card"><div class="card-body stat"><div><div class="text-muted fw-bold"><?= $l ?></div><div class="num <?= $c ?>"><?= (int) $n ?></div></div></div></div></div>
    <?php endforeach; ?>
</div>

<?php if ($hasil['kegiatan_baru'] !== []) : ?>
    <div class="alert alert-info">
        Kegiatan baru yang akan dibuat di master:
        <ul><?php foreach ($hasil['kegiatan_baru'] as $nama) : ?><li><?= esc($nama) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <h6 class="mb-0">Hasil pengecekan</h6>
        <form method="post" action="<?= site_url('import/commit') ?>" class="d-flex gap-2">
            <?= csrf_field() ?>
            <input type="hidden" name="token" value="<?= esc($token) ?>">
            <a href="<?= site_url('import') ?>" class="btn btn-soft btn-sm">Batal</a>
            <button class="btn btn-gradient btn-sm" type="submit" <?= $r['ok'] === 0 ? 'disabled' : '' ?>>Masukkan <?= (int) $r['ok'] ?> surat jalan</button>
        </form>
    </div>
    <div class="card-body">
        <?php if ($r['error'] > 0) : ?><p class="text-muted">Baris error tidak ikut dimasukkan. Perbaiki di Excel atau master, lalu upload ulang.</p><?php endif; ?>
        <div class="table-responsive"><table class="table mb-0">
            <thead><tr><th>Baris</th><th>Tanggal</th><th>Kegiatan</th><th>Desa</th><th>Petugas</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($hasil['rows'] as $x) : ?>
                <tr>
                    <td><?= (int) $x['baris'] ?></td>
                    <td class="text-nowrap"><?= $x['tanggal'] ? esc(date('d/m/Y', strtotime($x['tanggal']))) : '<span class="text-danger">kosong</span>' ?></td>
                    <td style="min-width:200px"><?= esc($x['kegiatan']) ?> <?= $x['kegiatan_baru'] ? '<span class="badge badge-new">baru</span>' : '' ?></td>
                    <td class="text-nowrap"><?= esc($x['desa']) ?></td>
                    <td><ul class="petugas-list"><?php foreach ($x['petugas'] as $p) : ?><li><?= esc($p) ?></li><?php endforeach; ?></ul></td>
                    <td>
                        <?php $cls = ['ok' => 'badge-ok', 'error' => 'badge-err', 'duplikat' => 'badge-dup'][$x['status']]; ?>
                        <span class="badge <?= $cls ?>"><?= $x['status'] === 'ok' ? 'siap' : esc($x['status']) ?></span>
                        <?php foreach ($x['pesan'] as $p) : ?><div class="text-muted small"><?= esc($p) ?></div><?php endforeach; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
    </div>
</div>
<?= $this->endSection() ?>
