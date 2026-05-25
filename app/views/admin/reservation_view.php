<?php
/** @var array $reservation */
/** @var array $approvals */
/** @var array|null $dispatchLog */
/** @var string|null $flash */
$pageTitle = 'Reservation Detail – ' . htmlspecialchars($reservation['reference_no']);
include VIEW_PATH . '/layouts/header.php';
?>
<div class="wrap">
    <main class="content">

        <section class="hero">
            <div>
                <h1><?= htmlspecialchars($reservation['reference_no']) ?></h1>
                <p>Full reservation details and approval history.</p>
            </div>
            <a href="<?= BASE_URL ?>admin/reservations" class="btn-outline">← Back</a>
        </section>

        <?php if (!empty($flash)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
        <?php endif; ?>

        <!-- Details Card -->
        <section class="panel active detail-grid">
            <div class="detail-section">
                <h3>Request Details</h3>
                <div class="detail-meta">
                    <div class="detail-meta-item">
                        <div class="detail-meta-label">Status</div>
                        <div class="detail-meta-value">
                            <span class="badge badge-<?= $reservation['status'] ?>">
                                <?= ucwords(str_replace('_',' ',$reservation['status'])) ?>
                            </span>
                        </div>
                    </div>
                    <div class="detail-meta-item">
                        <div class="detail-meta-label">Passengers</div>
                        <div class="detail-meta-value"><?= (int)$reservation['passengers'] ?></div>
                    </div>
                    <div class="detail-meta-item">
                        <div class="detail-meta-label">Trip Dates</div>
                        <div class="detail-meta-value">
                            <?= htmlspecialchars($reservation['departure_date']) ?> → <?= htmlspecialchars($reservation['return_date']) ?>
                        </div>
                    </div>
                </div>
                <dl class="detail-list">
                    <div><dt>Reference No.</dt><dd><?= htmlspecialchars($reservation['reference_no']) ?></dd></div>
                    <div><dt>Requester</dt>   <dd><?= htmlspecialchars($reservation['requester_name']) ?></dd></div>
                    <div><dt>Department</dt>  <dd><?= htmlspecialchars($reservation['department'] ?? '—') ?></dd></div>
                    <div><dt>Contact</dt>     <dd><?= htmlspecialchars($reservation['contact_no']  ?? '—') ?></dd></div>
                    <div><dt>Destination</dt> <dd><?= htmlspecialchars($reservation['destination']) ?></dd></div>
                    <div><dt>Purpose</dt>     <dd><?= nl2br(htmlspecialchars($reservation['purpose'])) ?></dd></div>
                    <?php if (!empty($reservation['requester_remarks'])): ?>
                    <div class="full-detail"><dt>Requester Justification</dt><dd><?= nl2br(htmlspecialchars($reservation['requester_remarks'])) ?></dd></div>
                    <?php endif; ?>
                    <div><dt>Departure</dt>   <dd><?= htmlspecialchars($reservation['departure_date'] . ' ' . $reservation['departure_time']) ?></dd></div>
                    <div><dt>Return</dt>      <dd><?= htmlspecialchars($reservation['return_date']   . ' ' . $reservation['return_time'])   ?></dd></div>
                    <?php if ($reservation['make_model']): ?>
                    <div><dt>Vehicle</dt>     <dd><?= htmlspecialchars($reservation['make_model'] . ' (' . $reservation['plate_number'] . ')') ?></dd></div>
                    <?php endif; ?>
                    <?php if ($reservation['remarks']): ?>
                    <div class="full-detail"><dt>Remarks</dt><dd><?= nl2br(htmlspecialchars($reservation['remarks'])) ?></dd></div>
                    <?php endif; ?>
                </dl>
            </div>

            <!-- Dispatch Info -->
            <?php if (!empty($dispatchLog)): ?>
            <div class="detail-section">
                <h3>Dispatch Information</h3>
                <div class="driver-info-card">
                    <div class="driver-info-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <div>
                        <div class="driver-info-label">Assigned Driver</div>
                        <div class="driver-info-name"><?= htmlspecialchars($dispatchLog['driver_name'] ?? '—') ?></div>
                    </div>
                </div>
                <dl class="detail-list" style="margin-top:12px;">
                    <div><dt>Vehicle</dt><dd><?= htmlspecialchars(($dispatchLog['make_model'] ?? '—') . ' (' . ($dispatchLog['plate_number'] ?? '—') . ')') ?></dd></div>
                    <?php if ($dispatchLog['actual_departure']): ?>
                    <div><dt>Departed</dt><dd><?= date('M d, Y g:i A', strtotime($dispatchLog['actual_departure'])) ?></dd></div>
                    <?php endif; ?>
                    <?php if ($dispatchLog['actual_return']): ?>
                    <div><dt>Returned</dt><dd><?= date('M d, Y g:i A', strtotime($dispatchLog['actual_return'])) ?></dd></div>
                    <?php endif; ?>
                    <?php if ($dispatchLog['trip_notes']): ?>
                    <div class="full-detail"><dt>Trip Notes</dt><dd><?= nl2br(htmlspecialchars($dispatchLog['trip_notes'])) ?></dd></div>
                    <?php endif; ?>
                </dl>
            </div>
            <?php endif; ?>
        </section>

        <!-- Approval History -->
        <?php if (!empty($approvals)): ?>
        <section class="panel active">
            <h3>Approval History</h3>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Level</th>
                            <th>Approver</th>
                            <th>Decision</th>
                            <th>Remarks</th>
                            <th>Date &amp; Time</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($approvals as $a): ?>
                        <tr>
                            <td><?= ucwords(str_replace('_',' ',$a['approval_level'])) ?></td>
                            <td><?= htmlspecialchars($a['approver_name']) ?></td>
                            <td>
                                <span class="badge badge-<?= $a['decision'] === 'approved' ? 'available' : 'rejected' ?>">
                                    <?= ucfirst($a['decision']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($a['remarks'] ?? '—') ?></td>
                            <td><?= date('M d, Y g:i A', strtotime($a['decided_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <?php endif; ?>

    </main>
</div>

<?php include VIEW_PATH . '/layouts/footer.php'; ?>