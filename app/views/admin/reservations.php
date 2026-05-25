<?php
$pageTitle = 'All Reservations';
include VIEW_PATH . '/layouts/header.php';
?>
<div class="wrap">
    <main class="content">

        <section class="hero">
            <div>
                <h1>All Reservations</h1>
                <p>Manage and track all vehicle reservation requests system-wide.</p>
            </div>
        </section>

        <?php if (!empty($flash)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
        <?php endif; ?>

        <div class="filter-tabs" id="res-filter">
            <button class="filter-tab active" data-filter="all">All</button>
            <button class="filter-tab" data-filter="pending">Pending</button>
            <button class="filter-tab" data-filter="approved">Approved</button>
            <button class="filter-tab" data-filter="dispatched">Dispatched</button>
            <button class="filter-tab" data-filter="completed">Completed</button>
            <button class="filter-tab" data-filter="rejected">Rejected</button>
        </div>

        <section class="panel active">
            <div class="panel-header">
                <h3>Reservation Records</h3>
                <input type="text" id="res-search"
                       placeholder="Search reference, name, destination…"
                       class="search-input"/>
            </div>

            <div class="table-wrap">
                <table id="res-table">
                    <thead>
                        <tr>
                            <th>Ref No.</th>
                            <th>Requester</th>
                            <th>Destination</th>
                            <th>Departure</th>
                            <th>Vehicle</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($reservations)): ?>
                        <tr><td colspan="7" class="empty-row">No reservations found.</td></tr>
                    <?php else: ?>
                    <?php foreach ($reservations as $r): ?>
                        <tr data-status="<?= $r['status'] ?>">
                            <td><strong><?= htmlspecialchars($r['reference_no']) ?></strong></td>
                            <td><?= htmlspecialchars($r['requester_name']) ?></td>
                            <td><?= htmlspecialchars($r['destination']) ?></td>
                            <td><?= date('M d, Y', strtotime($r['departure_date'])) ?></td>
                            <td>
                                <?= $r['make_model']
                                    ? htmlspecialchars($r['make_model'] . ' (' . $r['plate_number'] . ')')
                                    : '<span class="muted">—</span>' ?>
                            </td>
                            <td>
                                <span class="badge badge-<?= $r['status'] ?>">
                                    <?= ucwords(str_replace('_',' ',$r['status'])) ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>admin/reservations/view?id=<?= (int)$r['reservation_id'] ?>"
                                   class="btn-sm">View</a>
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

    document.querySelectorAll('#res-filter .filter-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('#res-filter .filter-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            const f = tab.dataset.filter;
            document.querySelectorAll('#res-table tbody tr').forEach(row => {
                row.style.display = (f === 'all' || row.dataset.status === f) ? '' : 'none';
            });
        });
    });

    document.getElementById('res-search').addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#res-table tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
</script>