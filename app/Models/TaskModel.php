<?php

namespace App\Models;

use CodeIgniter\Model;

class TaskModel extends Model
{
    public const MAKS_PETUGAS = 5;

    protected $table            = 'task';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['tanggal', 'tujuan', 'id_desa', 'id_kegiatan'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [
        'id'          => 'integer',
        'id_desa'     => 'integer',
        'id_kegiatan' => 'integer',
    ];

    protected $useTimestamps = false;

    /**
     * Join desa + kegiatan, plus kolom `petugas` berisi nama-nama dipisah '||'
     * dengan urutan input (petugas pertama = pembuat daftar).
     */
    public function withRelasi(): static
    {
        return $this->select(
            "task.*, master_desa.nama_desa, master_desa.jarak, master_kegiatan.nama_kegiatan,
             (SELECT GROUP_CONCAT(p.nama ORDER BY tm.id SEPARATOR '||')
                FROM task_member tm JOIN master_pegawai p ON p.id = tm.id_pegawai
               WHERE tm.id_task = task.id) AS petugas",
            false
        )
            ->join('master_desa', 'master_desa.id = task.id_desa')
            ->join('master_kegiatan', 'master_kegiatan.id = task.id_kegiatan');
    }

    /** ID pegawai untuk 1 task, urut sesuai input. */
    public function idPetugas(int $idTask): array
    {
        $rows = $this->db->table('task_member')->select('id_pegawai')->where('id_task', $idTask)->orderBy('id', 'ASC')->get()->getResultArray();

        return array_map(static fn ($r) => (int) $r['id_pegawai'], $rows);
    }

    /** Simpan task + petugas dalam satu transaksi. Return id task. */
    public function simpanLengkap(?int $id, array $data, array $idPegawai): int
    {
        $this->db->transStart();

        $this->skipValidation(true);

        if ($id === null) {
            $id = (int) $this->insert($data, true);
        } else {
            $this->update($id, $data);
            $this->db->table('task_member')->where('id_task', $id)->delete();
        }

        foreach ($idPegawai as $p) {
            $this->db->table('task_member')->insert(['id_task' => $id, 'id_pegawai' => $p]);
        }

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new \RuntimeException('Surat jalan gagal disimpan.');
        }

        return $id;
    }
}
