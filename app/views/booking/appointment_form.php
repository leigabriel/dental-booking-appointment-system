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
            font-family: 'Inter', sans-serif;
            font-weight: 500;
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
        .stepper {
            counter-reset: step;
        }

        .stepper li {
            position: relative;
            padding-left: 2.5rem;
        }

        .stepper li::before {
            counter-increment: step;
            content: counter(step);
            position: absolute;
            left: 0;
            top: 0.1rem;
            width: 1.6rem;
            height: 1.6rem;
            border-radius: 9999px;
            display: grid;
            place-items: center;
            font-weight: 700;
        }

        .step-idle::before {
            background: rgba(255, 255, 255, .08);
            color: #a5b4fc;
            border: 1px solid rgba(255, 255, 255, .15);
        }

        .step-active::before {
            background: #6366f1;
            color: white;
            box-shadow: 0 0 0 .25rem rgba(99, 102, 241, .25);
        }

        .step-done::before {
            background: #22c55e;
            color: white;
        }

        /* Chip styles (plain CSS to avoid build steps) */
        .chip {
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.875rem;
            user-select: none;
            transition: background-color .2s ease, box-shadow .2s ease, color .2s ease, border-color .2s ease;
        }

        .chip-idle {
            background: rgba(30, 58, 138, 0.4);
            color: #e5e7eb;
        }

        .chip-idle:hover {
            background: rgba(30, 58, 138, 0.6);
        }

        .chip-active {
            background: #6366f1;
            color: #fff;
            border-color: rgba(99, 102, 241, 0.6);
            box-shadow: 0 2px 10px rgba(99, 102, 241, 0.3);
        }

        /* Card Selection Styles */
        .selection-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .selection-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .selection-card:hover::before {
            opacity: 1;
        }

        .selection-card.selected {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.3) 0%, rgba(139, 92, 246, 0.3) 100%) !important;
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.3), 0 10px 25px rgba(99, 102, 241, 0.2);
            transform: translateY(-2px);
        }

        .selection-card.selected .check-icon {
            opacity: 1;
            transform: scale(1);
        }

        .check-icon {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 24px;
            height: 24px;
            background: #22c55e;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transform: scale(0);
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        /* Payment Card Styles */
        .payment-card {
            position: relative;
        }

        .payment-card.selected {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.4) 0%, rgba(139, 92, 246, 0.4) 100%) !important;
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.4), 0 10px 30px rgba(99, 102, 241, 0.3);
        }

        .payment-card.selected .payment-check {
            display: block !important;
        }

        .payment-check {
            position: absolute;
            top: 8px;
            right: 8px;
        }

        /* Hide scrollbars for chip scroller */
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        /* Calendar styles */
        .cal-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: .5rem;
        }

        .cal-day {
            padding: .5rem;
            border-radius: .5rem;
            text-align: center;
            font-size: .875rem;
            border: 1px solid rgba(255, 255, 255, .08);
            color: #e5e7eb;
            background: rgba(30, 58, 138, .4);
            cursor: pointer;
            transition: all .2s ease;
        }

        .cal-day:hover {
            background: rgba(30, 58, 138, .6);
        }

        .cal-empty {
            visibility: hidden;
        }

        .cal-disabled {
            opacity: .35;
            cursor: not-allowed;
            filter: grayscale(.2);
        }

        .cal-selected {
            background: #6366f1;
            color: #fff;
            border-color: rgba(99, 102, 241, .6);
            box-shadow: 0 2px 10px rgba(99, 102, 241, .3);
        }

        .cal-today {
            outline: 2px solid rgba(99, 102, 241, .5);
            outline-offset: 2px;
        }
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

            <div class="w-full max-w-7xl mx-auto">

                <div class="bg-gradient-to-br from-blue-800/50 to-blue-900/50 backdrop-blur-sm p-8 rounded-2xl shadow-2xl border-4 border-white/50 ring-1 ring-white/10">
                    <div class="space-y-8">
                        <div class="text-center">
                            <h2 class="text-4xl font-extrabold text-white mb-2 bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">Book Your Appointment</h2>
                            <p class="text-gray-300">Follow the steps below to schedule your dental visit</p>
                        </div>

                        <!-- Progress Stepper -->
                        <ol class="stepper grid grid-cols-5 gap-3 text-xs text-indigo-200">
                            <li id="step-service" class="step-idle bg-blue-900/40 rounded-lg px-4 py-3 border border-white/10 text-center">
                                <div class="font-semibold">Service</div>
                            </li>
                            <li id="step-doctor" class="step-idle bg-blue-900/40 rounded-lg px-4 py-3 border border-white/10 text-center">
                                <div class="font-semibold">Doctor</div>
                            </li>
                            <li id="step-date" class="step-idle bg-blue-900/40 rounded-lg px-4 py-3 border border-white/10 text-center">
                                <div class="font-semibold">Date</div>
                            </li>
                            <li id="step-time" class="step-idle bg-blue-900/40 rounded-lg px-4 py-3 border border-white/10 text-center">
                                <div class="font-semibold">Time</div>
                            </li>
                            <li id="step-confirm" class="step-idle bg-blue-900/40 rounded-lg px-4 py-3 border border-white/10 text-center">
                                <div class="font-semibold">Confirm</div>
                            </li>
                        </ol>

                        <form method="POST" action="<?= site_url('book/submit') ?>" class="space-y-8">
                            <?= csrf_field() ?>

                            <!-- Service Selection Section -->
                            <div class="bg-blue-900/30 rounded-xl p-6 border border-white/10">
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <h3 class="text-2xl font-bold text-white flex items-center gap-2">
                                            <svg class="w-7 h-7 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                            </svg>
                                            Select a Service
                                        </h3>
                                        <p id="serviceHelp" class="text-sm text-gray-400 mt-1">Choose the dental service you need</p>
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="button" id="serviceScrollLeft" class="p-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white transition-all shadow-lg disabled:opacity-40 disabled:cursor-not-allowed">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                            </svg>
                                        </button>
                                        <button type="button" id="serviceScrollRight" class="p-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white transition-all shadow-lg disabled:opacity-40 disabled:cursor-not-allowed">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="relative">
                                    <div id="serviceScroller" class="flex gap-4 overflow-x-auto pb-4 no-scrollbar snap-x snap-mandatory scroll-smooth">
                                        <?php if (!empty($services)): ?>
                                            <?php foreach ($services as $service): ?>
                                                <div class="service-card-select selection-card flex-shrink-0 w-72 p-5 rounded-xl border-2 border-white/10 bg-blue-900/40 snap-start"
                                                    data-service-id="<?= html_escape($service['id']) ?>"
                                                    data-service-name="<?= html_escape($service['name']) ?>"
                                                    data-duration="<?= html_escape($service['duration_mins']) ?>"
                                                    data-price="<?= number_format($service['price'], 2, '.', '') ?>">
                                                    <div class="check-icon">
                                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                        </svg>
                                                    </div>
                                                    <div class="flex items-start gap-4">
                                                        <div class="flex-shrink-0 w-16 h-16 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg">
                                                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <h4 class="text-lg font-bold text-white mb-2 truncate"><?= html_escape($service['name']) ?></h4>
                                                            <div class="space-y-1 text-sm">
                                                                <div class="flex items-center text-indigo-300">
                                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                    </svg>
                                                                    <?= html_escape($service['duration_mins']) ?> minutes
                                                                </div>
                                                                <div class="flex items-center text-green-400 font-bold text-base">
                                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                    </svg>
                                                                    $<?= number_format($service['price'], 2) ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p class="text-gray-400 text-center py-8">No services are currently available.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <input type="hidden" id="service_id" name="service_id" required>
                            </div>

                            <!-- Doctor Selection Section -->
                            <div class="bg-blue-900/30 rounded-xl p-6 border border-white/10">
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <h3 class="text-2xl font-bold text-white flex items-center gap-2">
                                            <svg class="w-7 h-7 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Choose Your Doctor
                                        </h3>
                                        <p id="doctorHelp" class="text-sm text-gray-400 mt-1">Select your preferred dental professional</p>
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="button" id="doctorScrollLeft" class="p-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white transition-all shadow-lg disabled:opacity-40 disabled:cursor-not-allowed">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                            </svg>
                                        </button>
                                        <button type="button" id="doctorScrollRight" class="p-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white transition-all shadow-lg disabled:opacity-40 disabled:cursor-not-allowed">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="relative">
                                    <div id="doctorScroller" class="flex gap-4 overflow-x-auto pb-4 no-scrollbar snap-x snap-mandatory scroll-smooth">
                                        <?php if (!empty($doctors)): ?>
                                            <?php foreach ($doctors as $doctor): ?>
                                                <div class="doctor-card-select selection-card flex-shrink-0 w-80 p-5 rounded-xl border-2 border-white/10 bg-blue-900/40 snap-start"
                                                    data-doctor-id="<?= html_escape($doctor['id']) ?>"
                                                    data-doctor-name="<?= html_escape('Dr. ' . $doctor['name']) ?>"
                                                    data-specialty="<?= html_escape($doctor['specialty']) ?>">
                                                    <div class="check-icon">
                                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                        </svg>
                                                    </div>
                                                    <div class="flex items-start gap-4">
                                                        <div class="flex-shrink-0">
                                                            <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-xl ring-4 ring-blue-500/20">
                                                                <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                                                </svg>
                                                            </div>
                                                        </div>
                                                        <div class="flex-1 min-w-0">
                                                            <h4 class="text-xl font-bold text-white mb-1"><?= html_escape($doctor['name']) ?></h4>
                                                            <div class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-500/30 border border-indigo-400/50">
                                                                <svg class="w-4 h-4 mr-2 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                                <span class="text-sm font-semibold text-indigo-200"><?= html_escape($doctor['specialty']) ?></span>
                                                            </div>
                                                            <div class="mt-3 flex items-center text-gray-400 text-xs">
                                                                <svg class="w-4 h-4 mr-1 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                                </svg>
                                                                <span>Experienced Professional</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p class="text-gray-400 text-center py-8">No dentists currently available.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <input type="hidden" id="doctor_id" name="doctor_id" required>
                            </div>

                            <!-- Date Selection Section -->
                            <div class="bg-blue-900/30 rounded-xl p-6 border border-white/10">
                                <div class="mb-4">
                                    <h3 class="text-2xl font-bold text-white flex items-center gap-2">
                                        <svg class="w-7 h-7 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        Select Date
                                    </h3>
                                    <p class="text-sm text-gray-400 mt-1">Pick your preferred appointment date</p>
                                </div>
                                <div id="dateCalendar" class="bg-blue-900/30 border border-white/10 rounded-xl p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <button type="button" id="calPrev" class="px-3 py-2 rounded-lg bg-blue-900/60 border border-white/10 text-gray-200 hover:bg-blue-800/60 transition-all" aria-label="Previous month">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-5 h-5">
                                                <path d="M15 19l-7-7 7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </button>
                                        <div id="calMonthLabel" class="text-lg font-bold text-white" aria-live="polite">Month YYYY</div>
                                        <button type="button" id="calNext" class="px-3 py-2 rounded-lg bg-blue-900/60 border border-white/10 text-gray-200 hover:bg-blue-800/60 transition-all" aria-label="Next month">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-5 h-5">
                                                <path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-7 text-xs text-indigo-300 mb-2 font-semibold">
                                        <div class="text-center py-2">Sun</div>
                                        <div class="text-center py-2">Mon</div>
                                        <div class="text-center py-2">Tue</div>
                                        <div class="text-center py-2">Wed</div>
                                        <div class="text-center py-2">Thu</div>
                                        <div class="text-center py-2">Fri</div>
                                        <div class="text-center py-2">Sat</div>
                                    </div>
                                    <div id="calGrid" class="cal-grid"></div>
                                </div>
                                <input type="date" id="appointment_date" name="appointment_date" required
                                    value="<?= repopulate('appointment_date', date('Y-m-d')) ?>"
                                    min="<?= date('Y-m-d') ?>"
                                    class="sr-only">

                                <!-- Daily Limit Warning -->
                                <div id="dailyLimitWarning" class="hidden mt-4 p-4 rounded-lg bg-amber-900/30 text-amber-200 border border-amber-500/50">
                                </div>
                            </div>

                            <!-- Time Selection Section -->
                            <div class="bg-blue-900/30 rounded-xl p-6 border border-white/10">
                                <div class="mb-4">
                                    <h3 class="text-2xl font-bold text-white flex items-center gap-2">
                                        <svg class="w-7 h-7 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Choose Time Slot
                                    </h3>
                                    <p id="availabilityHint" class="text-sm text-gray-400 mt-1">Select your preferred time</p>
                                </div>
                                <div id="timeChips" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
                                    <?php foreach ($time_slots as $slot): ?>
                                        <button type="button" class="chip chip-idle time-chip text-sm py-3 font-semibold" data-value="<?= html_escape($slot) ?>">
                                            <?= date('g:i A', strtotime($slot)) ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                                <select id="time_slot" name="time_slot" required class="sr-only">
                                    <option value="">Select Time</option>
                                    <?php foreach ($time_slots as $slot): ?>
                                        <option value="<?= html_escape($slot) ?>" <?= (repopulate('time_slot') == $slot) ? 'selected' : '' ?>>
                                            <?= date('g:i A', strtotime($slot)) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Payment Method Section -->
                            <div class="bg-blue-900/30 rounded-xl p-6 border border-white/10">
                                <div class="mb-4">
                                    <h3 class="text-2xl font-bold text-white flex items-center gap-2">
                                        <svg class="w-7 h-7 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                        Payment Method
                                    </h3>
                                    <p class="text-sm text-gray-400 mt-1">Choose your preferred payment option</p>
                                </div>

                                <!-- Payment Method Cards -->
                                <div class="grid md:grid-cols-3 gap-4 mb-4">
                                    <!-- GCash Option -->
                                    <div class="payment-card selection-card rounded-xl p-4 border-2 border-white/10 bg-gradient-to-br from-blue-900/40 to-blue-800/40 cursor-pointer transition-all hover:scale-105" data-payment="gcash">
                                        <div class="flex flex-col items-center gap-3">
                                            <div class="w-16 h-16 rounded-full flex items-center justify-center">
                                                <img class="w-16 h-16 rounded-full" src="https://i.pinimg.com/736x/fd/f5/15/fdf515e3ab09eb3b2c138452a7b4e291.jpg" alt="">
                                            </div>
                                            <div class="text-center">
                                                <h4 class="font-bold text-white text-lg">GCash</h4>
                                                <p class="text-xs text-gray-400">Mobile Wallet</p>
                                            </div>
                                            <div class="payment-check hidden">
                                                <svg class="w-6 h-6 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- PayPal Option -->
                                    <div class="payment-card selection-card rounded-xl p-4 border-2 border-white/10 bg-gradient-to-br from-blue-900/40 to-indigo-800/40 cursor-pointer transition-all hover:scale-105" data-payment="paypal">
                                        <div class="flex flex-col items-center gap-3">
                                            <div class="w-16 h-16 rounded-full bg-blue-500 flex items-center justify-center">
                                                <img class="w-16 h-16 rounded-full" src="https://i.pinimg.com/736x/b4/fc/bd/b4fcbdd3f6bc6275fe396754fdd34fda.jpg" alt="">
                                            </div>
                                            <div class="text-center">
                                                <h4 class="font-bold text-white text-lg">PayPal</h4>
                                                <p class="text-xs text-gray-400">Secure Payment</p>
                                            </div>
                                            <div class="payment-check hidden">
                                                <svg class="w-6 h-6 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Pay at Clinic Option -->
                                    <div class="payment-card selection-card rounded-xl p-4 border-2 border-white/10 bg-gradient-to-br from-blue-900/40 to-purple-800/40 cursor-pointer transition-all hover:scale-105" data-payment="clinic">
                                        <div class="flex flex-col items-center gap-3">
                                            <div class="w-16 h-16 rounded-full bg-purple-600 flex items-center justify-center">
                                                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                </svg>
                                            </div>
                                            <div class="text-center">
                                                <h4 class="font-bold text-white text-lg">Pay at Clinic</h4>
                                                <p class="text-xs text-gray-400">Cash Payment</p>
                                            </div>
                                            <div class="payment-check hidden">
                                                <svg class="w-6 h-6 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <input type="hidden" id="payment_method" name="payment_method" required>

                                <!-- Payment Instructions -->
                                <div id="paymentInstructions" class="hidden mt-4 p-4 rounded-lg bg-indigo-900/30 border border-indigo-500/30">
                                    <div id="gcashInstructions" class="hidden">
                                        <h4 class="font-bold text-white mb-2 flex items-center gap-2">
                                            <svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                            </svg>
                                            GCash Payment Instructions
                                        </h4>
                                        <p class="text-sm text-gray-300 mb-2">You will be redirected to GCash payment page after confirming your appointment.</p>
                                    </div>

                                    <div id="paypalInstructions" class="hidden">
                                        <h4 class="font-bold text-white mb-2 flex items-center gap-2">
                                            <svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                            </svg>
                                            PayPal Payment Instructions
                                        </h4>
                                        <p class="text-sm text-gray-300 mb-2">You will be redirected to PayPal sandbox payment page after confirming your appointment.</p>
                                    </div>

                                    <div id="clinicInstructions" class="hidden">
                                        <h4 class="font-bold text-white mb-2 flex items-center gap-2">
                                            <svg class="w-5 h-5 text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                            </svg>
                                            Pay at Clinic Instructions
                                        </h4>
                                        <p class="text-sm text-gray-300 mb-2">You can pay when you arrive at the clinic.</p>
                                        <ul class="text-xs text-gray-400 space-y-1 ml-6 list-disc">
                                            <li>Cash payment accepted</li>
                                            <li>Please arrive 10 minutes early</li>
                                            <li>Bring exact amount if possible</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Appointment Summary -->
                            <div class="bg-gradient-to-br from-indigo-900/40 to-purple-900/40 rounded-xl p-6 border border-indigo-500/30 shadow-xl" role="status" aria-live="polite">
                                <h3 class="text-2xl font-bold text-white mb-4 flex items-center gap-2">
                                    <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Appointment Summary
                                </h3>
                                <div class="grid md:grid-cols-2 gap-6">
                                    <div class="space-y-4">
                                        <div>
                                            <p class="text-xs text-indigo-300 uppercase tracking-wider mb-1">Service</p>
                                            <p id="sumService" class="text-lg font-bold text-white">—</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-indigo-300 uppercase tracking-wider mb-1">Doctor</p>
                                            <p id="sumDoctor" class="text-lg font-bold text-white">—</p>
                                        </div>
                                    </div>
                                    <div class="space-y-4">
                                        <div>
                                            <p class="text-xs text-indigo-300 uppercase tracking-wider mb-1">Date & Time</p>
                                            <p id="sumWhen" class="text-lg font-bold text-white">—</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-indigo-300 uppercase tracking-wider mb-1">Estimated End</p>
                                            <p id="sumEnd" class="text-lg font-bold text-white">—</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-6 pt-6 border-t border-indigo-500/30 flex items-center justify-between">
                                    <span class="text-lg text-indigo-300 font-semibold">Total Price</span>
                                    <span id="sumPrice" class="text-3xl font-extrabold text-white">—</span>
                                </div>
                                <p id="sumNote" class="mt-4 text-sm text-gray-400 text-center">Complete all fields to enable confirmation.</p>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex gap-4">
                                <button type="button" id="clearForm" class="flex-1 bg-blue-900/60 text-white py-4 rounded-xl font-bold text-base border border-white/10 hover:bg-blue-900/80 transition-all transform hover:scale-105">
                                    <span class="flex items-center justify-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Clear Form
                                    </span>
                                </button>
                                <button type="submit" id="submitBtn" disabled class="flex-[2] bg-gradient-to-r from-indigo-600 to-purple-600 cursor-not-allowed text-white py-4 rounded-xl font-bold text-base hover:from-indigo-500 hover:to-purple-500 transition-all duration-300 shadow-lg shadow-indigo-500/30 disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:from-indigo-600 disabled:hover:to-purple-600 transform hover:scale-105 disabled:transform-none">
                                    <span class="inline-flex items-center justify-center gap-2">
                                        <svg id="submitSpinner" class="size-5 animate-spin hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke-width="4"></circle>
                                            <path class="opacity-75" d="M4 12a8 8 0 018-8" stroke-width="4" stroke-linecap="round"></path>
                                        </svg>
                                        <span id="submitText">Confirm Appointment</span>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </span>
                                </button>
                            </div>

                        </form>
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
        (function() {
            const $ = (sel) => document.querySelector(sel);
            const $$ = (sel) => Array.from(document.querySelectorAll(sel));

            const serviceInp = $('#service_id');
            const doctorInp = $('#doctor_id');
            const dateInp = $('#appointment_date');
            const timeSel = $('#time_slot');
            const calGrid = $('#calGrid');
            const calMonthLabel = $('#calMonthLabel');
            const calPrev = $('#calPrev');
            const calNext = $('#calNext');

            const sumService = $('#sumService');
            const sumDoctor = $('#sumDoctor');
            const sumWhen = $('#sumWhen');
            const sumEnd = $('#sumEnd');
            const sumPrice = $('#sumPrice');
            const sumNote = $('#sumNote');

            const submitBtn = $('#submitBtn');
            const submitText = $('#submitText');
            const submitSpin = $('#submitSpinner');
            const clearBtn = $('#clearForm');

            const serviceCards = $$('.service-card-select');
            const doctorCards = $$('.doctor-card-select');
            const timeChips = $$('.time-chip');
            const paymentCards = $$('.payment-card');
            const paymentInp = $('#payment_method');
            const paymentInstructions = $('#paymentInstructions');
            const gcashInstructions = $('#gcashInstructions');
            const paypalInstructions = $('#paypalInstructions');
            const clinicInstructions = $('#clinicInstructions');

            const stepService = $('#step-service');
            const stepDoctor = $('#step-doctor');
            const stepDate = $('#step-date');
            const stepTime = $('#step-time');
            const stepConfirm = $('#step-confirm');

            const availabilityHint = $('#availabilityHint');
            const serviceHelp = $('#serviceHelp');
            const doctorHelp = $('#doctorHelp');

            const DRAFT_KEY = 'appt_form_draft_v1';

            // Store for booked slots
            let bookedSlots = [];

            // Scroll button elements
            const serviceScroller = $('#serviceScroller');
            const serviceScrollLeft = $('#serviceScrollLeft');
            const serviceScrollRight = $('#serviceScrollRight');
            const doctorScroller = $('#doctorScroller');
            const doctorScrollLeft = $('#doctorScrollLeft');
            const doctorScrollRight = $('#doctorScrollRight');

            // Scroll functionality
            function setupScrollButtons(scroller, leftBtn, rightBtn) {
                if (!scroller || !leftBtn || !rightBtn) return;

                const scrollAmount = 300; // pixels to scroll

                function updateScrollButtons() {
                    const isAtStart = scroller.scrollLeft <= 0;
                    const isAtEnd = scroller.scrollLeft >= scroller.scrollWidth - scroller.clientWidth - 5;

                    leftBtn.disabled = isAtStart;
                    rightBtn.disabled = isAtEnd;
                }

                leftBtn.addEventListener('click', () => {
                    scroller.scrollBy({
                        left: -scrollAmount,
                        behavior: 'smooth'
                    });
                    setTimeout(updateScrollButtons, 300);
                });

                rightBtn.addEventListener('click', () => {
                    scroller.scrollBy({
                        left: scrollAmount,
                        behavior: 'smooth'
                    });
                    setTimeout(updateScrollButtons, 300);
                });

                scroller.addEventListener('scroll', updateScrollButtons);

                // Initial check
                updateScrollButtons();

                // Check again after content loads
                setTimeout(updateScrollButtons, 100);
            }

            // Initialize scroll buttons for services and doctors
            setupScrollButtons(serviceScroller, serviceScrollLeft, serviceScrollRight);
            setupScrollButtons(doctorScroller, doctorScrollLeft, doctorScrollRight);

            function fmtDate(d) {
                const y = d.getFullYear();
                const m = String(d.getMonth() + 1).padStart(2, '0');
                const da = String(d.getDate()).padStart(2, '0');
                return `${y}-${m}-${da}`;
            }

            function fmtTimeHHMM(date) {
                let h = date.getHours();
                const m = String(date.getMinutes()).padStart(2, '0');
                const am = h < 12 ? 'AM' : 'PM';
                h = h % 12;
                if (h === 0) h = 12;
                return `${h}:${m} ${am}`;
            }

            function updateSummary() {
                const serviceId = serviceInp.value;
                const doctorId = doctorInp.value;
                const dateVal = dateInp.value;
                const timeVal = timeSel.value;

                // Get service name and details
                const serviceCard = serviceCards.find(c => c.dataset.serviceId === serviceId);
                const serviceName = serviceCard ? serviceCard.dataset.serviceName : '—';
                const serviceDuration = serviceCard ? parseInt(serviceCard.dataset.duration) : 0;
                const servicePrice = serviceCard ? parseFloat(serviceCard.dataset.price) : 0;

                // Get doctor name
                const doctorCard = doctorCards.find(c => c.dataset.doctorId === doctorId);
                const doctorName = doctorCard ? doctorCard.dataset.doctorName : '—';

                sumService.textContent = serviceName;
                sumDoctor.textContent = doctorName;

                if (dateVal && timeVal) {
                    const start = new Date(`${dateVal}T${timeVal}`);
                    sumWhen.textContent = `${start.toLocaleDateString(undefined, { year:'numeric', month:'short', day:'numeric' })} • ${fmtTimeHHMM(start)}`;
                    if (serviceDuration > 0) {
                        const end = new Date(start.getTime() + serviceDuration * 60000);
                        sumEnd.textContent = fmtTimeHHMM(end);
                    } else {
                        sumEnd.textContent = '—';
                    }
                } else {
                    sumWhen.textContent = '—';
                    sumEnd.textContent = '—';
                }

                sumPrice.textContent = servicePrice ? `$${servicePrice.toFixed(2)}` : '—';
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
                el.classList.remove('step-idle', 'step-active', 'step-done');
                el.classList.add(state);
            }

            function updateSteps() {
                const hasService = !!serviceInp.value;
                const hasDoctor = !!doctorInp.value;
                const hasDate = !!dateInp.value;
                const hasTime = !!timeSel.value;

                setStepState(stepService, hasService ? 'step-done' : 'step-active');
                setStepState(stepDoctor, hasService ? (hasDoctor ? 'step-done' : 'step-active') : 'step-idle');
                setStepState(stepDate, (hasService && hasDoctor) ? (hasDate ? 'step-done' : 'step-active') : 'step-idle');
                setStepState(stepTime, (hasService && hasDoctor && hasDate) ? (hasTime ? 'step-done' : 'step-active') : 'step-idle');
                setStepState(stepConfirm, (hasService && hasDoctor && hasDate && hasTime) ? 'step-active' : 'step-idle');
            }

            function highlightCards() {
                const srvId = serviceInp.value;
                const docId = doctorInp.value;

                serviceCards.forEach(c => {
                    c.classList.remove('selected');
                    if (srvId && c.dataset.serviceId === srvId) {
                        c.classList.add('selected');
                    }
                });

                doctorCards.forEach(c => {
                    c.classList.remove('selected');
                    if (docId && c.dataset.doctorId === docId) {
                        c.classList.add('selected');
                    }
                });
            }

            function highlightPaymentCards() {
                const paymentMethod = paymentInp.value;

                paymentCards.forEach(c => {
                    c.classList.remove('selected');
                    if (paymentMethod && c.dataset.payment === paymentMethod) {
                        c.classList.add('selected');
                    }
                });
            }

            function showPaymentInstructions() {
                const method = paymentInp.value;

                // Hide all instructions first
                gcashInstructions.classList.add('hidden');
                paypalInstructions.classList.add('hidden');
                clinicInstructions.classList.add('hidden');

                if (!method) {
                    paymentInstructions.classList.add('hidden');
                    return;
                }

                // Show the relevant instructions
                paymentInstructions.classList.remove('hidden');

                if (method === 'gcash') {
                    gcashInstructions.classList.remove('hidden');
                } else if (method === 'paypal') {
                    paypalInstructions.classList.remove('hidden');
                } else if (method === 'clinic') {
                    clinicInstructions.classList.remove('hidden');
                }
            }

            function hidePaymentInstructions() {
                paymentInstructions.classList.add('hidden');
                gcashInstructions.classList.add('hidden');
                paypalInstructions.classList.add('hidden');
                clinicInstructions.classList.add('hidden');
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

            // Fetch booked slots for a specific doctor and date
            async function fetchBookedSlots(doctorId, date) {
                if (!doctorId || !date) {
                    bookedSlots = [];
                    updateTimeChipsAvailability();
                    return;
                }

                try {
                    const response = await fetch(`<?= site_url('booking/get_booked_slots') ?>?doctor_id=${doctorId}&date=${date}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'include'
                    });

                    if (response.ok) {
                        const data = await response.json();
                        bookedSlots = data.booked_slots || [];

                        // Check daily limit
                        const dailyCount = data.daily_count || 0;
                        const dailyLimit = data.daily_limit || 5;

                        if (dailyCount >= dailyLimit) {
                            // Show warning that this date is fully booked
                            showDailyLimitWarning(date, dailyCount, dailyLimit);
                        } else {
                            hideDailyLimitWarning();
                        }

                        updateTimeChipsAvailability();
                    } else {
                        bookedSlots = [];
                        hideDailyLimitWarning();
                        updateTimeChipsAvailability();
                    }
                } catch (error) {
                    console.error('Error fetching booked slots:', error);
                    bookedSlots = [];
                    hideDailyLimitWarning();
                    updateTimeChipsAvailability();
                }
            }

            // Show daily limit warning
            function showDailyLimitWarning(date, count, limit) {
                const warningDiv = document.getElementById('dailyLimitWarning');
                if (warningDiv) {
                    warningDiv.innerHTML = `
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-amber-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <h4 class="font-bold text-amber-400 mb-1">Daily Limit Reached</h4>
                                <p class="text-sm text-amber-200">This date has reached the maximum booking limit (${count}/${limit} appointments). Please select a different date.</p>
                            </div>
                        </div>
                    `;
                    warningDiv.classList.remove('hidden');

                    // Disable all time slots
                    timeChips.forEach(btn => {
                        btn.disabled = true;
                        btn.classList.add('opacity-40', 'cursor-not-allowed');
                    });

                    // Clear time selection
                    timeSel.value = '';
                    refreshTimeChipSelection();
                    validateForm();
                }
            }

            // Hide daily limit warning
            function hideDailyLimitWarning() {
                const warningDiv = document.getElementById('dailyLimitWarning');
                if (warningDiv) {
                    warningDiv.classList.add('hidden');
                    warningDiv.innerHTML = '';
                }
            }

            // Update time chips to show which slots are booked
            function updateTimeChipsAvailability() {
                let bookedCount = 0;

                timeChips.forEach(btn => {
                    const timeValue = btn.dataset.value;
                    const isBooked = bookedSlots.includes(timeValue);

                    if (isBooked) bookedCount++;

                    btn.disabled = isBooked;
                    btn.classList.toggle('opacity-40', isBooked);
                    btn.classList.toggle('cursor-not-allowed', isBooked);
                    btn.classList.toggle('hover:bg-blue-900/60', !isBooked);

                    if (isBooked) {
                        btn.classList.add('cal-disabled');
                        btn.title = 'This time slot is already booked by another patient';
                        // Add a visual indicator
                        if (!btn.querySelector('.booked-indicator')) {
                            btn.innerHTML = btn.innerHTML + ' <span class="booked-indicator">✗</span>';
                        }
                        // Clear selection if it was selected
                        if (timeSel.value === timeValue) {
                            timeSel.value = '';
                        }
                    } else {
                        btn.classList.remove('cal-disabled');
                        btn.title = '';
                        // Remove booked indicator if exists
                        const indicator = btn.querySelector('.booked-indicator');
                        if (indicator) indicator.remove();
                    }
                });

                // Update availability hint
                const availHint = document.getElementById('availabilityHint');
                if (availHint) {
                    const totalSlots = timeChips.length;
                    const availableSlots = totalSlots - bookedCount;

                    if (bookedCount > 0) {
                        availHint.innerHTML = `<span class="text-amber-400">${bookedCount} slot${bookedCount > 1 ? 's' : ''} already booked</span> • ${availableSlots} available`;
                    } else {
                        availHint.textContent = 'All time slots are available';
                    }
                }

                refreshTimeChipSelection();
                validateForm();
            }

            function validateForm() {
                const allGood = serviceInp.value && doctorInp.value && dateInp.value && timeSel.value && paymentInp.value;
                submitBtn.disabled = !allGood;
                submitBtn.classList.toggle('cursor-not-allowed', !allGood);
                submitBtn.classList.toggle('bg-indigo-500/60', !allGood);
                submitBtn.classList.toggle('bg-indigo-500', !!allGood);
                sumNote.textContent = allGood ? 'Everything looks good. You can confirm your appointment.' : 'Complete all fields to enable confirmation.';
            }

            function persistDraft() {
                const payload = {
                    service_id: serviceInp.value || '',
                    doctor_id: doctorInp.value || '',
                    appointment_date: dateInp.value || '',
                    time_slot: timeSel.value || '',
                    payment_method: paymentInp.value || '',
                    ts: Date.now()
                };
                try {
                    localStorage.setItem(DRAFT_KEY, JSON.stringify(payload));
                } catch (_) {}
            }

            function restoreDraftIfEmpty() {
                if (serviceInp.value || doctorInp.value || dateInp.value || timeSel.value) return;
                let raw = null;
                try {
                    raw = localStorage.getItem(DRAFT_KEY);
                } catch (_) {}
                if (!raw) return;
                try {
                    const d = JSON.parse(raw);
                    if (!d || typeof d !== 'object') return;
                    if (Date.now() - (d.ts || 0) > 30 * 60 * 1000) return; // older than 30 minutes
                    if (d.service_id) serviceInp.value = String(d.service_id);
                    if (d.doctor_id) doctorInp.value = String(d.doctor_id);
                    if (d.appointment_date) dateInp.value = d.appointment_date;
                    if (d.time_slot) timeSel.value = d.time_slot;
                    if (d.payment_method) paymentInp.value = d.payment_method;
                    serviceHelp.textContent = 'Draft restored from last session.';
                    serviceHelp.classList.remove('text-gray-400');
                    serviceHelp.classList.add('text-indigo-300');
                    setTimeout(() => {
                        serviceHelp.textContent = 'Choose a service to see duration and cost.';
                        serviceHelp.classList.remove('text-indigo-300');
                        serviceHelp.classList.add('text-gray-400');
                    }, 2500);
                } catch (_) {}
            }

            function attachCardClicks() {
                serviceCards.forEach(card => card.addEventListener('click', () => {
                    const id = card.dataset.serviceId;
                    if (!id) return;
                    serviceInp.value = id;
                    triggerChange(serviceInp);
                }));

                doctorCards.forEach(card => card.addEventListener('click', () => {
                    const id = card.dataset.doctorId;
                    if (!id) return;
                    doctorInp.value = id;
                    triggerChange(doctorInp);
                }));

                paymentCards.forEach(card => card.addEventListener('click', () => {
                    const method = card.dataset.payment;
                    if (!method) return;
                    paymentInp.value = method;
                    triggerChange(paymentInp);
                }));

                timeChips.forEach(btn => btn.addEventListener('click', () => {
                    if (btn.disabled) return;
                    const v = btn.dataset.value || '';
                    timeSel.value = v;
                    triggerChange(timeSel);
                }));
            }

            function triggerChange(el) {
                el.dispatchEvent(new Event('change'));
            }

            // Calendar builder with month navigation
            let currentMonth = null; // Date set to first day of current month in view

            function iso(d) {
                return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
            }

            function startOfMonth(d) {
                return new Date(d.getFullYear(), d.getMonth(), 1);
            }

            function endOfMonth(d) {
                return new Date(d.getFullYear(), d.getMonth() + 1, 0);
            }

            function buildCalendar(monthDate) {
                if (!calGrid || !calMonthLabel) return;
                currentMonth = startOfMonth(monthDate);
                calGrid.innerHTML = '';
                calMonthLabel.textContent = currentMonth.toLocaleDateString(undefined, {
                    month: 'long',
                    year: 'numeric'
                });

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
                        triggerChange(dateInp);
                        // refresh selection styles
                        Array.from(calGrid.querySelectorAll('.cal-day')).forEach(n => n.classList.remove('cal-selected'));
                        btn.classList.add('cal-selected');
                    });
                    calGrid.appendChild(btn);
                }

                // Prev/Next state (disable prev if entire previous month < min)
                if (calPrev) {
                    const prevEnd = endOfMonth(new Date(currentMonth.getFullYear(), currentMonth.getMonth() - 1, 1));
                    const lockPrev = prevEnd < minDate;
                    calPrev.disabled = lockPrev;
                    calPrev.classList.toggle('opacity-40', lockPrev);
                    calPrev.classList.toggle('cursor-not-allowed', lockPrev);
                }
            }

            function attachHandlers() {
                serviceInp.addEventListener('change', () => {
                    updateSummary();
                    updateSteps();
                    highlightCards();
                    validateForm();
                    persistDraft();
                });

                doctorInp.addEventListener('change', () => {
                    setAvailabilityChecking();
                    updateSummary();
                    updateSteps();
                    highlightCards();
                    validateForm();
                    persistDraft();
                    // Fetch booked slots when doctor or date changes
                    fetchBookedSlots(doctorInp.value, dateInp.value);
                });

                dateInp.addEventListener('change', () => {
                    updateSummary();
                    updateSteps();
                    validateForm();
                    persistDraft();
                    // Fetch booked slots when date changes
                    fetchBookedSlots(doctorInp.value, dateInp.value);
                });

                timeSel.addEventListener('change', () => {
                    updateSummary();
                    updateSteps();
                    refreshTimeChipSelection();
                    validateForm();
                    persistDraft();
                });

                paymentInp.addEventListener('change', () => {
                    highlightPaymentCards();
                    showPaymentInstructions();
                    validateForm();
                    persistDraft();
                });

                clearBtn?.addEventListener('click', () => {
                    serviceInp.value = '';
                    doctorInp.value = '';
                    dateInp.value = '';
                    timeSel.value = '';
                    paymentInp.value = '';
                    bookedSlots = [];
                    updateSummary();
                    updateSteps();
                    highlightCards();
                    highlightPaymentCards();
                    hidePaymentInstructions();
                    updateTimeChipsAvailability();
                    validateForm();
                    try {
                        localStorage.removeItem(DRAFT_KEY);
                    } catch (_) {}
                });

                // Submit UX
                const form = serviceInp.closest('form');
                form.addEventListener('submit', () => {
                    submitBtn.disabled = true;
                    submitSpin.classList.remove('hidden');
                    submitText.textContent = 'Booking…';
                });

                // Calendar nav
                calPrev?.addEventListener('click', () => {
                    if (calPrev.disabled) return;
                    const next = new Date(currentMonth.getFullYear(), currentMonth.getMonth() - 1, 1);
                    buildCalendar(next);
                });
                calNext?.addEventListener('click', () => {
                    const next = new Date(currentMonth.getFullYear(), currentMonth.getMonth() + 1, 1);
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
                highlightPaymentCards();
                showPaymentInstructions();
                refreshTimeChipSelection();
                validateForm();
            })();
        })();
    </script>

</body>

</html>