<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');
$LAVA = lava_instance();
$user = $user ?? [];

// Helper to display messages from the session
$flash_message = $LAVA->session->flashdata('success_message') ?? $LAVA->session->flashdata('error_message');
$is_success = $LAVA->session->flashdata('success_message') ? true : false;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DENTALCARE: My Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary-color: #2563eb;
        }

        body {
            font-family: 'JetBrains Mono', monospace;
        }
    </style>
</head>

<body class="bg-gray-100 p-8">
    <div class="max-w-3xl mx-auto bg-white p-8 rounded-xl shadow-lg">
        <h1 class="text-3xl font-bold text-[--primary-color] border-b pb-4 mb-6">
            My DENTALCARE Account
        </h1>

        <?php if ($flash_message): ?>
            <div class="p-4 mb-4 rounded-lg <?= $is_success ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                <?= html_escape($flash_message) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= site_url('profile/update') ?>" class="space-y-6">
            <?= csrf_field() ?>

            <div>
                <label for="full_name" class="block text-sm font-medium text-gray-700">Full Name</label>
                <input type="text" id="full_name" name="full_name" value="<?= html_escape($user['full_name'] ?? '') ?>" required
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[--primary-color] focus:border-[--primary-color]">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                <input type="email" id="email" name="email" value="<?= html_escape($user['email'] ?? '') ?>" required
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[--primary-color] focus:border-[--primary-color]">
            </div>

            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Username (Non-Editable)</label>
                <input type="text" id="username" value="<?= html_escape($user['username'] ?? '') ?>" readonly
                    class="mt-1 block w-full px-4 py-2 border border-gray-200 bg-gray-50 rounded-lg">
            </div>

            <h2 class="text-xl font-semibold border-t pt-4 mt-4">Change Password (Optional)</h2>

            <div>
                <label for="new_password" class="block text-sm font-medium text-gray-700">New Password</label>
                <input type="password" id="new_password" name="new_password" placeholder="Leave blank to keep current password"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[--primary-color] focus:border-[--primary-color]">
            </div>

            <div>
                <label for="confirm_new_password" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                <input type="password" id="confirm_new_password" name="confirm_new_password"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[--primary-color] focus:border-[--primary-color]">
            </div>

            <div class="flex justify-between items-center pt-4">
                <a href="<?= site_url('/') ?>" class="text-gray-600 hover:text-gray-900 underline transition">← Back to Home</a>

                <button type="submit"
                    class="px-6 py-2 bg-[--primary-color] text-white font-semibold rounded-lg hover:bg-blue-500 transition shadow-md">
                    Save Changes
                </button>
            </div>
        </form>

        <div class="mt-8 pt-4 border-t border-red-200">
            <h2 class="text-xl font-semibold text-red-600">Danger Zone</h2>
            <p class="text-sm text-gray-600 mb-4">Permanently delete your account and all associated records.</p>
            <a href="<?= site_url('profile/delete') ?>"
                onclick="return confirm('WARNING: Are you sure you want to permanently delete your account? This action cannot be undone.');"
                class="px-4 py-2 bg-red-500 text-white font-semibold rounded-lg hover:bg-red-700 transition">
                Delete Account
            </a>
        </div>
    </div>
</body>

</html>