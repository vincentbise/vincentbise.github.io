<?php
$pageTitle = 'Manage Accounts';
include VIEW_PATH . '/layouts/header.php';
?>

<div class="wrap">
    <main class="content">

        <section class="hero">
            <div>
                <h1>Manage Accounts</h1>
                <p>View, create, and manage all system user accounts.</p>
            </div>
            <a href="<?= BASE_URL ?>admin/accounts/create" class="btn-primary">+ Add Account</a>
        </section>

        <?php if (!empty($flash)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
        <?php endif; ?>

        <section class="panel active">

            <div class="panel-header">
                <h3>All Accounts</h3>
                <input type="text" id="account-search"
                       placeholder="Search name, role, department…"
                       class="search-input"/>
            </div>

            <div class="table-wrap">
                <table id="accounts-table">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Full Name</th>
                            <th>Role</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="7" class="empty-row">No accounts found.</td></tr>
                    <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <tr id="user-row-<?= (int)$u['user_id'] ?>">
                            <td><?= (int)$u['user_id'] ?></td>
                            <td><?= htmlspecialchars($u['full_name']) ?></td>
                            <td>
                                <span class="role-tag role-<?= $u['role'] ?>">
                                    <?= ucwords(str_replace('_', ' ', $u['role'])) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($u['department'] ?? '—') ?></td>
                            <td>
                                <span class="badge <?= $u['is_active'] ? 'badge-available' : 'badge-retired' ?>"
                                      id="status-badge-<?= (int)$u['user_id'] ?>">
                                    <?= $u['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                            <td class="action-cell">
                                <a href="<?= BASE_URL ?>admin/accounts/edit?id=<?= (int)$u['user_id'] ?>"
                                   class="btn-sm">Edit</a>
                                <form method="POST" action="<?= BASE_URL ?>admin/accounts/toggle"
                                      class="ajax-toggle-form" style="display:inline"
                                      data-ajax-url="<?= BASE_URL ?>api/accounts/toggle">
                                    <?= Controller::csrfField() ?>
                                    <input type="hidden" name="user_id" value="<?= (int)$u['user_id'] ?>"/>
                                    <button type="submit" class="btn-sm <?= $u['is_active'] ? 'btn-warn' : 'btn-ok' ?>"
                                            data-is-active="<?= $u['is_active'] ? '1' : '0' ?>">
                                        <?= $u['is_active'] ? 'Deactivate' : 'Activate' ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </main>
</div>

<?php include VIEW_PATH . '/layouts/footer.php'; ?>
<script>

    document.getElementById('account-search').addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#accounts-table tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });


    document.querySelectorAll('.ajax-toggle-form').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = form.querySelector('button[type="submit"]');
            const isActive = btn.dataset.isActive === '1';

            const ok = await VRS.confirm.show(isActive ? 'Deactivate this account?' : 'Activate this account?');
            if (!ok) return;

            const result = await VRS.ajax.submitForm(form, {
                submitBtn: btn,
                onSuccess: (data) => {
                    VRS.notify.success(data.message);

                    const newActive = !isActive;
                    btn.dataset.isActive = newActive ? '1' : '0';
                    btn.textContent = newActive ? 'Deactivate' : 'Activate';
                    btn.className = 'btn-sm ' + (newActive ? 'btn-warn' : 'btn-ok');


                    const userId = form.querySelector('input[name="user_id"]').value;
                    const badge = document.getElementById('status-badge-' + userId);
                    if (badge) {
                        badge.textContent = newActive ? 'Active' : 'Inactive';
                        badge.className = 'badge ' + (newActive ? 'badge-available' : 'badge-retired');
                    }
                },
            });
        });
    });
</script>
