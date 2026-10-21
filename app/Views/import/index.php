<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Import surat jalan dari Excel</h6></div>
            <div class="card-body">
                <form method="post" action="<?= site_url('import/preview') ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <label class="form-label" for="berkas">File Excel (.xlsx / .xls)</label>
                    <input class="form-control mb-3" type="file" id="berkas" name="berkas" accept=".xlsx,.xls" required>
                    <button class="btn btn-gradient" type="submit">Upload dan cek dulu</button>
                    <div class="form-text mt-2">Belum ada data yang tersimpan sampai kamu konfirmasi di halaman preview.</div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Format file</h6></div>
            <div class="card-body">
                <p>Baris pertama berisi judul kolom, dibaca dari sheet pertama:</p>
                <p><code>tanggal</code> · <code>PETUGAS 1</code> sampai <code>PETUGAS 5</code> · <code>TUJUAN1</code> · <code>DESA</code> · <code>Kegiatan</code></p>
                <ul class="mb-0">
                    <li>Baris kosong sebagai pemisah blok boleh dipakai.</li>
                    <li>Kegiatan yang belum ada otomatis dibuat di master kegiatan.</li>
                    <li>Pegawai dan desa harus sudah ada di master, karena butuh NIP, jabatan, dan jarak. Baris yang tidak cocok ditandai error.</li>
                    <li>Tujuan kosong diisi nama desa. Task yang kembar dengan data yang sudah ada dilewati.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
