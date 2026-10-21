<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Default pengaturan instansi. Bisa di-override lewat setting() Shield:
 *   setting()->set('Instansi.pimpinanNama', '...');
 * Dibaca dengan: setting('Instansi.pimpinanNama')
 */
class Instansi extends BaseConfig
{
    public string $namaPuskesmas = 'UPTD Puskesmas Cibingbin';
    public string $kabupaten     = 'Kuningan';
    public string $kecamatan     = 'Cibingbin';

    /** Baris ke-2 judul laporan: "... DALAM RANGKA KEGIATAN" */
    public string $programAnggaran = 'DAK NON FISIK BANTUAN OPERASIONAL KESEHATAN (BOK) PUSKESMAS';

    public string $pimpinanJabatan = 'Pimpinan BLUD';
    public string $pimpinanNama    = 'H. YUDI SUPRIYADI, S.Kep., Ns';
    public string $pimpinanNip     = '19820201 200701 1 006';

    public string $bendaharaJabatan = 'Bendahara Pengeluaran BLUD';
    public string $bendaharaNama    = 'ROHETIN, SE';
    public string $bendaharaNip     = '19800421 200801 2 009';
}
