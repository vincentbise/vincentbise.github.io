<?php
$role = $_SESSION['role'] ?? '';
?>
<aside class="side" id="sideNav">
    <?php if (in_array($role, ['admin', 'asd_coordinator'])): ?>
        <a class="nav-btn" href="<?= BASE_URL ?>admin/dashboard">Dashboard</a>
        <a class="nav-btn" href="<?= BASE_URL ?>admin/reservations">Reservations</a>
        <a class="nav-btn" href="<?= BASE_URL ?>approvals">Approvals</a>
        <a class="nav-btn" href="<?= BASE_URL ?>admin/vehicles">Vehicles</a>
        <?php if ($role === 'admin'): ?>
        <a class="nav-btn" href="<?= BASE_URL ?>admin/accounts">Accounts</a>
        <?php endif; ?>
        <a class="nav-btn" href="<?= BASE_URL ?>admin/reports">Reports</a>

    <?php elseif ($role === 'unit_head'): ?>
        <a class="nav-btn" href="<?= BASE_URL ?>approvals">Pending Approvals</a>

    <?php elseif ($role === 'staff'): ?>
        <a class="nav-btn" href="<?= BASE_URL ?>approvals">Approvals</a>
        <a class="nav-btn" href="<?= BASE_URL ?>account/edit">Edit Account</a>

    <?php elseif ($role === 'requester'): ?>
        <a class="nav-btn" href="<?= BASE_URL ?>requester/dashboard">Dashboard</a>
        <a class="nav-btn" href="<?= BASE_URL ?>requester/new">New Request</a>
        <a class="nav-btn" href="<?= BASE_URL ?>requester/my_requests">My Requests</a>
        <a class="nav-btn" href="<?= BASE_URL ?>account/edit">Edit Account</a>

    <?php elseif ($role === 'driver'): ?>
        <a class="nav-btn" href="<?= BASE_URL ?>driver/dashboard">My Trips</a>
        <a class="nav-btn" href="<?= BASE_URL ?>driver/trips">Trip History</a>
        <a class="nav-btn" href="<?= BASE_URL ?>account/edit">Edit Account</a>
    <?php endif; ?>

    <a class="nav-btn nav-logout" href="<?= BASE_URL ?>auth/logout">Log Out</a>
</aside>
