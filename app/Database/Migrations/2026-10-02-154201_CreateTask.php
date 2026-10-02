<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTask extends Migration
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
            'tanggal' => [
                'type' => 'DATETIME',
            ],
            'tujuan' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'id_desa' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'id_kegiatan' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
        ]);

        $this->forge->addKey('id', true);

        $this->forge->addForeignKey(
            'id_desa',
            'master_desa',
            'id',
            'CASCADE',
            'RESTRICT'
        );

        $this->forge->addForeignKey(
            'id_kegiatan',
            'master_kegiatan',
            'id',
            'CASCADE',
            'RESTRICT'
        );

        $this->forge->createTable('task');
    }

    public function down()
    {
        $this->forge->dropTable('task', true);
    }
}