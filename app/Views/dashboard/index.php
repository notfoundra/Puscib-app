<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$kartu = [
    ['Surat jalan bulan ini', $stat['task_bulan'], 'bi-truck', 'var(--su-primary)'],
    ['Pegawai', $stat['pegawai'], 'bi-people', 'var(--su-info)'],
    ['Desa', $stat['desa'], 'bi-geo-alt', 'var(--su-success)'],
    ['Kegiatan', $stat['kegiatan'], 'bi-clipboard-check', 'var(--su-dark)'],
];
?>
<div class="row g-3 mb-3">
    <?php foreach ($kartu as [$label, $angka, $ikon, $warna]) : ?>
        <div class="col-sm-6 col-xl-3">
            <div class="card"><div class="card-body stat">
                <div><div class="text-muted fw-bold"><?= esc($label) ?></div><div class="num"><?= (int) $angka ?></div></div>
                <div class="ico" style="background:<?= $warna ?>"><i class="bi <?= $ikon ?>"></i></div>
            </div></div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Surat jalan terbaru</h6>
        <?php if (auth()->user()->can('task.create')) : ?><a href="<?= site_url('task/baru') ?>" class="btn btn-gradient btn-sm"><i class="bi bi-plus-lg me-1"></i>Surat jalan baru</a><?php endif; ?>
    </div>
    <div class="card-body">
        <?php if ($terbaru === []) : ?>
            <p class="text-muted mb-0">Belum ada surat jalan. Buat satu, atau import dari Excel.</p>
        <?php else : ?>
            <div class="table-responsive"><table class="table mb-0">
                <thead><tr><th>Tanggal</th><th>Kegiatan</th><th>Desa</th><th>Petugas</th></tr></thead>
                <tbody>
                <?php foreach ($terbaru as $t) : ?>
                    <tr>
                        <td class="text-nowrap"><?= esc(date('d/m/Y', strtotime($t['tanggal']))) ?></td>
                        <td><?= esc($t['nama_kegiatan']) ?></td>
                        <td><?= esc($t['nama_desa']) ?></td>
                        <td><?= esc(str_replace('||', ', ', (string) $t['petugas'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
