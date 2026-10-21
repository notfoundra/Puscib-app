<?php

namespace App\Controllers;

class Pegawai extends MasterController
{
    protected string $modelClass = \App\Models\MasterPegawaiModel::class;
    protected string $perm       = 'pegawai';
    protected string $uri        = 'pegawai';
    protected string $judul      = 'Master pegawai';
    protected string $satu       = 'pegawai';
    protected string $icon       = 'bi-people';
    protected array $cari        = ['nama', 'nip', 'jabatan'];
    protected ?string $kolomUnik = 'nama';
    protected string $urutan     = 'nama';

    protected array $fields = [
        ['name' => 'nama', 'label' => 'Nama lengkap', 'rules' => 'required|max_length[150]',
         'help' => 'Tulis lengkap dengan gelar, sama seperti di Excel, supaya import bisa mencocokkan.'],
        ['name' => 'nip', 'label' => 'NIP', 'rules' => 'required|max_length[30]'],
        ['name' => 'jabatan', 'label' => 'Jabatan', 'rules' => 'required|max_length[100]'],
        ['name' => 'pangkat', 'label' => 'Pangkat / golongan', 'rules' => 'required|max_length[100]'],
    ];
}
