<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');
$LAVA = lava_instance();
$username = $LAVA->session->userdata('username');
$current_role = $LAVA->session->userdata('role');

// Data from Management::appointments()
$appointments = $appointments ?? [];
$doctors = $doctors ?? [];
$services = $services ?? [];
$users = $users ?? [];

$flash_message = $LAVA->session->flashdata('success_message') ?? $LAVA->session->flashdata('error_message');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3B82F6;
            --primary-hover: #2563EB;
            --sidebar-bg: #111827;
            --sidebar-text: #D1D5DB;
            --sidebar-active-bg: #3B82F6;
            --sidebar-active-text: #FFFFFF;
        }

        body {
            font-family: 'JetBrains Mono', monospace;
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

        ::-webkit-scrollbar-thumb:hover {
            background: #888;
        }
    </style>
</head>

<body class="bg-gray-100">

    <div class="flex min-h-screen">

        <aside class="w-20 bg-blue-900 text-gray-300 p-3 flex flex-col items-center justify-between shadow-2xl sticky top-0 h-screen z-20">
            <div>
                <a href="<?= site_url('admin/dashboard') ?>" title="Dentalcare Home" class="flex items-center justify-center h-12 w-12 mb-8 rounded-full bg-gray-800 border-gray-700 border-2 text-white shadow-md hover:bg-gray-700">
                    <img src="<?= site_url('public/img/dentalcare512x512.png') ?>" alt="Dentalcare Logo" class="w-6 h-6">
                </a>

                <nav class="space-y-4">
                    <a href="<?= site_url('admin/dashboard') ?>" title="Dashboard"
                        class="flex items-center justify-center h-12 w-12 rounded-full transition-colors duration-200 relative group border-2
                              <?php /* ACTIVE STATE for dashboard.php: */ ?> bg-blue-600 border-blue-500 text-white shadow-md <?php /* END ACTIVE */ ?>
                              <?php /* INACTIVE STATE for other pages: */ ?> border-transparent text-gray-400 hover:bg-gray-800 hover:border-gray-700 hover:text-white <?php /* END INACTIVE */ ?>
                              ">
                        <img src="https://cdn-icons-png.flaticon.com/128/3914/3914820.png" alt="" class="w-6 h-6 invert">
                        <span class="absolute left-full ml-3 px-2 py-1 text-xs font-medium text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap -translate-x-2 group-hover:translate-x-0 pointer-events-none z-30">
                            Dashboard
                        </span>
                    </a>

                    <a href="<?= site_url('admin/calendar') ?>" title="Appointments"
                        class="flex items-center justify-center h-12 w-12 rounded-full transition-colors duration-200 relative group border-2
                              <?php /* ACTIVE STATE for appointments.php: */ ?> bg-blue-600 border-blue-500 text-white shadow-md <?php /* END ACTIVE */ ?>
                              <?php /* INACTIVE STATE for other pages: */ ?> border-transparent text-gray-400 hover:bg-gray-800 hover:border-gray-700 hover:text-white <?php /* END INACTIVE */ ?>
                              ">
                        <img src="https://cdn-icons-png.flaticon.com/128/747/747310.png" alt="" class="w-6 h-6 invert">
                        <span class="absolute left-full ml-3 px-2 py-1 text-xs font-medium text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap -translate-x-2 group-hover:translate-x-0 pointer-events-none z-30">
                            Calendar
                        </span>
                    </a>

                    <a href="<?= site_url('management/appointments') ?>" title="Appointments"
                        class="flex items-center justify-center h-12 w-12 rounded-full transition-colors duration-200 relative group border-2
                              <?php /* ACTIVE STATE for appointments.php: */ ?> bg-blue-600 border-blue-500 text-white shadow-md <?php /* END ACTIVE */ ?>
                              <?php /* INACTIVE STATE for other pages: */ ?> border-transparent text-gray-400 hover:bg-gray-800 hover:border-gray-700 hover:text-white <?php /* END INACTIVE */ ?>
                              ">
                        <img src="https://cdn-icons-png.flaticon.com/128/19027/19027040.png" alt="" class="w-6 h-6 invert">
                        <span class="absolute left-full ml-3 px-2 py-1 text-xs font-medium text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap -translate-x-2 group-hover:translate-x-0 pointer-events-none z-30">
                            Appointments
                        </span>
                    </a>

                    <a href="<?= site_url('management/doctors') ?>" title="Doctors"
                        class="flex items-center justify-center h-12 w-12 rounded-full transition-colors duration-200 relative group border-2
                              <?php /* ACTIVE STATE for doctor_management.php: */ ?> bg-blue-600 border-blue-500 text-white shadow-md <?php /* END ACTIVE */ ?>
                              <?php /* INACTIVE STATE for other pages: */ ?> border-transparent text-gray-400 hover:bg-gray-800 hover:border-gray-700 hover:text-white <?php /* END INACTIVE */ ?>
                              ">
                        <img src="https://cdn-icons-png.flaticon.com/128/9856/9856850.png" alt="" class="w-6 h-6 invert">
                        <span class="absolute left-full ml-3 px-2 py-1 text-xs font-medium text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap -translate-x-2 group-hover:translate-x-0 pointer-events-none z-30">
                            Doctors
                        </span>
                    </a>

                    <a href="<?= site_url('management/services') ?>" title="Services"
                        class="flex items-center justify-center h-12 w-12 rounded-full transition-colors duration-200 relative group border-2
                              <?php /* ACTIVE STATE for service_management.php: */ ?> bg-blue-600 border-blue-500 text-white shadow-md <?php /* END ACTIVE */ ?>
                              <?php /* INACTIVE STATE for other pages: */ ?> border-transparent text-gray-400 hover:bg-gray-800 hover:border-gray-700 hover:text-white <?php /* END INACTIVE */ ?>
                              ">
                        <img src="https://cdn-icons-png.flaticon.com/128/3914/3914079.png" alt="" class="w-6 h-6 invert">
                        <span class="absolute left-full ml-3 px-2 py-1 text-xs font-medium text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap -translate-x-2 group-hover:translate-x-0 pointer-events-none z-30">
                            Services
                        </span>
                    </a>

                    <a href="<?= site_url('reports') ?>" title="Services"
                        class="flex items-center justify-center h-12 w-12 rounded-full transition-colors duration-200 relative group border-2
                              <?php /* ACTIVE STATE for service_management.php: */ ?> bg-blue-600 border-blue-500 text-white shadow-md <?php /* END ACTIVE */ ?>
                              <?php /* INACTIVE STATE for other pages: */ ?> border-transparent text-gray-400 hover:bg-gray-800 hover:border-gray-700 hover:text-white <?php /* END INACTIVE */ ?>
                              ">
                        <img src="https://cdn-icons-png.flaticon.com/128/13085/13085474.png" alt="" class="w-6 h-6 invert">
                        <span class="absolute left-full ml-3 px-2 py-1 text-xs font-medium text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap -translate-x-2 group-hover:translate-x-0 pointer-events-none z-30">
                            Reports
                        </span>
                    </a>
                </nav>
            </div>

            <div>
                <a href="<?= site_url('logout') ?>" title="Logout"
                    class="flex items-center justify-center h-12 w-12 bg-red-600 border-2 border-red-500 rounded-full text-white hover:bg-red-700 hover:border-red-600 transition-colors relative group">
                    <img src="https://cdn-icons-png.flaticon.com/128/19006/19006863.png" alt="" class="w-6 h-6 invert">
                    <span class="absolute left-full ml-3 px-2 py-1 text-xs font-medium text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap -translate-x-2 group-hover:translate-x-0 pointer-events-none z-30">
                        Logout
                    </span>
                </a>
            </div>

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
                            href="#"
                            class="px-6 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition duration-150 font-medium shadow-md">
                            Logout
                        </a>
                    </div>
                </div>
            </div>

        </aside>

        <div class="flex-1 flex flex-col lg:flex-row">

            <main class="flex-1 p-6 sm:p-10 overflow-y-auto h-screen">
                <header class="mb-10">
                    <h1 class="text-8xl font-extrabold text-gray-900">Manage Appointments</h1>
                    <p class="text-lg text-gray-600 mt-1">View, confirm, and cancel patient bookings.</p>
                </header>

                <?php if ($flash_message): ?>
                    <div class="p-4 mb-6 rounded-lg <?= $LAVA->session->flashdata('success_message') ? 'bg-green-100 text-green-700 border-green-300' : 'bg-red-100 text-red-700 border-red-300' ?> border shadow-sm" role="alert">
                        <strong class="font-bold"><?= $LAVA->session->flashdata('success_message') ? 'Success!' : 'Error!' ?></strong>
                        <span><?= html_escape($flash_message) ?></span>
                    </div>
                <?php endif; ?>

                <section class="bg-white p-6 sm:p-8 rounded-xl shadow-lg border border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Doctor</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Service</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date & Time</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (!empty($appointments)): ?>
                                    <?php foreach ($appointments as $app): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-4 text-sm font-medium text-gray-900"><?= html_escape($app['id']) ?></td>
                                            <td class="px-3 py-4 text-sm text-gray-600"><?= html_escape($users[$app['user_id']]['full_name'] ?? 'N/A') ?></td>
                                            <td class="px-3 py-4 text-sm text-gray-600"><?= html_escape($doctors[$app['doctor_id']]['name'] ?? 'N/A') ?></td>
                                            <td class="px-3 py-4 text-sm text-gray-600">
                                                <div class="font-medium"><?= html_escape($services[$app['service_id']]['name'] ?? 'N/A') ?></div>
                                                <div class="text-xs text-green-600 font-bold">₱<?= number_format($services[$app['service_id']]['price'] ?? 0, 2) ?></div>
                                            </td>
                                            <td class="px-3 py-4 text-sm text-gray-600">
                                                <div><?= html_escape(date('M d, Y', strtotime($app['appointment_date']))) ?></div>
                                                <div class="text-xs text-gray-500"><?= html_escape($app['time_slot']) ?></div>
                                            </td>
                                            <td class="px-3 py-4 text-sm">
                                                <?php
                                                $payment_method = $app['payment_method'] ?? 'clinic';
                                                $payment_status = $app['payment_status'] ?? 'pending';

                                                // Payment Method Badge
                                                $method_badges = [
                                                    'gcash' => '<div class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-800 rounded-md text-xs font-semibold mb-1"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/><path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/></svg> GCash</div>',
                                                    'paypal' => '<div class="inline-flex items-center gap-1 px-2 py-1 bg-indigo-100 text-indigo-800 rounded-md text-xs font-semibold mb-1"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg> PayPal</div>',
                                                    'clinic' => '<div class="inline-flex items-center gap-1 px-2 py-1 bg-purple-100 text-purple-800 rounded-md text-xs font-semibold mb-1"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/></svg> At Clinic</div>'
                                                ];

                                                // Payment Status Badge
                                                $status_badges = [
                                                    'paid' => '<div class="inline-flex items-center gap-1 px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg> Paid</div>',
                                                    'unpaid' => '<div class="inline-flex items-center gap-1 px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-bold"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg> Unpaid</div>',
                                                    'pending' => '<div class="inline-flex items-center gap-1 px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-bold"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg> Pending</div>'
                                                ];

                                                echo $method_badges[$payment_method] ?? '<span class="text-xs text-gray-500">N/A</span>';
                                                echo $status_badges[$payment_status] ?? '';

                                                // Show "Mark as Paid" button for clinic payments that are not paid and appointment is not cancelled/declined
                                                if ($payment_method === 'clinic' && $payment_status !== 'paid' && !in_array($app['status'], ['cancelled', 'declined'])): ?>
                                                    <div class="mt-2">
                                                        <button onclick="markAsPaid(<?= html_escape($app['id']) ?>, '<?= html_escape($users[$app['user_id']]['full_name'] ?? 'Patient') ?>')"
                                                            class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md text-xs font-semibold transition-colors">
                                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                            </svg>
                                                            Mark as Paid
                                                        </button>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-3 py-4">
                                                <?php
                                                $status_class = match ($app['status']) {
                                                    'confirmed' => 'bg-green-100 text-green-800',
                                                    'cancelled' => 'bg-red-100 text-red-800',
                                                    default => 'bg-yellow-100 text-yellow-800',
                                                };
                                                ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $status_class ?>">
                                                    <?= html_escape(ucfirst($app['status'])) ?>
                                                </span>
                                            </td>
                                            <td class="px-3 py-4 text-sm space-x-2 whitespace-nowrap">
                                                <?php if ($app['status'] === 'pending'): ?>
                                                    <a href="<?= site_url('management/appointment_confirm/' . $app['id']) ?>" class="text-green-600 hover:text-green-800 font-medium">Confirm</a>
                                                    <a href="<?= site_url('management/appointment_cancel/' . $app['id']) ?>" class="text-red-600 hover:text-red-800 font-medium">Cancel</a>
                                                    <button type="button" onclick="openDeclineModal(<?= html_escape($app['id']) ?>, '<?= html_escape($users[$app['user_id']]['full_name'] ?? 'Patient') ?>')" class="text-orange-600 hover:text-orange-800 font-medium">Decline</button>
                                                <?php elseif ($app['status'] === 'confirmed'): ?>
                                                    <a href="<?= site_url('management/appointment_cancel/' . $app['id']) ?>" class="text-red-600 hover:text-red-800 font-medium">Cancel</a>
                                                    <button type="button" onclick="openDeclineModal(<?= html_escape($app['id']) ?>, '<?= html_escape($users[$app['user_id']]['full_name'] ?? 'Patient') ?>')" class="text-orange-600 hover:text-orange-800 font-medium">Decline</button>
                                                <?php elseif ($app['status'] === 'declined'): ?>
                                                    <span class="text-sm text-gray-600">Declined</span>
                                                <?php else: ?>
                                                    <span class="text-gray-400">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="px-3 py-4 text-center text-gray-500">No appointments found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>

        </div>
    </div>

    <!-- Decline Modal -->
    <div id="decline-modal" class="modal fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4" onclick="if(event.target.id==='decline-modal') closeDeclineModal();">
        <div class="bg-white w-full max-w-xl p-6 rounded-2xl shadow-xl" onclick="event.stopPropagation();">
            <h3 class="text-lg font-semibold mb-3">Decline Appointment</h3>
            <p class="text-sm text-gray-600 mb-4">Provide a short reason for declining this appointment. This message will be visible to the patient in their profile.</p>
            <form method="POST" action="<?= site_url('management/appointment_decline') ?>">
                <?= csrf_field() ?>
                <input type="hidden" id="decline-appointment-id" name="appointment_id" value="">
                <div class="mb-3">
                    <label for="decline_message" class="block text-sm font-medium text-gray-700">Message</label>
                    <textarea id="decline_message" name="decline_message" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeDeclineModal()" class="px-4 py-2 rounded-md border">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-md">Send & Decline</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Mark as Paid Confirmation Modal -->
    <div id="mark-paid-modal" class="modal fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4" onclick="if(event.target.id==='mark-paid-modal') closeMarkPaidModal();">
        <div class="bg-white w-full max-w-md p-6 rounded-2xl shadow-xl" onclick="event.stopPropagation();">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex-shrink-0 w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Mark Payment as Paid</h3>
                    <p class="text-sm text-gray-600">Confirm clinic payment received</p>
                </div>
            </div>
            <p class="text-sm text-gray-700 mb-6">
                Are you sure you want to mark this appointment payment as <span class="font-semibold text-emerald-600">PAID</span>?
                <br>
                <span class="text-xs text-gray-500 mt-1 block">Patient: <span id="mark-paid-patient-name" class="font-medium text-gray-700"></span></span>
            </p>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeMarkPaidModal()" class="px-4 py-2 rounded-md border border-gray-300 hover:bg-gray-50 text-gray-700 transition-colors">
                    Cancel
                </button>
                <a id="confirm-mark-paid-btn" href="#" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md font-medium transition-colors inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    Confirm Payment
                </a>
            </div>
        </div>
    </div>

    <script>
        // Logout modal functions
        (function() {
            const logoutAnchor = document.querySelector('a[title="Logout"]');
            const logoutModal = document.getElementById('logout-modal');
            const confirmBtn = document.getElementById('confirm-logout-btn');
            const logoutUrl = "<?= site_url('logout') ?>";

            function openLogoutModal() {
                confirmBtn.setAttribute('href', logoutUrl);
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

            // Expose to global scope for inline onclick calls used in markup
            window.openLogoutModal = openLogoutModal;
            window.closeLogoutModal = closeLogoutModal;

            if (logoutAnchor) {
                logoutAnchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    openLogoutModal();
                });
            }
        })();

        // Decline modal functions
        function openDeclineModal(appointmentId, patientName) {
            const modal = document.getElementById('decline-modal');
            modal.querySelector('#decline-appointment-id').value = appointmentId;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }

        function closeDeclineModal() {
            const modal = document.getElementById('decline-modal');
            modal.classList.remove('flex');
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            // clear textarea
            const ta = modal.querySelector('#decline_message');
            if (ta) ta.value = '';
        }

        // Mark as Paid modal functions
        function markAsPaid(appointmentId, patientName) {
            const modal = document.getElementById('mark-paid-modal');
            const confirmBtn = document.getElementById('confirm-mark-paid-btn');
            const patientNameSpan = document.getElementById('mark-paid-patient-name');

            patientNameSpan.textContent = patientName;
            confirmBtn.setAttribute('href', '<?= site_url('management/appointment_mark_paid/') ?>' + appointmentId);

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }

        function closeMarkPaidModal() {
            const modal = document.getElementById('mark-paid-modal');
            modal.classList.remove('flex');
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    </script>
</body>

</html>