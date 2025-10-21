<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');
$LAVA = lava_instance();
$username = $LAVA->session->userdata('username');
$role = $LAVA->session->userdata('role');

// Variables passed from Staff::dashboard() method
$all_users = $all_users ?? [];

// Define paths and icons used in the Admin dashboard for consistency
$logo_img = 'https://cdn-icons-png.flaticon.com/128/11873/11873346.png';
$booking_img = 'https://cdn-icons-png.flaticon.com/128/18669/18669653.png';
$doctors_img = 'https://cdn-icons-png.flaticon.com/128/2785/2785482.png';
$services_img = 'https://cdn-icons-png.flaticon.com/128/1041/1041898.png';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Define the Primary Color Theme: Sky Blue (matching Admin) */
        :root {
            --primary-color: #0EA5E9;
            /* Tailwind Sky-500 */
            --primary-hover: #0284C7;
            /* Tailwind Sky-600 */
        }

        body {
            font-family: 'JetBrains Mono', monospace;
        }

        /* Custom Blue for Doctor Card (matching Admin) */
        .card-blue {
            background-color: #3B82F6;
            border-color: #2563EB;
        }

        .card-blue-hover:hover {
            background-color: #2563EB;
        }
    </style>
</head>

<body class="bg-gray-100 p-6 sm:p-10 min-h-screen">
    <div class="max-w-7xl mx-auto">

        <header class="flex justify-between items-center bg-white p-6 rounded-xl shadow-2xl border border-gray-200 mb-10">
            <h1 class="text-4xl font-extrabold text-gray-900 flex items-center space-x-3">
                <span class="uppercase">DENTALCARE STAFF PANEL</span>
            </h1>
            <div class="text-right">
                <p class="text-lg font-bold text-[--primary-color]">Hello, <?= html_escape($username) ?></p>
                <p class="text-sm text-gray-500">Role: <span class="font-semibold text-blue-600"><?= html_escape(ucfirst($role)) ?></span></p>
                <a href="<?= site_url('logout') ?>" class="mt-1 inline-block text-sm text-red-500 hover:text-red-700 font-medium transition">
                    (Log Out)
                </a>
            </div>
        </header>

        <div class="mb-10">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-3">Core Staff Tools</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <a href="<?= site_url('management/appointments') ?>" class="group block bg-green-500 p-6 rounded-xl shadow-lg border border-green-600 hover:bg-green-600 transition duration-150">
                    <h3 class="text-xl font-extrabold text-white mb-2 flex items-center space-x-3">
                        <span>Manage Appointments</span>
                    </h3>
                    <p class="text-green-100 group-hover:text-white transition mb-4">View, confirm, or cancel patient bookings.</p>
                    <span class="inline-block px-6 py-2.5 bg-white text-green-700 font-bold rounded-lg shadow-md hover:text-green-900 transition">
                        Go to Appointment List
                    </span>
                </a>

                <a href="<?= site_url('management/doctors') ?>" class="group block card-blue p-6 rounded-xl shadow-lg border card-blue-hover transition duration-150">
                    <h3 class="text-xl font-extrabold text-white mb-2 flex items-center space-x-3">
                        <span>Doctor Records</span>
                    </h3>
                    <p class="text-blue-100 group-hover:text-white transition mb-4">View and edit doctor details.</p>
                    <span class="inline-block px-6 py-2.5 bg-white text-blue-700 font-bold rounded-lg shadow-md hover:text-blue-900 transition">
                        Manage Doctors
                    </span>
                </a>

                <a href="<?= site_url('management/services') ?>" class="group block bg-yellow-500 p-6 rounded-xl shadow-lg border border-yellow-600 hover:bg-yellow-600 transition duration-150">
                    <h3 class="text-xl font-extrabold text-white mb-2 flex items-center space-x-3">
                        <span>Service Records</span>
                    </h3>
                    <p class="text-yellow-100 group-hover:text-white transition mb-4">View and edit service details.</p>
                    <span class="inline-block px-6 py-2.5 bg-white text-yellow-700 font-bold rounded-lg shadow-md hover:text-yellow-900 transition">
                        Manage Services
                    </span>
                </a>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200 mb-10">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-3">Registered Patient Accounts</h2>

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
            <p class="text-gray-600 mb-4">Your access is focused on patient and appointment review.</p>
            <ul class="list-disc list-inside space-y-2 text-gray-700 ml-4">
                <li class="font-medium">✅ View and manage appointments.</li>
                <li class="font-medium">✅ View Patient Records (Read Access).</li>
                <li>❌ Cannot manage user roles or delete master data (doctors/services).</li>
            </ul>
        </div>
    </div>
</body>

</html>