<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');
$LAVA = lava_instance();
$doctors = $doctors ?? [];
$services = $services ?? [];
$errors = $errors ?? [];

$flash_message = $LAVA->session->flashdata('success_message') ?? $LAVA->session->flashdata('error_message');
$is_success = $LAVA->session->flashdata('success_message') ? true : false;
$is_logged_in = $LAVA->session->userdata('is_logged_in');
$username = $LAVA->session->userdata('username');

function display_validation_errors($errors)
{
    if (!empty($errors)) {
        echo '<div class="p-3 mb-4 rounded-lg bg-red-900/30 text-red-300 border border-red-500/50 font-semibold">';
        echo 'Please fix the following issues:';
        echo '<ul class="list-disc pl-5 mt-1 m-0 text-sm">';
        foreach ($errors as $error) {
            echo '<li>' . html_escape($error) . '</li>';
        }
        echo '</ul></div>';
    }
}

function repopulate($key, $default = '')
{
    global $data;
    if (!isset($data) || !is_array($data)) {
        $data = [];
    }
    $value = $_POST[$key] ?? ($data[$key] ?? $default);
    return html_escape($value);
}

$time_slots = ['08:00:00', '09:00:00', '10:00:00', '11:00:00', '12:00:00', '13:00:00', '14:00:00', '15:00:00', '16:00:00', '17:00:00'];
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Appointment - DENTALCARE</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
        body {
            font-family: 'JetBrains Mono', monospace;
        }

        input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(0.8);
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

        /* Stepper */
        .stepper { counter-reset: step; }
        .stepper li { position: relative; padding-left: 2.5rem; }
        .stepper li::before {
            counter-increment: step;
            content: counter(step);
            position: absolute; left: 0; top: 0.1rem;
            width: 1.6rem; height: 1.6rem; border-radius: 9999px;
            display: grid; place-items: center; font-weight: 700;
        }
        .step-idle::before { background: rgba(255,255,255,.08); color: #a5b4fc; border: 1px solid rgba(255,255,255,.15); }
        .step-active::before { background: #6366f1; color: white; box-shadow: 0 0 0 .25rem rgba(99,102,241,.25); }
        .step-done::before { background: #22c55e; color: white; }

        /* Chip styles (plain CSS to avoid build steps) */
        .chip {
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(255,255,255,0.1);
            font-size: 0.875rem;
            user-select: none;
            transition: background-color .2s ease, box-shadow .2s ease, color .2s ease, border-color .2s ease;
        }
        .chip-idle { background: rgba(30, 58, 138, 0.4); color: #e5e7eb; }
        .chip-idle:hover { background: rgba(30,58,138,0.6); }
        .chip-active { background: #6366f1; color: #fff; border-color: rgba(99,102,241,0.6); box-shadow: 0 2px 10px rgba(99,102,241,0.3); }

        /* Hide scrollbars for chip scroller */
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .no-scrollbar::-webkit-scrollbar { display: none; }

        /* Calendar styles */
        .cal-grid { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: .5rem; }
        .cal-day {
            padding: .5rem; border-radius: .5rem; text-align: center; font-size: .875rem;
            border: 1px solid rgba(255,255,255,.08); color: #e5e7eb;
            background: rgba(30,58,138,.4); cursor: pointer; transition: all .2s ease;
        }
        .cal-day:hover { background: rgba(30,58,138,.6); }
        .cal-empty { visibility: hidden; }
        .cal-disabled { opacity: .35; cursor: not-allowed; filter: grayscale(.2); }
        .cal-selected { background: #6366f1; color: #fff; border-color: rgba(99,102,241,.6); box-shadow: 0 2px 10px rgba(99,102,241,.3); }
        .cal-today { outline: 2px solid rgba(99,102,241,.5); outline-offset: 2px; }
    </style>
</head>

<body class="bg-blue-950 text-gray-300 min-h-screen flex flex-col pt-20">

    <header class="fixed top-6 left-1/2 transform -translate-x-1/2 z-50">
        <nav aria-label="Global" class="flex items-center justify-center gap-6 px-6 py-3 bg-white/10 backdrop-blur-md rounded-full shadow-xl">
            <div class="flex lg:flex-1">
                <a href="/#" class="-m-1.5 p-1.5">
                    <span class="sr-only">DENTALCARE</span>
                    <img src="<?= base_url() ?>public/img/favicon-32x32.png" alt="DENTALCARE Logo" class="h-6 w-auto" />
                </a>
            </div>
            <div class="flex lg:hidden">
                <button type="button" command="show-modal" commandfor="mobile-menu" class="-m-2.5 inline-flex items-center justify-center rounded-md p-2.5 text-gray-200">
                    <span class="sr-only">Open main menu</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6">
                        <path d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
            <div class="hidden lg:flex lg:gap-x-12">
                <a href="/#hero" class="text-lg/6 font-semibold text-white hover:text-blue-400">Home</a>
                <a href="/#services" class="text-lg/6 font-semibold text-white hover:text-blue-400">Services</a>
                <a href="/#about" class="text-lg/6 font-semibold text-white hover:text-blue-400">About</a>
                <a href="/#blog" class="text-lg/6 font-semibold text-white hover:text-blue-400">Blog</a>
                <a href="/#contact" class="text-lg/6 font-semibold text-white hover:text-blue-400">Contact</a>
                <a href="<?= site_url('book') ?>" class="text-lg/6 font-semibold text-white hover:text-blue-400">Book Now</a>
            </div>
            <div class="hidden lg:flex lg:flex-1 lg:justify-end relative gap-x-6">
                <?php if (isset($is_logged_in) && $is_logged_in): ?>
                    <!-- Profile Dropdown -->
                    <div class="group relative">
                        <a href="<?= site_url('profile') ?>" class="flex items-center gap-x-2 text-lg/6 uppercase font-semibold text-white hover:text-blue-400">
                            <img src="https://cdn-icons-png.flaticon.com/128/5393/5393061.png" alt="Profile" class="h-6 w-6 rounded-full object-cover invert">
                        </a>
                        <!-- Dropdown content -->
                        <div class="absolute mt-2 w-28 bg-gray-800 rounded-lg shadow-lg opacity-0 group-hover:opacity-100 invisible group-hover:visible transition-all duration-200">
                            <a href="<?= site_url('profile') ?>" class="block px-4 py-2 text-white hover:bg-gray-700 rounded-t-lg">Profile</a>
                            <a href="#" class="logout-confirm block px-4 py-2 text-white hover:bg-red-700 rounded-b-lg" data-logout-url="<?= site_url('logout') ?>">Log out</a>
                        </div>
                    </div>
                <?php else : ?>
                    <!-- Login / Register remain unchanged -->
                    <a href="<?= site_url('login') ?>" class="text-lg/6 font-semibold text-white hover:text-blue-400">Log in</a>
                    <span class="text-white">|</span>
                    <a href="<?= site_url('register') ?>" class="text-lg/6 font-semibold text-white hover:text-blue-400">Register</a>
                <?php endif; ?>
            </div>
        </nav>
        <el-dialog>
            <dialog id="mobile-menu" class="m-0 p-0 backdrop:bg-transparent lg:hidden">
                <div tabindex="0" class="fixed inset-0 focus:outline focus:outline-0">
                    <el-dialog-panel class="fixed inset-y-0 right-0 z-50 w-full overflow-y-auto bg-blue-900 p-6 sm:max-w-sm sm:ring-1 sm:ring-gray-100/10">
                        <div class="flex items-center justify-between">
                            <a href="#" class="-m-1.5 p-1.5">
                                <span class="sr-only">DENTALCARE</span>
                                <img src="<?= base_url() ?>public/img/favicon-32x32.png" alt="DENTALCARE Logo" class="h-8 w-auto" />
                            </a>
                            <button type="button" command="close" commandfor="mobile-menu" class="-m-2.5 rounded-md p-2.5 text-gray-200">
                                <span class="sr-only">Close menu</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6">
                                    <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>
                        <div class="mt-6 flow-root">
                            <div class="-my-6 divide-y divide-white/10">
                                <div class="space-y-2 py-6">
                                    <a href="#services" class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-white hover:bg-white/5">Services</a>
                                    <a href="#about" class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-white hover:bg-white/5">About</a>
                                    <a href="#blog" class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-white hover:bg-white/5">Blog</a>
                                    <a href="#contact" class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-white hover:bg-white/5">Contact</a>
                                    <a href="<?= site_url('book') ?>" class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-white hover:bg-white/5">Book Now</a>
                                </div>
                                <div class="py-6">
                                    <?php if ($is_logged_in) : ?>
                                        <a href="<?= site_url('profile') ?>" class="-mx-3 block rounded-lg px-3 py-2.5 text-base/7 font-semibold text-white hover:bg-white/5">Profile (<?= html_escape($username) ?>)</a>
                                        <a href="#" class="logout-confirm -mx-3 block rounded-lg px-3 py-2.5 text-base/7 font-semibold text-white hover:bg-white/5" data-logout-url="<?= site_url('logout') ?>">Log out</a>
                                    <?php else : ?>
                                        <a href="<?= site_url('login') ?>" class="-mx-3 block rounded-lg px-3 py-2.5 text-base/7 font-semibold text-white hover:bg-white/5">Log in</a>
                                        <a href="<?= site_url('register') ?>" class="-mx-3 block rounded-lg px-3 py-2.5 text-base/7 font-semibold text-white hover:bg-white/5">Register</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </el-dialog-panel>
                </div>
            </dialog>
        </el-dialog>
    </header>

    <main class="flex-grow">
        <div class="relative isolate max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div aria-hidden="true" class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80">
                <div style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" class="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-30 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]"></div>
            </div>
            <div class="flex justify-between items-center bg-blue-800/50 p-6 rounded-xl shadow-lg border border-white/10 mb-8 ring-1 ring-white/10">
                <h1 class="text-3xl font-extrabold text-white">
                    Schedule Your Visit
                </h1>
                <a href="<?= site_url('/profile') ?>" class="text-sm text-indigo-300 hover:text-white hover:underline font-medium">
                    View Appointments
                </a>
            </div>

            <?php if ($flash_message): ?>
                <div class="p-4 mb-6 rounded-lg <?= $is_success ? 'bg-green-800/30 text-green-300' : 'bg-red-800/30 text-red-300' ?> border <?= $is_success ? 'border-green-500/50' : 'border-red-500/50' ?>">
                    <?= html_escape($flash_message) ?>
                </div>
            <?php endif; ?>

            <?php display_validation_errors($errors); ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <div class="lg:col-span-2 bg-blue-800/50 p-6 rounded-xl shadow-lg border border-white/10 ring-1 ring-white/10">
                    <div class="bg-blue-900/40 p-6 rounded-lg shadow-inner h-full flex flex-col">
                        <h2 class="text-2xl font-bold text-white border-b border-white/10 pb-2 mb-4">Book Your Slot</h2>

                        <!-- Progress Stepper -->
                        <ol class="stepper grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6 text-xs text-indigo-200">
                            <li id="step-service" class="step-idle bg-blue-900/40 rounded-lg px-3 py-2 border border-white/10">Service</li>
                            <li id="step-doctor" class="step-idle bg-blue-900/40 rounded-lg px-3 py-2 border border-white/10">Doctor</li>
                            <li id="step-date" class="step-idle bg-blue-900/40 rounded-lg px-3 py-2 border border-white/10">Date</li>
                            <li id="step-time" class="step-idle bg-blue-900/40 rounded-lg px-3 py-2 border border-white/10">Time</li>
                            <li id="step-confirm" class="step-idle bg-blue-900/40 rounded-lg px-3 py-2 border border-white/10">Confirm</li>
                        </ol>

                        <form method="POST" action="<?= site_url('book/submit') ?>" class="space-y-5 flex-grow flex flex-col justify-between">
                            <div>
                                <?= csrf_field() ?>

                                <div>
                                    <label for="service_id" class="block text-xs font-medium text-gray-300 mb-1">Service</label>
                                    <select id="service_id" name="service_id" required class="w-full px-3 py-2 text-sm bg-blue-900/50 border border-white/10 rounded-lg focus:ring-1 focus:ring-indigo-400 focus:border-indigo-400 transition text-white placeholder-gray-400">
                                        <option value="" class="bg-blue-800 text-gray-400">Select Service</option>
                                        <?php foreach ($services as $service): ?>
                                            <option value="<?= html_escape($service['id']) ?>"
                                                data-duration="<?= html_escape($service['duration_mins']) ?>"
                                                data-price="<?= number_format($service['price'], 2, '.', '') ?>"
                                                <?= (repopulate('service_id') == $service['id']) ? 'selected' : '' ?>
                                                class="bg-blue-800 text-white">
                                                <?= html_escape($service['name']) ?> ($<?= number_format($service['price'], 2) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p id="serviceHelp" class="text-xs text-gray-400 mt-1">Choose a service to see duration and cost.</p>
                                </div>

                                <div>
                                    <label for="doctor_id" class="block text-xs font-medium text-gray-300 mb-1">Doctor</label>
                                    <select id="doctor_id" name="doctor_id" required class="w-full px-3 py-2 text-sm bg-blue-900/50 border border-white/10 rounded-lg focus:ring-1 focus:ring-indigo-400 focus:border-indigo-400 transition text-white placeholder-gray-400">
                                        <option value="" class="bg-blue-800 text-gray-400">Select Doctor</option>
                                        <?php foreach ($doctors as $doctor): ?>
                                            <option value="<?= html_escape($doctor['id']) ?>"
                                                data-name="<?= html_escape('Dr. ' . $doctor['name']) ?>"
                                                data-specialty="<?= html_escape($doctor['specialty']) ?>"
                                                <?= (repopulate('doctor_id') == $doctor['id']) ? 'selected' : '' ?>
                                                class="bg-blue-800 text-white">
                                                Dr. <?= html_escape($doctor['name']) ?> (<?= html_escape($doctor['specialty']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p id="doctorHelp" class="text-xs text-gray-400 mt-1">Select a dentist to proceed.</p>
                                </div>

                                <div>
                                    <label for="appointment_date" class="block text-xs font-medium text-gray-300 mb-1">Select Date</label>
                                    <!-- Full month calendar selector -->
                                    <div id="dateCalendar" class="bg-blue-900/30 border border-white/10 rounded-lg p-3">
                                        <div class="flex items-center justify-between mb-2">
                                            <button type="button" id="calPrev" class="px-2 py-1 rounded-md bg-blue-900/60 border border-white/10 text-gray-200 hover:bg-blue-900/80" aria-label="Previous month">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path d="M15 19l-7-7 7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </button>
                                            <div id="calMonthLabel" class="text-sm font-semibold text-white" aria-live="polite">Month YYYY</div>
                                            <button type="button" id="calNext" class="px-2 py-1 rounded-md bg-blue-900/60 border border-white/10 text-gray-200 hover:bg-blue-900/80" aria-label="Next month">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-4 h-4"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </button>
                                        </div>
                                        <div class="grid grid-cols-7 text-[11px] text-indigo-300 mb-1">
                                            <div class="text-center">Sun</div>
                                            <div class="text-center">Mon</div>
                                            <div class="text-center">Tue</div>
                                            <div class="text-center">Wed</div>
                                            <div class="text-center">Thu</div>
                                            <div class="text-center">Fri</div>
                                            <div class="text-center">Sat</div>
                                        </div>
                                        <div id="calGrid" class="cal-grid"></div>
                                    </div>
                                    <!-- Keep original input for backend; visually hidden but present for validation -->
                                    <input type="date" id="appointment_date" name="appointment_date" required
                                        value="<?= repopulate('appointment_date', date('Y-m-d')) ?>"
                                        min="<?= date('Y-m-d') ?>"
                                        class="sr-only">
                                </div>

                                <div>
                                    <label for="time_slot" class="block text-xs font-medium text-gray-300 mb-1">Select Time</label>
                                    <div id="timeChips" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
                                        <?php foreach ($time_slots as $slot): ?>
                                            <button type="button" class="chip chip-idle time-chip" data-value="<?= html_escape($slot) ?>">
                                                <?= date('g:i A', strtotime($slot)) ?>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                    <!-- Keep original select for backend; visually hidden but present for validation -->
                                    <select id="time_slot" name="time_slot" required class="sr-only">
                                        <option value="">Select Time</option>
                                        <?php foreach ($time_slots as $slot): ?>
                                            <option value="<?= html_escape($slot) ?>" <?= (repopulate('time_slot') == $slot) ? 'selected' : '' ?>>
                                                <?= date('g:i A', strtotime($slot)) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="text-xs text-gray-400 mt-1" id="availabilityHint">Times based on availability.</p>
                                </div>
                            </div>

                            <!-- Live Summary -->
                            <div class="mt-4 bg-blue-900/40 rounded-lg p-4 border border-white/10" role="status" aria-live="polite">
                                <h3 class="text-sm font-bold text-white mb-2">Appointment Summary</h3>
                                <div class="grid sm:grid-cols-2 gap-3 text-xs">
                                    <div>
                                        <p class="text-indigo-300">Service</p>
                                        <p id="sumService" class="font-semibold text-white">—</p>
                                        <p class="text-indigo-300 mt-2">Doctor</p>
                                        <p id="sumDoctor" class="font-semibold text-white">—</p>
                                    </div>
                                    <div>
                                        <p class="text-indigo-300">Date & Time</p>
                                        <p id="sumWhen" class="font-semibold text-white">—</p>
                                        <p class="text-indigo-300 mt-2">Est. End</p>
                                        <p id="sumEnd" class="font-semibold text-white">—</p>
                                    </div>
                                </div>
                                <div class="mt-3 flex items-center justify-between text-sm">
                                    <span class="text-indigo-300">Price</span>
                                    <span id="sumPrice" class="font-bold text-white">—</span>
                                </div>
                                <p id="sumNote" class="mt-2 text-[11px] text-gray-400">Complete all fields to enable confirmation.</p>
                            </div>

                            <div class="mt-auto pt-4">
                                <div class="flex gap-3">
                                    <button type="button" id="clearForm" class="w-1/3 bg-blue-900/40 text-gray-200 py-2.5 rounded-lg font-semibold text-xs border border-white/10 hover:bg-blue-900/60 transition">Clear</button>
                                    <button type="submit" id="submitBtn" disabled class="w-2/3 bg-indigo-500/60 cursor-not-allowed text-white py-2.5 rounded-lg font-semibold text-sm hover:bg-indigo-400 transition duration-200 shadow-md shadow-indigo-500/30 disabled:opacity-60 disabled:hover:bg-indigo-500/60">
                                        <span class="inline-flex items-center gap-2">
                                            <svg id="submitSpinner" class="size-4 animate-spin hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle class="opacity-25" cx="12" cy="12" r="10" stroke-width="4"></circle><path class="opacity-75" d="M4 12a8 8 0 018-8" stroke-width="4" stroke-linecap="round"></path></svg>
                                            <span id="submitText">Confirm Appointment</span>
                                        </span>
                                    </button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>

                <div class="space-y-8">
                    <div class="bg-blue-800/50 p-6 rounded-xl shadow-lg border border-white/10 ring-1 ring-white/10 space-y-4">
                        <h2 class="text-2xl font-bold text-white border-b border-white/10 pb-2">Available Dentists</h2>
                        <p class="text-gray-300 text-xs">Choose your preferred dental professional.</p>
                        <div class="max-h-[250px] overflow-y-auto space-y-3 pr-2">
                            <?php if (!empty($doctors)): ?>
                                <?php foreach ($doctors as $doctor): ?>
                                    <div class="bg-blue-900/40 rounded-lg p-3 border border-white/10 flex items-center space-x-3 transition hover:shadow-sm hover:bg-blue-900/60 doctor-card" data-doctor-id="<?= html_escape($doctor['id']) ?>">
                                        <div>
                                            <h3 class="text-sm font-bold text-white">Dr. <?= html_escape($doctor['name']) ?></h3>
                                            <p class="text-indigo-300 font-semibold text-xs"><?= html_escape($doctor['specialty']) ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-gray-400 text-sm">No dentists currently available.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="bg-blue-800/50 p-6 rounded-xl shadow-lg border border-white/10 ring-1 ring-white/10 space-y-4">
                        <h2 class="text-2xl font-bold text-white border-b border-white/10 pb-2">Available Services</h2>
                        <p class="text-gray-300 text-xs">Review services and duration.</p>
                        <div class="max-h-[250px] overflow-y-auto space-y-3 pr-2">
                            <?php if (!empty($services)): ?>
                                <?php foreach ($services as $service): ?>
                                    <div class="p-3 rounded-lg border border-white/10 hover:shadow-sm transition bg-blue-900/40 hover:bg-blue-900/60 service-card" data-service-id="<?= html_escape($service['id']) ?>">
                                        <h3 class="text-sm font-semibold text-indigo-300"><?= html_escape($service['name']) ?></h3>
                                        <p class="text-xs text-gray-300">Duration: <?= html_escape($service['duration_mins']) ?> mins</p>
                                        <p class="text-xs font-bold text-white">Price: $<?= number_format($service['price'], 2) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-gray-400 text-sm">No services are currently available.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <footer class="bg-[#212631]/40 rounded-t-4xl border-t border-white/50" id="contact-footer">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10 border-b border-gray-200/10 pb-10">

                <div class="space-y-4">
                    <a href="#">
                        <h3 class="text-4xl font-bold text-white">DENTALCARE</h3>
                    </a>
                    <p class="text-sm text-gray-300">
                        Committed to providing personalized and high-quality dental care in a comfortable and welcoming environment.
                    </p>
                    <div class="flex items-center space-x-2 text-sm text-gray-300">
                        <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.717 21 3 14.283 3 6V5z"></path>
                        </svg>
                        <span class="font-mono">0963-405-5941 DENTALCARE</span>
                    </div>
                </div>

                <div>
                    <h4 class="text-lg font-semibold mb-4 text-white">Quick Links</h4>
                    <ul class="space-y-3 text-sm">
                        <li><a href="/#about" class="text-gray-300 hover:text-white transition">About Us</a></li>
                        <li><a href="/#services" class="text-gray-300 hover:text-white transition">Our Services</a></li>
                        <li><a href="/#contact" class="text-gray-300 hover:text-white transition">Find Us</a></li>
                        <li><a href="<?= site_url('/book') ?>" class="text-gray-300 hover:text-white transition">Book</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-lg font-semibold mb-4 text-white">Patient Center</h4>
                    <ul class="space-y-3 text-sm">
                        <li><a href="<?= site_url('/book') ?>" class="text-gray-300 hover:text-white transition">Book Appointment</a></li>
                        <li><a href="<?= site_url('login') ?>" class="text-gray-300 hover:text-white transition">Patient Login</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition">FAQs</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition">Privacy Policy</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-lg font-semibold mb-4 text-white">Location & Hours</h4>
                    <address class="space-y-3 text-sm not-italic">
                        <p class="text-gray-300">Naujan, Oriental Mindoro, 5204</p>
                        <p class="text-gray-300">Mon - Fri: 8:00 AM - 5:00 PM</p>
                        <p class="text-gray-300">Sat: 8:00 AM - 21:00 PM</p>
                    </address>
                </div>
            </div>

            <div class="mt-8 pt-4 flex flex-col md:flex-row justify-between items-center text-sm text-gray-300">
                <p>&copy; <?= date('Y') ?> DENTALCARE. All rights reserved.</p>
                <div class="flex space-x-4 mt-4 md:mt-0">
                    <a href="#" class="text-gray-400 hover:text-indigo-400 transition">
                        <img src="https://cdn-icons-png.flaticon.com/128/174/174855.png" alt="Instagram Icon" class="w-6 h-6 rounded-md">
                    </a>
                    <a href="#" class="text-gray-400 hover:text-indigo-400 transition">
                        <img src="https://cdn-icons-png.flaticon.com/128/5968/5968764.png" alt="Facebook Icon" class="w-6 h-6 rounded-md">
                    </a>
                    <a href="#" class="text-gray-400 hover:text-indigo-400 transition">
                        <img src="https://cdn-icons-png.flaticon.com/128/5968/5968830.png" alt="Twitter/X Icon" class="w-6 h-6 rounded-md invert">
                    </a>
                </div>
            </div>
        </div>
    </footer>

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

    <!-- Elfsight AI Chatbot | Untitled AI Chatbot -->
    <script src="https://elfsightcdn.com/platform.js" async></script>
    <div class="elfsight-app-4f03267f-b8d0-4e92-9be7-5901554b587c" data-elfsight-app-lazy></div>

    <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>

    <script>
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

            // Close modal on Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                    hideModal();
                }
            });
        })();
    </script>

    <!-- Appointment Form Interactivity -->
    <script>
        (function () {
            const $ = (sel) => document.querySelector(sel);
            const $$ = (sel) => Array.from(document.querySelectorAll(sel));

            const serviceSel = $('#service_id');
            const doctorSel = $('#doctor_id');
            const dateInp   = $('#appointment_date');
            const timeSel   = $('#time_slot');
            const calGrid = $('#calGrid');
            const calMonthLabel = $('#calMonthLabel');
            const calPrev = $('#calPrev');
            const calNext = $('#calNext');

            const sumService = $('#sumService');
            const sumDoctor  = $('#sumDoctor');
            const sumWhen    = $('#sumWhen');
            const sumEnd     = $('#sumEnd');
            const sumPrice   = $('#sumPrice');
            const sumNote    = $('#sumNote');

            const submitBtn  = $('#submitBtn');
            const submitText = $('#submitText');
            const submitSpin = $('#submitSpinner');
            const clearBtn   = $('#clearForm');

            const serviceCards = $$('.service-card');
            const doctorCards  = $$('.doctor-card');
            const timeChips    = $$('.time-chip');

            const stepService = $('#step-service');
            const stepDoctor  = $('#step-doctor');
            const stepDate    = $('#step-date');
            const stepTime    = $('#step-time');
            const stepConfirm = $('#step-confirm');

            const availabilityHint = $('#availabilityHint');
            const serviceHelp = $('#serviceHelp');
            const doctorHelp = $('#doctorHelp');

            const DRAFT_KEY = 'appt_form_draft_v1';

            function fmtDate(d) {
                const y = d.getFullYear();
                const m = String(d.getMonth()+1).padStart(2,'0');
                const da = String(d.getDate()).padStart(2,'0');
                return `${y}-${m}-${da}`;
            }

            function fmtTimeHHMM(date) {
                let h = date.getHours();
                const m = String(date.getMinutes()).padStart(2,'0');
                const am = h < 12 ? 'AM' : 'PM';
                h = h % 12; if (h === 0) h = 12;
                return `${h}:${m} ${am}`;
            }

            function getSelectedText(sel) {
                return sel.options[sel.selectedIndex]?.text?.trim() || '';
            }

            function getSelectedOption(sel) {
                return sel.options[sel.selectedIndex] || null;
            }

            function updateSummary() {
                const srvOpt = getSelectedOption(serviceSel);
                const docOpt = getSelectedOption(doctorSel);
                const dateVal = dateInp.value;
                const timeVal = timeSel.value;

                sumService.textContent = srvOpt ? getSelectedText(serviceSel) : '—';
                sumDoctor.textContent  = docOpt ? (docOpt.dataset.name || getSelectedText(doctorSel)) : '—';

                if (dateVal && timeVal) {
                    const start = new Date(`${dateVal}T${timeVal}`);
                    sumWhen.textContent = `${start.toLocaleDateString(undefined, { year:'numeric', month:'short', day:'numeric' })} • ${fmtTimeHHMM(start)}`;
                    const mins = parseInt(srvOpt?.dataset.duration || '0', 10) || 0;
                    if (mins > 0) {
                        const end = new Date(start.getTime() + mins * 60000);
                        sumEnd.textContent = fmtTimeHHMM(end);
                    } else {
                        sumEnd.textContent = '—';
                    }
                } else {
                    sumWhen.textContent = '—';
                    sumEnd.textContent = '—';
                }

                const price = parseFloat(srvOpt?.dataset.price || '0');
                sumPrice.textContent = price ? `$${price.toFixed(2)}` : '—';
            }

            function refreshTimeChipSelection() {
                const val = timeSel.value;
                timeChips.forEach(btn => {
                    btn.classList.remove('chip-active');
                    btn.classList.add('chip-idle');
                    if (btn.dataset.value === val && val) {
                        btn.classList.remove('chip-idle');
                        btn.classList.add('chip-active');
                    }
                });
            }

            function setStepState(el, state) {
                el.classList.remove('step-idle','step-active','step-done');
                el.classList.add(state);
            }

            function updateSteps() {
                const hasService = !!serviceSel.value;
                const hasDoctor  = !!doctorSel.value;
                const hasDate    = !!dateInp.value;
                const hasTime    = !!timeSel.value;

                setStepState(stepService, hasService ? 'step-done' : 'step-active');
                setStepState(stepDoctor,  hasService ? (hasDoctor ? 'step-done' : 'step-active') : 'step-idle');
                setStepState(stepDate,    (hasService && hasDoctor) ? (hasDate ? 'step-done' : 'step-active') : 'step-idle');
                setStepState(stepTime,    (hasService && hasDoctor && hasDate) ? (hasTime ? 'step-done' : 'step-active') : 'step-idle');
                setStepState(stepConfirm, (hasService && hasDoctor && hasDate && hasTime) ? 'step-active' : 'step-idle');
            }

            function highlightCards() {
                const srvId = serviceSel.value;
                const docId = doctorSel.value;
                serviceCards.forEach(c => {
                    c.classList.remove('ring-2','ring-indigo-400','bg-blue-900/70');
                    if (srvId && c.dataset.serviceId === srvId) {
                        c.classList.add('ring-2','ring-indigo-400','bg-blue-900/70');
                    }
                });
                doctorCards.forEach(c => {
                    c.classList.remove('ring-2','ring-indigo-400','bg-blue-900/70');
                    if (docId && c.dataset.doctorId === docId) {
                        c.classList.add('ring-2','ring-indigo-400','bg-blue-900/70');
                    }
                });
            }

            function setAvailabilityChecking() {
                if (!availabilityHint) return;
                availabilityHint.textContent = 'Checking availability…';
                availabilityHint.classList.remove('text-gray-400');
                availabilityHint.classList.add('text-indigo-300');
                window.clearTimeout(setAvailabilityChecking._t);
                setAvailabilityChecking._t = window.setTimeout(() => {
                    availabilityHint.textContent = 'Times based on availability.';
                    availabilityHint.classList.remove('text-indigo-300');
                    availabilityHint.classList.add('text-gray-400');
                }, 600);
            }

            function validateForm() {
                const allGood = serviceSel.value && doctorSel.value && dateInp.value && timeSel.value;
                submitBtn.disabled = !allGood;
                submitBtn.classList.toggle('cursor-not-allowed', !allGood);
                submitBtn.classList.toggle('bg-indigo-500/60', !allGood);
                submitBtn.classList.toggle('bg-indigo-500', !!allGood);
                sumNote.textContent = allGood ? 'Everything looks good. You can confirm your appointment.' : 'Complete all fields to enable confirmation.';
            }

            function persistDraft() {
                const payload = {
                    service_id: serviceSel.value || '',
                    doctor_id: doctorSel.value || '',
                    appointment_date: dateInp.value || '',
                    time_slot: timeSel.value || '',
                    ts: Date.now()
                };
                try { localStorage.setItem(DRAFT_KEY, JSON.stringify(payload)); } catch (_) {}
            }

            function restoreDraftIfEmpty() {
                if (serviceSel.value || doctorSel.value || dateInp.value || timeSel.value) return;
                let raw = null; try { raw = localStorage.getItem(DRAFT_KEY); } catch(_) {}
                if (!raw) return;
                try {
                    const d = JSON.parse(raw);
                    if (!d || typeof d !== 'object') return;
                    if (Date.now() - (d.ts||0) > 30*60*1000) return; // older than 30 minutes
                    if (d.service_id) serviceSel.value = String(d.service_id);
                    if (d.doctor_id) doctorSel.value = String(d.doctor_id);
                    if (d.appointment_date) dateInp.value = d.appointment_date;
                    if (d.time_slot) timeSel.value = d.time_slot;
                    serviceHelp.textContent = 'Draft restored from last session.';
                    serviceHelp.classList.remove('text-gray-400');
                    serviceHelp.classList.add('text-indigo-300');
                    setTimeout(()=>{
                        serviceHelp.textContent = 'Choose a service to see duration and cost.';
                        serviceHelp.classList.remove('text-indigo-300');
                        serviceHelp.classList.add('text-gray-400');
                    }, 2500);
                } catch(_) {}
            }

            function attachCardClicks() {
                serviceCards.forEach(card => card.addEventListener('click', () => {
                    const id = card.dataset.serviceId;
                    if (!id) return;
                    serviceSel.value = id;
                    serviceSel.dispatchEvent(new Event('change'));
                }));
                doctorCards.forEach(card => card.addEventListener('click', () => {
                    const id = card.dataset.doctorId;
                    if (!id) return;
                    doctorSel.value = id;
                    doctorSel.dispatchEvent(new Event('change'));
                }));
                timeChips.forEach(btn => btn.addEventListener('click', () => {
                    const v = btn.dataset.value || '';
                    timeSel.value = v;
                    timeSel.dispatchEvent(new Event('change'));
                }));
            }

            // Calendar builder with month navigation
            let currentMonth = null; // Date set to first day of current month in view

            function iso(d) { return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`; }

            function startOfMonth(d) { return new Date(d.getFullYear(), d.getMonth(), 1); }
            function endOfMonth(d) { return new Date(d.getFullYear(), d.getMonth()+1, 0); }

            function buildCalendar(monthDate) {
                if (!calGrid || !calMonthLabel) return;
                currentMonth = startOfMonth(monthDate);
                calGrid.innerHTML = '';
                calMonthLabel.textContent = currentMonth.toLocaleDateString(undefined, { month: 'long', year: 'numeric' });

                const minIso = dateInp.min || fmtDate(new Date());
                const minDate = new Date(`${minIso}T00:00:00`);
                const selIso = dateInp.value;
                const todayIso = fmtDate(new Date());

                const firstDow = currentMonth.getDay();
                const days = endOfMonth(currentMonth).getDate();

                // Leading blanks
                for (let i = 0; i < firstDow; i++) {
                    const span = document.createElement('div');
                    span.className = 'cal-empty';
                    calGrid.appendChild(span);
                }

                for (let d = 1; d <= days; d++) {
                    const dayDate = new Date(currentMonth.getFullYear(), currentMonth.getMonth(), d);
                    const dayIso = iso(dayDate);
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'cal-day';
                    btn.textContent = String(d);
                    if (dayIso === todayIso) btn.classList.add('cal-today');
                    const disabled = dayDate < minDate;
                    if (disabled) btn.classList.add('cal-disabled');
                    if (dayIso === selIso) btn.classList.add('cal-selected');
                    btn.addEventListener('click', () => {
                        if (disabled) return;
                        dateInp.value = dayIso;
                        dateInp.dispatchEvent(new Event('change'));
                        // refresh selection styles
                        Array.from(calGrid.querySelectorAll('.cal-day')).forEach(n => n.classList.remove('cal-selected'));
                        btn.classList.add('cal-selected');
                    });
                    calGrid.appendChild(btn);
                }

                // Prev/Next state (disable prev if entire previous month < min)
                if (calPrev) {
                    const prevEnd = endOfMonth(new Date(currentMonth.getFullYear(), currentMonth.getMonth()-1, 1));
                    const lockPrev = prevEnd < minDate;
                    calPrev.disabled = lockPrev;
                    calPrev.classList.toggle('opacity-40', lockPrev);
                    calPrev.classList.toggle('cursor-not-allowed', lockPrev);
                }
            }

            function attachHandlers() {
                [serviceSel, doctorSel, dateInp, timeSel].forEach(el => {
                    el.addEventListener('change', () => {
                        if (el === serviceSel || el === doctorSel) setAvailabilityChecking();
                        updateSummary();
                        updateSteps();
                        highlightCards();
                        refreshTimeChipSelection();
                        validateForm();
                        persistDraft();
                    });
                });

                clearBtn?.addEventListener('click', () => {
                    serviceSel.value = '';
                    doctorSel.value = '';
                    dateInp.value = '';
                    timeSel.value = '';
                    updateSummary();
                    updateSteps();
                    highlightCards();
                    validateForm();
                    try { localStorage.removeItem(DRAFT_KEY); } catch(_) {}
                });

                // Submit UX
                const form = serviceSel.closest('form');
                form.addEventListener('submit', () => {
                    submitBtn.disabled = true;
                    submitSpin.classList.remove('hidden');
                    submitText.textContent = 'Booking…';
                });

                // Calendar nav
                calPrev?.addEventListener('click', () => {
                    if (calPrev.disabled) return;
                    const next = new Date(currentMonth.getFullYear(), currentMonth.getMonth()-1, 1);
                    buildCalendar(next);
                });
                calNext?.addEventListener('click', () => {
                    const next = new Date(currentMonth.getFullYear(), currentMonth.getMonth()+1, 1);
                    buildCalendar(next);
                });
            }

            // Initialize
            (function init() {
                // Ensure date min is today (already set by PHP, but guard if needed)
                if (!dateInp.min) {
                    dateInp.min = fmtDate(new Date());
                }
                restoreDraftIfEmpty();
                attachCardClicks();
                // Build calendar for month of selected value or today
                const initBase = dateInp.value ? new Date(`${dateInp.value}T00:00:00`) : (dateInp.min ? new Date(`${dateInp.min}T00:00:00`) : new Date());
                buildCalendar(initBase);
                attachHandlers();
                updateSummary();
                updateSteps();
                highlightCards();
                refreshTimeChipSelection();
                validateForm();
            })();
        })();
    </script>

</body>

</html>