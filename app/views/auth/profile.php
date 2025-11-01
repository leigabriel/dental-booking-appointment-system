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
            font-family: 'JetBrains Mono', monospace;
        }

        ::-webkit-scrollbar {
            width: 2px;
        }

        ::-webkit-scrollbar-track {
            background: #212631;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            border-radius: 10px;
        }
    </style>
</head>

<body class="relative isolate bg-blue-950 flex items-center justify-center min-h-screen p-6 sm:p-10 text-slate-800">

    <div aria-hidden="true" class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80">
        <div style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" class="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-30 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]"></div>
    </div>

    <div class="max-w-7xl bg-blue-900 border-4 border-white rounded-2xl p-8 mx-auto space-y-1">
        <!-- HEADER -->
        <header class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 bg-white shadow-sm rounded-2xl p-6 border">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-full bg-gradient-to-tr from-indigo-600 to-sky-500 flex items-center justify-center text-white text-4xl italic font-bold shadow-md">
                    <?= strtoupper(substr($user['username'] ?? 'U', 0, 1)) ?>
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900"><?= html_escape($user['full_name'] ?? 'User') ?></h1>
                    <p class="bg-blue-600 p-1 px-4 rounded-2xl text-sm text-white">@<?= html_escape($user['username'] ?? '') ?> • <span class="text-xs text-gray-300">Member since <?= date('M Y', strtotime($user['created_at'] ?? date('Y-m-d'))) ?></span></p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="<?= site_url('/') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-50 border rounded-lg text-sm text-slate-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Home
                </a>
                <a href="<?= site_url('/book') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm shadow">Book Appointment</a>
            </div>
        </header>

        <!-- FLASH MESSAGE -->
        <?php if ($flash_message): ?>
            <div class="p-4 rounded-xl <?= $is_success ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200' ?>">
                <?= html_escape($flash_message) ?>
            </div>
        <?php endif; ?>

        <!-- MAIN CONTENT -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-1">

            <!-- LEFT: PROFILE SETTINGS -->
            <aside class="col-span-1 bg-white border rounded-2xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Profile Settings</h2>
                <form method="POST" action="<?= site_url('profile/update') ?>" class="space-y-4">
                    <?= csrf_field() ?>

                    <div>
                        <label for="full_name" class="block text-sm font-medium text-slate-700">Full Name</label>
                        <input type="text" id="full_name" name="full_name" value="<?= html_escape($user['full_name'] ?? '') ?>" required
                            class="mt-1 block w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-indigo-400 outline-none transition">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700">Email Address</label>
                        <input type="email" id="email" name="email" value="<?= html_escape($user['email'] ?? '') ?>" required
                            class="mt-1 block w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-indigo-400 outline-none transition">
                    </div>

                    <div>
                        <label for="username" class="block text-sm font-medium text-slate-700">Username</label>
                        <input type="text" id="username" value="<?= html_escape($user['username'] ?? '') ?>" readonly
                            class="mt-1 block w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-500">
                    </div>

                    <h3 class="text-sm font-medium text-slate-800 mt-2">Change Password (Optional)</h3>
                    <div>
                        <input type="password" id="new_password" name="new_password" placeholder="New password"
                            class="mt-1 block w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-indigo-400 outline-none transition">
                    </div>

                    <div>
                        <input type="password" id="confirm_new_password" name="confirm_new_password" placeholder="Confirm new password"
                            class="mt-1 block w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-indigo-400 outline-none transition">
                    </div>

                    <div class="flex justify-between items-center pt-2">
                        <a href="<?= site_url('profile/delete') ?>" onclick="return confirm('WARNING: Are you sure you want to permanently delete your account? This action cannot be undone.');" class="text-sm text-rose-600 hover:underline">Delete account</a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-semibold">Save changes</button>
                    </div>
                </form>
            </aside>

            <!-- RIGHT: MY APPOINTMENTS -->
            <section class="col-span-2 bg-white border rounded-2xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-slate-900">My Appointments</h2>
                    <p class="text-sm text-slate-500">Upcoming and recent appointments</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm border-collapse">
                        <thead>
                            <tr class="text-left text-xs text-slate-500 border-b">
                                <th class="py-2 px-3">Doctor</th>
                                <th class="py-2 px-3">Service</th>
                                <th class="py-2 px-3">Date</th>
                                <th class="py-2 px-3">Time</th>
                                <th class="py-2 px-3">Status</th>
                                <th class="py-2 px-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php if (!empty($appointments)): ?>
                                <?php foreach ($appointments as $app): ?>
                                    <tr class="hover:bg-blue-200">
                                        <td class="py-3 px-3">Dr. <?= html_escape($doctors[$app['doctor_id']]['name'] ?? 'N/A') ?></td>
                                        <td class="py-3 px-3"><?= html_escape($services[$app['service_id']]['name'] ?? 'N/A') ?></td>
                                        <td class="py-3 px-3"><?= html_escape(date('M j, Y', strtotime($app['appointment_date']))) ?></td>
                                        <td class="py-3 px-3"><span class="font-medium text-slate-700"><?= html_escape(date('g:i A', strtotime($app['time_slot']))) ?></span></td>
                                        <td class="py-3 px-3">
                                            <?php
                                            $status_class = match ($app['status']) {
                                                'confirmed' => 'text-emerald-700 bg-emerald-50 border-emerald-100',
                                                'pending' => 'text-amber-700 bg-amber-50 border-amber-100',
                                                'declined' => 'text-rose-700 bg-rose-50 border-rose-100',
                                                default => 'text-slate-700 bg-slate-50 border-slate-100',
                                            };
                                            ?>
                                            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold border <?= $status_class ?>">
                                                <?= html_escape(ucfirst($app['status'])) ?>
                                            </span>
                                        </td>

                                        <td class="py-3 px-3">
                                            <div class="flex items-center gap-2">
                                                <?php if (in_array($app['status'], ['pending'])): ?>
                                                    <form method="POST" action="<?= site_url('profile/cancel_appointment') ?>" onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="appointment_id" value="<?= html_escape($app['id']) ?>">
                                                        <button type="submit" class="inline-flex items-center gap-2 px-3 py-1 bg-rose-600 hover:bg-rose-500 text-white text-xs font-semibold rounded">Cancel</button>
                                                    </form>
                                                <?php elseif ($app['status'] === 'declined' && !empty($app['decline_message'])): ?>
                                                    <button type="button" class="inline-flex items-center gap-2 px-3 py-1 bg-sky-600 hover:bg-sky-500 text-white text-xs font-semibold rounded" onclick="openViewDeclineModal(this)" data-message="<?= html_escape($app['decline_message']) ?>">View message</button>
                                                <?php else: ?>
                                                    <span class="text-xs text-slate-500">—</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-slate-500">You have no scheduled appointments.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

        </div>
    </div>

    <!-- View Decline Message Modal -->
    <div id="view-decline-modal" class="fixed inset-0 bg-blue-950/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4"
        onclick="if(event.target.id==='view-decline-modal') closeViewDeclineModal();">

        <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl border border-gray-200 p-6 sm:p-8"
            onclick="event.stopPropagation()">

            <div class="flex items-center gap-3">
                <span class="shrink-0 rounded-full bg-red-100 p-2 text-red-600">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3.75m0 3.75h.008v.008H12V16.5zm0-12a9 9 0 100 18 9 9 0 000-18z" />
                    </svg>
                </span>
                <h3 class="text-lg sm:text-xl font-semibold text-red-600">Decline Reason</h3>
            </div>

            <div class="mt-4 border border-gray-400/50 p-8">
                <p id="view-decline-message" class="text-[#212631] text-2xl leading-relaxed"></p>
            </div>

            <p class="mt-8 bg-blue-700 text-white rounded-xl p-2 px-4">From: DentalCare</p>
            <div class="mt-6 flex justify-end">
                <button onclick="closeViewDeclineModal()"
                    class="rounded-lg bg-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-800 hover:bg-gray-300 transition">
                    Close
                </button>
            </div>
        </div>
    </div>

    <div aria-hidden="true" class="absolute inset-x-0 top-[calc(100%-13rem)] -z-10 transform-gpu overflow-hidden blur-3xl sm:top-[calc(100%-30rem)]">
        <div style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" class="relative left-[calc(50%+3rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-30 sm:left-[calc(50%+36rem)] sm:w-[50.1875rem]"></div>
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