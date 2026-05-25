<?php
/** @var array $requests */
$pageTitle = 'My Dashboard';
include VIEW_PATH . '/layouts/header.php';


$total     = count($requests);
$pending   = count(array_filter($requests, fn($r) => $r['status'] === 'pending'));
$completed = count(array_filter($requests, fn($r) => $r['status'] === 'completed'));
$rejected  = count(array_filter($requests, fn($r) => in_array($r['status'], ['rejected','cancelled'])));
?>

<div class="wrap">
    <main class="content">

        <section class="hero">
            <div>
                <h1>My Dashboard</h1>
                <p>Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?>. Track your reservation requests here.</p>
            </div>
            <a href="<?= BASE_URL ?>requester/new" class="btn-primary">+ New Request</a>
        </section>


        <section class="stats">
            <article class="stat">
                <img src="<?= BASE_URL ?>images/reports.png" class="stat-icon" alt="Total"/>
                <h4>Total Requests</h4>
                <div class="num"><?= $total ?></div>
            </article>
            <article class="stat">
                <img src="<?= BASE_URL ?>images/pending.png" class="stat-icon" alt="Pending"/>
                <h4>Pending</h4>
                <div class="num"><?= $pending ?></div>
            </article>
            <article class="stat">
                <img src="<?= BASE_URL ?>images/vehicle.png" class="stat-icon" alt="Completed"/>
                <h4>Completed</h4>
                <div class="num"><?= $completed ?></div>
            </article>
            <article class="stat">
                <img src="<?= BASE_URL ?>images/rejected.png" class="stat-icon" alt="Rejected"/>
                <h4>Rejected / Cancelled</h4>
                <div class="num"><?= $rejected ?></div>
            </article>
        </section>


        <section class="panel active">
            <div class="panel-header">
                <h3>Recent Requests</h3>
                <a href="<?= BASE_URL ?>requester/my_requests" class="btn-sm">View All</a>
            </div>

            <?php if (empty($requests)): ?>
                <p class="empty-row">
                    You have not submitted any requests yet.
                    <a href="<?= BASE_URL ?>requester/new">Submit your first request →</a>
                </p>
            <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Ref No.</th>
                            <th>Destination</th>
                            <th>Departure</th>
                            <th>Vehicle</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach (array_slice($requests, 0, 5) as $r): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($r['reference_no']) ?></strong></td>
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
