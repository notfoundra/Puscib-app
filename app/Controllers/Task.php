<?php

namespace App\Controllers;

use App\Models\MasterDesaModel;
use App\Models\MasterKegiatanModel;
use App\Models\MasterPegawaiModel;
use App\Models\MasterVolumeModel;
use App\Models\TaskModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Throwable;

class Task extends BaseController
{
    public function index()
    {
        $this->izin('task.view');

        $kegiatan = (int) $this->request->getGet('kegiatan');
        $dari     = (string) $this->request->getGet('dari');
        $sampai   = (string) $this->request->getGet('sampai');
        $q        = trim((string) $this->request->getGet('q'));

        $m = model(TaskModel::class)->withRelasi();

        if ($kegiatan > 0) {
            $m->where('task.id_kegiatan', $kegiatan);
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dari)) {
            $m->where('task.tanggal >=', $dari . ' 00:00:00');
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $sampai)) {
            $m->where('task.tanggal <=', $sampai . ' 23:59:59');
        }
        if ($q !== '') {
            $m->groupStart()->like('master_desa.nama_desa', $q)->orLike('task.tujuan', $q)->groupEnd();
        }

        $rows = $m->orderBy('task.tanggal', 'DESC')->orderBy('task.id', 'DESC')->paginate(15);

        return view('task/index', [
            'title'    => 'Surat jalan',
            'rows'     => $rows,
            'pager'    => $m->pager,
            'kegiatan' => model(MasterKegiatanModel::class)->orderBy('nama_kegiatan')->findAll(),
            'f'        => compact('kegiatan', 'dari', 'sampai', 'q'),
        ]);
    }

    public function baru()
    {
        $this->izin('task.create');

        return $this->form(null, []);
    }

    public function simpan()
    {
        $this->izin('task.create');

        return $this->proses(null);
    }

    public function edit(int $id)
    {
        $this->izin('task.edit');
        $row = model(TaskModel::class)->find($id) ?? throw PageNotFoundException::forPageNotFound('Surat jalan tidak ditemukan.');

        return $this->form($row, model(TaskModel::class)->idPetugas($id));
    }

    public function ubah(int $id)
    {
        $this->izin('task.edit');
        model(TaskModel::class)->find($id) ?? throw PageNotFoundException::forPageNotFound('Surat jalan tidak ditemukan.');

        return $this->proses($id);
    }

    public function hapus(int $id)
    {
        $this->izin('task.delete');

        // task_member ikut terhapus lewat FK CASCADE
        model(TaskModel::class)->delete($id);

        return redirect()->to('task')->with('success', 'Surat jalan dihapus.');
    }

    private function form(?array $row, array $idPetugas)
    {
        return view('task/form', [
            'title'    => $row ? 'Ubah surat jalan' : 'Surat jalan baru',
            'row'      => $row,
            'idPetugas' => $idPetugas,
            'maks'     => TaskModel::MAKS_PETUGAS,
            'desa'     => model(MasterDesaModel::class)->orderBy('nama_desa')->findAll(),
            'kegiatan' => model(MasterKegiatanModel::class)->orderBy('nama_kegiatan')->findAll(),
            'pegawai'  => model(MasterPegawaiModel::class)->orderBy('nama')->findAll(),
            'tiers'    => model(MasterVolumeModel::class)->tiers(),
        ]);
    }

    private function proses(?int $id)
    {
        $rules = [
            'tanggal'     => ['label' => 'Tanggal', 'rules' => 'required|valid_date[Y-m-d]'],
            'id_desa'     => ['label' => 'Desa', 'rules' => 'required|is_not_unique[master_desa.id]'],
            'id_kegiatan' => ['label' => 'Kegiatan', 'rules' => 'required|is_not_unique[master_kegiatan.id]'],
            'tujuan'      => ['label' => 'Tujuan', 'rules' => 'permit_empty|max_length[255]'],
            'petugas.0'   => ['label' => 'Petugas 1', 'rules' => 'required|is_not_unique[master_pegawai.id]'],
            'petugas.*'   => ['label' => 'Petugas', 'rules' => 'permit_empty|is_not_unique[master_pegawai.id]'],
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $petugas = array_values(array_filter(array_map('intval', (array) $this->request->getPost('petugas'))));

        if (count($petugas) !== count(array_unique($petugas))) {
            return redirect()->back()->withInput()->with('errors', ['Petugas yang sama dipilih lebih dari sekali.']);
        }

        $petugas = array_slice($petugas, 0, TaskModel::MAKS_PETUGAS);

        $idDesa = (int) $this->request->getPost('id_desa');
        $tujuan = trim((string) $this->request->getPost('tujuan'));

        if ($tujuan === '') {
            $tujuan = model(MasterDesaModel::class)->find($idDesa)['nama_desa'];
        }

        $data = [
            'tanggal'     => $this->request->getPost('tanggal') . ' 00:00:00',
            'tujuan'      => $tujuan,
            'id_desa'     => $idDesa,
            'id_kegiatan' => (int) $this->request->getPost('id_kegiatan'),
        ];

        try {
            model(TaskModel::class)->simpanLengkap($id, $data, $petugas);
        } catch (Throwable $e) {
            log_message('error', $e->getMessage());

            return redirect()->back()->withInput()->with('error', 'Surat jalan gagal disimpan. Coba lagi.');
        }

        return redirect()->to('task')->with('success', $id === null ? 'Surat jalan ditambahkan.' : 'Surat jalan diperbarui.');
    }
}
