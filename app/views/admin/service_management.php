<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');
$LAVA = lava_instance();
$services = $services ?? [];
$is_edit = isset($service['id']);
$service_data = $service ?? [];

// Repopulate logic for form fields
$name = $name ?? $service_data['name'] ?? '';
$price = $price ?? $service_data['price'] ?? '';
$duration_mins = $duration_mins ?? $service_data['duration_mins'] ?? '';

$errors = $errors ?? [];
$flash_message = $LAVA->session->flashdata('success_message') ?? $LAVA->session->flashdata('error_message');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Services</title>
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
                Service Management
            </h1>
            <a href="<?= site_url('admin/dashboard') ?>" class="text-sm text-gray-600 hover:text-[--primary-color] font-medium">
                ← Back to Dashboard
            </a>
        </div>

        <?php if ($flash_message): ?>
            <div class="p-4 mb-4 rounded-lg <?= $LAVA->session->flashdata('success_message') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                <?= html_escape($flash_message) ?>
            </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6"><?= $is_edit ? 'Edit Service: ' . html_escape($service_data['name']) : 'Add New Service' ?></h2>

            <?php // Display validation errors
            if (!empty($errors)): ?>
                <div class="p-3 mb-4 rounded-lg bg-red-100 text-red-700 border border-red-300">
                    Validation failed: <?= html_escape(implode(', ', $errors)) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= site_url('management/service_add_update/' . ($is_edit ? $service_data['id'] : '')) ?>" class="space-y-4">
                <?= csrf_field() ?>

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Service Name</label>
                    <input type="text" id="name" name="name"
                        value="<?= html_escape($name) ?>" required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[--primary-color] focus:border-[--primary-color]">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700">Price (USD)</label>
                        <input type="number" step="0.01" id="price" name="price"
                            value="<?= html_escape($price) ?>" required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[--primary-color] focus:border-[--primary-color]">
                    </div>
                    <div>
                        <label for="duration_mins" class="block text-sm font-medium text-gray-700">Duration (Minutes)</label>
                        <input type="number" id="duration_mins" name="duration_mins"
                            value="<?= html_escape($duration_mins) ?>" required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[--primary-color] focus:border-[--primary-color]">
                    </div>
                </div>

                <button type="submit" class="w-full bg-[--primary-color] text-white py-2.5 rounded-lg font-semibold hover:bg-[--primary-hover] transition">
                    <?= $is_edit ? 'Save Changes' : 'Add Service' ?>
                </button>
            </form>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Current Services</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration (Mins)</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (!empty($services)): ?>
                            <?php foreach ($services as $s): ?>
                                <tr>
                                    <td class="px-3 py-4 text-sm font-medium text-gray-900"><?= html_escape($s['id']) ?></td>
                                    <td class="px-3 py-4 text-sm text-gray-600"><?= html_escape($s['name']) ?></td>
                                    <td class="px-3 py-4 text-sm text-gray-600">$<?= html_escape(number_format($s['price'], 2)) ?></td>
                                    <td class="px-3 py-4 text-sm text-gray-600"><?= html_escape($s['duration_mins']) ?></td>
                                    <td class="px-3 py-4 text-sm space-x-3">
                                        <a href="<?= site_url('management/service_add_update/' . $s['id']) ?>" class="text-blue-600 hover:text-blue-800">Edit</a>
                                        <a href="<?= site_url('management/service_delete/' . $s['id']) ?>" onclick="return confirm('Are you sure you want to delete this service?')" class="text-red-600 hover:text-red-800">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-3 py-4 text-center text-gray-500">No services defined.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>