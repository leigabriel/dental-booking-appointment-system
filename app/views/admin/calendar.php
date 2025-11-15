<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');
$LAVA = lava_instance();
$username = $LAVA->session->userdata('username');
$current_role = $LAVA->session->userdata('role');

// Variables passed from controller
$admin_details = $admin_details ?? [];
$admin_full_name = $admin_details['full_name'] ?? $username;
$admin_email = $admin_details['email'] ?? 'No Email Set';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar View - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
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

        /* Custom styles for bigger calendar */
        .calendar-day {
            min-height: 120px;
            transition: all 0.2s ease;
        }

        .calendar-day:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .calendar-day.today {
            border: 2px solid #3B82F6;
            background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%);
        }

        .appointment-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 4px;
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

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col lg:flex-row">

            <main class="flex-1 p-6 sm:p-10 overflow-y-auto h-screen">
                <header class="mb-8">
                    <h1 class="text-7xl font-extrabold text-gray-900">Calendar View</h1>
                    <p class="text-lg text-gray-600 mt-1">Full calendar overview of all appointments</p>
                </header>

                <!-- Big Calendar Section -->
                <section class="bg-white rounded-xl shadow-lg border border-gray-200 p-8 mb-6">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-4">
                            <button id="calPrev" type="button"
                                class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow-md transition-all"
                                aria-label="Previous month">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-5 h-5">
                                    <path d="M15 19l-7-7 7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                            <h2 id="calMonthLabel" class="text-3xl font-bold text-gray-800" aria-live="polite">—</h2>
                            <button id="calNext" type="button"
                                class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow-md transition-all"
                                aria-label="Next month">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-5 h-5">
                                    <path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>
                        <div class="flex items-center gap-4">
                            <button id="todayBtn" type="button"
                                class="px-4 py-2 rounded-lg bg-gray-600 hover:bg-gray-700 text-white font-semibold shadow-md transition-all">
                                Today
                            </button>
                            <div class="text-base text-gray-700 font-semibold">
                                Total Appointments: <span id="calCount" class="text-blue-600">0</span>
                            </div>
                        </div>
                    </div>

                    <div id="calError" class="hidden mb-4 p-3 text-sm rounded-lg border border-red-200 bg-red-50 text-red-700"></div>

                    <!-- Calendar Header - Days of Week -->
                    <div class="grid grid-cols-7 gap-2 mb-2">
                        <div class="text-center text-sm font-bold text-gray-700 py-2">Sunday</div>
                        <div class="text-center text-sm font-bold text-gray-700 py-2">Monday</div>
                        <div class="text-center text-sm font-bold text-gray-700 py-2">Tuesday</div>
                        <div class="text-center text-sm font-bold text-gray-700 py-2">Wednesday</div>
                        <div class="text-center text-sm font-bold text-gray-700 py-2">Thursday</div>
                        <div class="text-center text-sm font-bold text-gray-700 py-2">Friday</div>
                        <div class="text-center text-sm font-bold text-gray-700 py-2">Saturday</div>
                    </div>

                    <!-- Calendar Grid -->
                    <div id="calGrid" class="grid grid-cols-7 gap-2"></div>
                </section>

                <!-- Selected Day Details -->
                <section class="bg-white rounded-xl shadow-lg border border-gray-200 p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 id="dayTitle" class="text-2xl font-bold text-gray-800">Selected Day</h3>
                        <button id="printBtn" type="button"
                            class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white font-semibold shadow-md transition-all">
                            Print Schedule
                        </button>
                    </div>
                    <div id="dayEvents" class="divide-y divide-gray-100 rounded-lg border border-gray-200 overflow-hidden">
                        <div class="p-6 text-center text-gray-500">No appointments for this day.</div>
                    </div>
                </section>

            </main>

        </div>
    </div>

    <script>
        // Calendar JavaScript - Same functions, no changes to logic
        (function() {
            const calGrid = document.getElementById('calGrid');
            const calLabel = document.getElementById('calMonthLabel');
            const calPrev = document.getElementById('calPrev');
            const calNext = document.getElementById('calNext');
            const todayBtn = document.getElementById('todayBtn');
            const calCount = document.getElementById('calCount');
            const dayTitle = document.getElementById('dayTitle');
            const dayEvents = document.getElementById('dayEvents');
            const calError = document.getElementById('calError');
            const printBtn = document.getElementById('printBtn');

            let current = new Date();
            current.setDate(1);
            let events = [];
            let selectedDay = new Date();
            let firstLoad = true;
            const monthCache = {};

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

            function renderCalendar() {
                calLabel.textContent = current.toLocaleDateString(undefined, {
                    month: 'long',
                    year: 'numeric'
                });
                calGrid.innerHTML = '';

                const first = startOfMonth(current);
                const last = endOfMonth(current);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                const todayIso = iso(today);

                // Leading blanks
                for (let i = 0; i < first.getDay(); i++) {
                    const blank = document.createElement('div');
                    blank.className = 'calendar-day';
                    calGrid.appendChild(blank);
                }

                // Days
                for (let d = 1; d <= last.getDate(); d++) {
                    const day = new Date(current.getFullYear(), current.getMonth(), d);
                    const dayIso = iso(day);
                    const dayEl = document.createElement('button');
                    dayEl.type = 'button';
                    dayEl.className = 'calendar-day relative p-4 rounded-lg border border-gray-200 text-left bg-white hover:border-blue-400 cursor-pointer';

                    // Highlight today
                    if (dayIso === todayIso) {
                        dayEl.classList.add('today');
                    }

                    // Day number
                    const dayNum = document.createElement('div');
                    dayNum.className = 'text-xl font-bold text-gray-800 mb-2';
                    dayNum.textContent = d;
                    dayEl.appendChild(dayNum);

                    // Appointments for this day
                    const dayAppts = events.filter(e => e.date === dayIso);
                    if (dayAppts.length > 0) {
                        const apptContainer = document.createElement('div');
                        apptContainer.className = 'space-y-1';

                        // Show first 3 appointments
                        dayAppts.slice(0, 3).forEach(e => {
                            const apptDiv = document.createElement('div');
                            apptDiv.className = 'text-xs p-1 rounded bg-blue-50 border-l-2 border-blue-500 text-gray-700';
                            apptDiv.innerHTML = `<span class="appointment-dot bg-${getStatusColor(e.status)}"></span>${fmtTime(e.time)}`;
                            apptContainer.appendChild(apptDiv);
                        });

                        // Show count if more than 3
                        if (dayAppts.length > 3) {
                            const moreDiv = document.createElement('div');
                            moreDiv.className = 'text-xs text-blue-600 font-semibold mt-1';
                            moreDiv.textContent = `+${dayAppts.length - 3} more`;
                            apptContainer.appendChild(moreDiv);
                        }

                        dayEl.appendChild(apptContainer);
                    }

                    dayEl.addEventListener('click', () => {
                        selectedDay = day;
                        renderDayList(day);
                        // Highlight selected day
                        document.querySelectorAll('.calendar-day').forEach(el => {
                            el.classList.remove('ring-2', 'ring-blue-500');
                        });
                        dayEl.classList.add('ring-2', 'ring-blue-500');
                    });

                    calGrid.appendChild(dayEl);
                }
            }

            function renderDayList(day) {
                const dayIso = iso(day);
                dayTitle.textContent = day.toLocaleDateString(undefined, {
                    weekday: 'long',
                    month: 'long',
                    day: 'numeric',
                    year: 'numeric'
                });
                const list = events.filter(e => e.date === dayIso);
                dayEvents.innerHTML = '';

                if (!list.length) {
                    dayEvents.innerHTML = '<div class="p-6 text-center text-gray-500">No appointments for this day.</div>';
                    return;
                }

                list.forEach(e => {
                    const row = document.createElement('div');
                    row.className = 'p-4 flex items-center justify-between bg-white hover:bg-gray-50 transition-colors';
                    row.innerHTML = `
                        <div class="flex-1">
                            <div class="text-base font-semibold text-gray-800 mb-1">
                                ${fmtTime(e.time)} • ${e.service}
                            </div>
                            <div class="text-sm text-gray-600">
                                Patient: ${e.user}
                            </div>
                            <div class="text-sm text-gray-600">
                                Doctor: Dr. ${e.doctor}
                            </div>
                        </div>
                        <span class="text-xs px-3 py-1 rounded-full font-semibold ${statusBadge(e.status)}">${e.status.toUpperCase()}</span>
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

            function getStatusColor(status) {
                const map = {
                    confirmed: 'green-500',
                    pending: 'yellow-500',
                    declined: 'red-500',
                    cancelled: 'gray-500'
                };
                return map[status] || 'blue-500';
            }

            function monthKey(dateObj) {
                return `${dateObj.getFullYear()}-${String(dateObj.getMonth()+1).padStart(2,'0')}`;
            }

            async function loadData(immediate = false) {
                const month = current.getMonth() + 1,
                    year = current.getFullYear();
                const jsonPath = "<?= parse_url(site_url('management/appointments_json'), PHP_URL_PATH) ?>";
                const url = `${jsonPath}?month=${month}&year=${year}`;
                const absUrl = `<?= site_url('management/appointments_json') ?>?month=${month}&year=${year}`;
                const key = monthKey(current);

                try {
                    calError.classList.add('hidden');
                    calError.textContent = '';

                    if (immediate) {
                        if (monthCache[key] && Array.isArray(monthCache[key])) {
                            events = monthCache[key];
                            calCount.textContent = String(events.length);
                            renderCalendar();
                            renderDayList(selectedDay);
                        } else {
                            events = [];
                            calCount.textContent = '0';
                            renderCalendar();
                            renderDayList(selectedDay);
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
                    renderDayList(selectedDay);
                    monthCache[key] = events.slice();
                } catch (e) {
                    console.warn('Appointments fetch failed', e);
                    const hasCache = monthCache[key] && monthCache[key].length > 0;
                    if ((!events || events.length === 0) && firstLoad && !hasCache) {
                        calError.textContent = 'Could not load calendar data. Please check your connection or login again.';
                        calError.classList.remove('hidden');
                    }
                } finally {
                    firstLoad = false;
                }
            }

            calPrev.addEventListener('click', () => {
                current = new Date(current.getFullYear(), current.getMonth() - 1, 1);
                selectedDay = new Date(current.getFullYear(), current.getMonth(), 1);
                loadData(true);
            });

            calNext.addEventListener('click', () => {
                current = new Date(current.getFullYear(), current.getMonth() + 1, 1);
                selectedDay = new Date(current.getFullYear(), current.getMonth(), 1);
                loadData(true);
            });

            todayBtn.addEventListener('click', () => {
                current = new Date();
                current.setDate(1);
                selectedDay = new Date();
                loadData(true);
            });

            printBtn.addEventListener('click', () => {
                window.print();
            });

            // Bootstrap with server data
            try {
                const boot = <?php echo json_encode($initial_calendar ?? null); ?>;
                if (boot && boot.events) {
                    current = new Date(boot.year, boot.month - 1, 1);
                    events = boot.events;
                    monthCache[`${boot.year}-${String(boot.month).padStart(2,'0')}`] = boot.events.slice();
                    calCount.textContent = String(events.length);
                    renderCalendar();
                    renderDayList(selectedDay);
                }
            } catch (e) {
                /* ignore */
            }

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