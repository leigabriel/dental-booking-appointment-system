<?php defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Staff Dashboard</title>
    <style>
        body {
            font-family: sans-serif;
            background-color: #e0f2fe;
            padding: 30px;
        }

        h1 {
            color: #3b82f6;
        }
    </style>
</head>

<body>
    <h1>Welcome, Staff (<?= html_escape(lava_instance()->session->userdata('username')) ?>)</h1>
    <p>This is the **Staff Panel** with **limited operation access**.</p>
    <ul>
        <li>View and manage appointments.</li>
        <li>Limited access to patient records.</li>
        <li>No access to system configurations.</li>
    </ul>
    <a href="<?= site_url('logout') ?>">Logout</a>
</body>

</html>