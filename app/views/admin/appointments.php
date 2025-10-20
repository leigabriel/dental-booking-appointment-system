<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');
$LAVA = lava_instance();
$appointments = $appointments ?? [];
$doctors = $doctors ?? [];
$services = $services ?? [];
$users = $users ?? [];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary-color: #0EA5E9;
            --primary-hover: #0284C7;
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
                Appointment Management
            </h1>
            <a href="<?= site_url('admin/dashboard') ?>" class="text-sm text-gray-600 hover:text-[--primary-color] font-medium">
                ← Back to Dashboard
            </a>
        </div>

        <?php
        $flash_message = $LAVA->session->flashdata('success_message') ?? $LAVA->session->flashdata('error_message');
        if ($flash_message): ?>
            <div class="p-4 mb-4 rounded-lg <?= $LAVA->session->flashdata('success_message') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                <?= html_escape($flash_message) ?>
            </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">All Scheduled Bookings</h2>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Doctor</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date / Time</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (!empty($appointments)): ?>
                            <?php foreach ($appointments as $app): ?>
                                <tr>
                                    <td class="px-3 py-4 text-sm font-medium text-gray-900"><?= html_escape($app['id']) ?></td>

                                    <td class="px-3 py-4 text-sm text-gray-600">
                                        <?= html_escape($users[$app['user_id']]['full_name'] ?? 'N/A') ?>
                                    </td>

                                    <td class="px-3 py-4 text-sm text-gray-600">
                                        <?= html_escape($doctors[$app['doctor_id']]['name'] ?? 'N/A') ?>
                                    </td>

                                    <td class="px-3 py-4 text-sm text-gray-600">
                                        <?= html_escape($services[$app['service_id']]['name'] ?? 'N/A') ?>
                                    </td>

                                    <td class="px-3 py-4 text-sm text-gray-600">
                                        <?= html_escape(date('M j, Y', strtotime($app['appointment_date']))) ?><br>
                                        <span class="font-semibold"><?= html_escape(date('g:i A', strtotime($app['time_slot']))) ?></span>
                                    </td>

                                    <td class="px-3 py-4 text-sm whitespace-nowrap">
                                        <?php
                                        $status_class = match ($app['status']) {
                                            'confirmed' => 'bg-green-100 text-green-800',
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            default => 'bg-red-100 text-red-800',
                                        };
                                        ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $status_class ?>">
                                            <?= html_escape(ucfirst($app['status'])) ?>
                                        </span>
                                    </td>

                                    <td class="px-3 py-4 text-sm space-x-2 whitespace-nowrap">
                                        <?php if ($app['status'] === 'pending'): ?>
                                            <a href="<?= site_url('management/appointment_confirm/' . $app['id']) ?>"
                                                class="text-green-600 hover:text-green-800 font-medium">Confirm</a>
                                            <span class="text-gray-300">|</span>
                                        <?php endif; ?>
                                        <?php if ($app['status'] !== 'cancelled'): ?>
                                            <a href="<?= site_url('management/appointment_cancel/' . $app['id']) ?>"
                                                onclick="return confirm('Confirm cancellation for appointment #<?= $app['id'] ?>?')"
                                                class="text-red-600 hover:text-red-800 font-medium">Cancel</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-3 py-4 text-center text-gray-500">No appointments scheduled yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>