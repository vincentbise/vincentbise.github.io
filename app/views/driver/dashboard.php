<?php
$pageTitle = 'Driver Dashboard';
include VIEW_PATH . '/layouts/header.php';
?>
<div class="wrap">
    <main class="content">

        <section class="hero">
            <div>
                <h1>Driver Dashboard</h1>
                <p>Hello, <?= htmlspecialchars($_SESSION['full_name']) ?>. Here are your assigned trips.</p>
            </div>
        </section>

        <?php if (!empty($flash)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
        <?php endif; ?>

        <?php if (empty($trips)): ?>
            <div class="panel active">
                <p class="empty-row">No trips currently assigned to you.</p>
            </div>
        <?php else: ?>
            <?php foreach ($trips as $t): ?>
            <section class="panel active trip-card" id="trip-card-<?= (int)$t['reservation_id'] ?>">
                <div class="trip-header">
                    <span class="trip-ref"><?= htmlspecialchars($t['reference_no']) ?></span>
                    <span class="badge badge-<?= $t['status'] ?>"><?= ucfirst(str_replace('_',' ',$t['status'])) ?></span>
                </div>
                <div class="trip-details">
                    <div><strong>Destination:</strong> <?= htmlspecialchars($t['destination']) ?></div>
                    <div><strong>Departure:</strong>
                        <?= date('M d, Y', strtotime($t['departure_date'])) ?>
                        <?= $t['departure_time'] ? date('h:i A', strtotime($t['departure_time'])) : '' ?>
                    </div>
                    <div><strong>Return:</strong>
                        <?= date('M d, Y', strtotime($t['return_date'])) ?>
                        <?= $t['return_time'] ? date('h:i A', strtotime($t['return_time'])) : '' ?>
                    </div>
                    <div><strong>Vehicle:</strong> <?= htmlspecialchars($t['make_model'] ?? '—') ?> (<?= htmlspecialchars($t['plate_number'] ?? '—') ?>)</div>
                    <div><strong>Passengers:</strong> <?= (int)$t['passengers'] ?></div>
                </div>

                <?php if ($t['status'] === 'approved'): ?>
                    <form method="POST" action="<?= BASE_URL ?>driver/dispatch"
                          data-ajax-url="<?= BASE_URL ?>api/driver/dispatch"
                          class="ajax-driver-form">
                        <?= Controller::csrfField() ?>
                        <input type="hidden" name="reservation_id" value="<?= (int)$t['reservation_id'] ?>"/>
                        <div class="form-group" style="margin-bottom:12px;">
                            <label>Passengers Present <span class="required">*</span></label>
                            <input type="number" name="actual_passengers" min="1"
                                   max="<?= (int)$t['passengers'] ?>" required
                                   placeholder="Enter actual passenger count"/>
                        </div>
                        <button type="submit" class="btn-primary btn-trip-action">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                            Start Trip
                        </button>
                    </form>

                <?php elseif ($t['status'] === 'dispatched'): ?>
                    <form method="POST" action="<?= BASE_URL ?>driver/complete"
                          data-ajax-url="<?= BASE_URL ?>api/driver/complete"
                          class="ajax-driver-form">
                        <?= Controller::csrfField() ?>
                        <input type="hidden" name="reservation_id" value="<?= (int)$t['reservation_id'] ?>"/>
                        <div class="form-group" style="margin-bottom:12px;">
                            <label>Trip Notes <span class="optional-tag">Optional</span></label>
                            <textarea name="trip_notes" rows="2" placeholder="Any notes about this trip..."></textarea>
                        </div>
                        <button type="submit" class="btn-success btn-trip-action">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Complete Trip
                        </button>
                    </form>
                <?php endif; ?>
            </section>
            <?php endforeach; ?>
        <?php endif; ?>

    </main>
</div>

<?php include VIEW_PATH . '/layouts/footer.php'; ?>
<script src="<?= BASE_URL ?>public/js/dashboard.js"></script>
<script>

    document.querySelectorAll('.ajax-driver-form').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = form.querySelector('button[type="submit"]');
            const isStart = btn.textContent.trim().includes('Start');
            if (isStart) {
                const paxInput = form.querySelector('input[name="actual_passengers"]');
                const paxVal = paxInput ? parseInt(paxInput.value || '0', 10) : 0;
                if (!paxVal || paxVal < 1) {
                    VRS.notify.warning('Please enter the passenger count before starting the trip.');
                    if (paxInput) paxInput.focus();
                    return;
                }
            }
            const msg = isStart
                ? 'Are you sure you want to start this trip?'
                : 'Are you sure you want to mark this trip as complete?';

            const ok = await VRS.confirm.show(msg);
            if (!ok) return;

            const result = await VRS.ajax.submitForm(form, {
                submitBtn: btn,
                onSuccess: (data) => {
                    VRS.notify.success(data.message);

                    setTimeout(() => window.location.reload(), 1200);
                },
            });
        });
    });
</script>