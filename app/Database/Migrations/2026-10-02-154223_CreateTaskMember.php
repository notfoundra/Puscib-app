<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTaskMember extends Migration
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
            'id_task' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'id_pegawai' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
        ]);

        $this->forge->addKey('id', true);

        $this->forge->addKey('id_task');
        $this->forge->addKey('id_pegawai');

        $this->forge->addForeignKey(
            'id_task',
            'task',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->forge->addForeignKey(
            'id_pegawai',
            'master_pegawai',
            'id',
            'CASCADE',
            'RESTRICT'
        );

        $this->forge->createTable('task_member');
    }

    public function down()
    {
        $this->forge->dropTable('task_member', true);
    }
}