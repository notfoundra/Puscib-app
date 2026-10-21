<?php

namespace App\Controllers;

class Volume extends MasterController
{
    protected string $modelClass = \App\Models\MasterVolumeModel::class;
    protected string $perm       = 'volume';
    protected string $uri        = 'volume';
    protected string $judul      = 'Tarif perjalanan';
    protected string $satu       = 'tarif';
    protected string $icon       = 'bi-cash-coin';
    protected string $urutan     = 'jarak_min';

    protected array $fields = [
        ['name' => 'keterangan', 'label' => 'Keterangan', 'rules' => 'required|max_length[255]',
         'help' => 'Contoh: 0-5 KM. Jadi judul kolom di laporan.'],
        ['name' => 'jarak_min', 'label' => 'Jarak minimum', 'type' => 'number', 'suffix' => 'km', 'rules' => 'required|is_natural',
         'help' => 'Batas bawah, tidak termasuk. Kecuali 0.'],
        ['name' => 'jarak_max', 'label' => 'Jarak maksimum', 'type' => 'number', 'suffix' => 'km', 'rules' => 'required|is_natural_no_zero|greater_than[{jarak_min}]',
         'help' => 'Batas atas, termasuk. Desa 5 km masuk tarif 0-5 KM, bukan 5-10 KM.'],
        ['name' => 'harga', 'label' => 'Tarif per perjalanan', 'type' => 'number', 'format' => 'rupiah', 'rules' => 'required|is_natural'],
    ];

    protected function validasiTambahan(array $data, ?int $id): ?string
    {
        if ($this->m()->bentrok((int) $data['jarak_min'], (int) $data['jarak_max'], $id)) {
            return 'Rentang jarak bertabrakan dengan tarif lain. Satu jarak hanya boleh punya satu tarif.';
        }

        return null;
    }
}
