<?php
$roles = $roles ?? [];
$users = $users ?? [];
$crmUsers = $crmUsers ?? [];
$campuses = $campuses ?? [];
$accessOptions = $accessOptions ?? [];
$roleDefaults = [];
foreach ($roles as $role) {
    $decoded = json_decode((string) ($role['default_permissions'] ?? '[]'), true);
    $roleDefaults[$role['name']] = is_array($decoded) ? $decoded : [];
}
$campusNamesById = [];
foreach ($campuses as $campus) {
    $campusNamesById[(int) $campus['id']] = $campus['name'];
}
$isCampusScopedRole = static function (string $role): bool {
    $role = strtolower(trim((string) preg_replace('/\s+/', ' ', $role)));

    return in_array($role, ['kepala kampus', 'markom', 'marketing akademik'], true);
};
$newUserRole = old('role') ?: ($roles[0]['name'] ?? '');
$newUserAccess = old('access_permissions') ?: ($roleDefaults[$newUserRole] ?? ['dashboard']);
$newUserCampusId = old('campus_id') ?: '';
$activeUsers = count(array_filter($users, static fn (array $user): bool => empty($user['deleted_at']) && ($user['status'] ?? '') === 'Aktif'));
$reviewUsers = array_values(array_filter($users, static fn (array $user): bool => empty($user['deleted_at']) && ($user['status'] ?? '') === 'Review'));
$activeCrmUsers = count(array_filter($crmUsers, static fn (array $user): bool => empty($user['deleted_at']) && ($user['status'] ?? '') === 'Active'));
$reviewCrmUsers = array_values(array_filter($crmUsers, static fn (array $user): bool => empty($user['deleted_at']) && ($user['status'] ?? '') === 'Review'));
?>

<div class="toolbar user-toolbar">
    <div>
        <span class="eyebrow">Role based access control</span>
        <h2>Manajemen user</h2>
    </div>
    <div class="toolbar-summary">
        <span class="status status-aktif"><?= esc((string) $activeUsers) ?> aktif</span>
        <span class="status status-review"><?= esc((string) count($reviewUsers)) ?> pendaftar baru</span>
        <span class="status status-review"><?= esc((string) count($reviewCrmUsers)) ?> review CRM</span>
        <span class="status status-calendar"><?= esc((string) $activeCrmUsers) ?> CRM</span>
        <span class="status status-review"><?= esc((string) count($roles)) ?> role</span>
    </div>
</div>

<?php if ($reviewUsers !== []): ?>
    <section class="panel pending-user-panel">
        <div class="panel-header">
            <div>
                <span class="eyebrow">Pendaftar baru</span>
                <h2>Menunggu review admin</h2>
            </div>
            <span class="status status-review"><?= esc((string) count($reviewUsers)) ?> perlu diproses</span>
        </div>
        <div class="pending-user-list">
            <?php foreach ($reviewUsers as $pendingUser): ?>
                <?php
                $pendingAccess = json_decode((string) ($pendingUser['access_permissions'] ?? '[]'), true);
                $pendingAccess = is_array($pendingAccess) ? $pendingAccess : ($roleDefaults[$pendingUser['role']] ?? ['dashboard']);
                ?>
                <article class="pending-user-card">
                    <div>
                        <strong><?= esc($pendingUser['name']) ?></strong>
                        <small><?= esc($pendingUser['username'] ?? '-') ?> - <?= esc($pendingUser['email']) ?></small>
                    </div>
                    <div class="pending-user-meta">
                        <span class="status status-review"><?= esc($pendingUser['role']) ?></span>
                        <small>Daftar <?= esc(! empty($pendingUser['created_at']) ? date('d M Y H:i', strtotime($pendingUser['created_at'])) : '-') ?></small>
                    </div>
                    <label class="pending-user-role">
                        Role approval
                        <select class="control compact" form="pending-approve-<?= esc($pendingUser['id']) ?>" name="role" data-role-preset-target="pending-user-access-<?= esc($pendingUser['id']) ?>" data-campus-role-select>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= esc($role['name']) ?>" data-permissions="<?= esc(json_encode($roleDefaults[$role['name']] ?? []), 'attr') ?>" <?= $pendingUser['role'] === $role['name'] ? 'selected' : '' ?>>
                                    <?= esc($role['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="pending-user-role" data-campus-role-field>
                        Kampus pelaksana
                        <select class="control compact" form="pending-approve-<?= esc($pendingUser['id']) ?>" name="campus_id">
                            <option value="">Pilih kampus</option>
                            <?php foreach ($campuses as $campus): ?>
                                <option value="<?= esc((string) $campus['id']) ?>" <?= (string) ($pendingUser['campus_id'] ?? '') === (string) $campus['id'] ? 'selected' : '' ?>>
                                    <?= esc($campus['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <div class="permission-pills pending-user-access">
                        <?php foreach ($pendingAccess as $permission): ?>
                            <span><?= esc($accessOptions[$permission] ?? $permission) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="button-row pending-user-actions">
                        <form id="pending-approve-<?= esc($pendingUser['id']) ?>" action="<?= site_url('users/' . $pendingUser['id'] . '/update') ?>" method="post" class="inline-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="name" value="<?= esc($pendingUser['name'], 'attr') ?>">
                            <input type="hidden" name="username" value="<?= esc($pendingUser['username'] ?? '', 'attr') ?>">
                            <input type="hidden" name="email" value="<?= esc($pendingUser['email'], 'attr') ?>">
                            <input type="hidden" name="status" value="Aktif">
                            <span data-access-scope="pending-user-access-<?= esc($pendingUser['id']) ?>" hidden>
                                <?php foreach ($accessOptions as $key => $label): ?>
                                    <input type="checkbox" name="access_permissions[]" value="<?= esc($key) ?>" <?= in_array($key, $pendingAccess, true) ? 'checked' : '' ?>>
                                <?php endforeach; ?>
                            </span>
                            <button class="primary-button compact-button" type="submit">Setujui</button>
                        </form>
                        <form action="<?= site_url('users/' . $pendingUser['id'] . '/update') ?>" method="post" class="inline-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="name" value="<?= esc($pendingUser['name'], 'attr') ?>">
                            <input type="hidden" name="username" value="<?= esc($pendingUser['username'] ?? '', 'attr') ?>">
                            <input type="hidden" name="email" value="<?= esc($pendingUser['email'], 'attr') ?>">
                            <input type="hidden" name="role" value="<?= esc($pendingUser['role'], 'attr') ?>">
                            <input type="hidden" name="campus_id" value="<?= esc((string) ($pendingUser['campus_id'] ?? ''), 'attr') ?>">
                            <input type="hidden" name="status" value="Nonaktif">
                            <?php foreach ($pendingAccess as $permission): ?>
                                <input type="hidden" name="access_permissions[]" value="<?= esc($permission, 'attr') ?>">
                            <?php endforeach; ?>
                            <button class="danger-button compact-button" type="submit">Tolak</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<?php if ($reviewCrmUsers !== []): ?>
    <section class="panel pending-user-panel">
        <div class="panel-header">
            <div>
                <span class="eyebrow">Pendaftar CRM</span>
                <h2>Menunggu review admin</h2>
            </div>
            <span class="status status-review"><?= esc((string) count($reviewCrmUsers)) ?> perlu diproses</span>
        </div>
        <div class="pending-user-list">
            <?php foreach ($reviewCrmUsers as $pendingCrmUser): ?>
                <?php $crmApproveFormId = 'crm-pending-approve-' . $pendingCrmUser['id']; ?>
                <article class="pending-user-card pending-crm-card">
                    <div>
                        <strong><?= esc($pendingCrmUser['full_name']) ?></strong>
                        <small><?= esc($pendingCrmUser['username']) ?></small>
                        <small><?= esc($pendingCrmUser['campus_name'] ?: 'Kampus belum dipilih') ?></small>
                    </div>
                    <div class="pending-user-meta">
                        <span class="status status-review">Review</span>
                        <small>Didaftarkan oleh <?= esc($pendingCrmUser['registered_by_name'] ?: 'Kepala Kampus') ?></small>
                        <small>Daftar <?= esc(! empty($pendingCrmUser['created_at']) ? date('d M Y H:i', strtotime($pendingCrmUser['created_at'])) : '-') ?></small>
                    </div>
                    <label class="pending-user-role">
                        Role CRM
                        <select class="control compact" form="<?= esc($crmApproveFormId) ?>" name="role">
                            <?php foreach (['Agent', 'Admin'] as $role): ?>
                                <option value="<?= esc($role) ?>" <?= $pendingCrmUser['role'] === $role ? 'selected' : '' ?>><?= esc($role) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <div class="permission-pills pending-user-access">
                        <span>CRM</span>
                        <span><?= esc($pendingCrmUser['role']) ?></span>
                    </div>
                    <div class="button-row pending-user-actions">
                        <form id="<?= esc($crmApproveFormId) ?>" action="<?= site_url('admin/crm-users/' . $pendingCrmUser['id'] . '/update') ?>" method="post" class="inline-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="full_name" value="<?= esc($pendingCrmUser['full_name'], 'attr') ?>">
                            <input type="hidden" name="username" value="<?= esc($pendingCrmUser['username'], 'attr') ?>">
                            <input type="hidden" name="crm_campus_id" value="<?= esc((string) ($pendingCrmUser['campus_id'] ?? ''), 'attr') ?>">
                            <input type="hidden" name="status" value="Active">
                            <button class="primary-button compact-button" type="submit">Setujui</button>
                        </form>
                        <form action="<?= site_url('admin/crm-users/' . $pendingCrmUser['id'] . '/update') ?>" method="post" class="inline-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="full_name" value="<?= esc($pendingCrmUser['full_name'], 'attr') ?>">
                            <input type="hidden" name="username" value="<?= esc($pendingCrmUser['username'], 'attr') ?>">
                            <input type="hidden" name="role" value="<?= esc($pendingCrmUser['role'], 'attr') ?>">
                            <input type="hidden" name="crm_campus_id" value="<?= esc((string) ($pendingCrmUser['campus_id'] ?? ''), 'attr') ?>">
                            <input type="hidden" name="status" value="Inactive">
                            <button class="danger-button compact-button" type="submit">Tolak</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<section class="user-summary-grid">
    <article class="panel user-summary-card">
        <span class="eyebrow">Total user</span>
        <strong><?= esc((string) count($users)) ?></strong>
        <small>Termasuk akun aktif, review, nonaktif, dan soft delete.</small>
    </article>
    <article class="panel user-summary-card">
        <span class="eyebrow">User aktif</span>
        <strong><?= esc((string) $activeUsers) ?></strong>
        <small>Akun yang bisa login ke aplikasi.</small>
    </article>
    <article class="panel user-summary-card">
        <span class="eyebrow">Kategori role</span>
        <strong><?= esc((string) count($roles)) ?></strong>
        <small>Role sistem dan role custom dari admin.</small>
    </article>
    <article class="panel user-summary-card">
        <span class="eyebrow">Akun CRM</span>
        <strong><?= esc((string) $activeCrmUsers) ?></strong>
        <small>Admin CRM dan Agent CRM aktif.</small>
    </article>
</section>

<section class="admin-drawer-grid">
    <details class="panel management-drawer" <?= old('username') ? 'open' : '' ?>>
        <summary>
            <div>
                <span class="eyebrow">User baru</span>
                <strong>Tambah akun</strong>
            </div>
            <span class="ghost-button compact-button">Buka</span>
        </summary>
        <form class="drawer-form user-create-form" action="<?= site_url('users') ?>" method="post">
            <?= csrf_field() ?>
            <label>
                Nama
                <input class="control" type="text" name="name" placeholder="Nama user" value="<?= esc(old('name') ?? '') ?>" required>
            </label>
            <label>
                Username
                <input class="control" type="text" name="username" placeholder="username" value="<?= esc(old('username') ?? '') ?>" required>
            </label>
            <label>
                Email
                <input class="control" type="email" name="email" placeholder="nama@eventhub.test" value="<?= esc(old('email') ?? '') ?>" required>
            </label>
            <label>
                Role
                <select class="control" name="role" required data-role-preset-target="create-user-access" data-campus-role-select>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= esc($role['name']) ?>" data-permissions="<?= esc(json_encode($roleDefaults[$role['name']] ?? []), 'attr') ?>" <?= $newUserRole === $role['name'] ? 'selected' : '' ?>>
                            <?= esc($role['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label data-campus-role-field>
                Kampus pelaksana
                <select class="control" name="campus_id">
                    <option value="">Pilih kampus</option>
                    <?php foreach ($campuses as $campus): ?>
                        <option value="<?= esc((string) $campus['id']) ?>" <?= (string) $newUserCampusId === (string) $campus['id'] ? 'selected' : '' ?>>
                            <?= esc($campus['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Status
                <select class="control" name="status">
                    <?php foreach (['Aktif', 'Review', 'Nonaktif'] as $status): ?>
                        <option <?= (old('status') ?: 'Aktif') === $status ? 'selected' : '' ?>><?= esc($status) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Password awal
                <span class="password-field">
                    <input class="control" type="password" name="password" placeholder="Default: password">
                    <button class="password-toggle" type="button" data-password-toggle aria-label="Lihat password" aria-pressed="false"></button>
                </span>
            </label>
            <fieldset class="access-fieldset" data-access-scope="create-user-access">
                <legend>Akses utama user</legend>
                <?php foreach ($accessOptions as $key => $label): ?>
                    <label class="check-row compact-check">
                        <input type="checkbox" name="access_permissions[]" value="<?= esc($key) ?>" <?= in_array($key, $newUserAccess, true) ? 'checked' : '' ?>>
                        <?= esc($label) ?>
                    </label>
                <?php endforeach; ?>
            </fieldset>
            <button class="primary-button" type="submit">Tambah User</button>
        </form>
    </details>

    <details class="panel management-drawer">
        <summary>
            <div>
                <span class="eyebrow">Kategori role</span>
                <strong>Tambah role</strong>
            </div>
            <span class="ghost-button compact-button">Buka</span>
        </summary>
        <form class="drawer-form role-create-form" action="<?= site_url('roles') ?>" method="post">
            <?= csrf_field() ?>
            <label>
                Nama role
                <input class="control" type="text" name="name" placeholder="Contoh: Koordinator Event" required>
            </label>
            <label>
                Deskripsi
                <input class="control" type="text" name="description" placeholder="Ringkasan tanggung jawab role">
            </label>
            <fieldset class="access-fieldset role-access-fieldset">
                <legend>Akses default</legend>
                <?php foreach ($accessOptions as $key => $label): ?>
                    <label class="check-row compact-check">
                        <input type="checkbox" name="default_permissions[]" value="<?= esc($key) ?>" <?= in_array($key, ['dashboard', 'events', 'calendar', 'notifications'], true) ? 'checked' : '' ?>>
                        <?= esc($label) ?>
                    </label>
                <?php endforeach; ?>
            </fieldset>
            <button class="primary-button" type="submit">Tambah Role</button>
        </form>
    </details>

    <details class="panel management-drawer" <?= old('crm_username') ? 'open' : '' ?>>
        <summary>
            <div>
                <span class="eyebrow">CRM</span>
                <strong>Tambah akun CRM</strong>
            </div>
            <span class="ghost-button compact-button">Buka</span>
        </summary>
        <form class="drawer-form crm-admin-create-form" action="<?= site_url('admin/crm-users') ?>" method="post">
            <?= csrf_field() ?>
            <label>
                Username
                <input class="control" type="text" name="crm_username" placeholder="username agent" value="<?= esc(old('crm_username') ?? '') ?>" required>
            </label>
            <label>
                Password
                <span class="password-field">
                    <input class="control" type="password" name="password" placeholder="Minimal 6 karakter" required minlength="6">
                    <button class="password-toggle" type="button" data-password-toggle aria-label="Lihat password" aria-pressed="false"></button>
                </span>
            </label>
            <label>
                Nama Lengkap
                <input class="control" type="text" name="full_name" placeholder="Nama agent" value="<?= esc(old('full_name') ?? '') ?>" required>
            </label>
            <label>
                Role CRM
                <select class="control" name="role" required>
                    <option value="Agent">Agent</option>
                    <option value="Admin">Admin</option>
                </select>
            </label>
            <label>
                Kampus Agent
                <select class="control" name="crm_campus_id">
                    <option value="">Pilih kampus</option>
                    <?php foreach ($campuses as $campus): ?>
                        <option value="<?= esc((string) $campus['id']) ?>"><?= esc($campus['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Status
                <select class="control" name="status" required>
                    <option value="Active">Active</option>
                    <option value="Review">Review</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </label>
            <button class="primary-button" type="submit">Tambah Akun CRM</button>
        </form>
    </details>
</section>

<section class="panel role-management-panel">
    <div class="panel-header">
        <div>
            <span class="eyebrow">Role</span>
            <h2>Kategori akses</h2>
        </div>
    </div>
    <div class="table-control-bar">
        <input class="control table-search-control" type="search" placeholder="Cari role atau akses" data-table-filter>
        <span class="table-page-summary" data-table-summary></span>
    </div>
    <div class="table-wrap">
        <table class="data-table compact-admin-table role-crud-table" data-paginated-table data-page-size="5">
            <thead>
                <tr>
                    <th>Role</th>
                    <th>Akses default</th>
                    <th>Tipe</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($roles as $role): ?>
                    <?php
                    $roleFormId = 'role-update-' . $role['id'];
                    $roleAccess = $roleDefaults[$role['name']] ?? [];
                    $isSystemRole = (int) $role['is_system'] === 1;
                    ?>
                    <tr data-page-row>
                        <td>
                            <form id="<?= esc($roleFormId) ?>" action="<?= site_url('roles/' . $role['id'] . '/update') ?>" method="post"><?= csrf_field() ?></form>
                            <strong class="table-title"><?= esc($role['name']) ?></strong>
                            <small><?= esc($role['description'] ?? 'Tanpa deskripsi') ?></small>
                        </td>
                        <td>
                            <div class="permission-pills">
                                <?php foreach ($roleAccess as $permission): ?>
                                    <span><?= esc($accessOptions[$permission] ?? $permission) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </td>
                        <td>
                            <span class="status <?= $isSystemRole ? 'status-aktif' : 'status-review' ?>"><?= $isSystemRole ? 'Sistem' : 'Custom' ?></span>
                        </td>
                        <td>
                            <details class="row-drawer">
                                <summary class="ghost-button compact-button">Kelola</summary>
                                <div class="row-edit-panel">
                                    <div class="form-grid compact-edit-grid">
                                        <label>
                                            Nama role
                                            <input class="control table-control" form="<?= esc($roleFormId) ?>" type="text" name="name" value="<?= esc($role['name']) ?>" required <?= $isSystemRole ? 'readonly' : '' ?>>
                                        </label>
                                        <label>
                                            Deskripsi
                                            <input class="control table-control" form="<?= esc($roleFormId) ?>" type="text" name="description" value="<?= esc($role['description'] ?? '') ?>" placeholder="Deskripsi role">
                                        </label>
                                    </div>
                                    <fieldset class="access-fieldset dense-access-fieldset">
                                        <legend>Akses default</legend>
                                        <?php foreach ($accessOptions as $key => $label): ?>
                                            <label class="check-row compact-check">
                                                <input form="<?= esc($roleFormId) ?>" type="checkbox" name="default_permissions[]" value="<?= esc($key) ?>" <?= in_array($key, $roleAccess, true) ? 'checked' : '' ?> <?= $isSystemRole ? 'disabled' : '' ?>>
                                                <?= esc($label) ?>
                                            </label>
                                        <?php endforeach; ?>
                                    </fieldset>
                                    <div class="button-row">
                                        <button class="primary-button compact-button" form="<?= esc($roleFormId) ?>" type="submit">Simpan Role</button>
                                        <?php if (! $isSystemRole): ?>
                                            <form action="<?= site_url('roles/' . $role['id'] . '/delete') ?>" method="post" class="inline-form">
                                                <?= csrf_field() ?>
                                                <button class="danger-button compact-button" type="submit">Hapus Role</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </details>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <nav class="pagination-bar table-pagination-bar" aria-label="Navigasi kategori akses" data-table-pagination></nav>
</section>

<section class="panel crm-admin-panel">
    <div class="panel-header">
        <div>
            <span class="eyebrow">CRM</span>
            <h2>Akun CRM Agent</h2>
        </div>
        <a class="ghost-button compact-button" href="<?= site_url('crm') ?>">Buka CRM</a>
    </div>
    <div class="table-control-bar">
        <input class="control table-search-control" type="search" placeholder="Cari akun CRM, role, status, atau kampus" data-table-filter>
        <span class="table-page-summary" data-table-summary></span>
    </div>
    <div class="table-wrap">
        <table class="data-table compact-admin-table crm-admin-table" data-paginated-table data-page-size="5">
            <thead>
                <tr>
                    <th>User CRM</th>
                    <th>Hak Akses</th>
                    <th>Status</th>
                    <th>Terakhir Login</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($crmUsers as $crmUser): ?>
                    <?php
                    $crmFormId = 'crm-user-update-' . $crmUser['id'];
                    $isCrmDeleted = ! empty($crmUser['deleted_at']);
                    $crmCampusName = $crmUser['campus_name'] ?: 'Tanpa kampus';
                    $crmStatusClass = ['Active' => 'status-aktif', 'Review' => 'status-review', 'Inactive' => 'status-nonaktif'][$crmUser['status']] ?? 'status-nonaktif';
                    $crmSearchText = implode(' ', [
                        $crmUser['full_name'] ?? '',
                        $crmUser['username'] ?? '',
                        $crmUser['role'] ?? '',
                        $crmUser['status'] ?? '',
                        $crmCampusName,
                        $crmUser['registered_by_name'] ?: 'Admin Sistem',
                        $isCrmDeleted ? 'Terhapus' : '',
                    ]);
                    ?>
                    <tr class="<?= $isCrmDeleted ? 'is-muted-row' : '' ?>" data-page-row data-search-text="<?= esc($crmSearchText, 'attr') ?>">
                        <td>
                            <form id="<?= esc($crmFormId) ?>" action="<?= site_url('admin/crm-users/' . $crmUser['id'] . '/update') ?>" method="post"><?= csrf_field() ?></form>
                            <strong class="table-title"><?= esc($crmUser['full_name']) ?></strong>
                            <small><?= esc($crmUser['username']) ?></small>
                            <small><?= esc($crmCampusName) ?></small>
                            <small>Didaftarkan oleh <?= esc($crmUser['registered_by_name'] ?: 'Admin Sistem') ?></small>
                        </td>
                        <td>
                            <span class="status <?= $crmUser['role'] === 'Admin' ? 'status-calendar' : 'status-aktif' ?>"><?= esc($crmUser['role']) ?></span>
                            <small><?= $crmUser['role'] === 'Admin' ? 'Akses semua fitur CRM' : 'Hanya data/tugas yang diassign' ?></small>
                        </td>
                        <td>
                            <span class="status <?= esc($crmStatusClass) ?>"><?= esc($crmUser['status']) ?></span>
                            <?php if ($isCrmDeleted): ?>
                                <span class="status status-nonaktif">Terhapus</span>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($crmUser['last_login_at'] ?: '-') ?></td>
                        <td>
                            <details class="row-drawer">
                                <summary class="ghost-button compact-button">Kelola</summary>
                                <div class="row-edit-panel">
                                    <div class="form-grid compact-edit-grid">
                                        <label>
                                            Nama Lengkap
                                            <input class="control table-control" form="<?= esc($crmFormId) ?>" type="text" name="full_name" value="<?= esc($crmUser['full_name']) ?>" required <?= $isCrmDeleted ? 'disabled' : '' ?>>
                                        </label>
                                        <label>
                                            Username
                                            <input class="control table-control" form="<?= esc($crmFormId) ?>" type="text" name="username" value="<?= esc($crmUser['username']) ?>" required <?= $isCrmDeleted ? 'disabled' : '' ?>>
                                        </label>
                                        <label>
                                            Role CRM
                                            <select class="control table-control" form="<?= esc($crmFormId) ?>" name="role" required <?= $isCrmDeleted ? 'disabled' : '' ?>>
                                                <?php foreach (['Agent', 'Admin'] as $role): ?>
                                                    <option value="<?= esc($role) ?>" <?= $crmUser['role'] === $role ? 'selected' : '' ?>><?= esc($role) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </label>
                                        <label>
                                            Kampus Agent
                                            <select class="control table-control" form="<?= esc($crmFormId) ?>" name="crm_campus_id" <?= $isCrmDeleted ? 'disabled' : '' ?>>
                                                <option value="">Pilih kampus</option>
                                                <?php foreach ($campuses as $campus): ?>
                                                    <option value="<?= esc((string) $campus['id']) ?>" <?= (string) ($crmUser['campus_id'] ?? '') === (string) $campus['id'] ? 'selected' : '' ?>>
                                                        <?= esc($campus['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </label>
                                        <label>
                                            Status
                                            <select class="control table-control" form="<?= esc($crmFormId) ?>" name="status" required <?= $isCrmDeleted ? 'disabled' : '' ?>>
                                                <?php foreach (['Active', 'Review', 'Inactive'] as $status): ?>
                                                    <option value="<?= esc($status) ?>" <?= $crmUser['status'] === $status ? 'selected' : '' ?>><?= esc($status) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </label>
                                    </div>
                                    <div class="button-row">
                                        <?php if (! $isCrmDeleted): ?>
                                            <button class="primary-button compact-button" form="<?= esc($crmFormId) ?>" type="submit">Simpan Akun CRM</button>
                                            <form action="<?= site_url('admin/crm-users/' . $crmUser['id'] . '/reset-password') ?>" method="post" class="inline-reset-form">
                                                <?= csrf_field() ?>
                                                <span class="password-field compact-password-field">
                                                    <input class="control table-control" type="password" name="password" placeholder="Password baru" required minlength="6">
                                                    <button class="password-toggle" type="button" data-password-toggle aria-label="Lihat password" aria-pressed="false"></button>
                                                </span>
                                                <button class="ghost-button compact-button" type="submit">Reset Password</button>
                                            </form>
                                            <?php if (($crmUser['status'] ?? '') === 'Inactive'): ?>
                                                <form action="<?= site_url('admin/crm-users/' . $crmUser['id'] . '/delete') ?>" method="post" class="inline-form">
                                                    <?= csrf_field() ?>
                                                    <button class="danger-button compact-button" type="submit">Hapus Akun CRM</button>
                                                </form>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <form action="<?= site_url('admin/crm-users/' . $crmUser['id'] . '/restore') ?>" method="post" class="inline-form">
                                                <?= csrf_field() ?>
                                                <button class="primary-button compact-button" type="submit">Aktifkan Akun CRM</button>
                                            </form>
                                            <form action="<?= site_url('admin/crm-users/' . $crmUser['id'] . '/force-delete') ?>" method="post" class="inline-form">
                                                <?= csrf_field() ?>
                                                <button class="danger-button compact-button" type="submit">Hapus dari Database</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </details>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($crmUsers === []): ?>
                    <tr data-empty-row>
                        <td colspan="5">Belum ada akun CRM.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <nav class="pagination-bar table-pagination-bar" aria-label="Navigasi akun CRM" data-table-pagination></nav>
</section>

<section class="panel">
    <div class="panel-header">
        <div>
            <span class="eyebrow">User</span>
            <h2>Daftar akun</h2>
        </div>
    </div>
    <div class="table-control-bar">
        <input class="control table-search-control" type="search" placeholder="Cari nama, username, email, role, status, atau kampus" data-table-filter>
        <span class="table-page-summary" data-table-summary></span>
    </div>
    <div class="table-wrap">
        <table class="data-table compact-admin-table user-crud-table" data-paginated-table data-page-size="5">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Akses utama</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <?php
                    $formId = 'user-update-' . $user['id'];
                    $isDeleted = ! empty($user['deleted_at']);
                    $userAccess = json_decode((string) ($user['access_permissions'] ?? '[]'), true);
                    $userAccess = is_array($userAccess) ? $userAccess : [];
                    $userCampusName = $campusNamesById[(int) ($user['campus_id'] ?? 0)] ?? 'Kampus belum dipilih';
                    $userSearchText = implode(' ', [
                        $user['name'] ?? '',
                        $user['username'] ?? '',
                        $user['email'] ?? '',
                        $user['role'] ?? '',
                        $user['status'] ?? '',
                        $userCampusName,
                        implode(' ', array_map(static fn (string $permission): string => (string) ($accessOptions[$permission] ?? $permission), $userAccess)),
                        $isDeleted ? 'Terhapus' : '',
                    ]);
                    ?>
                    <tr class="<?= $isDeleted ? 'is-muted-row' : '' ?>" data-page-row data-search-text="<?= esc($userSearchText, 'attr') ?>">
                        <td>
                            <form id="<?= esc($formId) ?>" action="<?= site_url('users/' . $user['id'] . '/update') ?>" method="post"><?= csrf_field() ?></form>
                            <strong class="table-title"><?= esc($user['name']) ?></strong>
                            <small><?= esc($user['username'] ?? '-') ?> - <?= esc($user['email']) ?></small>
                        </td>
                        <td>
                            <?= esc($user['role']) ?>
                            <?php if ($isCampusScopedRole((string) ($user['role'] ?? ''))): ?>
                                <small><?= esc($userCampusName) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status status-<?= esc(strtolower((string) $user['status'])) ?>"><?= esc($user['status']) ?></span>
                            <?php if ($isDeleted): ?>
                                <span class="status status-nonaktif">Terhapus</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="permission-pills">
                                <?php foreach ($userAccess as $permission): ?>
                                    <span><?= esc($accessOptions[$permission] ?? $permission) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </td>
                        <td>
                            <details class="row-drawer">
                                <summary class="ghost-button compact-button">Kelola</summary>
                                <div class="row-edit-panel">
                                    <div class="form-grid compact-edit-grid">
                                        <label>
                                            Nama
                                            <input class="control table-control" form="<?= esc($formId) ?>" type="text" name="name" value="<?= esc($user['name']) ?>" required <?= $isDeleted ? 'disabled' : '' ?>>
                                        </label>
                                        <label>
                                            Username
                                            <input class="control table-control" form="<?= esc($formId) ?>" type="text" name="username" value="<?= esc($user['username'] ?? '') ?>" required <?= $isDeleted ? 'disabled' : '' ?>>
                                        </label>
                                        <label>
                                            Email
                                            <input class="control table-control" form="<?= esc($formId) ?>" type="email" name="email" value="<?= esc($user['email']) ?>" required <?= $isDeleted ? 'disabled' : '' ?>>
                                        </label>
                                        <label>
                                            Role
                                            <select class="control table-control" form="<?= esc($formId) ?>" name="role" required data-campus-role-select <?= $isDeleted ? 'disabled' : '' ?>>
                                                <?php foreach ($roles as $role): ?>
                                                    <option value="<?= esc($role['name']) ?>" <?= $user['role'] === $role['name'] ? 'selected' : '' ?>><?= esc($role['name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </label>
                                        <label data-campus-role-field>
                                            Kampus pelaksana
                                            <select class="control table-control" form="<?= esc($formId) ?>" name="campus_id" <?= $isDeleted ? 'disabled' : '' ?>>
                                                <option value="">Pilih kampus</option>
                                                <?php foreach ($campuses as $campus): ?>
                                                    <option value="<?= esc((string) $campus['id']) ?>" <?= (string) ($user['campus_id'] ?? '') === (string) $campus['id'] ? 'selected' : '' ?>>
                                                        <?= esc($campus['name']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </label>
                                        <label>
                                            Status
                                            <select class="control table-control" form="<?= esc($formId) ?>" name="status" required <?= $isDeleted ? 'disabled' : '' ?>>
                                                <?php foreach (['Aktif', 'Review', 'Nonaktif'] as $status): ?>
                                                    <option <?= $user['status'] === $status ? 'selected' : '' ?>><?= esc($status) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </label>
                                        <label>
                                            Password baru
                                            <span class="password-field compact-password-field">
                                                <input class="control table-control" form="<?= esc($formId) ?>" type="password" name="password" placeholder="Kosongkan jika tidak diubah" minlength="6" <?= $isDeleted ? 'disabled' : '' ?>>
                                                <button class="password-toggle" type="button" data-password-toggle aria-label="Lihat password" aria-pressed="false" <?= $isDeleted ? 'disabled' : '' ?>></button>
                                            </span>
                                        </label>
                                    </div>
                                    <fieldset class="access-fieldset dense-access-fieldset">
                                        <legend>Akses utama</legend>
                                        <?php foreach ($accessOptions as $key => $label): ?>
                                            <label class="check-row compact-check">
                                                <input form="<?= esc($formId) ?>" type="checkbox" name="access_permissions[]" value="<?= esc($key) ?>" <?= in_array($key, $userAccess, true) ? 'checked' : '' ?> <?= $isDeleted ? 'disabled' : '' ?>>
                                                <?= esc($label) ?>
                                            </label>
                                        <?php endforeach; ?>
                                    </fieldset>
                                    <div class="button-row">
                                        <?php if (! $isDeleted): ?>
                                            <button class="primary-button compact-button" form="<?= esc($formId) ?>" type="submit">Simpan User</button>
                                            <form action="<?= site_url('users/' . $user['id'] . '/reset-password') ?>" method="post" class="inline-reset-form">
                                                <?= csrf_field() ?>
                                                <span class="password-field compact-password-field">
                                                    <input class="control table-control" type="password" name="password" placeholder="Password baru" required minlength="6">
                                                    <button class="password-toggle" type="button" data-password-toggle aria-label="Lihat password" aria-pressed="false"></button>
                                                </span>
                                                <button class="ghost-button compact-button" type="submit">Reset Password</button>
                                            </form>
                                            <form action="<?= site_url('users/' . $user['id'] . '/delete') ?>" method="post" class="inline-form">
                                                <?= csrf_field() ?>
                                                <button class="danger-button compact-button" type="submit">Hapus User</button>
                                            </form>
                                        <?php else: ?>
                                            <form action="<?= site_url('users/' . $user['id'] . '/restore') ?>" method="post" class="inline-form">
                                                <?= csrf_field() ?>
                                                <button class="primary-button compact-button" type="submit">Aktifkan User</button>
                                            </form>
                                            <form action="<?= site_url('users/' . $user['id'] . '/force-delete') ?>" method="post" class="inline-form">
                                                <?= csrf_field() ?>
                                                <button class="danger-button compact-button" type="submit">Hapus dari Database</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </details>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($users === []): ?>
                    <tr data-empty-row>
                        <td colspan="5">Belum ada akun.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <nav class="pagination-bar table-pagination-bar" aria-label="Navigasi daftar akun" data-table-pagination></nav>
</section>
