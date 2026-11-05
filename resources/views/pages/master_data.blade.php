<?php
$campuses = $campuses ?? [];
$categories = $categories ?? [];
$activeCampuses = count(array_filter($campuses, static fn (array $campus): bool => empty($campus['deleted_at'])));
$activeCategories = count(array_filter($categories, static fn (array $category): bool => empty($category['deleted_at'])));
?>

<div class="toolbar">
    <div>
        <span class="eyebrow">Admin only</span>
        <h2>Master data event</h2>
    </div>
    <div class="toolbar-summary">
        <span class="status status-aktif"><?= esc((string) $activeCampuses) ?> kampus</span>
        <span class="status status-review"><?= esc((string) $activeCategories) ?> kategori</span>
    </div>
</div>

<section class="master-data-grid">
    <article class="panel">
        <div class="panel-header">
            <div>
                <span class="eyebrow">Kampus pelaksana</span>
                <h2>Tambah kampus</h2>
            </div>
        </div>
        <form class="stacked-form" action="<?= site_url('master-data/campuses') ?>" method="post">
            <?= csrf_field() ?>
            <label>
                Nama kampus
                <input class="control" type="text" name="name" placeholder="Contoh: Kampus Jakarta" value="<?= esc(old('name') ?? '') ?>" required>
            </label>
            <button class="primary-button" type="submit">Simpan Kampus</button>
        </form>
    </article>

    <article class="panel">
        <div class="panel-header">
            <div>
                <span class="eyebrow">Kategori event</span>
                <h2>Tambah kategori</h2>
            </div>
        </div>
        <form class="stacked-form" action="<?= site_url('master-data/categories') ?>" method="post">
            <?= csrf_field() ?>
            <label>
                Nama kategori
                <input class="control" type="text" name="name" placeholder="Contoh: Seminar" value="<?= esc(old('name') ?? '') ?>" required>
            </label>
            <button class="primary-button" type="submit">Simpan Kategori</button>
        </form>
    </article>
</section>

<section class="master-data-grid">
    <article class="panel">
        <div class="panel-header">
            <div>
                <span class="eyebrow">Daftar</span>
                <h2>Kampus pelaksana</h2>
            </div>
        </div>
        <div class="table-wrap">
            <table class="data-table compact-admin-table">
                <thead>
                    <tr>
                        <th>Nama kampus</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($campuses as $campus): ?>
                        <tr>
                            <td>
                                <form class="inline-edit-form" action="<?= site_url('master-data/campuses/' . $campus['id'] . '/update') ?>" method="post">
                                    <?= csrf_field() ?>
                                    <input class="control compact" type="text" name="name" value="<?= esc($campus['name']) ?>" required>
                                    <button class="ghost-button compact-button" type="submit">Update</button>
                                </form>
                            </td>
                            <td>
                                <span class="status <?= empty($campus['deleted_at']) ? 'status-aktif' : 'status-nonaktif' ?>">
                                    <?= empty($campus['deleted_at']) ? 'aktif' : 'nonaktif' ?>
                                </span>
                            </td>
                            <td>
                                <?php if (empty($campus['deleted_at'])): ?>
                                    <form class="inline-form" action="<?= site_url('master-data/campuses/' . $campus['id'] . '/delete') ?>" method="post">
                                        <?= csrf_field() ?>
                                        <button class="danger-button compact-button" type="submit">Nonaktifkan</button>
                                    </form>
                                <?php else: ?>
                                    <span class="status status-nonaktif">arsip</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>

    <article class="panel">
        <div class="panel-header">
            <div>
                <span class="eyebrow">Daftar</span>
                <h2>Kategori event</h2>
            </div>
        </div>
        <div class="table-wrap">
            <table class="data-table compact-admin-table">
                <thead>
                    <tr>
                        <th>Nama kategori</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td>
                                <form class="inline-edit-form" action="<?= site_url('master-data/categories/' . $category['id'] . '/update') ?>" method="post">
                                    <?= csrf_field() ?>
                                    <input class="control compact" type="text" name="name" value="<?= esc($category['name']) ?>" required>
                                    <button class="ghost-button compact-button" type="submit">Update</button>
                                </form>
                            </td>
                            <td>
                                <span class="status <?= empty($category['deleted_at']) ? 'status-aktif' : 'status-nonaktif' ?>">
                                    <?= empty($category['deleted_at']) ? 'aktif' : 'nonaktif' ?>
                                </span>
                            </td>
                            <td>
                                <?php if (empty($category['deleted_at'])): ?>
                                    <form class="inline-form" action="<?= site_url('master-data/categories/' . $category['id'] . '/delete') ?>" method="post">
                                        <?= csrf_field() ?>
                                        <button class="danger-button compact-button" type="submit">Nonaktifkan</button>
                                    </form>
                                <?php else: ?>
                                    <span class="status status-nonaktif">arsip</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>
