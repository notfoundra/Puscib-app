<?php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Model;
use Throwable;

/**
 * CRUD generik untuk tabel master. Subclass cukup mengisi konfigurasi
 * (model, permission, uri, label, daftar field).
 */
abstract class MasterController extends BaseController
{
    protected string $modelClass;
    protected string $perm;          // prefix permission: desa => desa.view, desa.create, ...
    protected string $uri;           // segmen URL
    protected string $judul;         // "Master desa"
    protected string $satu;          // "desa"
    protected string $icon = 'bi-collection';

    /**
     * @var list<array{name:string,label:string,rules:string,type?:string,help?:string,suffix?:string,format?:string,list?:bool}>
     */
    protected array $fields = [];

    /** Kolom yang dicari lewat kotak pencarian. */
    protected array $cari = [];

    /** Kolom yang namanya harus unik (dibandingkan setelah dinormalisasi, sama seperti saat import). */
    protected ?string $kolomUnik = null;

    protected string $urutan = 'id';

    private ?Model $model = null;

    protected function m(): Model
    {
        return $this->model ??= model($this->modelClass);
    }

    public function index()
    {
        $this->izin("{$this->perm}.view");

        $q = trim((string) $this->request->getGet('q'));
        $m = $this->m();

        if ($q !== '' && $this->cari !== []) {
            $m->groupStart();
            foreach ($this->cari as $i => $kolom) {
                $i === 0 ? $m->like($kolom, $q) : $m->orLike($kolom, $q);
            }
            $m->groupEnd();
        }

        $rows = $m->orderBy($this->urutan)->paginate(15);

        return view('master/index', $this->ctx(['rows' => $rows, 'pager' => $m->pager, 'q' => $q]));
    }

    public function baru()
    {
        $this->izin("{$this->perm}.create");

        return view('master/form', $this->ctx(['row' => null]));
    }

    public function simpan()
    {
        $this->izin("{$this->perm}.create");

        return $this->proses(null);
    }

    public function edit(int $id)
    {
        $this->izin("{$this->perm}.edit");

        return view('master/form', $this->ctx(['row' => $this->cariAtauGagal($id)]));
    }

    public function ubah(int $id)
    {
        $this->izin("{$this->perm}.edit");
        $this->cariAtauGagal($id);

        return $this->proses($id);
    }

    public function hapus(int $id)
    {
        $this->izin("{$this->perm}.delete");
        $this->cariAtauGagal($id);

        try {
            $this->m()->delete($id);
        } catch (Throwable) {
            return redirect()->to($this->uri)->with('error', ucfirst($this->satu) . ' tidak bisa dihapus karena masih dipakai di data lain.');
        }

        return redirect()->to($this->uri)->with('success', ucfirst($this->satu) . ' dihapus.');
    }

    /** Hook validasi tambahan. Return pesan error atau null. */
    protected function validasiTambahan(array $data, ?int $id): ?string
    {
        if ($this->kolomUnik === null) {
            return null;
        }

        $baru = normalisasi_nama($data[$this->kolomUnik] ?? '');

        foreach ($this->m()->select("id, {$this->kolomUnik}")->findAll() as $r) {
            if ((int) $r['id'] !== (int) $id && normalisasi_nama($r[$this->kolomUnik]) === $baru) {
                return ucfirst($this->satu) . " dengan nama itu sudah ada. Nama harus unik supaya import Excel tidak salah cocok.";
            }
        }

        return null;
    }

    private function proses(?int $id)
    {
        $rules = [];
        foreach ($this->fields as $f) {
            $rules[$f['name']] = ['label' => $f['label'], 'rules' => $f['rules']];
        }

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->request->getPost(array_column($this->fields, 'name'));

        if ($pesan = $this->validasiTambahan($data, $id)) {
            return redirect()->back()->withInput()->with('errors', [$pesan]);
        }

        // Validasi sudah dijalankan di atas
        $m = $this->m()->skipValidation(true);

        $id === null ? $m->insert($data) : $m->update($id, $data);

        return redirect()->to($this->uri)->with('success', ucfirst($this->satu) . ($id === null ? ' ditambahkan.' : ' diperbarui.'));
    }

    private function cariAtauGagal(int $id): array
    {
        return $this->m()->find($id) ?? throw PageNotFoundException::forPageNotFound(ucfirst($this->satu) . ' tidak ditemukan.');
    }

    private function ctx(array $extra): array
    {
        return $extra + [
            'title'  => $this->judul,
            'judul'  => $this->judul,
            'satu'   => $this->satu,
            'uri'    => $this->uri,
            'perm'   => $this->perm,
            'icon'   => $this->icon,
            'fields' => $this->fields,
            'cari'   => $this->cari !== [],
        ];
    }
}
