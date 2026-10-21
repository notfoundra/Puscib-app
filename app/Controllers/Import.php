<?php

namespace App\Controllers;

use App\Libraries\TaskImporter;
use Throwable;

class Import extends BaseController
{
    public function index()
    {
        $this->izin('import.run');

        return view('import/index', ['title' => 'Import Excel']);
    }

    public function preview()
    {
        $this->izin('import.run');

        $file = $this->request->getFile('berkas');
        $ext  = $file ? strtolower($file->getClientExtension()) : '';

        if (! $file || ! $file->isValid() || ! in_array($ext, ['xlsx', 'xls'], true)) {
            return redirect()->back()->with('error', 'Pilih file Excel (.xlsx atau .xls).');
        }

        $nama = bin2hex(random_bytes(16)) . '.' . $ext;
        $file->move($this->folder(), $nama);

        try {
            $hasil = (new TaskImporter())->preview($this->folder() . $nama);
        } catch (Throwable $e) {
            @unlink($this->folder() . $nama);

            return redirect()->back()->with('error', 'File tidak bisa dibaca: ' . $e->getMessage());
        }

        return view('import/preview', ['title' => 'Preview import', 'hasil' => $hasil, 'token' => $nama]);
    }

    public function commit()
    {
        $this->izin('import.run');

        $token = (string) $this->request->getPost('token');

        if (! preg_match('/^[a-f0-9]{32}\.(xlsx|xls)$/', $token) || ! is_file($this->folder() . $token)) {
            return redirect()->to('import')->with('error', 'Sesi import sudah habis. Upload ulang file-nya.');
        }

        try {
            $hasil = (new TaskImporter())->commit($this->folder() . $token);
        } catch (Throwable $e) {
            log_message('error', $e->getMessage());

            return redirect()->to('import')->with('error', 'Import gagal dan tidak ada data yang tersimpan: ' . $e->getMessage());
        }

        @unlink($this->folder() . $token);

        $r     = $hasil['ringkasan'];
        $pesan = "{$r['masuk']} surat jalan masuk";
        if ($r['kegiatan_dibuat'] > 0) {
            $pesan .= ", {$r['kegiatan_dibuat']} kegiatan baru dibuat";
        }
        if ($r['duplikat'] > 0) {
            $pesan .= ", {$r['duplikat']} duplikat dilewati";
        }
        if ($r['error'] > 0) {
            $pesan .= ", {$r['error']} baris error dilewati";
        }

        return redirect()->to('task')->with('success', $pesan . '.');
    }

    private function folder(): string
    {
        $d = WRITEPATH . 'uploads/import/';

        if (! is_dir($d)) {
            mkdir($d, 0775, true);
        }

        return $d;
    }
}
