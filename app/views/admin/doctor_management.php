<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');
$LAVA = lava_instance();

// Data variables
$doctors = $doctors ?? [];
$is_edit = isset($doctor['id']);
$doctor_data = $doctor ?? [];

// Repopulate logic for form fields (safe access via coalescing)
$name = $name ?? $doctor_data['name'] ?? '';
$specialty = $specialty ?? $doctor_data['specialty'] ?? '';
$email = $email ?? $doctor_data['email'] ?? '';

$errors = $errors ?? [];
$flash_message = $LAVA->session->flashdata('success_message') ?? $LAVA->session->flashdata('error_message');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Doctors</title>
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
                Doctor Management
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
            <h2 class="text-2xl font-bold text-gray-800 mb-6"><?= $is_edit ? 'Edit Doctor: ' . html_escape($doctor_data['name']) : 'Add New Doctor' ?></h2>

            <?php // Display validation errors
            if (!empty($errors)): ?>
                <div class="p-3 mb-4 rounded-lg bg-red-100 text-red-700 border border-red-300">
                    Validation failed: <?= html_escape(implode(', ', $errors)) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= site_url('management/doctor_add_update/' . ($is_edit ? $doctor_data['id'] : '')) ?>" class="space-y-4">
                <?= csrf_field() ?>

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" id="name" name="name"
                        value="<?= html_escape($name) ?>" required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[--primary-color] focus:border-[--primary-color]">
                </div>

                <div>
                    <label for="specialty" class="block text-sm font-medium text-gray-700">Specialty</label>
                    <input type="text" id="specialty" name="specialty"
                        value="<?= html_escape($specialty) ?>" required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[--primary-color] focus:border-[--primary-color]">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                    <input type="email" id="email" name="email"
                        value="<?= html_escape($email) ?>" required
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[--primary-color] focus:border-[--primary-color]">
                </div>

                <button type="submit" class="w-full bg-[--primary-color] text-white py-2.5 rounded-lg font-semibold hover:bg-[--primary-hover] transition">
                    <?= $is_edit ? 'Save Changes' : 'Add Doctor' ?>
                </button>
            </form>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Current Doctors</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Specialty</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (!empty($doctors)): ?>
                            <?php foreach ($doctors as $d): ?>
                                <tr>
                                    <td class="px-3 py-4 text-sm font-medium text-gray-900"><?= html_escape($d['id']) ?></td>
                                    <td class="px-3 py-4 text-sm text-gray-600"><?= html_escape($d['name']) ?></td>
                                    <td class="px-3 py-4 text-sm text-gray-600"><?= html_escape($d['specialty']) ?></td>
                                    <td class="px-3 py-4 text-sm text-gray-600"><?= html_escape($d['email']) ?></td>
                                    <td class="px-3 py-4 text-sm space-x-3">
                                        <a href="<?= site_url('management/doctor_add_update/' . $d['id']) ?>" class="text-blue-600 hover:text-blue-800">Edit</a>
                                        <a href="<?= site_url('management/doctor_delete/' . $d['id']) ?>" onclick="return confirm('Are you sure you want to delete this doctor?')" class="text-red-600 hover:text-red-800">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-3 py-4 text-center text-gray-500">No doctors registered.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>