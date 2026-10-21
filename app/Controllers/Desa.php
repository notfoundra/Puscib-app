<?php

namespace App\Controllers;

class Desa extends MasterController
{
    protected string $modelClass = \App\Models\MasterDesaModel::class;
    protected string $perm       = 'desa';
    protected string $uri        = 'desa';
    protected string $judul      = 'Master desa';
    protected string $satu       = 'desa';
    protected string $icon       = 'bi-geo-alt';
    protected array $cari        = ['nama_desa'];
    protected ?string $kolomUnik = 'nama_desa';
    protected string $urutan     = 'nama_desa';

    protected array $fields = [
        ['name' => 'nama_desa', 'label' => 'Nama desa', 'rules' => 'required|max_length[150]'],
        ['name' => 'jarak', 'label' => 'Jarak', 'type' => 'number', 'suffix' => 'km', 'rules' => 'required|is_natural',
         'help' => 'Jarak dari puskesmas dalam km. Dipakai untuk menentukan tarif perjalanan.'],
    ];
}
