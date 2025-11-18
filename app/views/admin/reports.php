<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');
$LAVA = lava_instance();
$username = $LAVA->session->userdata('username');
$current_role = $LAVA->session->userdata('role');

// Variables passed from Reports controller
$admin_details = $admin_details ?? [];
$start_date = $start_date ?? date('Y-m-01');
$end_date = $end_date ?? date('Y-m-t');
$appointments_data = $appointments_data ?? [];
$revenue_data = $revenue_data ?? [];
$doctors_data = $doctors_data ?? [];
$services_data = $services_data ?? [];
$payment_methods_data = $payment_methods_data ?? [];
$status_distribution = $status_distribution ?? [];

$admin_full_name = $admin_details['full_name'] ?? $username;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
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
            overflow-x: hidden;
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

        .stat-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        /* Fix chart container to prevent infinite scrolling */
        canvas {
            max-height: 300px !important;
            height: 300px !important;
            width: 100% !important;
        }

        @media print {
            .no-print {
                display: none !important;
            }
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

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Reports & Analytics</h1>
                    <p class="text-gray-600 mt-1">Comprehensive reporting and data insights</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <p class="text-sm text-gray-600">Welcome back,</p>
                        <p class="font-semibold text-gray-800"><?= html_escape($admin_full_name) ?></p>
                    </div>
                </div>
            </div>

            <!-- Date Range Filter -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6 no-print">
                <form method="GET" action="<?= site_url('reports') ?>" class="flex items-end gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                        <input type="date" name="start_date" value="<?= html_escape($start_date) ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                        <input type="date" name="end_date" value="<?= html_escape($end_date) ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-filter mr-2"></i>Apply Filter
                    </button>
                    <button type="button" onclick="window.print()" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                        <i class="fas fa-print mr-2"></i>Print
                    </button>
                </form>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Appointments -->
                <div class="stat-card bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Total Appointments</p>
                            <h3 class="text-3xl font-bold mt-2"><?= number_format($appointments_data['total'] ?? 0) ?></h3>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-full p-4">
                            <i class="fas fa-calendar-check text-3xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Revenue -->
                <div class="stat-card bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium">Total Revenue</p>
                            <h3 class="text-3xl font-bold mt-2">₱<?= number_format($revenue_data['total_revenue'] ?? 0, 2) ?></h3>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-full p-4">
                            <i class="fas fa-peso-sign text-3xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Paid Revenue -->
                <div class="stat-card bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-emerald-100 text-sm font-medium">Paid Revenue</p>
                            <h3 class="text-3xl font-bold mt-2">₱<?= number_format($revenue_data['paid_revenue'] ?? 0, 2) ?></h3>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-full p-4">
                            <i class="fas fa-check-circle text-3xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Pending Revenue -->
                <div class="stat-card bg-gradient-to-br from-amber-500 to-amber-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-amber-100 text-sm font-medium">Pending Revenue</p>
                            <h3 class="text-3xl font-bold mt-2">₱<?= number_format($revenue_data['pending_revenue'] ?? 0, 2) ?></h3>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-full p-4">
                            <i class="fas fa-clock text-3xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Appointment Status Distribution -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Appointment Status Distribution</h2>
                    <div style="position: relative; height: 300px; max-height: 300px;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>

                <!-- Payment Methods -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">Payment Methods</h2>
                    <div style="position: relative; height: 300px; max-height: 300px;">
                        <canvas id="paymentChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Reports Sections -->
            <div class="space-y-6">
                <!-- Appointments Report -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold text-gray-800">
                            <i class="fas fa-calendar-alt text-blue-600 mr-2"></i>Appointments Report
                        </h2>
                        <div class="flex gap-2 no-print">
                            <a href="<?= site_url('reports/export_appointments?format=csv&start_date=' . $start_date . '&end_date=' . $end_date) ?>"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm">
                                <i class="fas fa-file-csv mr-1"></i>Export CSV
                            </a>
                            <a href="<?= site_url('reports/export_appointments?format=pdf&start_date=' . $start_date . '&end_date=' . $end_date) ?>"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm">
                                <i class="fas fa-file-pdf mr-1"></i>Export PDF
                            </a>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="border border-blue-200 rounded-lg p-4 bg-blue-50">
                            <p class="text-sm text-blue-600 font-medium">Confirmed</p>
                            <p class="text-2xl font-bold text-blue-700 mt-1"><?= number_format($appointments_data['confirmed'] ?? 0) ?></p>
                        </div>
                        <div class="border border-amber-200 rounded-lg p-4 bg-amber-50">
                            <p class="text-sm text-amber-600 font-medium">Pending</p>
                            <p class="text-2xl font-bold text-amber-700 mt-1"><?= number_format($appointments_data['pending'] ?? 0) ?></p>
                        </div>
                        <div class="border border-red-200 rounded-lg p-4 bg-red-50">
                            <p class="text-sm text-red-600 font-medium">Cancelled</p>
                            <p class="text-2xl font-bold text-red-700 mt-1"><?= number_format($appointments_data['cancelled'] ?? 0) ?></p>
                        </div>
                        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                            <p class="text-sm text-gray-600 font-medium">Declined</p>
                            <p class="text-2xl font-bold text-gray-700 mt-1"><?= number_format($appointments_data['declined'] ?? 0) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Revenue Report -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold text-gray-800">
                            <i class="fas fa-chart-line text-green-600 mr-2"></i>Revenue Report
                        </h2>
                        <div class="flex gap-2 no-print">
                            <a href="<?= site_url('reports/export_revenue?format=csv&start_date=' . $start_date . '&end_date=' . $end_date) ?>"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm">
                                <i class="fas fa-file-csv mr-1"></i>Export CSV
                            </a>
                            <a href="<?= site_url('reports/export_revenue?format=pdf&start_date=' . $start_date . '&end_date=' . $end_date) ?>"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm">
                                <i class="fas fa-file-pdf mr-1"></i>Export PDF
                            </a>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <div class="border border-green-200 rounded-lg p-4 bg-green-50">
                            <p class="text-sm text-green-600 font-medium">Total Revenue</p>
                            <p class="text-2xl font-bold text-green-700 mt-1">₱<?= number_format($revenue_data['total_revenue'] ?? 0, 2) ?></p>
                        </div>
                        <div class="border border-emerald-200 rounded-lg p-4 bg-emerald-50">
                            <p class="text-sm text-emerald-600 font-medium">Paid</p>
                            <p class="text-2xl font-bold text-emerald-700 mt-1">₱<?= number_format($revenue_data['paid_revenue'] ?? 0, 2) ?></p>
                        </div>
                        <div class="border border-amber-200 rounded-lg p-4 bg-amber-50">
                            <p class="text-sm text-amber-600 font-medium">Pending</p>
                            <p class="text-2xl font-bold text-amber-700 mt-1">₱<?= number_format($revenue_data['pending_revenue'] ?? 0, 2) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Doctors Performance Report -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold text-gray-800">
                            <i class="fas fa-user-md text-purple-600 mr-2"></i>Doctors Performance
                        </h2>
                        <div class="flex gap-2 no-print">
                            <a href="<?= site_url('reports/export_doctors?format=csv&start_date=' . $start_date . '&end_date=' . $end_date) ?>"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm">
                                <i class="fas fa-file-csv mr-1"></i>Export CSV
                            </a>
                            <a href="<?= site_url('reports/export_doctors?format=pdf&start_date=' . $start_date . '&end_date=' . $end_date) ?>"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm">
                                <i class="fas fa-file-pdf mr-1"></i>Export PDF
                            </a>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Doctor</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Specialty</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Confirmed</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Pending</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($doctors_data)): ?>
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">No data available for this period</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($doctors_data as $doctor): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900"><?= html_escape($doctor['doctor_name']) ?></td>
                                            <td class="px-4 py-3 text-sm text-gray-600"><?= html_escape($doctor['specialty']) ?></td>
                                            <td class="px-4 py-3 text-sm text-center text-gray-900 font-semibold"><?= number_format($doctor['total_appointments']) ?></td>
                                            <td class="px-4 py-3 text-sm text-center text-green-600"><?= number_format($doctor['confirmed']) ?></td>
                                            <td class="px-4 py-3 text-sm text-center text-amber-600"><?= number_format($doctor['pending']) ?></td>
                                            <td class="px-4 py-3 text-sm text-right text-gray-900 font-semibold">₱<?= number_format($doctor['revenue_generated'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Services Report -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold text-gray-800">
                            <i class="fas fa-briefcase-medical text-indigo-600 mr-2"></i>Services Report
                        </h2>
                        <div class="flex gap-2 no-print">
                            <a href="<?= site_url('reports/export_services?format=csv&start_date=' . $start_date . '&end_date=' . $end_date) ?>"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm">
                                <i class="fas fa-file-csv mr-1"></i>Export CSV
                            </a>
                            <a href="<?= site_url('reports/export_services?format=pdf&start_date=' . $start_date . '&end_date=' . $end_date) ?>"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm">
                                <i class="fas fa-file-pdf mr-1"></i>Export PDF
                            </a>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Service</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Bookings</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Revenue</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider no-print">Popularity</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($services_data)): ?>
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">No data available for this period</td>
                                    </tr>
                                <?php else: ?>
                                    <?php
                                    $max_bookings = max(array_column($services_data, 'times_booked'));
                                    foreach ($services_data as $service):
                                        $popularity = $max_bookings > 0 ? ($service['times_booked'] / $max_bookings) * 100 : 0;
                                    ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900"><?= html_escape($service['service_name']) ?></td>
                                            <td class="px-4 py-3 text-sm text-right text-gray-600">₱<?= number_format($service['base_price'], 2) ?></td>
                                            <td class="px-4 py-3 text-sm text-center text-gray-900 font-semibold"><?= number_format($service['times_booked']) ?></td>
                                            <td class="px-4 py-3 text-sm text-right text-green-600 font-semibold">₱<?= number_format($service['total_revenue'], 2) ?></td>
                                            <td class="px-4 py-3 text-sm no-print">
                                                <div class="flex items-center justify-center gap-2">
                                                    <div class="w-24 bg-gray-200 rounded-full h-2">
                                                        <div class="bg-indigo-600 h-2 rounded-full" style="width: <?= $popularity ?>%"></div>
                                                    </div>
                                                    <span class="text-xs text-gray-600"><?= number_format($popularity, 0) ?>%</span>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusData = <?= json_encode($status_distribution) ?>;

        if (statusData && statusData.length > 0) {
            const statusLabels = statusData.map(item => item.status.charAt(0).toUpperCase() + item.status.slice(1));
            const statusCounts = statusData.map(item => parseInt(item.count));

            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        data: statusCounts,
                        backgroundColor: [
                            '#3B82F6', // blue - confirmed
                            '#F59E0B', // amber - pending
                            '#EF4444', // red - cancelled
                            '#6B7280' // gray - declined
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        } else {
            // Show "No Data" message
            statusCtx.font = '16px Arial';
            statusCtx.fillStyle = '#6B7280';
            statusCtx.textAlign = 'center';
            statusCtx.fillText('No appointment data available', statusCtx.canvas.width / 2, statusCtx.canvas.height / 2);
        }

        // Payment Methods Chart
        const paymentCtx = document.getElementById('paymentChart').getContext('2d');
        const paymentData = <?= json_encode($payment_methods_data) ?>;

        if (paymentData && paymentData.length > 0) {
            const paymentLabels = paymentData.map(item => item.payment_method.toUpperCase());
            const paymentCounts = paymentData.map(item => parseFloat(item.total_amount));

            new Chart(paymentCtx, {
                type: 'bar',
                data: {
                    labels: paymentLabels,
                    datasets: [{
                        label: 'Revenue (₱)',
                        data: paymentCounts,
                        backgroundColor: [
                            '#10B981',
                            '#3B82F6',
                            '#F59E0B'
                        ],
                        borderWidth: 0,
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return '₱' + context.parsed.y.toLocaleString('en-US', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₱' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        } else {
            // Show "No Data" message
            paymentCtx.font = '16px Arial';
            paymentCtx.fillStyle = '#6B7280';
            paymentCtx.textAlign = 'center';
            paymentCtx.fillText('No payment data available', paymentCtx.canvas.width / 2, paymentCtx.canvas.height / 2);
        }
    </script>

</body>

</html>