<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMasterKegiatan extends Migration
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
            'nama_kegiatan' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
        ]);

        $this->forge->addKey('id', true);

        $this->forge->createTable('master_kegiatan');
    }

    public function down()
    {
        $this->forge->dropTable('master_kegiatan', true);
    }
}