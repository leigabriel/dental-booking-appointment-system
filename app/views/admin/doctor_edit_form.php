<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');
$LAVA = lava_instance();

// Data variables passed from Management::doctor_edit
$doctor_data = $doctor ?? [];
$errors = $errors ?? [];
$post_data = $post_data ?? []; 

// Repopulate logic: use failed form data (post_data) if present, otherwise use existing doctor data
$id = $doctor_data['id'] ?? ($post_data['id'] ?? '');
$name = $post_data['name'] ?? $doctor_data['name'] ?? '';
$specialty = $post_data['specialty'] ?? $doctor_data['specialty'] ?? '';
$email = $post_data['email'] ?? $doctor_data['email'] ?? '';

$flash_message = $LAVA->session->flashdata('success_message') ?? $LAVA->session->flashdata('error_message');

function display_errors($errors) {
    if (!empty($errors)) {
        echo '<div class="p-3 mb-4 rounded-lg bg-red-100 text-red-700 border border-red-300">';
        echo '<ul>';
        foreach ($errors as $error) {
            echo '<li>' . html_escape($error) . '</li>';
        }
        echo '</ul></div>';
    }
}

// Check if we have doctor data to edit
if (empty($doctor_data) && empty($post_data)) {
    // If somehow we land here without data, redirect to the list
    redirect('management/doctors');
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Doctor: <?= html_escape($name) ?></title>
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
    <div class="max-w-xl mx-auto">
        <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-lg border border-gray-200 mb-8">
            <h1 class="text-3xl font-extrabold text-[--primary-color]">
                Edit Doctor: ID #<?= html_escape($id) ?>
            </h1>
            <a href="<?= site_url('management/doctors') ?>" class="text-sm text-gray-600 hover:text-[--primary-color] font-medium">
                ← Back to Doctor List
            </a>
        </div>

        <?php if ($flash_message): ?>
            <div class="p-4 mb-4 rounded-lg <?= $LAVA->session->flashdata('success_message') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                <?= html_escape($flash_message) ?>
            </div>
        <?php endif; ?>

        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Updating <?= html_escape($doctor_data['name'] ?? 'Doctor') ?></h2>
            
            <?php // Display validation errors
            if (!empty($errors)): ?>
                <div class="p-3 mb-4 rounded-lg bg-red-100 text-red-700 border border-red-300">
                    Validation failed:
                    <?php display_errors($errors); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= site_url('management/doctor_add_update/' . html_escape($id)) ?>" class="space-y-4">
                <?= csrf_field() ?>

                <input type="hidden" name="id" value="<?= html_escape($id) ?>">

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

                <button type="submit" class="w-full bg-green-500 text-white py-2.5 rounded-lg font-semibold hover:bg-green-600 transition">
                    Save Changes
                </button>
            </form>
        </div>
    </div>
</body>

</html>