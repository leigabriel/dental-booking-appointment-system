<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');
$LAVA = lava_instance();
$user = $user ?? [];

$appointments = $appointments ?? [];
$doctors = $doctors ?? [];
$services = $services ?? [];

$flash_message = $LAVA->session->flashdata('success_message') ?? $LAVA->session->flashdata('error_message');
$is_success = $LAVA->session->flashdata('success_message') ? true : false;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - DENTALCARE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-blue-950 min-h-screen p-6 sm:p-10 text-blue-100">
    <div class="max-w-8xl mx-auto space-y-8">
        <!-- HEADER -->
        <header class="flex justify-between items-center bg-blue-800/40 backdrop-blur-sm border border-blue-700 p-6 rounded-2xl shadow-xl">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-indigo-400" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7H3zm0-2h18M9 10h6m-3 4h.01" />
                </svg>
                My DENTALCARE Account
            </h1>
            <div class="flex gap-4 text-sm">
                <a href="<?= site_url('/') ?>" class="text-blue-300 hover:text-white transition">← Home</a>
                <a href="<?= site_url('/book') ?>" class="text-blue-300 hover:text-white transition">← Book</a>
            </div>
        </header>

        <!-- FLASH MESSAGE -->
        <?php if ($flash_message): ?>
            <div class="p-4 rounded-xl <?= $is_success ? 'bg-green-600/20 text-green-300 border border-green-500/30' : 'bg-red-600/20 text-red-300 border border-red-500/30' ?>">
                <?= html_escape($flash_message) ?>
            </div>
        <?php endif; ?>

        <!-- MAIN CONTENT -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- LEFT: MY APPOINTMENTS -->
            <div class="bg-blue-800/40 border border-blue-700 rounded-2xl shadow-2xl p-6 backdrop-blur-sm">
                <h2 class="text-xl font-semibold text-white mb-6 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    My Appointments
                </h2>

                <div class="overflow-x-auto rounded-xl border border-blue-700/50">
                    <table class="min-w-full text-sm">
                        <thead class="bg-blue-700/50 text-blue-100 uppercase text-xs">
                                <tr>
                                <th class="px-3 py-3 text-left">Doctor</th>
                                <th class="px-3 py-3 text-left">Service</th>
                                <th class="px-3 py-3 text-left">Date / Time</th>
                                <th class="px-3 py-3 text-left">Status</th>
                                <th class="px-3 py-3 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-blue-700/40">
                            <?php if (!empty($appointments)): ?>
                                <?php foreach ($appointments as $app): ?>
                                    <tr class="hover:bg-blue-700/30 transition">
                                        <td class="px-3 py-3">Dr. <?= html_escape($doctors[$app['doctor_id']]['name'] ?? 'N/A') ?></td>
                                        <td class="px-3 py-3"><?= html_escape($services[$app['service_id']]['name'] ?? 'N/A') ?></td>
                                        <td class="px-3 py-3">
                                            <?= html_escape(date('M j, Y', strtotime($app['appointment_date']))) ?><br>
                                            <span class="text-blue-200 font-semibold"><?= html_escape(date('g:i A', strtotime($app['time_slot']))) ?></span>
                                        </td>
                                        <td class="px-3 py-3">
                                            <?php
                                            $status_class = match ($app['status']) {
                                                'confirmed' => 'bg-green-600/30 text-green-300 border border-green-500/40',
                                                'pending' => 'bg-yellow-600/30 text-yellow-300 border border-yellow-500/40',
                                                'declined' => 'bg-red-600/30 text-red-300 border border-red-500/40',
                                                default => 'bg-red-600/30 text-red-300 border border-red-500/40',
                                            };
                                            ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $status_class ?>">
                                                <?= html_escape(ucfirst($app['status'])) ?>
                                            </span>

                                            <?php if (isset($app['status']) && $app['status'] === 'declined' && !empty($app['decline_message'])): ?>
                                            <?php endif; ?>
                                        </td>

                                        <td class="px-3 py-3">
                                            <?php if (in_array($app['status'], ['pending', 'confirmed'])): ?>
                                                <form method="POST" action="<?= site_url('profile/cancel_appointment') ?>" onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="appointment_id" value="<?= html_escape($app['id']) ?>">
                                                    <button type="submit" class="px-3 py-1 bg-red-600 hover:bg-red-500 text-white text-xs font-semibold rounded-lg">Cancel</button>
                                                </form>
                                            <?php elseif ($app['status'] === 'declined' && !empty($app['decline_message'])): ?>
                                                <button type="button" class="px-3 py-1 bg-blue-600 hover:bg-blue-500 text-white text-xs font-semibold rounded-lg" onclick="openViewDeclineModal(this)" data-message="<?= html_escape($app['decline_message']) ?>">View message</button>
                                            <?php else: ?>
                                                <span class="text-xs text-blue-300">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="px-3 py-4 text-center text-blue-300">You have no scheduled appointments.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- RIGHT: PROFILE SETTINGS -->
            <div class="bg-blue-800/40 border border-blue-700 rounded-2xl shadow-2xl p-6 backdrop-blur-sm">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-tr from-indigo-400 to-blue-500 flex items-center justify-center text-white text-2xl font-bold shadow-lg">
                        <?= strtoupper(substr($user['username'] ?? 'U', 0, 1)) ?>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-white"><?= html_escape($user['full_name'] ?? 'User') ?></h2>
                        <p class="text-blue-300 text-sm">@<?= html_escape($user['username'] ?? '') ?></p>
                    </div>
                </div>

                <form method="POST" action="<?= site_url('profile/update') ?>" class="space-y-5">
                    <?= csrf_field() ?>

                    <div>
                        <label for="full_name" class="block text-sm font-medium text-blue-200">Full Name</label>
                        <input type="text" id="full_name" name="full_name" value="<?= html_escape($user['full_name'] ?? '') ?>" required
                            class="mt-1 block w-full px-4 py-2 rounded-lg bg-blue-900/40 border border-blue-700 text-white placeholder-blue-300 focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-blue-200">Email Address</label>
                        <input type="email" id="email" name="email" value="<?= html_escape($user['email'] ?? '') ?>" required
                            class="mt-1 block w-full px-4 py-2 rounded-lg bg-blue-900/40 border border-blue-700 text-white placeholder-blue-300 focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    </div>

                    <div>
                        <label for="username" class="block text-sm font-medium text-blue-200">Username (Non-Editable)</label>
                        <input type="text" id="username" value="<?= html_escape($user['username'] ?? '') ?>" readonly
                            class="mt-1 block w-full px-4 py-2 rounded-lg bg-blue-900/20 border border-blue-700 text-blue-300">
                    </div>

                    <h3 class="text-lg font-semibold border-t border-blue-700 pt-4 mt-4 text-blue-100">Change Password (Optional)</h3>

                    <div>
                        <label for="new_password" class="block text-sm font-medium text-blue-200">New Password</label>
                        <input type="password" id="new_password" name="new_password" placeholder="Leave blank to keep current password"
                            class="mt-1 block w-full px-4 py-2 rounded-lg bg-blue-900/40 border border-blue-700 text-white placeholder-blue-300 focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    </div>

                    <div>
                        <label for="confirm_new_password" class="block text-sm font-medium text-blue-200">Confirm New Password</label>
                        <input type="password" id="confirm_new_password" name="confirm_new_password"
                            class="mt-1 block w-full px-4 py-2 rounded-lg bg-blue-900/40 border border-blue-700 text-white placeholder-blue-300 focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    </div>

                    <div class="flex justify-end pt-4">
                        <button type="submit"
                            class="px-6 py-2 bg-indigo-500 hover:bg-indigo-400 text-white font-semibold rounded-lg transition shadow-lg">
                            Save Changes
                        </button>
                    </div>
                </form>

                <div class="mt-8 pt-6 border-t border-blue-700">
                    <h3 class="text-lg font-semibold text-red-400">Danger Zone</h3>
                    <p class="text-sm text-blue-300 mb-4">Permanently delete your account and all associated records.</p>
                    <a href="<?= site_url('profile/delete') ?>"
                        onclick="return confirm('WARNING: Are you sure you want to permanently delete your account? This action cannot be undone.');"
                        class="inline-block px-4 py-2 bg-red-600 hover:bg-red-500 text-white font-semibold rounded-lg transition shadow-md">
                        Delete Account
                    </a>
                </div>
            </div>
        </div>
    </div>
                <!-- View Decline Message Modal -->
                <div id="view-decline-modal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-50 p-4" onclick="if(event.target.id==='view-decline-modal') closeViewDeclineModal();">
                    <div class="bg-blue-900/95 text-white w-full max-w-lg p-6 rounded-2xl shadow-xl" onclick="event.stopPropagation()">
                        <h3 class="text-lg font-semibold">Decline Reason</h3>
                        <div id="view-decline-message" class="mt-4 text-sm leading-relaxed"></div>
                        <div class="mt-6 text-right">
                            <button onclick="closeViewDeclineModal()" class="px-4 py-2 bg-white text-blue-900 rounded">Close</button>
                        </div>
                    </div>
                </div>

                <script>
                    function openViewDeclineModal(btn) {
                        const message = btn.getAttribute('data-message') || '';
                        const modal = document.getElementById('view-decline-modal');
                        const container = document.getElementById('view-decline-message');
                        container.textContent = message;
                        modal.classList.remove('hidden');
                        modal.classList.add('flex');
                        document.body.classList.add('overflow-hidden');
                    }

                    function closeViewDeclineModal() {
                        const modal = document.getElementById('view-decline-modal');
                        modal.classList.remove('flex');
                        modal.classList.add('hidden');
                        document.body.classList.remove('overflow-hidden');
                        const container = document.getElementById('view-decline-message');
                        container.textContent = '';
                    }
                </script>

            </body>

            </html>