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
            /* blue-500 */
            --primary-hover: #2563EB;
            /* blue-600 */
            --sidebar-bg: #111827;
            /* gray-900 */
            --sidebar-text: #D1D5DB;
            /* gray-300 */
            --sidebar-active-bg: #3B82F6;
            /* blue-500 */
            --sidebar-active-text: #FFFFFF;
            /* white */
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
                <a href="<?= site_url('admin/dashboard') ?>" title="Dentalcare Home" class="flex items-center justify-center h-12 w-12 mb-8 rounded-full bg-blue-500 text-white shadow-md">
                    <img src="https://cdn-icons-png.flaticon.com/128/3914/3914549.png" alt="Dentalcare Logo" class="w-6 h-6 invert">
                </a>

                <nav class="space-y-4">
                    <a href="<?= site_url('admin/dashboard') ?>" title="Dashboard"
                        class="flex items-center justify-center h-12 w-12 rounded-full transition-colors duration-200 relative group
                              <?php /* ACTIVE STATE for dashboard.php: */ ?> bg-blue-500 text-white shadow-md <?php /* END ACTIVE */ ?>
                              <?php /* INACTIVE STATE for other pages: */ ?> text-gray-400 hover:bg-blue-600 hover:text-white <?php /* END INACTIVE */ ?>
                              ">
                        <img src="https://cdn-icons-png.flaticon.com/128/3914/3914820.png" alt="" class="w-6 h-6 invert">
                        <span class="absolute left-full ml-3 px-2 py-1 text-xs font-medium text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap -translate-x-2 group-hover:translate-x-0 pointer-events-none z-30">
                            Dashboard
                        </span>
                    </a>

                    <a href="<?= site_url('management/appointments') ?>" title="Appointments"
                        class="flex items-center justify-center h-12 w-12 rounded-full transition-colors duration-200 relative group
                              <?php /* ACTIVE STATE for appointments.php: */ ?> bg-blue-500 text-white shadow-md <?php /* END ACTIVE */ ?>
                              <?php /* INACTIVE STATE for other pages: */ ?> text-gray-400 hover:bg-blue-600 hover:text-white <?php /* END INACTIVE */ ?>
                              ">
                        <img src="https://cdn-icons-png.flaticon.com/128/19027/19027040.png" alt="" class="w-6 h-6 invert">
                        <span class="absolute left-full ml-3 px-2 py-1 text-xs font-medium text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap -translate-x-2 group-hover:translate-x-0 pointer-events-none z-30">
                            Appointments
                        </span>
                    </a>

                    <a href="<?= site_url('management/doctors') ?>" title="Doctors"
                        class="flex items-center justify-center h-12 w-12 rounded-full transition-colors duration-200 relative group
                              <?php /* ACTIVE STATE for doctor_management.php: */ ?> bg-blue-500 text-white shadow-md <?php /* END ACTIVE */ ?>
                              <?php /* INACTIVE STATE for other pages: */ ?> text-gray-400 hover:bg-blue-600 hover:text-white <?php /* END INACTIVE */ ?>
                              ">
                        <img src="https://cdn-icons-png.flaticon.com/128/9856/9856850.png" alt="" class="w-6 h-6 invert">
                        <span class="absolute left-full ml-3 px-2 py-1 text-xs font-medium text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap -translate-x-2 group-hover:translate-x-0 pointer-events-none z-30">
                            Doctors
                        </span>
                    </a>

                    <a href="<?= site_url('management/services') ?>" title="Services"
                        class="flex items-center justify-center h-12 w-12 rounded-full transition-colors duration-200 relative group
                              <?php /* ACTIVE STATE for service_management.php: */ ?> bg-blue-500 text-white shadow-md <?php /* END ACTIVE */ ?>
                              <?php /* INACTIVE STATE for other pages: */ ?> text-gray-400 hover:bg-blue-600 hover:text-white <?php /* END INACTIVE */ ?>
                              ">
                        <img src="https://cdn-icons-png.flaticon.com/128/3914/3914079.png" alt="" class="w-6 h-6 invert">
                        <span class="absolute left-full ml-3 px-2 py-1 text-xs font-medium text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap -translate-x-2 group-hover:translate-x-0 pointer-events-none z-30">
                            Services
                        </span>
                    </a>
                </nav>
            </div>

            <div>
                <a href="<?= site_url('logout') ?>" title="Logout"
                    class="flex items-center justify-center h-12 w-12 bg-pink-500 rounded-full text-red-400 hover:bg-pink-600 hover:text-red-300 transition-colors relative group">
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
                                            <td class="px-3 py-4 text-sm text-gray-600"><?= html_escape($services[$app['service_id']]['name'] ?? 'N/A') ?></td>
                                            <td class="px-3 py-4 text-sm text-gray-600">
                                                <div><?= html_escape(date('M d, Y', strtotime($app['appointment_date']))) ?></div>
                                                <div class="text-xs text-gray-500"><?= html_escape($app['time_slot']) ?></div>
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
                                                <?php elseif ($app['status'] === 'confirmed'): ?>
                                                    <a href="<?= site_url('management/appointment_cancel/' . $app['id']) ?>" class="text-red-600 hover:text-red-800 font-medium">Cancel</a>
                                                <?php else: ?>
                                                    <span class="text-gray-400">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="px-3 py-4 text-center text-gray-500">No appointments found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>

            <aside class="w-full lg:w-80 h-screen sticky top-0 flex flex-col overflow-y-auto bg-gradient-to-b from-blue-900 via-blue-800 to-blue-900 shadow-2xl border-r border-blue-800 text-white">

                <?php
                // Keep the same logic as before
                $details_to_show = $userDetails ?? [];
                $display_name = html_escape($details_to_show['full_name'] ?? $username);
                $display_username = html_escape($details_to_show['username'] ?? $username);
                $display_email = html_escape($details_to_show['email'] ?? 'N/A');
                $display_role = html_escape(ucfirst($details_to_show['role'] ?? $current_role));

                $icon_class = 'fas fa-user-shield';
                $gradient_class = 'from-blue-700 to-blue-600';
                $ring_color_class = 'ring-blue-400 ring-offset-blue-900';
                $text_color_class = 'text-blue-300';
                $badge_color_class = 'bg-blue-700 text-blue-100';
                $info_title = 'Admin Information';

                if (strtolower($details_to_show['role'] ?? $current_role) === 'staff') {
                    $icon_class = 'fas fa-user-nurse';
                    $gradient_class = 'from-sky-600 to-sky-500';
                    $ring_color_class = 'ring-sky-400 ring-offset-blue-900';
                    $text_color_class = 'text-sky-300';
                    $badge_color_class = 'bg-sky-700 text-sky-100';
                    $info_title = 'Staff Information';
                }
                ?>

                <!-- Profile Header -->
                <div class="relative p-6 text-center bg-white/10 backdrop-blur-md rounded-b-3xl shadow-lg mb-6">
                    <div class="w-24 h-24 rounded-full bg-white flex items-center justify-center mx-auto ring-4 ring-offset-2 <?= $ring_color_class ?> ring-white shadow-xl transition-all hover:scale-105 duration-300">
                        <i class="<?= $icon_class ?> text-4xl <?= $text_color_class ?>"></i>
                    </div>
                    <h2 class="mt-3 text-xl font-bold text-white tracking-wide"><?= $display_name ?></h2>
                    <p class="mt-1 text-xs uppercase font-semibold <?= $badge_color_class ?> px-4 py-1 rounded-full inline-block tracking-wider shadow-inner"><?= $display_role ?></p>
                </div>

                <!-- Info Section -->
                <div class="px-6 pb-10 flex-grow">
                    <h3 class="text-sm font-semibold text-blue-200 mb-4 uppercase tracking-wider border-b border-blue-700 pb-2"><?= $info_title ?></h3>

                    <ul class="space-y-6 text-sm">
                        <li>
                            <span class="block text-blue-200 mb-1">Full Name</span>
                            <span class="text-white font-semibold text-base"><?= $display_name ?></span>
                        </li>
                        <li>
                            <span class="block text-blue-200 mb-1">Username</span>
                            <span class="text-white font-semibold text-base"><?= $display_username ?></span>
                        </li>
                        <li>
                            <span class="block text-blue-200 mb-1">Email Address</span>
                            <span class="text-white font-semibold text-base break-words"><?= $display_email ?></span>
                        </li>
                        <li>
                            <span class="block text-blue-200 mb-1">Role</span>
                            <span class="text-white font-semibold text-base"><?= $display_role ?></span>
                        </li>
                    </ul>
                </div>

                <!-- Footer -->
                <footer class="p-6 mt-auto border-t border-blue-800 bg-blue-950/50 backdrop-blur-sm text-center">
                    <p class="text-xs text-blue-300">&copy; <?= date('Y') ?> DENTALCARE. All rights reserved.</p>
                </footer>
            </aside>

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
    </script>
</body>

</html>