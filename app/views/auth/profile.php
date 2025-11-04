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

    <div class="max-w-8xl bg-blue-900 border-4 border-white rounded-2xl p-8 mx-auto space-y-1">
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
                <a href="#" class="logout-confirm group relative rounded-full p-1 bg-red-700 flex border-2 border-gray-600 items-center text-sm/6 font-semibold text-white hover:text-red-300" data-logout-url="<?= site_url('logout') ?>">
                    <img src="https://cdn-icons-png.flaticon.com/128/10609/10609328.png" alt="Logout" class="h-6 w-6 filter invert">
                    <span class="absolute -bottom-8 left-1/2 -translate-x-1/2 whitespace-nowrap rounded bg-gray-700 px-2 py-1 text-xs text-white opacity-0 transition-opacity group-hover:opacity-100">
                        Log out
                    </span>
                </a>
            </div>
        </header>

        <!-- FLASH MESSAGE -->
        <?php if ($flash_message): ?>
            <div class="p-4 rounded-xl <?= $is_success ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200' ?>">
                <?= html_escape($flash_message) ?>
            </div>
        <?php endif; ?>

        <!-- MAIN CONTENT -->
        <div class="space-y-6">

            <!-- TOP SECTION: PROFILE & CONFIRMED APPOINTMENTS -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

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

                <!-- RIGHT: CONFIRMED APPOINTMENTS RECEIPT CARDS -->
                <section class="col-span-2 bg-white border rounded-2xl shadow-sm p-6">
                    <?php
                    $confirmed_appointments = array_filter($appointments, function($app) {
                        return $app['status'] === 'confirmed';
                    });
                    ?>
                    
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                                <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Confirmed Appointments
                            </h2>
                            <p class="text-sm text-slate-500 mt-1">Your upcoming confirmed visits</p>
                        </div>
                        <span class="px-4 py-2 bg-emerald-100 text-emerald-700 rounded-full text-sm font-bold shadow-sm">
                            <?= count($confirmed_appointments) ?> Active
                        </span>
                    </div>
                    
                    <?php if (!empty($confirmed_appointments)): ?>
                        <div class="grid gap-4 md:grid-cols-2 max-h-[600px] overflow-y-auto pr-2">
                            <?php foreach ($confirmed_appointments as $app): ?>
                                <?php
                                $doctor = $doctors[$app['doctor_id']] ?? ['name' => 'N/A', 'specialty' => 'N/A'];
                                $service = $services[$app['service_id']] ?? ['name' => 'N/A', 'price' => 0, 'duration_mins' => 0];
                                $appointment_date = date('l, M j, Y', strtotime($app['appointment_date']));
                                $appointment_time = date('g:i A', strtotime($app['time_slot']));
                                ?>
                                <div class="relative bg-gradient-to-br from-emerald-50 to-blue-50 border-2 border-emerald-200 rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300">
                                    <!-- Receipt Header -->
                                    <div class="bg-emerald-600 text-white p-4 relative">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h3 class="text-lg font-bold">DENTALCARE</h3>
                                                <p class="text-xs text-emerald-100">Appointment Receipt</p>
                                            </div>
                                            <div class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full">
                                                <span class="text-xs font-bold">CONFIRMED</span>
                                            </div>
                                        </div>
                                        <!-- Dotted tear line -->
                                        <div class="absolute bottom-0 left-0 right-0 h-4 bg-white" style="
                                            background-image: radial-gradient(circle at 10px -5px, transparent 12px, white 13px);
                                            background-size: 20px 20px;
                                            background-repeat: repeat-x;
                                        "></div>
                                    </div>

                                    <!-- Receipt Body -->
                                    <div class="p-5 space-y-4">
                                        <!-- Appointment ID -->
                                        <div class="text-center pb-3 border-b border-dashed border-slate-300">
                                            <p class="text-xs text-slate-500 uppercase tracking-wider">Appointment ID</p>
                                            <p class="text-lg font-mono font-bold text-slate-900">#<?= str_pad($app['id'], 6, '0', STR_PAD_LEFT) ?></p>
                                        </div>

                                        <!-- Doctor Info -->
                                        <div class="flex items-start gap-3">
                                            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-md">
                                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-xs text-slate-500 uppercase tracking-wider">Doctor</p>
                                                <p class="text-base font-bold text-slate-900">Dr. <?= html_escape($doctor['name']) ?></p>
                                                <p class="text-xs text-indigo-600 font-semibold"><?= html_escape($doctor['specialty']) ?></p>
                                            </div>
                                        </div>

                                        <!-- Service Info -->
                                        <div class="bg-white rounded-lg p-3 border border-slate-200">
                                            <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Service</p>
                                            <p class="text-sm font-bold text-slate-900"><?= html_escape($service['name']) ?></p>
                                            <div class="flex items-center justify-between mt-2 text-xs text-slate-600">
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    <?= html_escape($service['duration_mins']) ?> mins
                                                </span>
                                                <span class="text-emerald-600 font-bold text-base">$<?= number_format($service['price'], 2) ?></span>
                                            </div>
                                        </div>

                                        <!-- Date & Time -->
                                        <div class="grid grid-cols-2 gap-3">
                                            <div class="bg-blue-50 rounded-lg p-3 border border-blue-200">
                                                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Date</p>
                                                <p class="text-xs font-bold text-slate-900"><?= html_escape($appointment_date) ?></p>
                                            </div>
                                            <div class="bg-purple-50 rounded-lg p-3 border border-purple-200">
                                                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Time</p>
                                                <p class="text-xs font-bold text-slate-900"><?= html_escape($appointment_time) ?></p>
                                            </div>
                                        </div>

                                        <!-- Footer Note -->
                                        <div class="pt-3 border-t border-dashed border-slate-300 text-center">
                                            <p class="text-xs text-slate-500">Please arrive 10 minutes early</p>
                                            <p class="text-xs text-emerald-600 font-semibold mt-1">✓ Confirmed & Ready</p>
                                        </div>
                                    </div>

                                    <!-- Receipt Bottom Tear -->
                                    <div class="h-4 bg-emerald-50" style="
                                        background-image: radial-gradient(circle at 10px 9px, transparent 12px, #ecfdf5 13px);
                                        background-size: 20px 20px;
                                        background-repeat: repeat-x;
                                    "></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12">
                            <svg class="w-20 h-20 mx-auto text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h3 class="text-lg font-semibold text-slate-900 mb-2">No Confirmed Appointments</h3>
                            <p class="text-slate-500 mb-4">You don't have any confirmed appointments yet.</p>
                            <a href="<?= site_url('/book') ?>" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg font-semibold shadow-lg transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Book Your First Appointment
                            </a>
                        </div>
                    <?php endif; ?>
                </section>
            </div>

            <!-- BOTTOM SECTION: ALL APPOINTMENTS TABLE -->
            <section class="bg-white border rounded-2xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                            <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Appointment History
                        </h2>
                        <p class="text-sm text-slate-500 mt-1">Complete record of all your appointments</p>
                    </div>
                    <?php if (!empty($appointments)): ?>
                        <button type="button" onclick="openClearHistoryModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-lg text-sm font-semibold shadow-lg transition-all transform hover:scale-105">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Clear All History
                        </button>
                    <?php endif; ?>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm border-collapse">
                        <thead>
                            <tr class="text-left text-xs text-slate-500 border border-gray-300/50">
                                <th class="py-2 px-3">Doctor</th>
                                <th class="py-2 px-3">Service</th>
                                <th class="py-2 px-3">Date</th>
                                <th class="py-2 px-3">Time</th>
                                <th class="py-2 px-3">Status</th>
                                <th class="py-2 px-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y border border-gray-300/50">
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

    <!-- Clear All History Confirmation Modal -->
    <div id="clearHistoryModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div class="relative w-full max-w-md border-2 border-rose-300 duration-500 group overflow-hidden rounded-2xl bg-white text-slate-900 p-6 shadow-2xl">
            <!-- Warning Icon -->
            <div class="flex flex-col items-center text-center mb-6">
                <div class="mb-4 p-4 rounded-full bg-rose-100">
                    <svg class="w-12 h-12 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-slate-900">Clear All History?</h3>
                <p class="text-sm text-slate-600 mt-2">This will permanently delete all your appointment records</p>
            </div>

            <!-- Warning Message -->
            <div class="bg-rose-50 border border-rose-200 rounded-lg p-4 mb-6">
                <p class="text-sm text-rose-800 font-semibold mb-2">⚠️ Warning: This action cannot be undone!</p>
                <ul class="text-xs text-rose-700 space-y-1 list-disc list-inside">
                    <li>All appointment history will be deleted</li>
                    <li>Pending, declined, and cancelled records will be removed</li>
                    <li>Confirmed appointments will remain active</li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <button
                    id="cancelClearHistory"
                    class="px-6 py-3 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-lg font-semibold transition-all transform hover:scale-105">
                    Cancel
                </button>
                <form method="POST" action="<?= site_url('profile/clear_history') ?>" class="flex-1">
                    <?= csrf_field() ?>
                    <button
                        type="submit"
                        class="w-full px-6 py-3 bg-rose-600 hover:bg-rose-500 text-white rounded-lg font-semibold transition-all transform hover:scale-105 shadow-lg">
                        Yes, Clear All History
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- View Decline Message Modal -->
    <div id="view-decline-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4"
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

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div
            class="relative w-96 h-64 border-2 border-white duration-500 group overflow-hidden rounded-xl bg-neutral-900 text-neutral-50 p-6 flex flex-col justify-evenly border border-neutral-800 shadow-xl">
            <!-- Background Blobs -->
            <div class="absolute blur duration-500 group-hover:blur-none w-72 h-72 rounded-full group-hover:translate-x-10 group-hover:translate-y-10 bg-red-900 right-1 -bottom-24"></div>
            <div class="absolute blur duration-500 group-hover:blur-none w-12 h-12 rounded-full group-hover:translate-x-8 group-hover:translate-y-2 bg-rose-700 right-12 bottom-12"></div>
            <div class="absolute blur duration-500 group-hover:blur-none w-36 h-36 rounded-full group-hover:translate-x-10 group-hover:-translate-y-10 bg-rose-800 right-1 -top-12"></div>
            <div class="absolute blur duration-500 group-hover:blur-none w-24 h-24 bg-red-700 rounded-full group-hover:-translate-x-10"></div>

            <!-- Foreground Content -->
            <div class="z-10 flex flex-col justify-evenly h-full text-center">
                <h3 class="text-2xl font-bold mb-1">Confirm Logout</h3>
                <p class="text-sm text-gray-300 mb-4">
                    Are you sure you want to end your current session?
                </p>

                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <button
                        id="cancelLogout"
                        class="hover:bg-neutral-200 cursor-pointer bg-neutral-50 rounded text-neutral-800 font-semibold w-full sm:w-1/2 py-2 transition">
                        Cancel
                    </button>
                    <button
                        id="confirmLogout"
                        class="bg-red-600 cursor-pointer hover:bg-red-500 text-white rounded font-semibold w-full sm:w-1/2 py-2 transition">
                        Log Out
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div aria-hidden="true" class="absolute inset-x-0 top-[calc(100%-13rem)] -z-10 transform-gpu overflow-hidden blur-3xl sm:top-[calc(100%-30rem)]">
        <div style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" class="relative left-[calc(50%+3rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-30 sm:left-[calc(50%+36rem)] sm:w-[50.1875rem]"></div>
    </div>

    <script>
        function openClearHistoryModal() {
            const modal = document.getElementById('clearHistoryModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }

        function closeClearHistoryModal() {
            const modal = document.getElementById('clearHistoryModal');
            modal.classList.remove('flex');
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        // Close clear history modal on cancel button
        document.getElementById('cancelClearHistory')?.addEventListener('click', closeClearHistoryModal);

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

        (function() {
            const modal = document.getElementById('logoutModal');
            const confirmBtn = document.getElementById('confirmLogout');
            const cancelBtn = document.getElementById('cancelLogout');
            let targetLogoutUrl = null;

            function showModal(url) {
                targetLogoutUrl = url || '<?= site_url('logout') ?>';
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function hideModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                targetLogoutUrl = null;
            }

            document.querySelectorAll('.logout-confirm').forEach(el => {
                el.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = this.getAttribute('data-logout-url') || this.dataset.logoutUrl || '<?= site_url('logout') ?>';
                    showModal(url);
                });
            });

            cancelBtn.addEventListener('click', function() {
                hideModal();
            });

            confirmBtn.addEventListener('click', function() {
                if (targetLogoutUrl) {
                    window.location.href = targetLogoutUrl;
                } else {
                    window.location.href = '<?= site_url('logout') ?>';
                }
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    if (!modal.classList.contains('hidden')) {
                        hideModal();
                    }
                    if (!document.getElementById('clearHistoryModal').classList.contains('hidden')) {
                        closeClearHistoryModal();
                    }
                    if (!document.getElementById('view-decline-modal').classList.contains('hidden')) {
                        closeViewDeclineModal();
                    }
                }
            });
        })();
    </script>

</body>

</html>