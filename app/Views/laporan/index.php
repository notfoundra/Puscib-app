<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="row g-3">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Daftar penerimaan (mingguan)</h6></div>
            <div class="card-body">
                <form method="get" action="<?= site_url('laporan/mingguan') ?>" target="_blank">
                    <div class="mb-3">
                        <label class="form-label" for="m_kegiatan">Kegiatan</label>
                        <select class="form-select select2" id="m_kegiatan" name="kegiatan" required data-placeholder="Pilih kegiatan">
                            <option></option>
                            <?php foreach ($kegiatan as $k) : ?><option value="<?= $k['id'] ?>"><?= esc($k['nama_kegiatan']) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6"><label class="form-label" for="dari">Dari tanggal</label><input class="form-control" type="date" id="dari" name="dari" value="<?= esc($dari) ?>" required></div>
                        <div class="col-6"><label class="form-label" for="sampai">Sampai tanggal</label><input class="form-control" type="date" id="sampai" name="sampai" value="<?= esc($sampai) ?>" required></div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6"><label class="form-label" for="m_kode">Kode rekening</label><input class="form-control" id="m_kode" name="kode_rekening"></div>
                        <div class="col-6"><label class="form-label" for="m_tb">No. TB</label><input class="form-control" id="m_tb" name="no_tb"></div>
                    </div>
                    <button class="btn btn-gradient" type="submit"><i class="bi bi-printer me-1"></i>Tampilkan daftar</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Laporan bulanan (Excel)</h6></div>
            <div class="card-body">
                <p class="text-muted">Satu file Excel berisi rekap bulanan dan semua laporan mingguan dari seluruh kegiatan di bulan itu. Kode rekening, No. TB, dan tanggal lunas diisi langsung di Excel.</p>
                <form method="get" action="<?= site_url('laporan/bulanan') ?>">
                    <div class="row g-3 mb-3">
                        <div class="col-7">
                            <label class="form-label" for="bulan">Bulan</label>
                            <select class="form-select" id="bulan" name="bulan">
                                <?php for ($b = 1; $b <= 12; $b++) : ?><option value="<?= $b ?>" <?= $b === (int) date('n') ? 'selected' : '' ?>><?= bulan_indo($b) ?></option><?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-5"><label class="form-label" for="tahun">Tahun</label><input class="form-control" type="number" id="tahun" name="tahun" value="<?= date('Y') ?>" min="2001" max="2099"></div>
                    </div>
                    <button class="btn btn-gradient" type="submit"><i class="bi bi-file-earmark-excel me-1"></i>Unduh Excel</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
