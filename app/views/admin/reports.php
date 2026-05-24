<?php
$pageTitle = 'Reports';
include VIEW_PATH . '/layouts/header.php';

$types = [
    'utilization' => 'Vehicle Utilization Report',
    'monthly'     => 'Monthly Request Trends',
    'drivers'     => 'Driver Assignment Summary',
];
$currentType = $type ?? array_key_first($types);
if (!isset($types[$currentType])) {
    $currentType = array_key_first($types);
}
$currentLabel = $types[$currentType] ?? 'Report';
?>

<div class="wrap">
    <main class="content">

        <section class="hero">
            <div>
                <h1>Reports</h1>
                <p>Generate and export operational reports for the USeP Vehicle Reservation System.</p>
            </div>
        </section>

        <!-- Report type selector -->
        <section class="panel active">
            <div class="report-grid">
                <?php foreach ($types as $key => $label): ?>
                <a href="<?= BASE_URL ?>admin/reports?type=<?= $key ?>"
                   class="report-card <?= $currentType === $key ? 'report-card--active' : '' ?>">
                    <span><?= $label ?></span>
                    <span class="btn-sm">View</span>
                </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Report Output -->
        <section class="panel active">
            <div class="panel-header">
                <h3><?= $currentLabel ?></h3>
            </div>

            <?php if (empty($data)): ?>
                <p class="empty-row">No data available for this report.</p>

            <?php elseif ($currentType === 'utilization'): ?>
                <div class="table-wrap">
                    <table>
                        <thead><tr>
                            <th>Vehicle</th><th>Plate</th><th>Status</th>
                            <th>Trips</th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($data as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['make_model']) ?></td>
                            <td><?= htmlspecialchars($r['plate_number']) ?></td>
                            <td><span class="badge badge-<?= $r['status'] ?>"><?= ucwords(str_replace('_',' ',$r['status'])) ?></span></td>
                            <td><?= (int)$r['trips_completed'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($currentType === 'monthly'): ?>
                <div class="table-wrap">
                    <table>
                        <thead><tr>
                            <th>Month</th><th>Total</th>
                            <th>Completed</th><th>Rejected</th><th>Cancelled</th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($data as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['month']) ?></td>
                            <td><strong><?= (int)$r['total'] ?></strong></td>
                            <td><?= (int)$r['completed'] ?></td>
                            <td><?= (int)$r['rejected'] ?></td>
                            <td><?= (int)$r['cancelled'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($currentType === 'drivers'): ?>
                <div class="table-wrap">
                    <table>
                        <thead><tr>
                            <th>Driver</th><th>License No.</th>
                            <th>Trips Completed</th>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($data as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['driver']) ?></td>
                            <td><?= htmlspecialchars($r['license_no']) ?></td>
                            <td><?= (int)$r['trips'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

        </section>

    </main>
</div>

<?php include VIEW_PATH . '/layouts/footer.php'; ?>
<script src="<?= BASE_URL ?>public/js/reports.js"></script>
