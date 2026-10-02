<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMasterDesa extends Migration
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
            'nama_desa' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'jarak' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
        ]);

        $this->forge->addKey('id', true);

        $this->forge->createTable('master_desa');
    }

    public function down()
    {
        $this->forge->dropTable('master_desa', true);
    }
}