<?php
$user = $user ?? null;
$pageTitle = 'Edit Account';
include VIEW_PATH . '/layouts/header.php';
?>

<div class="wrap">
    <main class="content">

        <section class="hero">
            <div>
                <h1>Edit Account</h1>
                <p>Update your personal information and contact details.</p>
            </div>
        </section>

        <?php if (!empty($flash)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <section class="panel active form-panel form-panel-wide">
            <form method="POST"
                  action="<?= BASE_URL ?>account/update"
                  data-ajax-url="<?= BASE_URL ?>api/account/update"
                  id="profile-form" novalidate>
                <?= Controller::csrfField() ?>

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

                    <div class="form-group">
                        <label for="password">Password (leave blank to keep current)</label>
                        <input type="password" id="password" name="password"
                               placeholder="Enter new password to change"/>
                    </div>

                    <div class="form-group">
                        <label for="department">Department / Office</label>
                        <input type="text" id="department" name="department"
                               value="<?= htmlspecialchars($user['department'] ?? '') ?>"
                               placeholder="e.g. College of Engineering"/>
                    </div>

                    <div class="form-group">
                        <label for="contact_no">Contact Number</label>
                        <input type="text" id="contact_no" name="contact_no"
                               value="<?= htmlspecialchars($user['contact_no'] ?? '') ?>"
                               placeholder="09XX XXX XXXX"/>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </section>

    </main>
</div>

<?php include VIEW_PATH . '/layouts/footer.php'; ?>
<script>
    const form = document.getElementById('profile-form');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            await VRS.ajax.submitForm(form, {
                onSuccess: (data) => {
                    VRS.notify.success(data.message || 'Profile updated successfully.');
                },
            });
        });
    }
</script>