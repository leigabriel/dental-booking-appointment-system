<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');
$LAVA = lava_instance();
$username = $LAVA->session->userdata('username');
$current_role = $LAVA->session->userdata('role');

// Variables passed from Admin::dashboard()
$total_users = $total_users ?? 0;
$total_staff = $total_staff ?? 0;
$total_admin = $total_admin ?? 0;
$total_appointments = $total_appointments ?? 0; // From controller update
$all_users = $all_users ?? [];
$admin_details = $admin_details ?? []; // From controller update

// Extract admin details for the profile card
$admin_full_name = $admin_details['full_name'] ?? $username;
$admin_email = $admin_details['email'] ?? 'No Email Set';

$errors = $errors ?? [];
$post_data = $post_data ?? [];

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
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
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

        .modal {
            transition: opacity 0.25s ease;
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

<body class="bg-gray-200">

    <div class="flex min-h-screen">

        <aside class="w-20 bg-blue-900 text-gray-300 p-3 flex flex-col items-center justify-between shadow-2xl sticky top-0 h-screen z-20">
            <div>
                <a href="<?= site_url('admin/dashboard') ?>" title="Dentalcare Home" class="flex items-center justify-center h-12 w-12 mb-8 rounded-full bg-gray-800 border-gray-700 border-2 text-white shadow-md hover:bg-gray-700">
                    <img src="<?= site_url('public/img/dentalcare512x512.png') ?>" alt="Dentalcare Logo" class="w-6 h-6">
                </a>

                <nav class="space-y-4">
                    <a href="<?= site_url('admin/dashboard') ?>" title="Dashboard"
                        class="flex items-center justify-center h-12 w-12 rounded-full transition-all duration-300 relative group border-2 bg-gradient-to-br from-blue-500 to-blue-600 border-blue-400 text-white shadow-lg hover:shadow-xl hover:scale-105">
                        <img src="https://cdn-icons-png.flaticon.com/128/3914/3914820.png" alt="" class="w-6 h-6 invert">
                        <span class="absolute left-full ml-3 px-2 py-1 text-xs font-medium text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap -translate-x-2 group-hover:translate-x-0 pointer-events-none z-30">
                            Dashboard
                        </span>
                    </a>

                    <a href="<?= site_url('admin/calendar') ?>" title="Appointments"
                        class="flex items-center justify-center h-12 w-12 rounded-full transition-all duration-300 relative group border-2 bg-gradient-to-br from-purple-500 to-purple-600 border-purple-400 text-white shadow-lg hover:shadow-xl hover:scale-105">
                        <img src="https://cdn-icons-png.flaticon.com/128/747/747310.png" alt="" class="w-6 h-6 invert">
                        <span class="absolute left-full ml-3 px-2 py-1 text-xs font-medium text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap -translate-x-2 group-hover:translate-x-0 pointer-events-none z-30">
                            Calendar
                        </span>
                    </a>

                    <a href="<?= site_url('management/appointments') ?>" title="Appointments"
                        class="flex items-center justify-center h-12 w-12 rounded-full transition-all duration-300 relative group border-2 bg-gradient-to-br from-amber-500 to-amber-600 border-amber-400 text-white shadow-lg hover:shadow-xl hover:scale-105">
                        <img src="https://cdn-icons-png.flaticon.com/128/19027/19027040.png" alt="" class="w-6 h-6 invert">
                        <span class="absolute left-full ml-3 px-2 py-1 text-xs font-medium text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap -translate-x-2 group-hover:translate-x-0 pointer-events-none z-30">
                            Appointments
                        </span>
                    </a>

                    <a href="<?= site_url('management/doctors') ?>" title="Doctors"
                        class="flex items-center justify-center h-12 w-12 rounded-full transition-all duration-300 relative group border-2 bg-gradient-to-br from-teal-500 to-teal-600 border-teal-400 text-white shadow-lg hover:shadow-xl hover:scale-105">
                        <img src="https://cdn-icons-png.flaticon.com/128/9856/9856850.png" alt="" class="w-6 h-6 invert">
                        <span class="absolute left-full ml-3 px-2 py-1 text-xs font-medium text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap -translate-x-2 group-hover:translate-x-0 pointer-events-none z-30">
                            Doctors
                        </span>
                    </a>

                    <a href="<?= site_url('management/services') ?>" title="Services"
                        class="flex items-center justify-center h-12 w-12 rounded-full transition-all duration-300 relative group border-2 bg-gradient-to-br from-indigo-500 to-indigo-600 border-indigo-400 text-white shadow-lg hover:shadow-xl hover:scale-105">
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
                    <h1 class="text-8xl font-extrabold text-gray-900">Dashboard</h1>
                    <p class="text-lg text-gray-600 mt-1">Welcome back, <?= html_escape($admin_full_name) ?>. Here's a summary of your clinic.</p>
                </header>

                <?php if ($flash_message): ?>
                    <div class="p-4 mb-6 rounded-lg <?= $LAVA->session->flashdata('success_message') ? 'bg-green-100 text-green-700 border-green-300' : 'bg-red-100 text-red-700 border-red-300' ?> border shadow-sm" role="alert">
                        <strong class="font-bold"><?= $LAVA->session->flashdata('success_message') ? 'Success!' : 'Error!' ?></strong>
                        <span><?= html_escape($flash_message) ?></span>
                    </div>
                <?php endif; ?>
                <?php display_validation_errors($errors); ?>

                <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6 mb-10">

                    <div class="flex min-h-[20em] flex-col justify-between gap-[0.5em] rounded-[1.5em] bg-[#E0F2FE] p-[1.5em] text-[#0369A1] shadow-[0px_4px_16px_0px_rgba(0,0,0,0.1)] transition hover:shadow-lg">
                        <div class="flex h-fit w-full items-start justify-between">
                            <div class="flex flex-col items-start justify-center">
                                <p class="text-[1rem] font-semibold uppercase tracking-wider">Total Patients</p>
                                <p class="text-[8em] font-extrabold mt-1"><?= html_escape($total_users) ?></p>
                            </div>
                            <div class="text-4xl opacity-80">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="h-[1px] w-full rounded-full bg-[hsla(206,90%,50%,0.2)]"></div>
                        <p class="text-[0.75rem] font-light text-sky-600">All registered patient accounts.</p>
                    </div>

                    <div class="flex min-h-[20em] flex-col justify-between gap-[0.5em] rounded-[1.5em] bg-[#D1FAE5] p-[1.5em] text-[#047857] shadow-[0px_4px_16px_0px_rgba(0,0,0,0.1)] transition hover:shadow-lg">
                        <div class="flex h-fit w-full items-start justify-between">
                            <div class="flex flex-col items-start justify-center">
                                <p class="text-[1rem] font-semibold uppercase tracking-wider">Total Staff</p>
                                <p class="text-[8em] font-extrabold mt-1"><?= html_escape($total_staff) ?></p>
                            </div>
                            <div class="text-4xl opacity-80">
                                <i class="fas fa-user-nurse"></i>
                            </div>
                        </div>
                        <div class="h-[1px] w-full rounded-full bg-[hsla(158,90%,40%,0.2)]"></div>
                        <p class="text-[0.75rem] font-light text-emerald-600">All registered staff accounts.</p>
                    </div>

                    <div class="flex min-h-[20em] flex-col justify-between gap-[0.5em] rounded-[1.5em] bg-[#EDE9FE] p-[1.5em] text-[#5B21B6] shadow-[0px_4px_16px_0px_rgba(0,0,0,0.1)] transition hover:shadow-lg">
                        <div class="flex h-fit w-full items-start justify-between">
                            <div class="flex flex-col items-start justify-center">
                                <p class="text-[1rem] font-semibold uppercase tracking-wider">Total Admins</p>
                                <p class="text-[8em] font-extrabold mt-1"><?= html_escape($total_admin) ?></p>
                            </div>
                            <div class="text-4xl opacity-80">
                                <i class="fas fa-user-shield"></i>
                            </div>
                        </div>
                        <div class="h-[1px] w-full rounded-full bg-[hsla(263,90%,50%,0.2)]"></div>
                        <p class="text-[0.75rem] font-light text-violet-600">All registered admin accounts.</p>
                    </div>

                    <div class="flex min-h-[20em] flex-col justify-between gap-[0.5em] rounded-[1.5em] bg-[#FEF3C7] p-[1.5em] text-[#B45309] shadow-[0px_4px_16px_0px_rgba(0,0,0,0.1)] transition hover:shadow-lg">
                        <div class="flex h-fit w-full items-start justify-between">
                            <div class="flex flex-col items-start justify-center">
                                <p class="text-[1rem] font-semibold uppercase tracking-wider">Total Bookings</p>
                                <p class="text-[8em] font-extrabold mt-1"><?= html_escape($total_appointments) ?></p>
                            </div>
                            <div class="text-4xl opacity-80">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                        </div>
                        <div class="h-[1px] w-full rounded-full bg-[hsla(39,90%,40%,0.2)]"></div>
                        <p class="text-[0.75rem] font-light text-amber-700">All appointment records.</p>
                    </div>

                    <div class="flex min-h-[20em] flex-col justify-between gap-[0.5em] rounded-[1.5em] bg-[#F3E8FF] p-[1.5em] text-[#7C3AED] shadow-[0px_4px_16px_0px_rgba(0,0,0,0.1)] transition hover:shadow-lg">
                        <div class="flex h-fit w-full items-start justify-between">
                            <div class="flex flex-col items-start justify-center">
                                <p class="text-[1rem] font-semibold uppercase tracking-wider">Total Doctors</p>
                                <p class="text-[8em] font-extrabold mt-1"><?= html_escape($total_doctors ?? 0) ?></p>
                            </div>
                            <div class="text-4xl opacity-80">
                                <i class="fas fa-user-md"></i>
                            </div>
                        </div>
                        <div class="h-[1px] w-full rounded-full bg-[hsla(270,90%,50%,0.2)]"></div>
                        <p class="text-[0.75rem] font-light text-purple-600">All registered doctors.</p>
                    </div>

                </section>

                <!-- Calendar + Analytics -->
                <section class="grid grid-cols-1 2xl:grid-cols-3 gap-6 mb-10">
                    <!-- Calendar Panel -->
                    <div class="2xl:col-span-2 bg-white rounded-xl shadow-lg border border-gray-200 p-6" aria-busy="false">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <button id="calPrev" type="button" class="px-2 py-1 rounded-md bg-gray-100 hover:bg-gray-200 border border-gray-200" aria-label="Previous month">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4">
                                        <path d="M15 19l-7-7 7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>
                                <h3 id="calMonthLabel" class="text-lg font-semibold" aria-live="polite">—</h3>
                                <button id="calNext" type="button" class="px-2 py-1 rounded-md bg-gray-100 hover:bg-gray-200 border border-gray-200" aria-label="Next month">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4">
                                        <path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </button>
                            </div>
                            <div class="text-sm text-gray-500">
                                Appointments: <span id="calCount">0</span>
                            </div>
                        </div>
                        <div id="calError" class="hidden mb-3 p-2 text-sm rounded-md border border-red-200 bg-red-50 text-red-700"></div>
                        <div class="grid grid-cols-7 text-[11px] text-gray-500 mb-2">
                            <div class="text-center">Sun</div>
                            <div class="text-center">Mon</div>
                            <div class="text-center">Tue</div>
                            <div class="text-center">Wed</div>
                            <div class="text-center">Thu</div>
                            <div class="text-center">Fri</div>
                            <div class="text-center">Sat</div>
                        </div>
                        <div id="calGrid" class="grid grid-cols-7 gap-2"></div>
                        <div class="mt-6">
                            <h4 id="dayTitle" class="text-sm font-semibold text-gray-700 mb-2">Selected Day</h4>
                            <div id="dayEvents" class="divide-y divide-gray-100 rounded-lg border border-gray-200 overflow-hidden">
                                <div class="p-4 text-sm text-gray-500">No appointments for this day.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Analytics Panel -->
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 space-y-6">
                        <h3 class="text-lg font-semibold">Analytics</h3>
                        <div>
                            <p class="text-xs text-gray-500 mb-2">Appointments per day (current month)</p>
                            <canvas id="chartDaily" height="160"></canvas>
                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div>
                                <p class="text-xs text-gray-500 mb-2">By status</p>
                                <canvas id="chartStatus" height="180"></canvas>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 mb-2">By service</p>
                                <canvas id="chartService" height="180"></canvas>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="bg-white p-6 sm:p-8 rounded-xl shadow-lg border border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">User Accounts</h2>
                        <button type="button" onclick="openModal('add')"
                            class="px-5 py-2 bg-[--primary-color] text-white text-sm font-semibold rounded-lg shadow-md hover:bg-[--primary-hover] transition-colors">
                            <i class="fas fa-plus mr-1"></i> Add Admin/Staff
                        </button>
                    </div>
                    
                    <!-- Search Bar -->
                    <div class="mb-4">
                        <div class="relative">
                            <input 
                                type="text" 
                                id="search-users" 
                                placeholder="Search by Name, Email, Username, or Role..." 
                                class="w-full px-4 py-3 pl-12 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                            <svg class="absolute left-4 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" id="users-table">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Full Name</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Username</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (!empty($all_users)): ?>
                                    <?php foreach ($all_users as $user): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-4 text-sm font-medium text-gray-900"><?= html_escape($user['id']) ?></td>
                                            <td class="px-3 py-4 text-sm text-gray-600"><?= html_escape($user['full_name'] ?? 'N/A') ?></td>
                                            <td class="px-3 py-4 text-sm text-gray-600"><?= html_escape($user['email'] ?? 'N/A') ?></td>
                                            <td class="px-3 py-4 text-sm text-gray-600"><?= html_escape($user['username']) ?></td>
                                            <td class="px-3 py-4">
                                                <?php
                                                $role_class = match ($user['role']) {
                                                    'admin' => 'bg-blue-100 text-blue-800',
                                                    'staff' => 'bg-sky-100 text-sky-800',
                                                    'user' => 'bg-green-100 text-green-800',
                                                    'doctor' => 'bg-purple-100 text-purple-800',
                                                    default => 'bg-gray-100 text-gray-800',
                                                };
                                                ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $role_class ?>">
                                                    <?= html_escape(ucfirst($user['role'])) ?>
                                                </span>
                                            </td>
                                            <td class="px-3 py-4">
                                                <?php
                                                $is_suspended = isset($user['is_suspended']) && $user['is_suspended'] == 1;
                                                ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $is_suspended ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' ?>">
                                                    <?= $is_suspended ? 'Suspended' : 'Active' ?>
                                                </span>
                                            </td>
                                            <td class="px-3 py-4 text-sm space-x-2">
                                                <?php if ($user['role'] === 'admin' || $user['role'] === 'staff'): ?>
                                                    <button
                                                        class="text-blue-600 hover:text-blue-800 font-medium"
                                                        onclick="openModal('edit', {
                                                            id: '<?= $user['id'] ?>',
                                                            username: '<?= html_escape($user['username']) ?>',
                                                            full_name: '<?= html_escape($user['full_name']) ?>',
                                                            email: '<?= html_escape($user['email']) ?>',
                                                            role: '<?= $user['role'] ?>'
                                                        })">
                                                        Edit
                                                    </button>
                                                    <button type="button"
                                                        class="text-red-600 hover:text-red-800 font-medium ml-2"
                                                        onclick="document.getElementById('delete-modal-<?= $user['id'] ?>').classList.remove('hidden'); document.getElementById('delete-modal-<?= $user['id'] ?>').classList.add('flex'); document.body.classList.add('overflow-hidden');">
                                                        Delete
                                                    </button>

                                                    <!-- Delete Modal for user -->
                                                    <div id="delete-modal-<?= $user['id'] ?>"
                                                        class="modal fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4"
                                                        onclick="closeDeleteModal(event, '<?= $user['id'] ?>')">
                                                        <div class="bg-white w-full max-w-md p-6 rounded-2xl shadow-xl transform transition-transform duration-300 ease-in-out scale-95"
                                                            onclick="event.stopPropagation()">

                                                            <div class="flex flex-col items-center text-center mb-6">
                                                                <div class="mb-4 text-red-500 text-5xl">
                                                                    <i class="fas fa-exclamation-triangle"></i>
                                                                </div>
                                                                <h3 class="text-2xl font-semibold text-gray-800">Confirm Delete</h3>
                                                            </div>

                                                            <p class="text-gray-600 text-center mb-6">
                                                                WARNING: Delete <strong><?= html_escape($user['username']) ?></strong>? This action cannot be undone.
                                                            </p>

                                                            <div class="flex justify-center gap-4">
                                                                <button type="button"
                                                                    onclick="(function(id){ var el = document.getElementById('delete-modal-'+id); el.classList.remove('flex'); el.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); })('<?= $user['id'] ?>')"
                                                                    class="px-6 py-2.5 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2 transition duration-150 font-medium">
                                                                    Cancel
                                                                </button>
                                                                <a href="<?= site_url('admin/admin_staff_delete/' . $user['id']) ?>"
                                                                    onclick="document.body.classList.remove('overflow-hidden');"
                                                                    class="px-6 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition duration-150 font-medium shadow-md">
                                                                    Delete
                                                                </a>
                                                            </div>
                                                        Delete
                                                    </button>

                                                <?php else: ?>
                                                    <?php if ($user['role'] !== 'admin'): ?>
                                                        <?php
                                                        $is_suspended = isset($user['is_suspended']) && $user['is_suspended'] == 1;
                                                        ?>
                                                        <?php if ($is_suspended): ?>
                                                            <a href="#" 
                                                               class="text-green-600 hover:text-green-800 font-medium"
                                                               onclick="event.preventDefault(); openUnsuspendModal(<?= $user['id'] ?>, '<?= html_escape($user['username']) ?>')">
                                                                Unsuspend
                                                            </a>
                                                        <?php else: ?>
                                                            <a href="#" 
                                                               class="text-orange-600 hover:text-orange-800 font-medium"
                                                               onclick="event.preventDefault(); openSuspendModal(<?= $user['id'] ?>, '<?= html_escape($user['username']) ?>', '<?= html_escape($user['full_name']) ?>')">
                                                                Suspend
                                                            </a>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                    <span class="text-gray-400">—</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="px-3 py-4 text-center text-gray-500">No registered accounts found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>

        </div>
    </div>

    <!-- Suspend Confirmation Modal -->
    <div id="suspend-modal" class="modal fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4" onclick="closeSuspendModal(event)">
        <div class="bg-white w-full max-w-md p-8 rounded-2xl shadow-xl" onclick="event.stopPropagation()">
            <div class="flex flex-col items-center text-center mb-6">
                <div class="mb-4 text-orange-500 text-5xl">
                    <i class="fas fa-ban"></i>
                </div>
                <h3 class="text-2xl font-semibold text-gray-800">Suspend User Account</h3>
            </div>

            <div class="mb-6">
                <p class="text-gray-600 text-center mb-2">
                    Are you sure you want to suspend:
                </p>
                <p class="text-center font-semibold text-gray-800" id="suspend-user-info"></p>
                <p class="text-sm text-gray-500 text-center mt-2">
                    This will prevent the user from logging in.
                </p>
            </div>

            <form id="suspend-form" method="GET" action="">
                <div class="mb-6">
                    <label for="suspend-reason" class="block text-sm font-medium text-gray-700 mb-2">Reason (Optional)</label>
                    <textarea id="suspend-reason" name="reason" rows="3" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                        placeholder="Enter reason for suspension..."></textarea>
                </div>

                <div class="flex justify-center gap-4">
                    <button type="button" onclick="closeSuspendModal()"
                        class="px-6 py-2.5 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-100 transition font-medium">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-6 py-2.5 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition font-medium shadow-md">
                        Suspend User
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Unsuspend Confirmation Modal -->
    <div id="unsuspend-modal" class="modal fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4" onclick="closeUnsuspendModal(event)">
        <div class="bg-white w-full max-w-md p-8 rounded-2xl shadow-xl" onclick="event.stopPropagation()">
            <div class="flex flex-col items-center text-center mb-6">
                <div class="mb-4 text-green-500 text-5xl">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 class="text-2xl font-semibold text-gray-800">Unsuspend User Account</h3>
            </div>

            <p class="text-gray-600 text-center mb-6">
                Are you sure you want to unsuspend:
            </p>
            <p class="text-center font-semibold text-gray-800 mb-6" id="unsuspend-user-info"></p>
            <p class="text-sm text-gray-500 text-center mb-8">
                This will allow the user to login again.
            </p>

            <div class="flex justify-center gap-4">
                <button type="button" onclick="closeUnsuspendModal()"
                    class="px-6 py-2.5 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-100 transition font-medium">
                    Cancel
                </button>
                <a id="confirm-unsuspend-btn" href="#"
                    class="px-6 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium shadow-md">
                    Unsuspend User
                </a>
            </div>
        </div>
    </div>

    <div id="user-modal" class="modal fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4" onclick="closeModal(event)">
        <div class="bg-white w-full max-w-lg p-6 rounded-xl shadow-2xl" onclick="event.stopPropagation()">
            <h2 id="modal-title" class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2"></h2>
            <form id="user-form" method="POST" action="">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="form-id">
                <div class="space-y-4">
                    <div>
                        <label for="form-full-name" class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" id="form-full-name" name="full_name" required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[--primary-color] focus:border-[--primary-color]">
                    </div>
                    <div>
                        <label for="form-email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="form-email" name="email" required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[--primary-color] focus:border-[--primary-color]">
                    </div>
                    <div id="username-field">
                        <label for="form-username" class="block text-sm font-medium text-gray-700">Username</label>
                        <input type="text" id="form-username" name="username" required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[--primary-color] focus:border-[--primary-color]">
                    </div>
                    <div>
                        <label for="form-role" class="block text-sm font-medium text-gray-700">Role</label>
                        <select id="form-role" name="role" required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[--primary-color] focus:border-[--primary-color]">
                            <option value="staff">Staff</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div>
                        <label for="form-password" class="block text-sm font-medium text-gray-700">Password <span id="password-hint" class="text-xs text-gray-500 ml-1"></span></label>
                        <input type="password" id="form-password" name="password"
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[--primary-color] focus:border-[--primary-color]">
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition">Cancel</button>
                    <button type="submit" id="form-submit-button" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Save User</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Search functionality for users table
        document.getElementById('search-users')?.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#users-table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Dashboard Calendar + Analytics
        (function() {
            const calGrid = document.getElementById('calGrid');
            const calLabel = document.getElementById('calMonthLabel');
            const calPrev = document.getElementById('calPrev');
            const calNext = document.getElementById('calNext');
            const calCount = document.getElementById('calCount');
            const dayTitle = document.getElementById('dayTitle');
            const dayEvents = document.getElementById('dayEvents');
            const calError = document.getElementById('calError');
            const calPanel = calGrid.closest('[aria-busy]');

            let current = new Date(); // current in view
            current.setDate(1);
            let events = [];
            let charts = {
                daily: null,
                status: null,
                service: null
            };
            let firstLoad = true;
            const monthCache = {}; // key: YYYY-MM -> events array

            function iso(d) {
                return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
            }

            function startOfMonth(d) {
                return new Date(d.getFullYear(), d.getMonth(), 1);
            }

            function endOfMonth(d) {
                return new Date(d.getFullYear(), d.getMonth() + 1, 0);
            }

            function fmtTime(hhmmss) {
                const [h, m] = (hhmmss || '00:00:00').split(':');
                const date = new Date();
                date.setHours(+h, +m, 0, 0);
                let hr = date.getHours();
                const am = hr < 12 ? 'AM' : 'PM';
                hr %= 12;
                if (hr === 0) hr = 12;
                return `${hr}:${String(date.getMinutes()).padStart(2,'0')} ${am}`;
            }

            function groupBy(arr, key) {
                return arr.reduce((acc, it) => {
                    const k = it[key] ?? 'Unknown';
                    (acc[k] ||= []).push(it);
                    return acc;
                }, {});
            }

            function renderCalendar() {
                calLabel.textContent = current.toLocaleDateString(undefined, {
                    month: 'long',
                    year: 'numeric'
                });
                calGrid.innerHTML = '';
                const first = startOfMonth(current);
                const last = endOfMonth(current);
                const minDate = new Date();
                minDate.setHours(0, 0, 0, 0);
                // leading blanks
                for (let i = 0; i < first.getDay(); i++) calGrid.appendChild(document.createElement('div'));
                const selectedIso = iso(new Date());
                for (let d = 1; d <= last.getDate(); d++) {
                    const day = new Date(current.getFullYear(), current.getMonth(), d);
                    const dayIso = iso(day);
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'relative p-2 rounded-lg border text-left hover:bg-gray-50 ' + (dayIso === selectedIso ? 'ring-2 ring-blue-400' : '');
                    btn.innerHTML = `<div class="text-xs text-gray-500">${d}</div>`;
                    const dayCount = events.filter(e => e.date === dayIso).length;
                    if (dayCount) {
                        const dot = document.createElement('div');
                        dot.className = 'absolute right-2 top-2 w-2 h-2 rounded-full bg-blue-500';
                        btn.appendChild(dot);
                    }
                    btn.addEventListener('click', () => renderDayList(day));
                    calGrid.appendChild(btn);
                }
            }

            function renderDayList(day) {
                const dayIso = iso(day);
                dayTitle.textContent = day.toLocaleDateString(undefined, {
                    weekday: 'long',
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric'
                });
                const list = events.filter(e => e.date === dayIso);
                dayEvents.innerHTML = '';
                if (!list.length) {
                    dayEvents.innerHTML = '<div class="p-4 text-sm text-gray-500">No appointments for this day.</div>';
                    return;
                }
                list.forEach(e => {
                    const row = document.createElement('div');
                    row.className = 'p-3 flex items-center justify-between bg-white';
                    row.innerHTML = `
                        <div>
                            <div class="text-sm font-medium text-gray-800">${fmtTime(e.time)} • ${e.service}</div>
                            <div class="text-xs text-gray-500">${e.user} with Dr. ${e.doctor}</div>
                        </div>
                        <span class="text-[11px] px-2 py-1 rounded-full ${statusBadge(e.status)}">${e.status}</span>
                    `;
                    dayEvents.appendChild(row);
                });
            }

            function statusBadge(status) {
                const map = {
                    confirmed: 'bg-green-100 text-green-700',
                    pending: 'bg-yellow-100 text-yellow-700',
                    declined: 'bg-red-100 text-red-700',
                    cancelled: 'bg-gray-100 text-gray-600'
                };
                return map[status] || 'bg-blue-100 text-blue-700';
            }

            function monthKey(dateObj) {
                return `${dateObj.getFullYear()}-${String(dateObj.getMonth()+1).padStart(2,'0')}`;
            }

            async function loadData(immediate = false) {
                const month = current.getMonth() + 1,
                    year = current.getFullYear();
                // Use only the path portion from PHP to ensure same-origin fetch even behind proxies/ports
                const jsonPath = "<?= parse_url(site_url('management/appointments_json'), PHP_URL_PATH) ?>";
                const url = `${jsonPath}?month=${month}&year=${year}`;
                const absUrl = `<?= site_url('management/appointments_json') ?>?month=${'${month}'}&year=${'${year}'}`;
                const key = monthKey(current);

                try {
                    if (calPanel) calPanel.setAttribute('aria-busy', 'true');
                    calError.classList.add('hidden');
                    calError.textContent = '';

                    // Immediate mode is used for month navigation: render grid now with zeroed events
                    if (immediate) {
                        // If we have cached data for this month, show it immediately
                        if (monthCache[key] && Array.isArray(monthCache[key])) {
                            events = monthCache[key];
                            calCount.textContent = String(events.length);
                            renderCalendar();
                            renderDayList(new Date(current.getFullYear(), current.getMonth(), 1));
                            renderCharts();
                        } else {
                            events = [];
                            calCount.textContent = '0';
                            renderCalendar();
                            renderDayList(new Date(current.getFullYear(), current.getMonth(), 1));
                        }
                    }

                    let res = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'include'
                    });
                    let ct = res.headers.get('content-type') || '';
                    if (!res.ok || !ct.includes('application/json')) {
                        // Fallback: try absolute URL (useful when app is served via a dev proxy)
                        res = await fetch(absUrl, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'include'
                        });
                        ct = res.headers.get('content-type') || '';
                    }
                    if (!res.ok) throw new Error(`HTTP ${res.status}`);
                    if (!ct.includes('application/json')) throw new Error('Unexpected response');

                    const data = await res.json();
                    events = data.events || [];
                    calCount.textContent = String(events.length);
                    renderCalendar();
                    renderDayList(new Date(current.getFullYear(), current.getMonth(), 1));
                    renderCharts();
                    // Update cache on success
                    monthCache[key] = events.slice();
                } catch (e) {
                    console.warn('Appointments fetch failed; keeping existing UI', e);
                    // Keep current UI (server bootstrap), only show a subtle banner if we have no data at all
                    const hasCache = monthCache[key] && monthCache[key].length > 0;
                    if ((!events || events.length === 0) && firstLoad && !hasCache) {
                        calError.textContent = 'Could not load calendar data. You may need to login again or check your network.';
                        calError.classList.remove('hidden');
                        // Do not destroy charts or clear grid; maintain whatever is showing
                    }
                } finally {
                    if (calPanel) calPanel.setAttribute('aria-busy', 'false');
                    firstLoad = false;
                }
            }

            function renderCharts() {
                const canvasDaily = document.getElementById('chartDaily');
                const canvasStatus = document.getElementById('chartStatus');
                const canvasService = document.getElementById('chartService');
                if (!canvasDaily || !canvasStatus || !canvasService) return;
                const ctxDaily = canvasDaily.getContext('2d');
                const ctxStatus = canvasStatus.getContext('2d');
                const ctxService = canvasService.getContext('2d');

                const labels = Array.from({
                    length: endOfMonth(current).getDate()
                }, (_, i) => i + 1);
                const dayCounts = new Array(labels.length).fill(0);
                events.forEach(e => {
                    const d = new Date(e.date + 'T00:00:00');
                    if (d.getMonth() === current.getMonth() && d.getFullYear() === current.getFullYear()) {
                        dayCounts[d.getDate() - 1]++;
                    }
                });

                if (charts.daily) charts.daily.destroy();
                charts.daily = new Chart(ctxDaily, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Appointments',
                            data: dayCounts,
                            borderColor: '#3B82F6',
                            backgroundColor: 'rgba(59,130,246,.15)',
                            fill: true,
                            tension: .3
                        }]
                    },
                    options: {
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });

                const byStatus = groupBy(events, 'status');
                const statusLabels = Object.keys(byStatus);
                const statusCounts = statusLabels.map(k => byStatus[k].length);
                const statusColors = ['#22C55E', '#EAB308', '#EF4444', '#9CA3AF', '#3B82F6'];
                if (charts.status) charts.status.destroy();
                charts.status = new Chart(ctxStatus, {
                    type: 'doughnut',
                    data: {
                        labels: statusLabels,
                        datasets: [{
                            data: statusCounts,
                            backgroundColor: statusColors.slice(0, Math.max(1, statusLabels.length))
                        }]
                    },
                    options: {
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });

                const byService = groupBy(events, 'service');
                const serviceLabels = Object.keys(byService);
                const serviceCounts = serviceLabels.map(k => byService[k].length);
                if (charts.service) charts.service.destroy();
                charts.service = new Chart(ctxService, {
                    type: 'bar',
                    data: {
                        labels: serviceLabels,
                        datasets: [{
                            label: 'Count',
                            data: serviceCounts,
                            backgroundColor: '#6366F1'
                        }]
                    },
                    options: {
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }

            calPrev.addEventListener('click', () => {
                current = new Date(current.getFullYear(), current.getMonth() - 1, 1);
                loadData(true);
            });
            calNext.addEventListener('click', () => {
                current = new Date(current.getFullYear(), current.getMonth() + 1, 1);
                loadData(true);
            });

            // Bootstrap with server-provided data for current month (fallback when fetch has issues)
            try {
                const boot = <?php echo json_encode($initial_calendar ?? null); ?>;
                if (boot && boot.events) {
                    // Set current month based on server payload to keep label in sync
                    current = new Date(boot.year, boot.month - 1, 1);
                    events = boot.events;
                    // Seed cache with server data for instant back/forward month revisits
                    monthCache[`${boot.year}-${String(boot.month).padStart(2,'0')}`] = boot.events.slice();
                    calCount.textContent = String(events.length);
                    renderCalendar();
                    renderDayList(new Date(current.getFullYear(), current.getMonth(), 1));
                    renderCharts();
                }
            } catch (e) {
                /* ignore */
            }

            // Also attempt fetch (for navigation and to refresh data)
            loadData(false);
        })();
        const modal = document.getElementById('user-modal');
        const form = document.getElementById('user-form');
        const modalTitle = document.getElementById('modal-title');
        const formId = document.getElementById('form-id');
        const formFullName = document.getElementById('form-full-name');
        const formEmail = document.getElementById('form-email');
        const formUsername = document.getElementById('form-username');
        const usernameField = document.getElementById('username-field');
        const formRole = document.getElementById('form-role');
        const formPassword = document.getElementById('form-password');
        const passwordHint = document.getElementById('password-hint');
        const formSubmitButton = document.getElementById('form-submit-button');
        const siteUrl = "<?= site_url('admin/admin_staff_add_update') ?>";

        function openModal(mode, user = {}) {
            form.reset();
            formId.value = '';
            document.querySelectorAll('.p-3.mb-4.rounded-lg.bg-red-100').forEach(el => el.remove());

            if (mode === 'add') {
                modalTitle.textContent = "Add New Admin/Staff";
                form.action = siteUrl;
                usernameField.classList.remove('hidden');
                formUsername.required = true;
                formPassword.required = true;
                passwordHint.textContent = "(Required, min 6 chars)";
                formSubmitButton.textContent = "Add User";
            } else if (mode === 'edit') {
                modalTitle.textContent = `Edit User: ${user.username}`;
                form.action = `${siteUrl}/${user.id}`;
                formId.value = user.id;
                usernameField.classList.add('hidden');
                formUsername.required = false;
                formFullName.value = user.full_name;
                formEmail.value = user.email;
                formRole.value = user.role;
                formPassword.required = false;
                passwordHint.textContent = "(Optional - leave blank to keep current)";
                formSubmitButton.textContent = "Save Changes";
            }
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }

        function closeModal(event = null) {
            if (!event || event.target.id === 'user-modal') {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        }

        // Expose modal functions to global scope for inline onclick handlers
        window.openModal = openModal;
        window.closeModal = closeModal;

        <?php if (!empty($errors) && !empty($post_data)): ?>
            <?php if (isset($post_data['id']) && !empty($post_data['id'])): ?>
                openModal('edit', {
                    id: '<?= html_escape($post_data['id']) ?>',
                    username: '<?= html_escape($post_data['username'] ?? '') ?>',
                    full_name: '<?= html_escape($post_data['full_name'] ?? '') ?>',
                    email: '<?= html_escape($post_data['email'] ?? '') ?>',
                    role: '<?= html_escape($post_data['role'] ?? 'staff') ?>'
                });
            <?php else: ?>
                openModal('add');
                formFullName.value = '<?= html_escape($post_data['full_name'] ?? '') ?>';
                formEmail.value = '<?= html_escape($post_data['email'] ?? '') ?>';
                formUsername.value = '<?= html_escape($post_data['username'] ?? '') ?>';
                formRole.value = '<?= html_escape($post_data['role'] ?? 'staff') ?>';
            <?php endif; ?>
        <?php endif; ?>

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

                // Suspend/Unsuspend Modal Functions
                const suspendModal = document.getElementById('suspend-modal');
                const unsuspendModal = document.getElementById('unsuspend-modal');
                const suspendForm = document.getElementById('suspend-form');
                const suspendUserInfo = document.getElementById('suspend-user-info');
                const unsuspendUserInfo = document.getElementById('unsuspend-user-info');
                const confirmUnsuspendBtn = document.getElementById('confirm-unsuspend-btn');

                function openSuspendModal(userId, username, fullName) {
                    suspendUserInfo.textContent = `${fullName} (@${username})`;
                    suspendForm.action = `<?= site_url('management/user_suspend/') ?>${userId}`;
                    suspendModal.classList.remove('hidden');
                    suspendModal.classList.add('flex');
                    document.body.classList.add('overflow-hidden');
                }

                function closeSuspendModal(event = null) {
                    if (!event || event.target.id === 'suspend-modal') {
                        suspendModal.classList.remove('flex');
                        suspendModal.classList.add('hidden');
                        document.body.classList.remove('overflow-hidden');
                        document.getElementById('suspend-reason').value = '';
                    }
                }

                function openUnsuspendModal(userId, username) {
                    unsuspendUserInfo.textContent = `@${username}`;
                    confirmUnsuspendBtn.href = `<?= site_url('management/user_unsuspend/') ?>${userId}`;
                    unsuspendModal.classList.remove('hidden');
                    unsuspendModal.classList.add('flex');
                    document.body.classList.add('overflow-hidden');
                }

                function closeUnsuspendModal(event = null) {
                    if (!event || event.target.id === 'unsuspend-modal') {
                        unsuspendModal.classList.remove('flex');
                        unsuspendModal.classList.add('hidden');
                        document.body.classList.remove('overflow-hidden');
                    }
                }

                window.openSuspendModal = openSuspendModal;
                window.closeSuspendModal = closeSuspendModal;
                window.openUnsuspendModal = openUnsuspendModal;
                window.closeUnsuspendModal = closeUnsuspendModal;

                if (logoutAnchor) {
                    logoutAnchor.addEventListener('click', function(e) {
                        e.preventDefault();
                        openLogoutModal();
                    });
                }
            })();

        // Close function for this modal instance
        function closeDeleteModal(event, id) {
            if (!event || event.target.id === 'delete-modal-' + id) {
                var el = document.getElementById('delete-modal-' + id);
                if (el) {
                    el.classList.remove('flex');
                    el.classList.add('hidden');
                }
                document.body.classList.remove('overflow-hidden');
            }
        }
    </script>
</body>

</html>