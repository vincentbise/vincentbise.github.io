<?php
$user = $user ?? null;
$isEdit    = !empty($user);
$pageTitle = $isEdit ? 'Edit Account' : 'Add Account';
include VIEW_PATH . '/layouts/header.php';
?>

<div class="wrap">
    <main class="content">

        <section class="hero">
            <div>
                <h1><?= $isEdit ? 'Edit Account' : 'Create New Account' ?></h1>
                <p><?= $isEdit ? 'Update user information and role.' : 'Add a new user to the system.' ?></p>
            </div>
        </section>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
    <section class="panel active form-panel form-panel-wide">
            <form method="POST"
                  action="<?= BASE_URL ?><?= $isEdit ? 'admin/accounts/update' : 'admin/accounts/store' ?>"
                  data-ajax-url="<?= BASE_URL ?><?= $isEdit ? 'api/accounts/update' : 'api/accounts/store' ?>"
                  id="account-form" novalidate>
                <?= Controller::csrfField() ?>

                <?php if ($isEdit): ?>
                    <input type="hidden" name="user_id" value="<?= (int)($user['user_id'] ?? 0) ?>"/>
                <?php endif; ?>

                <div class="detail-grid">
                    <div class="detail-section">
                        <h3 class="section-title">Account Details</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="full_name">Full Name <span class="required">*</span></label>
                                <input type="text" id="full_name" name="full_name"
                                       value="<?= htmlspecialchars($user['full_name'] ?? '') ?>"
                                       placeholder="First Middle Last" required/>
                            </div>

                            <div class="form-group">
                                <label for="email">Email Address <span class="required">*</span></label>
                                <input type="email" id="email" name="email"
                                       value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                                       placeholder="name@usep.edu.ph" required/>
                            </div>

                            <?php if (!$isEdit): ?>
                            <div class="form-group">
                                <label for="username">Username <span class="required">*</span></label>
                                <input type="text" id="username" name="username"
                                       placeholder="Unique login username" required/>
                            </div>
                            <?php endif; ?>

                            <div class="form-group <?= $isEdit ? 'span-2' : '' ?>">
                                <label for="password">
                                    Password <?= $isEdit ? '(leave blank to keep current)' : '<span class="required">*</span>' ?>
                                </label>
                                <input type="password" id="password" name="password"
                                       placeholder="<?= $isEdit ? 'Enter new password to change' : 'Set password' ?>"
                                       <?= $isEdit ? '' : 'required' ?>/>
                            </div>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h3 class="section-title">Role & Contact</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="role">Role <span class="required">*</span></label>
                                <select id="role" name="role" required>
                                    <?php
                                    $roles = [
                                        'admin'           => 'Administrator',
                                        'staff'           => 'Staff',
                                        'requester'       => 'Requester',
                                        'driver'          => 'Driver',
                                    ];
                                    $current = $user['role'] ?? 'requester';
                                    foreach ($roles as $val => $label):
                                    ?>
                                    <option value="<?= $val ?>" <?= $current === $val ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="department">Department / Office</label>
                                <input type="text" id="department" name="department"
                                       value="<?= htmlspecialchars($user['department'] ?? '') ?>"
                                       placeholder="e.g. College of Engineering"/>
                            </div>

                            <div class="form-group span-2">
                                <label for="contact_no">Contact Number</label>
                                <input type="text" id="contact_no" name="contact_no"
                                       value="<?= htmlspecialchars($user['contact_no'] ?? '') ?>"
                                       placeholder="09XX XXX XXXX"/>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="<?= BASE_URL ?>admin/accounts" class="btn-outline">Cancel</a>
                    <button type="submit" class="btn-primary">
                        <?= $isEdit ? 'Save Changes' : 'Create Account' ?>
                    </button>
                </div>

            </form>
        </section>

    </main>
</div>

<?php include VIEW_PATH . '/layouts/footer.php'; ?>
<script>

    const form = document.getElementById('account-form');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            await VRS.ajax.submitForm(form, {
                onSuccess: (data) => {
                    VRS.notify.success(data.message);
                    if (data.redirect) {
                        setTimeout(() => { window.location.href = data.redirect; }, 800);
                    }
                },
            });
        });
    }
</script>