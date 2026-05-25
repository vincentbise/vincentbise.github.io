<?php
$pageTitle = 'Pending Approvals';
include VIEW_PATH . '/layouts/header.php';
$levelLabel = 'Staff Review';
$levelDesc  = 'Approve or reject reservation requests.';
?>

<div class="wrap">
    <main class="content">

        <section class="hero">
            <div>
                <h1><?= $levelLabel ?></h1>
                <p><?= $levelDesc ?></p>
            </div>
        </section>

        <?php if (!empty($flash)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (empty($reservations)): ?>
            <div class="panel active">
                <p class="empty-row">✅ No pending requests at this time. Check back later.</p>
            </div>
        <?php else: ?>
        <?php foreach ($reservations as $r): ?>
        <section class="panel active approval-card" id="approval-card-<?= (int)$r['reservation_id'] ?>">

            <div class="approval-header">
                <div>
                    <span class="trip-ref"><?= htmlspecialchars($r['reference_no']) ?></span>
                    <span class="badge badge-pending" style="margin-left:8px">Awaiting Review</span>
                </div>
                <span class="muted" style="font-size:12px">
                    Requested: <?= date('M d, Y', strtotime($r['requested_at'])) ?>
                </span>
            </div>

            <div class="approval-details">
                <div><strong>Requester:</strong> <?= htmlspecialchars($r['requester_name']) ?></div>
                <div><strong>Department:</strong> <?= htmlspecialchars($r['department'] ?? '—') ?></div>
                <div><strong>Destination:</strong> <?= htmlspecialchars($r['destination']) ?></div>
                <div><strong>Vehicle:</strong> <?= htmlspecialchars(($r['make_model'] ?? '—') . ' (' . ($r['plate_number'] ?? '—') . ')') ?></div>
                <?php if (!empty($r['requester_remarks'])): ?>
                <div class="full-detail"><strong>Requester Justification:</strong> <?= nl2br(htmlspecialchars($r['requester_remarks'])) ?></div>
                <?php endif; ?>
                <div><strong>Departure:</strong>  <?= htmlspecialchars($r['departure_date'] . ' ' . $r['departure_time']) ?></div>
                <div><strong>Return:</strong>     <?= htmlspecialchars($r['return_date']   . ' ' . $r['return_time']) ?></div>
                <div><strong>Passengers:</strong> <?= (int)$r['passengers'] ?></div>
                <div class="full-detail"><strong>Purpose:</strong> <?= nl2br(htmlspecialchars($r['purpose'])) ?></div>
            </div>


            <?php $hasDriver = !empty($r['assigned_driver_id']); ?>
            <form method="POST" action="<?= BASE_URL ?>approvals/decide"
                  data-ajax-url="<?= BASE_URL ?>api/approvals/decide"
                data-has-driver="<?= $hasDriver ? '1' : '0' ?>"
                  class="decision-form-block ajax-decision-form">
                <?= Controller::csrfField() ?>
                <input type="hidden" name="reservation_id" value="<?= (int)$r['reservation_id'] ?>"/>

                <div class="form-group">
                    <label for="remarks_<?= $r['reservation_id'] ?>">Remarks (optional)</label>
                    <textarea id="remarks_<?= $r['reservation_id'] ?>"
                              name="remarks" rows="2"
                              placeholder="Add a note or reason for your decision…"></textarea>
                </div>

                <div class="decision-actions">
                    <button type="submit" name="decision" value="approved"
                        class="btn-success decision-btn">✔ Approve</button>
                    <button type="submit" name="decision" value="rejected"
                        class="btn-danger decision-btn">✘ Reject</button>
                </div>
            </form>

        </section>
        <?php endforeach; ?>
        <?php endif; ?>

    </main>
</div>

<?php include VIEW_PATH . '/layouts/footer.php'; ?>
<script>

    document.querySelectorAll('.ajax-decision-form').forEach(form => {
        const buttons = form.querySelectorAll('button[type="submit"]');

        buttons.forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();

                const decision = btn.value;
                if (decision === 'approved') {
                    const hasDriver = form.getAttribute('data-has-driver') === '1';
                    if (!hasDriver) {
                        const ok = await VRS.confirm.show(
                            'This vehicle has no assigned driver. Approval will be blocked. Continue?'
                        );
                        if (!ok) return;
                    }
                }

                if (decision === 'rejected') {
                    const ok = await VRS.confirm.show('Reject this request?');
                    if (!ok) return;
                }


                const formData = new FormData(form);
                formData.append('decision', decision);

                btn.disabled = true;
                btn.textContent = 'Processing…';

                try {
                    const result = await VRS.ajax.post(
                        form.getAttribute('data-ajax-url').replace(window.location.origin, '').replace(
                            document.querySelector('meta[name="base-url"]')?.getAttribute('content') || '/', ''
                        ),
                        formData
                    );

                    if (result.success) {
                        VRS.notify.success(result.message);

                        const card = form.closest('.approval-card');
                        if (card) {
                            card.style.transition = 'opacity 0.4s, transform 0.4s';
                            card.style.opacity = '0';
                            card.style.transform = 'translateX(40px)';
                            setTimeout(() => card.remove(), 400);
                        }


                        setTimeout(() => {
                            const remaining = document.querySelectorAll('.approval-card');
                            if (remaining.length === 0) {
                                const content = document.querySelector('.content');
                                const emptyPanel = document.createElement('div');
                                emptyPanel.className = 'panel active';
                                emptyPanel.innerHTML = '<p class="empty-row">✅ No pending requests at this time. Check back later.</p>';
                                content.appendChild(emptyPanel);
                            }
                        }, 500);
                    } else {
                        VRS.notify.error(result.message);
                    }
                } catch (err) {
                    VRS.notify.error(err.message || 'An error occurred.');
                } finally {
                    btn.disabled = false;
                    btn.textContent = decision === 'approved' ? '✔ Approve' : '✘ Reject';
                }
            });
        });
    });
</script>