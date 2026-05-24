<?php
$pageTitle = 'My Requests';
include VIEW_PATH . '/layouts/header.php';
?>

<div class="wrap">
    <main class="content">

        <section class="hero">
            <div>
                <h1>My Reservations</h1>
                <p>Track all your vehicle reservation requests.</p>
            </div>
            <a href="<?= BASE_URL ?>requester/new" class="btn-primary">+ New Request</a>
        </section>

        <?php if (!empty($flash)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
        <?php endif; ?>

        <section class="panel active">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Ref No.</th>
                            <th>Destination</th>
                            <th>Departure</th>
                            <th>Return</th>
                            <th>Vehicle</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($requests)): ?>
                        <tr><td colspan="7" class="empty-row">You have no reservation requests yet.</td></tr>
                    <?php else: ?>
                    <?php foreach ($requests as $r): ?>
                        <tr id="request-row-<?= (int)$r['reservation_id'] ?>">
                            <td><?= htmlspecialchars($r['reference_no']) ?></td>
                            <td><?= htmlspecialchars($r['destination']) ?></td>
                            <td><?= htmlspecialchars($r['departure_date'] . ' ' . $r['departure_time']) ?></td>
                            <td><?= htmlspecialchars($r['return_date'] . ' ' . $r['return_time']) ?></td>
                            <td><?= $r['make_model'] ? htmlspecialchars($r['make_model'] . ' (' . $r['plate_number'] . ')') : '—' ?></td>
                            <td><span class="badge badge-<?= $r['status'] ?>"><?= ucfirst(str_replace('_',' ',$r['status'])) ?></span></td>
                            <td>
                                <?php if ($r['status'] === 'pending'): ?>
                                    <form method="POST" action="<?= BASE_URL ?>requester/cancel"
                                          data-ajax-url="<?= BASE_URL ?>api/reservations/cancel"
                                          class="ajax-cancel-form">
                                        <?= Controller::csrfField() ?>
                                        <input type="hidden" name="reservation_id" value="<?= (int)$r['reservation_id'] ?>"/>
                                        <button type="submit" class="btn-danger btn-sm">Cancel</button>
                                    </form>
                                <?php else: ?>
                                    <span class="muted">—</span>
                                <?php endif; ?>
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

    document.querySelectorAll('.ajax-cancel-form').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const ok = await VRS.confirm.show('Cancel this reservation?');
            if (!ok) return;

            const btn = form.querySelector('button[type="submit"]');
            const result = await VRS.ajax.submitForm(form, {
                submitBtn: btn,
                onSuccess: (data) => {
                    VRS.notify.success(data.message);

                    const row = form.closest('tr');
                    if (row) {
                        const badgeCell = row.querySelector('.badge');
                        if (badgeCell) {
                            badgeCell.className = 'badge badge-cancelled';
                            badgeCell.textContent = 'Cancelled';
                        }

                        const actionCell = form.parentElement;
                        form.remove();
                        actionCell.innerHTML = '<span class="muted">—</span>';
                    }
                },
            });
        });
    });
</script>
