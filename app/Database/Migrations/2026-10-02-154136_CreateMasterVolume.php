<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMasterVolume extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'keterangan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'harga' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
        ]);

        $this->forge->addKey('id', true);

        $this->forge->createTable('master_volume');
    }

    public function down()
    {
        $this->forge->dropTable('master_volume', true);
    }
}