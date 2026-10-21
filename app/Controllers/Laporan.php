<?php

namespace App\Controllers;

use App\Libraries\ReportService;
use App\Models\MasterKegiatanModel;
use DateTimeImmutable;

class Laporan extends BaseController
{
    public function index()
    {
        $this->izin('laporan.view');

        $senin = new DateTimeImmutable('monday this week');

        return view('laporan/index', [
            'title'    => 'Laporan',
            'kegiatan' => model(MasterKegiatanModel::class)->orderBy('nama_kegiatan')->findAll(),
            'dari'     => $senin->format('Y-m-d'),
            'sampai'   => $senin->modify('+6 days')->format('Y-m-d'),
        ]);
    }

    /** Daftar penerimaan uang perjalanan dinas: 1 kegiatan, 1 rentang tanggal (biasanya 1 minggu). */
    public function mingguan()
    {
        $this->izin('laporan.view');

        $ok = $this->validateData($this->request->getGet(), [
            'kegiatan' => 'required|is_natural_no_zero',
            'dari'     => 'required|valid_date[Y-m-d]',
            'sampai'   => 'required|valid_date[Y-m-d]',
        ]);

        $dari   = (string) $this->request->getGet('dari');
        $sampai = (string) $this->request->getGet('sampai');

        if (! $ok || $dari > $sampai) {
            return redirect()->to('laporan')->with('error', 'Pilih kegiatan dan rentang tanggal yang benar.');
        }

        $lap = (new ReportService())->mingguan((int) $this->request->getGet('kegiatan'), $dari, $sampai);

        if ($lap['rows'] === []) {
            return redirect()->to('laporan')->with('error', 'Tidak ada surat jalan untuk kegiatan itu pada rentang tanggal tersebut.');
        }

        return view('laporan/mingguan', [
            'title'         => 'Daftar penerimaan',
            'lap'           => $lap,
            'kodeRekening'  => trim((string) $this->request->getGet('kode_rekening')),
            'noTb'          => trim((string) $this->request->getGet('no_tb')),
        ]);
    }

    /** Rekap bulanan: 1 kegiatan, 1 baris per minggu. */
    public function bulanan()
    {
        $this->izin('laporan.view');

        $ok = $this->validateData($this->request->getGet(), [
            'kegiatan' => 'required|is_natural_no_zero',
            'bulan'    => 'required|integer|greater_than[0]|less_than[13]',
            'tahun'    => 'required|integer|greater_than[2000]|less_than[2100]',
        ]);

        if (! $ok) {
            return redirect()->to('laporan')->with('error', 'Pilih kegiatan, bulan, dan tahun yang benar.');
        }

        $lap = (new ReportService())->bulanan(
            (int) $this->request->getGet('kegiatan'),
            (int) $this->request->getGet('tahun'),
            (int) $this->request->getGet('bulan'),
        );

        if ($lap['items'] === []) {
            return redirect()->to('laporan')->with('error', 'Tidak ada surat jalan untuk kegiatan itu pada bulan tersebut.');
        }

        $lunas = (string) $this->request->getGet('tgl_lunas');

        return view('laporan/bulanan', [
            'title'        => 'Rekap bulanan',
            'lap'          => $lap,
            'kodeRekening' => trim((string) $this->request->getGet('kode_rekening')),
            'noTb'         => trim((string) $this->request->getGet('no_tb')),
            'tglLunas'     => preg_match('/^\d{4}-\d{2}-\d{2}$/', $lunas) ? daftar_tanggal_indo([$lunas]) : '',
        ]);
    }
}
