<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Login, register, logout, magic link (Shield)
service('auth')->routes($routes);

$routes->group('', ['filter' => 'session'], static function (RouteCollection $routes): void {
    $routes->get('/', 'Dashboard::index');

    // CRUD standar: master + surat jalan
    foreach (['desa' => 'Desa', 'pegawai' => 'Pegawai', 'kegiatan' => 'Kegiatan', 'volume' => 'Volume', 'task' => 'Task'] as $uri => $ctl) {
        $routes->get($uri, "{$ctl}::index");
        $routes->get("{$uri}/baru", "{$ctl}::baru");
        $routes->post($uri, "{$ctl}::simpan");
        $routes->get("{$uri}/(:num)/edit", "{$ctl}::edit/$1");
        $routes->post("{$uri}/(:num)", "{$ctl}::ubah/$1");
        $routes->post("{$uri}/(:num)/hapus", "{$ctl}::hapus/$1");
    }

    // Import Excel
    $routes->get('import', 'Import::index');
    $routes->post('import/preview', 'Import::preview');
    $routes->post('import/commit', 'Import::commit');

    // Laporan
    $routes->get('laporan', 'Laporan::index');
    $routes->get('laporan/mingguan', 'Laporan::mingguan');
    $routes->get('laporan/bulanan', 'Laporan::bulanan');
});
