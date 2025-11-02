<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');
$LAVA = lava_instance();
$username = $LAVA->session->userdata('username');
$current_role = $LAVA->session->userdata('role'); // Staff role

// Variables passed from Staff::dashboard() - Make sure controller provides these
$all_users = $all_users ?? []; // Should contain only 'user' role accounts
$total_patients = $total_patients ?? 0;
$total_appointments = $total_appointments ?? 0;

// Fetch staff details (similar to how admin details are fetched for admin dashboard)
// This requires fetching the logged-in staff's details in the Staff controller
$staff_details = $staff_details ?? []; // Assuming controller passes this
$staff_full_name = $staff_details['full_name'] ?? $username;
$staff_email = $staff_details['email'] ?? 'No Email Set';


// Fetch flash messages if any
$flash_message = $LAVA->session->flashdata('success_message') ?? $LAVA->session->flashdata('error_message');

// Helper function (can be shared or duplicated if not using includes)
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
    <title>Staff Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            /* Using Admin styles for consistency */
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

        /* Match Admin font */
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
                <a href="<?= site_url('staff/dashboard') ?>" title="Dentalcare Home" class="flex items-center justify-center h-12 w-12 mb-8 rounded-full bg-blue-500 text-white shadow-md">
                    <img src="https://cdn-icons-png.flaticon.com/128/3914/3914549.png" alt="Dentalcare Logo" class="w-6 h-6 invert">
                </a>

                <nav class="space-y-4">
                    <a href="<?= site_url('staff/dashboard') ?>" title="Dashboard"
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
                    <h1 class="text-8xl font-extrabold text-gray-900">Dashboard</h1>
                    <p class="text-lg text-gray-600 mt-1">Welcome back, <?= html_escape($staff_full_name) ?>. Here's a summary for staff.</p>
                </header>

                <?php if ($flash_message): ?>
                    <div class="p-4 mb-6 rounded-lg <?= $LAVA->session->flashdata('success_message') ? 'bg-green-100 text-green-700 border-green-300' : 'bg-red-100 text-red-700 border-red-300' ?> border shadow-sm" role="alert">
                        <strong class="font-bold"><?= $LAVA->session->flashdata('success_message') ? 'Success!' : 'Error!' ?></strong>
                        <span><?= html_escape($flash_message) ?></span>
                    </div>
                <?php endif; ?>
                <?php // display_validation_errors($errors); // Usually no forms here, but can add if needed 
                ?>

                <section class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-10">
                    <div class="flex min-h-[20em] flex-col justify-between gap-[0.5em] rounded-[1.5em] bg-[#E0F2FE] p-[1.5em] text-[#0369A1] shadow-[0px_4px_16px_0px_rgba(0,0,0,0.1)] transition hover:shadow-lg">
                        <div class="flex h-fit w-full items-start justify-between">
                            <div class="flex flex-col items-start justify-center">
                                <p class="text-[1rem] font-semibold uppercase tracking-wider">Total Patients</p>
                                <p class="text-[8em] font-extrabold mt-1"><?= html_escape($total_patients) ?></p>
                            </div>
                            <div class="text-4xl opacity-80">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="h-[1px] w-full rounded-full bg-[hsla(206,90%,50%,0.2)]"></div>
                        <p class="text-[0.75rem] font-light text-sky-600">All registered patient accounts.</p>
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
                </section>

                <!-- Calendar Panel (no analytics) -->
                <section class="bg-white p-6 sm:p-8 rounded-xl shadow-lg border border-gray-200 mb-10" aria-busy="false">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <button id="calPrev" type="button" class="px-2 py-1 rounded-md bg-gray-100 hover:bg-gray-200 border border-gray-200" aria-label="Previous month">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path d="M15 19l-7-7 7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </button>
                            <h3 id="calMonthLabel" class="text-lg font-semibold" aria-live="polite">—</h3>
                            <button id="calNext" type="button" class="px-2 py-1 rounded-md bg-gray-100 hover:bg-gray-200 border border-gray-200" aria-label="Next month">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
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
                </section>

                <section class="bg-white p-6 sm:p-8 rounded-xl shadow-lg border border-gray-200">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Registered Patient Accounts</h2>
                    <p class="text-gray-600 mb-4 text-sm">
                        View registered patient accounts for reference.
                    </p>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Full Name</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Username</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
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
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    <?= html_escape(ucfirst($user['role'])) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="px-3 py-4 text-center text-gray-500">No patient accounts found.</td>
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
        // Staff Calendar (no analytics)
        (function() {
            const calGrid = document.getElementById('calGrid');
            const calLabel = document.getElementById('calMonthLabel');
            const calPrev = document.getElementById('calPrev');
            const calNext = document.getElementById('calNext');
            const calCount = document.getElementById('calCount');
            const dayTitle = document.getElementById('dayTitle');
            const dayEvents = document.getElementById('dayEvents');
            const calError = document.getElementById('calError');
            const calPanel = calGrid ? calGrid.closest('[aria-busy]') : null;

            let current = new Date();
            current.setDate(1);
            let events = [];
            let firstLoad = true;
            const monthCache = {};

            function iso(d) { return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`; }
            function startOfMonth(d) { return new Date(d.getFullYear(), d.getMonth(), 1); }
            function endOfMonth(d) { return new Date(d.getFullYear(), d.getMonth()+1, 0); }
            function fmtTime(hhmmss) {
                const [h,m] = (hhmmss||'00:00:00').split(':');
                const date = new Date(); date.setHours(+h, +m, 0,0);
                let hr = date.getHours(); const am = hr<12?'AM':'PM'; hr%=12; if(hr===0) hr=12;
                return `${hr}:${String(date.getMinutes()).padStart(2,'0')} ${am}`;
            }
            function monthKey(dateObj){ return `${dateObj.getFullYear()}-${String(dateObj.getMonth()+1).padStart(2,'0')}`; }

            function statusBadge(status) {
                const map = {
                    confirmed: 'bg-green-100 text-green-700',
                    pending: 'bg-yellow-100 text-yellow-700',
                    declined: 'bg-red-100 text-red-700',
                    cancelled: 'bg-gray-100 text-gray-600'
                };
                return map[status] || 'bg-blue-100 text-blue-700';
            }

            function renderCalendar() {
                if (!calGrid) return;
                calLabel.textContent = current.toLocaleDateString(undefined, { month:'long', year:'numeric' });
                calGrid.innerHTML = '';
                const first = startOfMonth(current);
                const last = endOfMonth(current);
                for (let i=0;i<first.getDay();i++) calGrid.appendChild(document.createElement('div'));
                const selectedIso = iso(new Date());
                for (let d=1; d<=last.getDate(); d++) {
                    const day = new Date(current.getFullYear(), current.getMonth(), d);
                    const dayIso = iso(day);
                    const btn = document.createElement('button');
                    btn.type='button';
                    btn.className = 'relative p-2 rounded-lg border text-left hover:bg-gray-50 ' + (dayIso===selectedIso?'ring-2 ring-blue-400':'' );
                    btn.innerHTML = `<div class="text-xs text-gray-500">${d}</div>`;
                    const dayCount = events.filter(e => e.date===dayIso).length;
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
                dayTitle.textContent = day.toLocaleDateString(undefined, { weekday:'long', month:'short', day:'numeric', year:'numeric' });
                const list = events.filter(e => e.date===dayIso);
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

            async function loadData(immediate = false) {
                const month = current.getMonth()+1, year = current.getFullYear();
                const jsonPath = "<?= parse_url(site_url('management/appointments_json'), PHP_URL_PATH) ?>";
                const url = `${jsonPath}?month=${month}&year=${year}`;
                const absUrl = `<?= site_url('management/appointments_json') ?>?month=${'${month}'}&year=${'${year}'}`;
                const key = monthKey(current);

                try {
                    if (calPanel) calPanel.setAttribute('aria-busy', 'true');
                    calError.classList.add('hidden');
                    calError.textContent = '';

                    if (immediate) {
                        if (monthCache[key] && Array.isArray(monthCache[key])) {
                            events = monthCache[key];
                            calCount.textContent = String(events.length);
                            renderCalendar();
                            renderDayList(new Date(current.getFullYear(), current.getMonth(), 1));
                        } else {
                            events = [];
                            calCount.textContent = '0';
                            renderCalendar();
                            renderDayList(new Date(current.getFullYear(), current.getMonth(), 1));
                        }
                    }

                    let res = await fetch(url, { headers: { 'X-Requested-With':'XMLHttpRequest' }, credentials: 'include' });
                    let ct = res.headers.get('content-type') || '';
                    if (!res.ok || !ct.includes('application/json')) {
                        res = await fetch(absUrl, { headers: { 'X-Requested-With':'XMLHttpRequest' }, credentials: 'include' });
                        ct = res.headers.get('content-type') || '';
                    }
                    if (!res.ok) throw new Error(`HTTP ${res.status}`);
                    if (!ct.includes('application/json')) throw new Error('Unexpected response');

                    const data = await res.json();
                    events = data.events || [];
                    calCount.textContent = String(events.length);
                    renderCalendar();
                    renderDayList(new Date(current.getFullYear(), current.getMonth(), 1));
                    monthCache[key] = events.slice();
                } catch (e) {
                    console.warn('Appointments fetch failed; keeping existing UI (staff)', e);
                    const hasCache = monthCache[key] && monthCache[key].length > 0;
                    if ((!events || events.length === 0) && firstLoad && !hasCache) {
                        calError.textContent = 'Could not load calendar data. You may need to login again or check your network.';
                        calError.classList.remove('hidden');
                    }
                } finally {
                    if (calPanel) calPanel.setAttribute('aria-busy', 'false');
                    firstLoad = false;
                }
            }

            calPrev.addEventListener('click', () => { current = new Date(current.getFullYear(), current.getMonth()-1, 1); loadData(true); });
            calNext.addEventListener('click', () => { current = new Date(current.getFullYear(), current.getMonth()+1, 1); loadData(true); });

            // Bootstrap from server
            try {
                const boot = <?php echo json_encode($initial_calendar ?? null); ?>;
                if (boot && boot.events) {
                    current = new Date(boot.year, boot.month - 1, 1);
                    events = boot.events;
                    monthCache[`${boot.year}-${String(boot.month).padStart(2,'0')}`] = boot.events.slice();
                    calCount.textContent = String(events.length);
                    renderCalendar();
                    renderDayList(new Date(current.getFullYear(), current.getMonth(), 1));
                }
            } catch (e) { /* ignore */ }

            // Fetch to refresh
            loadData(false);
        })();
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