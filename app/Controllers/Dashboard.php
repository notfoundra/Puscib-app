<?php

namespace App\Controllers;

use App\Models\TaskModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $this->izin('dashboard.access');

        $db = db_connect();

        $stat = [
            'pegawai'    => $db->table('master_pegawai')->countAllResults(),
            'desa'       => $db->table('master_desa')->countAllResults(),
            'kegiatan'   => $db->table('master_kegiatan')->countAllResults(),
            'task_bulan' => $db->table('task')
                ->where('tanggal >=', date('Y-m-01 00:00:00'))
                ->where('tanggal <=', date('Y-m-t 23:59:59'))
                ->countAllResults(),
        ];

        $terbaru = model(TaskModel::class)->withRelasi()->orderBy('task.tanggal', 'DESC')->orderBy('task.id', 'DESC')->findAll(6);

        return view('dashboard/index', ['title' => 'Dashboard', 'stat' => $stat, 'terbaru' => $terbaru]);
    }
}
