<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $user = auth()->user(); ?>
<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-start">
            <form method="get" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label mb-1" for="kegiatan">Kegiatan</label>
                    <select class="form-select form-select-sm" id="kegiatan" name="kegiatan" style="max-width:260px">
                        <option value="">Semua</option>
                        <?php foreach ($kegiatan as $k) : ?><option value="<?= $k['id'] ?>" <?= (int) $f['kegiatan'] === (int) $k['id'] ? 'selected' : '' ?>><?= esc($k['nama_kegiatan']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto"><label class="form-label mb-1" for="dari">Dari</label><input type="date" class="form-control form-control-sm" id="dari" name="dari" value="<?= esc($f['dari']) ?>"></div>
                <div class="col-auto"><label class="form-label mb-1" for="sampai">Sampai</label><input type="date" class="form-control form-control-sm" id="sampai" name="sampai" value="<?= esc($f['sampai']) ?>"></div>
                <div class="col-auto"><label class="form-label mb-1" for="q">Desa / tujuan</label><input type="search" class="form-control form-control-sm" id="q" name="q" value="<?= esc($f['q']) ?>"></div>
                <div class="col-auto"><button class="btn btn-soft btn-sm" type="submit">Terapkan</button></div>
            </form>
            <?php if ($user->can('task.create')) : ?><a href="<?= site_url('task/baru') ?>" class="btn btn-gradient btn-sm"><i class="bi bi-plus-lg me-1"></i>Surat jalan baru</a><?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive"><table class="table mb-0">
            <thead><tr><th>Tanggal</th><th>Kegiatan</th><th>Desa</th><th>Petugas</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r) : ?>
                <tr>
                    <td class="text-nowrap"><?= esc(date('d/m/Y', strtotime($r['tanggal']))) ?></td>
                    <td style="min-width:220px"><?= esc($r['nama_kegiatan']) ?></td>
                    <td class="text-nowrap"><?= esc($r['nama_desa']) ?> <span class="text-muted">· <?= (int) $r['jarak'] ?> km</span></td>
                    <td>
                        <ul class="petugas-list">
                            <?php foreach (explode('||', (string) $r['petugas']) as $nama) : ?><li><?= esc($nama) ?></li><?php endforeach; ?>
                        </ul>
                    </td>
                    <td class="text-end text-nowrap">
                        <?php if ($user->can('task.edit')) : ?><a href="<?= site_url("task/{$r['id']}/edit") ?>" class="btn btn-soft btn-sm">Ubah</a><?php endif; ?>
                        <?php if ($user->can('task.delete')) : ?>
                            <form method="post" action="<?= site_url("task/{$r['id']}/hapus") ?>" class="d-inline" data-confirm="Hapus surat jalan ini?">
                                <?= csrf_field() ?><button class="btn btn-soft btn-sm text-danger" type="submit">Hapus</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if ($rows === []) : ?><tr><td colspan="5" class="text-center text-muted py-5">Tidak ada surat jalan yang cocok.</td></tr><?php endif; ?>
            </tbody>
        </table></div>
        <div class="mt-3"><?= $pager->links() ?></div>
    </div>
</div>
<?= $this->endSection() ?>
