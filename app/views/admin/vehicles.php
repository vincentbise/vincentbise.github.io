<?php
$pageTitle = 'Fleet Management';
include VIEW_PATH . '/layouts/header.php';
?>
<div class="wrap">
    <main class="content">

        <section class="hero">
            <div>
                <h1>Fleet Management</h1>
                <p>View and manage all registered vehicles in the university fleet.</p>
            </div>
            <a href="<?= BASE_URL ?>admin/vehicles/create" class="btn-primary">+ Add Vehicle</a>
        </section>

        <?php if (!empty($flash)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
        <?php endif; ?>


        <div class="filter-tabs" id="vehicle-filter">
            <button class="filter-tab active" data-filter="all">All</button>
            <button class="filter-tab" data-filter="available">Available</button>
            <button class="filter-tab" data-filter="in_use">In Use</button>
            <button class="filter-tab" data-filter="maintenance">Maintenance</button>
            <button class="filter-tab" data-filter="retired">Retired</button>
        </div>

        <section class="panel active">
            <div class="panel-header">
                <h3>All Vehicles</h3>
                <input type="text" id="vehicle-search"
                       placeholder="Search plate, model…"
                       class="search-input"/>
            </div>

            <div class="table-wrap">
                <table id="vehicles-table">
                    <thead>
                        <tr>
                            <th>Plate No.</th>
                            <th>Make / Model</th>
                            <th>Type</th>
                            <th>Capacity</th>
                            <th>Year</th>
                            <th>Assigned Driver</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($vehicles)): ?>
                        <tr><td colspan="8" class="empty-row">No vehicles registered yet.</td></tr>
                    <?php else: ?>
                    <?php foreach ($vehicles as $v): ?>
                        <tr data-status="<?= $v['status'] ?>">
                            <td><strong><?= htmlspecialchars($v['plate_number']) ?></strong></td>
                            <td><?= htmlspecialchars($v['make_model']) ?></td>
                            <td><?= htmlspecialchars($v['vehicle_type'] ?? '—') ?></td>
                            <td><?= (int)$v['capacity'] ?> pax</td>
                            <td><?= htmlspecialchars($v['year'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($v['assigned_driver_name'] ?? '—') ?></td>
                            <td>
                                <span class="badge badge-<?= $v['status'] ?>">
                                    <?= ucwords(str_replace('_',' ',$v['status'])) ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>admin/vehicles/edit?id=<?= (int)$v['vehicle_id'] ?>"
                                   class="btn-sm">Edit</a>
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

    document.querySelectorAll('#vehicle-filter .filter-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('#vehicle-filter .filter-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            const f = tab.dataset.filter;
            document.querySelectorAll('#vehicles-table tbody tr').forEach(row => {
                row.style.display = (f === 'all' || row.dataset.status === f) ? '' : 'none';
            });
        });
    });

    document.getElementById('vehicle-search').addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#vehicles-table tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
</script>