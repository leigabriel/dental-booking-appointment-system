<?php defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dental Booking System</title>
    <style>
        body {
            font-family: sans-serif;
            background-color: #fffbeb;
            padding: 30px;
        }

        h1 {
            color: #f59e0b;
        }
    </style>
</head>

<body>
    <h1>Welcome to the Dental Booking System</h1>
    <p>This is the main **Patient/User Interface** for viewing services and booking appointments.</p>

    <?php if (lava_instance()->session->userdata('is_logged_in')): ?>
        <p>You are logged in as: <strong><?= html_escape(lava_instance()->session->userdata('username')) ?></strong> (Role: <?= html_escape(lava_instance()->session->userdata('role')) ?>)</p>
        <a href="<?= site_url('logout') ?>">Logout</a>
    <?php else: ?>
        <p>Please <a href="<?= site_url('login') ?>">Login</a> or <a href="<?= site_url('register') ?>">Register</a> to view our services and book an appointment.</p>
    <?php endif; ?>
</body>

</html>