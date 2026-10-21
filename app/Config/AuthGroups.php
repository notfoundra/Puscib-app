<?php

declare(strict_types=1);

namespace Config;

use CodeIgniter\Shield\Config\AuthGroups as ShieldAuthGroups;

class AuthGroups extends ShieldAuthGroups
{
    public array $groups = [
        'admin' => [
            'title'       => 'Administrator',
            'description' => 'Administrator aplikasi',
        ],

        'user' => [
            'title'       => 'User',
            'description' => 'Pengguna aplikasi',
        ],
    ];

    public string $defaultGroup = 'user';

    public array $permissions = [
        'dashboard.access' => 'Can access dashboard',

        'task.view'   => 'Can view tasks',
        'task.create' => 'Can create tasks',
        'task.edit'   => 'Can edit tasks',
        'task.delete' => 'Can delete tasks',

        'pegawai.view'   => 'Can view employees',
        'pegawai.create' => 'Can create employees',
        'pegawai.edit'   => 'Can edit employees',
        'pegawai.delete' => 'Can delete employees',

        'desa.view'   => 'Can view villages',
        'desa.create' => 'Can create villages',
        'desa.edit'   => 'Can edit villages',
        'desa.delete' => 'Can delete villages',

        'kegiatan.view'   => 'Can view activities',
        'kegiatan.create' => 'Can create activities',
        'kegiatan.edit'   => 'Can edit activities',
        'kegiatan.delete' => 'Can delete activities',

        'volume.view'   => 'Can view travel rates',
        'volume.create' => 'Can create travel rates',
        'volume.edit'   => 'Can edit travel rates',
        'volume.delete' => 'Can delete travel rates',

        'import.run'   => 'Can import tasks from Excel',
        'laporan.view' => 'Can view and print reports',
    ];

    public array $matrix = [
        'admin' => [
            'dashboard.*',
            'task.*',
            'pegawai.*',
            'desa.*',
            'kegiatan.*',
            'volume.*',
            'import.*',
            'laporan.*',
        ],

        'user' => [
            'dashboard.access',
            'task.view',
            'laporan.view',
        ],
    ];
}
