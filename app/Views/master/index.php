<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $user = auth()->user(); ?>
<div class="card">
    <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi <?= esc($icon) ?> me-1"></i><?= esc($judul) ?></h6>
        <div class="d-flex gap-2">
            <?php if ($cari) : ?>
                <form method="get" class="d-flex gap-2">
                    <input type="search" name="q" value="<?= esc($q) ?>" class="form-control form-control-sm" placeholder="Cari…" aria-label="Cari">
                    <button class="btn btn-soft btn-sm" type="submit">Cari</button>
                </form>
            <?php endif; ?>
            <?php if ($user->can("$perm.create")) : ?>
                <a href="<?= site_url("$uri/baru") ?>" class="btn btn-gradient btn-sm"><i class="bi bi-plus-lg me-1"></i>Tambah <?= esc($satu) ?></a>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <?php foreach ($fields as $f) : if (($f['list'] ?? true) === false) continue; ?><th><?= esc($f['label']) ?></th><?php endforeach; ?>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $r) : ?>
                    <tr>
                        <?php foreach ($fields as $f) : if (($f['list'] ?? true) === false) continue;
                            $v = $r[$f['name']];
                            $v = ($f['format'] ?? '') === 'rupiah' ? 'Rp ' . format_rupiah((int) $v) : esc($v) . (isset($f['suffix']) ? ' ' . esc($f['suffix']) : ''); ?>
                            <td><?= $v ?></td>
                        <?php endforeach; ?>
                        <td class="text-end text-nowrap">
                            <?php if ($user->can("$perm.edit")) : ?>
                                <a href="<?= site_url("$uri/{$r['id']}/edit") ?>" class="btn btn-soft btn-sm">Ubah</a>
                            <?php endif; ?>
                            <?php if ($user->can("$perm.delete")) : ?>
                                <form method="post" action="<?= site_url("$uri/{$r['id']}/hapus") ?>" class="d-inline" data-confirm="Hapus <?= esc($satu, 'attr') ?> ini?">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-soft btn-sm text-danger" type="submit">Hapus</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($rows === []) : ?>
                    <tr><td colspan="<?= count($fields) + 1 ?>" class="text-center text-muted py-5">
                        <?= $q !== '' ? 'Tidak ada ' . esc($satu) . ' yang cocok dengan pencarian.' : 'Belum ada ' . esc($satu) . '. Tambahkan yang pertama.' ?>
                    </td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-3"><?= $pager->links() ?></div>
    </div>
</div>
<?= $this->endSection() ?>
