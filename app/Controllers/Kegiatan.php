<?php

namespace App\Controllers;

class Kegiatan extends MasterController
{
    protected string $modelClass = \App\Models\MasterKegiatanModel::class;
    protected string $perm       = 'kegiatan';
    protected string $uri        = 'kegiatan';
    protected string $judul      = 'Master kegiatan';
    protected string $satu       = 'kegiatan';
    protected string $icon       = 'bi-clipboard-check';
    protected array $cari        = ['nama_kegiatan'];
    protected ?string $kolomUnik = 'nama_kegiatan';
    protected string $urutan     = 'nama_kegiatan';

    protected array $fields = [
        ['name' => 'nama_kegiatan', 'label' => 'Nama kegiatan', 'type' => 'textarea', 'rules' => 'required|max_length[150]',
         'help' => 'Nama ini tampil sebagai judul di laporan penerimaan.'],
    ];
}
