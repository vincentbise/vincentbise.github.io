<?php
$driver = $driver ?? ['full_name' => '—'];
$pageTitle = 'My Trip History';
include VIEW_PATH . '/layouts/header.php';
?>

<div class="wrap">
    <main class="content">

        <section class="hero">
            <div>
                <h1>Trip History</h1>
                <p>Viewing trip history for: <strong><?= htmlspecialchars($driver['full_name']) ?></strong></p>
            </div>
        </section>

        <?php if (!empty($flash)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
        <?php endif; ?>

        <section class="panel active">
            <div class="panel-header">
                <h3>Completed & Dispatched Trips</h3>
                <input type="text" id="trip-search" placeholder="Search reference, destination..." class="search-input"/>
            </div>

            <div class="table-wrap">
                <table id="trips-table">
                    <thead>
                        <tr>
                            <th>Ref No.</th>
                            <th>Destination</th>
                            <th>Period</th>
                            <th>Vehicle</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($trips)): ?>
                        <tr><td colspan="5" class="empty-row">No trip records found.</td></tr>
                    <?php else: ?>
                    <?php foreach ($trips as $t): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($t['reference_no']) ?></strong></td>
                            <td><?= htmlspecialchars($t['destination']) ?></td>
                            <td>
                                <?= date('M d, Y', strtotime($t['departure_date'])) ?>
                                <?php if ($t['return_date'] != $t['departure_date']): ?>
                                    – <?= date('M d, Y', strtotime($t['return_date'])) ?>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars(($t['make_model'] ?? '—') . ' (' . ($t['plate_number'] ?? '—') . ')') ?></td>
                            <td><span class="badge badge-<?= $t['status'] ?>"><?= ucfirst(str_replace('_',' ',$t['status'])) ?></span></td>
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
    document.getElementById('trip-search').addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#trips-table tbody tr').forEach(r => {
            if (r.classList.contains('empty-row')) return;
            r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
</script>
