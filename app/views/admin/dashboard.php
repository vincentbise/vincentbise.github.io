<?php
$pageTitle = 'Admin Dashboard';
$totalReservations = $totalReservations ?? 0;
$pending = $pending ?? 0;
$availableVehicles = $availableVehicles ?? 0;
$availableDrivers = $availableDrivers ?? 0;
$recentReservations = $recentReservations ?? [];
include VIEW_PATH . '/layouts/header.php';
?>
<div class="wrap">
    <main class="content">

        <section class="hero">
            <div>
                <h1>Admin Dashboard</h1>
                <p>Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?>. Here's your system overview.</p>
            </div>
        </section>

        <!-- Stats -->
        <section class="stats">
            <article class="stat">
                <img src="<?= BASE_URL ?>images/reports.png" class="stat-icon" alt="Reservations"/>
                <h4>Total Reservations</h4>
                <div class="num"><?= $totalReservations ?></div>
            </article>
            <article class="stat">
                <img src="<?= BASE_URL ?>images/pending.png" class="stat-icon" alt="Pending"/>
                <h4>Pending</h4>
                <div class="num"><?= $pending ?></div>
            </article>
            <article class="stat">
                <img src="<?= BASE_URL ?>images/vehicle.png" class="stat-icon" alt="Vehicles"/>
                <h4>Available Vehicles</h4>
                <div class="num"><?= $availableVehicles ?></div>
            </article>
            <article class="stat">
                <img src="<?= BASE_URL ?>images/driver.png" class="stat-icon" alt="Drivers"/>
                <h4>Available Drivers</h4>
                <div class="num"><?= $availableDrivers ?></div>
            </article>
        </section>

        <!-- Recent Reservations -->
        <section class="panel active">
            <div class="panel-header">
                <h3>Recent Reservations</h3>
                <a href="<?= BASE_URL ?>admin/reservations" class="btn-sm">View All</a>
            </div>

            <?php if (empty($recentReservations)): ?>
                <p class="empty-row">No reservations yet.</p>
            <?php else: ?>
            <div class="table-wrap">
                <table>
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
                    <?php foreach ($recentReservations as $r): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($r['reference_no']) ?></strong></td>
                            <td><?= htmlspecialchars($r['requester_name']) ?></td>
                            <td><?= htmlspecialchars($r['destination']) ?></td>
                            <td><?= htmlspecialchars($r['departure_date']) ?></td>
                            <td>
                                <?= $r['make_model']
                                    ? htmlspecialchars($r['make_model'])
                                    : '<span class="muted">Not assigned</span>' ?>
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
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </section>

    </main>
</div>

<?php include VIEW_PATH . '/layouts/footer.php'; ?>
<script src="<?= BASE_URL ?>public/js/dashboard.js"></script>