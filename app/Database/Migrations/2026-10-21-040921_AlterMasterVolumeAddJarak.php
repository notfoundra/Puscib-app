<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterMasterVolumeAddJarak extends Migration
{
    public function up()
    {
        // Tier berlaku untuk: jarak > jarak_min DAN jarak <= jarak_max
        $this->forge->addColumn('master_volume', [
            'jarak_min' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
                'after'      => 'keterangan',
            ],
            'jarak_max' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
                'after'      => 'jarak_min',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('master_volume', ['jarak_min', 'jarak_max']);
    }
}
