<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MasterVolumeSeeder extends Seeder
{
    public function run()
    {
        $tbl = $this->db->table('master_volume');

        if ($tbl->countAllResults() > 0) {
            return;
        }

        $tbl->insertBatch([
            ['keterangan' => '0-5 KM',  'jarak_min' => 0, 'jarak_max' => 5,  'harga' => 30000],
            ['keterangan' => '5-10 KM', 'jarak_min' => 5, 'jarak_max' => 10, 'harga' => 40000],
        ]);
    }
}
