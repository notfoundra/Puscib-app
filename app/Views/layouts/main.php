<?php
$seg  = service('uri')->getSegment(1);
$user = auth()->user();

$menu = [
    ['', 'Dashboard', 'bi-speedometer2', 'dashboard.access'],
    ['task', 'Surat jalan', 'bi-truck', 'task.view'],
    ['import', 'Import Excel', 'bi-file-earmark-arrow-up', 'import.run'],
    ['laporan', 'Laporan', 'bi-printer', 'laporan.view'],
];
$master = [
    ['pegawai', 'Pegawai', 'bi-people', 'pegawai.view'],
    ['desa', 'Desa', 'bi-geo-alt', 'desa.view'],
    ['kegiatan', 'Kegiatan', 'bi-clipboard-check', 'kegiatan.view'],
    ['volume', 'Tarif perjalanan', 'bi-cash-coin', 'volume.view'],
];
$item = static function (array $m) use ($seg, $user) {
    if (! $user->can($m[3])) {
        return '';
    }

    return '<a class="nav-link' . ($seg === $m[0] ? ' active' : '') . '" href="' . site_url($m[0]) . '"><span class="ico"><i class="bi ' . $m[2] . '"></i></span>' . esc($m[1]) . '</a>';
};
$itemMaster = implode('', array_map($item, $master));
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Surat Jalan') ?> · Surat Jalan</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/css/soft-ui.css') ?>" rel="stylesheet">
</head>
<body>
<aside class="sidenav" id="sidenav">
    <a class="brand" href="<?= site_url('/') ?>"><i class="bi bi-truck"></i> Surat Jalan</a>
    <nav class="nav flex-column">
        <?php foreach ($menu as $m) : ?><?= $item($m) ?><?php endforeach; ?>
        <?php if ($itemMaster !== '') : ?>
            <div class="heading">Data master</div>
            <?= $itemMaster ?>
        <?php endif; ?>
    </nav>
</aside>

<div class="main">
    <div class="topbar">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-soft btn-sm menu-btn" type="button" data-toggle-sidenav aria-label="Buka menu"><i class="bi bi-list"></i></button>
            <h5><?= esc($title ?? '') ?></h5>
        </div>
        <div class="dropdown">
            <button class="btn btn-soft btn-sm dropdown-toggle" data-bs-toggle="dropdown" type="button"><i class="bi bi-person-circle me-1"></i><?= esc($user->username ?: $user->email) ?></button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="<?= url_to('logout') ?>">Keluar</a></li>
            </ul>
        </div>
    </div>

    <?php if (session('success')) : ?><div class="alert alert-success"><?= esc(session('success')) ?></div><?php endif; ?>
    <?php if (session('error')) : ?><div class="alert alert-danger"><?= esc(session('error')) ?></div><?php endif; ?>
    <?php if (session('errors')) : ?>
        <div class="alert alert-danger"><ul><?php foreach ((array) session('errors') as $e) : ?><li><?= esc($e) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <?= $this->renderSection('content') ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="<?= base_url('assets/js/app.js') ?>"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
