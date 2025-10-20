<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');
$LAVA = lava_instance();
$username = $LAVA->session->userdata('username');
$role = $LAVA->session->userdata('role');

// Variables passed from Staff::dashboard() method
$all_users = $all_users ?? [];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Define the Primary Blue Color Theme */
        :root {
            --primary-color: #2563eb;
            /* Tailwind Blue-600 */
            --primary-hover: #1d4ed8;
            /* Tailwind Blue-700 */
        }

        body {
            font-family: 'JetBrains Mono', monospace;
        }
    </style>
</head>

<body class="bg-gray-50 p-6 sm:p-10 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-lg border border-gray-200 mb-8">
            <h1 class="text-3xl font-extrabold text-[--primary-color]">
                Staff Panel
            </h1>
            <div class="text-right">
                <p class="text-lg font-medium text-[--primary-color]">Welcome, <?= html_escape($username) ?></p>
                <p class="text-sm text-gray-500">Role: <?= html_escape(ucfirst($role)) ?></p>
                <a href="<?= site_url('logout') ?>" class="text-sm text-red-500 hover:text-red-700 font-medium">
                    (Log Out)
                </a>
            </div>
        </div>

        <div class="bg-green-500 p-6 rounded-xl shadow-lg border border-green-600 mb-8 hover:bg-green-600 transition duration-150">
            <h2 class="text-2xl font-extrabold text-white mb-2">Manage Appointments</h2>
            <p class="text-green-100 mb-4">View, confirm, or cancel all scheduled patient bookings.</p>
            <a href="<?= site_url('management/appointments') ?>"
                class="inline-block px-6 py-2.5 bg-white text-green-700 font-bold rounded-lg shadow-md hover:text-green-900 transition">
                Go to Appointment List
            </a>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200 mb-10">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">User Accounts Overview</h2>

            <p class="text-gray-600 mb-4">
                This table shows **registered patient/user accounts** for quick reference and identity verification.
            </p>

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
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Staff Privileges</h2>
            <p class="text-gray-600 mb-4">You have limited operational access focused on patient and appointment review.</p>
            <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                <li class="font-medium">✅ View all Patient Records (Read Access).</li>
                <li class="font-medium">✅ View and manage appointments (Ready to use at the top of this page).</li>
                <li>❌ Cannot manage user roles or edit system configurations.</li>
            </ul>
        </div>
    </div>
</body>

</html>