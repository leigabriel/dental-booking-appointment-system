<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');
$LAVA = lava_instance();
$username = $LAVA->session->userdata('username');
$role = $LAVA->session->userdata('role');

// Variables passed from Admin::dashboard() method
$total_users = $total_users ?? 0;
$total_staff = $total_staff ?? 0;
$total_admin = $total_admin ?? 0;
$all_users = $all_users ?? [];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Define the Primary Color Theme: Sky Blue */
        :root {
            --primary-color: #0EA5E9;
            /* Tailwind Sky-500 (Main Accent) */
            --primary-hover: #0284C7;
            /* Tailwind Sky-600 */
            --accent-light: #38BDF8;
            /* Tailwind Sky-400 */
        }

        body {
            font-family: 'JetBrains Mono', monospace;
            /* Applying requested font */
        }
    </style>
</head>

<body class="bg-gray-50 p-6 sm:p-10 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-lg border border-gray-200 mb-8">
            <h1 class="text-3xl font-extrabold text-[--primary-color]">
                Admin Dashboard
            </h1>
            <div class="text-right">
                <p class="text-lg font-medium text-[--primary-color]">Welcome, <?= html_escape($username) ?></p>
                <p class="text-sm text-gray-500">Role: <?= html_escape(ucfirst($role)) ?></p>
                <a href="<?= site_url('logout') ?>" class="text-sm text-red-500 hover:text-red-700 font-medium">
                    (Log Out)
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">

            <div class="bg-white p-6 rounded-xl shadow-md border-b-4 border-[--primary-color]">
                <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Total Administrators</p>
                <p class="text-5xl font-extrabold text-gray-900 mt-2"><?= html_escape($total_admin) ?></p>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-md border-b-4 border-green-400">
                <p class="text-sm font-semibold text-green-600 uppercase tracking-wider">Total Staff Accounts</p>
                <p class="text-5xl font-extrabold text-gray-900 mt-2"><?= html_escape($total_staff) ?></p>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-md border-b-4 border-[--accent-light]">
                <p class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Total Patients/Users</p>
                <p class="text-5xl font-extrabold text-gray-900 mt-2"><?= html_escape($total_users) ?></p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200 mb-10">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Registered Accounts Overview</h2>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ID
                            </th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Username
                            </th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Full Name
                            </th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Email
                            </th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Role
                            </th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Joined Date
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (!empty($all_users)): ?>
                            <?php foreach ($all_users as $user): ?>
                                <tr>
                                    <td class="px-3 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?= html_escape($user['id']) ?>
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <?= html_escape($user['username']) ?>
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <?= html_escape($user['full_name'] ?? 'N/A') ?>
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <?= html_escape($user['email'] ?? 'N/A') ?>
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap">
                                        <?php
                                        // Determine role badge color
                                        $role_class = match ($user['role']) {
                                            'admin' => 'bg-red-100 text-red-800',
                                            'staff' => 'bg-blue-100 text-blue-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                        ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $role_class ?>">
                                            <?= html_escape(ucfirst($user['role'])) ?>
                                        </span>
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= html_escape($user['created_at']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-3 py-4 text-center text-gray-500">No registered accounts found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Core Management Tools</h2>
            <p class="text-gray-600 mb-4">This panel provides full administrative control over the system data.</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="<?= site_url('management/doctors') ?>" class="text-center bg-[--primary-color] hover:bg-[--primary-hover] text-white font-bold py-3 px-4 rounded-lg transition">Manage Doctors (CRUD)</a>
                <a href="<?= site_url('management/services') ?>" class="text-center bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-4 rounded-lg transition">Manage Services (CRUD)</a>
                <a href="<?= site_url('management/appointments') ?>" class="text-center bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-4 rounded-lg transition">View Appointments</a>
            </div>
        </div>
    </div>
</body>

</html>