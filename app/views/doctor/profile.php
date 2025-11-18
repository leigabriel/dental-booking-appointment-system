<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');
$LAVA = lava_instance();
$username = $LAVA->session->userdata('username');
$current_role = $LAVA->session->userdata('role');

$doctorProfile = $doctorProfile ?? null;
$userDetails = $userDetails ?? [];

$doctor_name = $userDetails['full_name'] ?? $username;
$doctor_email = $userDetails['email'] ?? '';
$doctor_specialty = $doctorProfile['specialty'] ?? '';

$errors = $errors ?? [];
$flash_message = $LAVA->session->flashdata('success_message') ?? $LAVA->session->flashdata('error_message');

function display_validation_errors($errors)
{
    if (!empty($errors)) {
        echo '<div class="p-3 mb-4 rounded-lg bg-red-100 text-red-700 border border-red-300">';
        echo '<ul class="list-disc pl-5 m-0">';
        foreach ($errors as $error) {
            echo '<li>' . html_escape($error) . '</li>';
        }
        echo '</ul></div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary-color: #3B82F6;
            --primary-hover: #2563EB;
        }

        body {
            font-family: 'Inter', sans-serif;
        }

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #a8a8a8;
            border-radius: 4px;
        }
    </style>
</head>

<body class="bg-gray-100">

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-20 bg-blue-900 text-gray-300 p-3 flex flex-col items-center justify-between shadow-2xl sticky top-0 h-screen z-20">
            <div>
                <a href="<?= site_url('doctor/dashboard') ?>" title="Dentalcare Home" class="flex items-center justify-center h-12 w-12 mb-8 rounded-full bg-gray-800 border-gray-700 border-2 text-white shadow-md hover:bg-gray-700">
                    <img src="<?= site_url('public/img/dentalcare512x512.png') ?>" alt="Dentalcare Logo" class="w-6 h-6">
                </a>

                <nav class="space-y-4">
                    <a href="<?= site_url('doctor/dashboard') ?>" title="Dashboard"
                        class="flex items-center justify-center h-12 w-12 rounded-full transition-colors duration-200 relative group border-2 border-transparent hover:bg-blue-700 hover:border-blue-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span class="absolute left-16 bg-gray-800 text-white px-2 py-1 rounded text-xs whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-30">Dashboard</span>
                    </a>

                    <a href="<?= site_url('doctor/profile') ?>" title="My Profile"
                        class="flex items-center justify-center h-12 w-12 rounded-full transition-colors duration-200 relative group border-2 bg-blue-600 border-blue-400 text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span class="absolute left-16 bg-gray-800 text-white px-2 py-1 rounded text-xs whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-30">My Profile</span>
                    </a>
                </nav>
            </div>

            <a href="#" onclick="event.preventDefault(); openLogoutModal();" title="Logout"
                class="flex items-center justify-center h-12 w-12 rounded-full transition-colors duration-200 relative group border-2 border-transparent hover:bg-red-600 hover:border-red-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span class="absolute left-16 bg-gray-800 text-white px-2 py-1 rounded text-xs whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-30">Logout</span>
            </a>
        </aside>

        <!-- Logout Confirmation Modal -->
        <div id="logout-modal"
            class="modal fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4 transition-opacity duration-300 ease-in-out"
            onclick="closeLogoutModal(event)">

            <div class="bg-white w-full max-w-md p-8 rounded-2xl shadow-xl transform transition-transform duration-300 ease-in-out scale-95"
                onclick="event.stopPropagation()">

                <div class="flex flex-col items-center text-center mb-6">
                    <div class="mb-4 text-red-500 text-5xl">
                        <i class="fas fa-right-from-bracket"></i>
                    </div>
                    <h3 class="text-2xl font-semibold text-gray-800">Confirm Logout</h3>
                </div>

                <p class="text-gray-600 text-center mb-8">
                    Are you sure you want to logout? This will end your current session.
                </p>

                <div class="flex justify-center gap-4">
                    <button type="button"
                        onclick="closeLogoutModal()"
                        class="px-6 py-2.5 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2 transition duration-150 font-medium">
                        Cancel
                    </button>
                    <a id="confirm-logout-btn"
                        href="<?= site_url('logout') ?>"
                        class="px-6 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition duration-150 font-medium shadow-md">
                        Logout
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <div class="max-w-4xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-800">My Profile</h1>
                    <p class="text-gray-600 mt-2">Manage your profile information</p>
                </div>

                <?php if ($flash_message): ?>
                    <div class="mb-6 p-4 rounded-lg <?= strpos($flash_message, 'success') !== false ? 'bg-green-100 text-green-700 border border-green-300' : 'bg-red-100 text-red-700 border border-red-300' ?>">
                        <?= html_escape($flash_message) ?>
                    </div>
                <?php endif; ?>

                <?php display_validation_errors($errors); ?>

                <!-- Profile Edit Form -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <form method="POST" action="<?= site_url('doctor/profile/update') ?>">
                        <?= csrf_field() ?>

                        <div class="space-y-4">
                            <div>
                                <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                <input type="text" id="full_name" name="full_name" value="<?= html_escape($doctor_name) ?>" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" id="email" name="email" value="<?= html_escape($doctor_email) ?>" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>

                            <div>
                                <label for="specialty" class="block text-sm font-medium text-gray-700 mb-1">Specialty</label>
                                <input type="text" id="specialty" name="specialty" value="<?= html_escape($doctor_specialty) ?>" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="e.g., General Dentistry, Orthodontics">
                            </div>

                            <hr class="my-6">

                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Change Password (Optional)</h3>

                            <div>
                                <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                <input type="password" id="new_password" name="new_password"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Leave blank to keep current password">
                            </div>

                            <div>
                                <label for="confirm_new_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                                <input type="password" id="confirm_new_password" name="confirm_new_password"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Confirm your new password">
                            </div>

                            <div class="flex gap-3 pt-4">
                                <button type="submit"
                                    class="px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition font-semibold">
                                    Update Profile
                                </button>
                                <a href="<?= site_url('doctor/dashboard') ?>"
                                    class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-semibold">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Danger Zone -->
                <div class="bg-red-50 border border-red-200 rounded-xl p-6 mt-8">
                    <h3 class="text-lg font-bold text-red-700 mb-2">Danger Zone</h3>
                    <p class="text-sm text-red-600 mb-4">Once you delete your account, there is no going back. Please be certain.</p>
                    <button onclick="openDeleteModal()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-semibold">
                        Delete My Account
                    </button>
                </div>

            </div>
        </main>
    </div>

    <!-- Delete Account Confirmation Modal -->
    <div id="delete-modal"
        class="modal fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4 transition-opacity duration-300 ease-in-out"
        onclick="closeDeleteModal(event)">

        <div class="bg-white w-full max-w-md p-8 rounded-2xl shadow-xl transform transition-transform duration-300 ease-in-out scale-95"
            onclick="event.stopPropagation()">

            <div class="flex flex-col items-center text-center mb-6">
                <div class="mb-4 text-red-500 text-5xl">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-semibold text-gray-800">Delete Account</h3>
            </div>

            <div class="mb-6">
                <p class="text-gray-600 text-center mb-3">
                    Are you absolutely sure you want to delete your account?
                </p>
                <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-3">
                    <p class="text-sm text-red-700 font-medium">⚠️ This action cannot be undone!</p>
                    <ul class="text-xs text-red-600 mt-2 space-y-1 list-disc list-inside">
                        <li>Your user account will be permanently deleted</li>
                        <li>You will be unlinked from your doctor profile</li>
                        <li>You will be logged out immediately</li>
                    </ul>
                </div>
            </div>

            <form id="delete-form" method="POST" action="<?= site_url('doctor/profile_delete') ?>">
                <?= csrf_field() ?>
                <div class="flex justify-center gap-4">
                    <button type="button" onclick="closeDeleteModal()"
                        class="px-6 py-2.5 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-100 transition font-medium">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-6 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium shadow-md">
                        Delete My Account
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script>
        // Logout Modal
        (function() {
            const logoutModal = document.getElementById('logout-modal');

            function openLogoutModal() {
                logoutModal.classList.remove('hidden');
                logoutModal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            }

            function closeLogoutModal(event = null) {
                if (!event || event.target.id === 'logout-modal') {
                    logoutModal.classList.remove('flex');
                    logoutModal.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                }
            }

            window.openLogoutModal = openLogoutModal;
            window.closeLogoutModal = closeLogoutModal;
        })();

        // Delete Account Modal
        (function() {
            const deleteModal = document.getElementById('delete-modal');

            function openDeleteModal() {
                deleteModal.classList.remove('hidden');
                deleteModal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            }

            function closeDeleteModal(event = null) {
                if (!event || event.target.id === 'delete-modal') {
                    deleteModal.classList.remove('flex');
                    deleteModal.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                }
            }

            window.openDeleteModal = openDeleteModal;
            window.closeDeleteModal = closeDeleteModal;
        })();
    </script>

</body>

</html>
