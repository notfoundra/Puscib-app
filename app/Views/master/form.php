<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $edit = $row !== null; ?>
<div class="row">
    <div class="col-lg-7 col-xl-6">
        <div class="card">
            <div class="card-header"><h6 class="mb-0"><?= $edit ? 'Ubah' : 'Tambah' ?> <?= esc($satu) ?></h6></div>
            <div class="card-body">
                <form method="post" action="<?= site_url($edit ? "$uri/{$row['id']}" : $uri) ?>" novalidate>
                    <?= csrf_field() ?>
                    <?php foreach ($fields as $f) :
                        $n = $f['name']; $type = $f['type'] ?? 'text'; $val = old($n, $row[$n] ?? '', false); ?>
                        <div class="mb-3">
                            <label class="form-label" for="f_<?= esc($n) ?>"><?= esc($f['label']) ?></label>
                            <?php $grup = isset($f['suffix']) || ($f['format'] ?? '') === 'rupiah'; ?>
                            <?php if ($grup) : ?><div class="input-group"><?php endif; ?>
                            <?php if (($f['format'] ?? '') === 'rupiah') : ?><span class="input-group-text">Rp</span><?php endif; ?>
                            <?php if ($type === 'textarea') : ?>
                                <textarea class="form-control" id="f_<?= esc($n) ?>" name="<?= esc($n) ?>" rows="3"><?= esc($val) ?></textarea>
                            <?php else : ?>
                                <input class="form-control" id="f_<?= esc($n) ?>" type="<?= esc($type) ?>" name="<?= esc($n) ?>" value="<?= esc($val) ?>" <?= $type === 'number' ? 'min="0" inputmode="numeric"' : '' ?>>
                            <?php endif; ?>
                            <?php if (isset($f['suffix'])) : ?><span class="input-group-text"><?= esc($f['suffix']) ?></span><?php endif; ?>
                            <?php if ($grup) : ?></div><?php endif; ?>
                            <?php if (! empty($f['help'])) : ?><div class="form-text"><?= esc($f['help']) ?></div><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex gap-2">
                        <button class="btn btn-gradient" type="submit">Simpan</button>
                        <a class="btn btn-soft" href="<?= site_url($uri) ?>">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
