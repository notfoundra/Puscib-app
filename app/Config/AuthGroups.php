<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

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
    ];

    public array $matrix = [
        'admin' => [
            'dashboard.*',
            'task.*',
            'pegawai.*',
        ],

        'user' => [
            'dashboard.access',
            'task.view',
        ],
    ];
}