<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');
$LAVA = lava_instance();
$username = $LAVA->session->userdata('username');
$current_role = $LAVA->session->userdata('role');

$doctorProfile = $doctorProfile ?? null;
$userDetails = $userDetails ?? [];
$pending_appointments = $pending_appointments ?? [];
$confirmed_appointments = $confirmed_appointments ?? [];

$doctor_name = $userDetails['full_name'] ?? $username;
$doctor_email = $userDetails['email'] ?? 'No Email Set';
$doctor_specialty = $doctorProfile['specialty'] ?? 'Not Set';

$flash_message = $LAVA->session->flashdata('success_message') ?? $LAVA->session->flashdata('error_message');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary-color: #3B82F6;
            --primary-hover: #2563EB;
            --sidebar-bg: #111827;
            --sidebar-text: #D1D5DB;
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
        <aside class="w-20 bg-blue-900 text-gray-300 p-3 flex flex-col items-center justify-between shadow-2xl sticky top-0 h-screen z-20">
            <div>
                <a href="<?= site_url('doctor/dashboard') ?>" title="Dentalcare Home" class="flex items-center justify-center h-12 w-12 mb-8 rounded-full bg-gray-800 border-gray-700 border-2 text-white shadow-md hover:bg-gray-700">
                    <img src="<?= site_url('public/img/dentalcare512x512.png') ?>" alt="Dentalcare Logo" class="w-6 h-6">
                </a>

                <nav class="space-y-4">
                    <a href="<?= site_url('doctor/dashboard') ?>" title="Dashboard"
                        class="flex items-center justify-center h-12 w-12 rounded-full transition-colors duration-200 relative group border-2 bg-blue-600 border-blue-400 text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span class="absolute left-16 bg-gray-800 text-white px-2 py-1 rounded text-xs whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-30">Dashboard</span>
                    </a>

                    <a href="<?= site_url('doctor/profile') ?>" title="My Profile"
                        class="flex items-center justify-center h-12 w-12 rounded-full transition-colors duration-200 relative group border-2 border-transparent hover:bg-blue-700 hover:border-blue-500">
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
                <p class="text-gray-600 text-center mb-8">Are you sure you want to logout? This will end your current session.</p>
                <div class="flex justify-center gap-4">
                    <button type="button" onclick="closeLogoutModal()"
                        class="px-6 py-2.5 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-100 font-medium">Cancel</button>
                    <a id="confirm-logout-btn" href="<?= site_url('logout') ?>"
                        class="px-6 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium shadow-md">Logout</a>
                </div>
            </div>
        </div>

        <div id="decline-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4" onclick="closeDeclineModal(event)">
            <div class="bg-white w-full max-w-md p-6 rounded-xl shadow-xl transform transition-all" onclick="event.stopPropagation()">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Decline Appointment</h3>
                <form action="<?= site_url('doctor/appointment_decline') ?>" method="POST">
                    <input type="hidden" name="appointment_id" id="decline_appointment_id">
                    <div class="mb-4">
                        <label for="decline_message" class="block text-sm font-medium text-gray-700 mb-1">Reason for declining:</label>
                        <textarea name="decline_message" id="decline_message" rows="3" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2 border"
                            placeholder="e.g., Doctor unavailable, schedule conflict..."></textarea>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeDeclineModal()"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Cancel</button>
                        <button type="submit"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Decline Appointment</button>
                    </div>
                </form>
            </div>
        </div>

        <main class="flex-1 p-8 overflow-y-auto h-screen">
            <div class="max-w-7xl mx-auto">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-800">Doctor Dashboard</h1>
                    <p class="text-gray-600 mt-2">Welcome back, Dr. <?= html_escape($doctor_name) ?>!</p>
                    <?php if (!$doctorProfile): ?>
                        <p class="mt-2 p-2 bg-yellow-100 text-yellow-700 rounded border border-yellow-300 inline-block">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Your account is not linked to a doctor profile. Appointments may not appear.
                        </p>
                    <?php endif; ?>
                </div>

                <?php if ($flash_message): ?>
                    <div class="mb-6 p-4 rounded-lg <?= strpos($flash_message, 'success') !== false ? 'bg-green-100 text-green-700 border border-green-300' : 'bg-red-100 text-red-700 border border-red-300' ?>">
                        <?= html_escape($flash_message) ?>
                    </div>
                <?php endif; ?>

                <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">My Profile</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">Name</p>
                            <p class="text-lg font-semibold text-gray-800"><?= html_escape($doctor_name) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Email</p>
                            <p class="text-lg font-semibold text-gray-800"><?= html_escape($doctor_email) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Specialty</p>
                            <p class="text-lg font-semibold text-gray-800"><?= html_escape($doctor_specialty) ?></p>
                        </div>
                    </div>
                    <div class="mt-4 flex gap-3">
                        <a href="<?= site_url('doctor/profile') ?>" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">Edit Profile</a>
                        <?php if ($doctorProfile): ?>
                            <?php 
                                $is_available = isset($doctorProfile['is_available']) ? $doctorProfile['is_available'] : 1;
                                $status_class = $is_available ? 'bg-green-500 hover:bg-green-600' : 'bg-red-500 hover:bg-red-600';
                                $status_text = $is_available ? 'Available' : 'Unavailable';
                                $icon = $is_available ? 'check-circle' : 'x-circle';
                            ?>
                            <a href="<?= site_url('doctor/toggle_availability') ?>" 
                               class="px-4 py-2 <?= $status_class ?> text-white rounded-lg transition flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <?php if ($is_available): ?>
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    <?php else: ?>
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    <?php endif; ?>
                                </svg>
                                <?= $status_text ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm opacity-80">Pending Appointments</p>
                                <p class="text-3xl font-bold mt-2"><?= count($pending_appointments) ?></p>
                            </div>
                            <div class="bg-white/20 p-3 rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-green-400 to-green-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm opacity-80">Confirmed Appointments</p>
                                <p class="text-3xl font-bold mt-2"><?= count($confirmed_appointments) ?></p>
                            </div>
                            <div class="bg-white/20 p-3 rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm opacity-80">Total Appointments</p>
                                <p class="text-3xl font-bold mt-2"><?= count($pending_appointments) + count($confirmed_appointments) ?></p>
                            </div>
                            <div class="bg-white/20 p-3 rounded-full">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Pending Appointments</h2>
                    <?php if (empty($pending_appointments)): ?>
                        <p class="text-gray-500 text-center py-8">No pending appointments</p>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50 border-b">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Patient</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Service</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Date</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Time</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php foreach ($pending_appointments as $apt): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm text-gray-800"><?= html_escape($apt['user_full_name'] ?? $apt['username'] ?? 'N/A') ?></td>
                                            <td class="px-4 py-3 text-sm text-gray-800"><?= html_escape($apt['service_name'] ?? 'N/A') ?></td>
                                            <td class="px-4 py-3 text-sm text-gray-800"><?= html_escape(date('M d, Y', strtotime($apt['appointment_date']))) ?></td>
                                            <td class="px-4 py-3 text-sm text-gray-800"><?= html_escape(date('h:i A', strtotime($apt['time_slot']))) ?></td>
                                            <td class="px-4 py-3">
                                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                            </td>
                                            <td class="px-4 py-3 text-sm flex gap-2">
                                                <a href="<?= site_url('doctor/appointment_confirm/' . $apt['id']) ?>"
                                                    class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs transition"
                                                    onclick="return confirm('Confirm this appointment?');">
                                                    Confirm
                                                </a>
                                                <button onclick="openDeclineModal(<?= $apt['id'] ?>)"
                                                    class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs transition">
                                                    Decline
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Confirmed Appointments</h2>
                    <?php if (empty($confirmed_appointments)): ?>
                        <p class="text-gray-500 text-center py-8">No confirmed appointments</p>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50 border-b">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Patient</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Service</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Date</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Time</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Payment</th>
                                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php foreach ($confirmed_appointments as $apt): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm text-gray-800"><?= html_escape($apt['user_full_name'] ?? $apt['username'] ?? 'N/A') ?></td>
                                            <td class="px-4 py-3 text-sm text-gray-800"><?= html_escape($apt['service_name'] ?? 'N/A') ?></td>
                                            <td class="px-4 py-3 text-sm text-gray-800"><?= html_escape(date('M d, Y', strtotime($apt['appointment_date']))) ?></td>
                                            <td class="px-4 py-3 text-sm text-gray-800"><?= html_escape(date('h:i A', strtotime($apt['time_slot']))) ?></td>
                                            <td class="px-4 py-3">
                                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $apt['payment_status'] === 'paid' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                                    <?= html_escape(ucfirst($apt['payment_status'])) ?>
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Confirmed</span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </main>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script>
        // Logout Modal
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

        // Decline Modal
        const declineModal = document.getElementById('decline-modal');
        const declineInput = document.getElementById('decline_appointment_id');

        function openDeclineModal(id) {
            declineInput.value = id;
            declineModal.classList.remove('hidden');
            declineModal.classList.add('flex');
        }

        function closeDeclineModal(event = null) {
            if (!event || event.target.id === 'decline-modal') {
                declineModal.classList.remove('flex');
                declineModal.classList.add('hidden');
            }
        }

        window.openLogoutModal = openLogoutModal;
        window.closeLogoutModal = closeLogoutModal;
        window.openDeclineModal = openDeclineModal;
        window.closeDeclineModal = closeDeclineModal;
    </script>

</body>

</html>