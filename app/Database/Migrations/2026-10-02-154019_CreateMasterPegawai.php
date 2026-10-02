<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMasterPegawai extends Migration
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
            'nama' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'jabatan' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'pangkat' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'nip' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
            ],
        ]);

        $this->forge->addKey('id', true);

        $this->forge->createTable('master_pegawai');
    }

    public function down()
    {
        $this->forge->dropTable('master_pegawai', true);
    }
}