<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');
$LAVA = lava_instance();
$doctors = $doctors ?? [];
$services = $services ?? [];
$errors = $errors ?? [];

$flash_message = $LAVA->session->flashdata('success_message') ?? $LAVA->session->flashdata('error_message');
$is_success = $LAVA->session->flashdata('success_message') ? true : false;
$is_logged_in = $LAVA->session->userdata('is_logged_in'); // Added for header
$username = $LAVA->session->userdata('username'); // Added for header

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
    </style>
</head>

<body class="bg-blue-950 text-gray-300 min-h-screen flex flex-col pt-20">

    <header class="absolute inset-x-0 top-0 z-50">
        <header class="absolute inset-x-0 top-0 z-50">
            <nav aria-label="Global" class="flex items-center justify-between p-6 lg:px-90 lg:py-10">
                <div class="flex lg:flex-1">
                    <a href="#" class="-m-1.5 p-1.5">
                        <span class="sr-only">DENTALCARE</span>
                        <img src="<?= base_url() ?>public/img/favicon-32x32.png" alt="DENTALCARE Logo" class="h-8 w-auto" />
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
                <div class="hidden lg:flex lg:flex-1 lg:justify-end gap-x-6">
                    <?php if (isset($is_logged_in) && $is_logged_in): ?>
                        <a href="<?= site_url('profile') ?>" class="group relative flex items-center gap-x-2 text-lg/6 uppercase font-semibold text-white hover:text-blue-400">
                            <img src="https://cdn-icons-png.flaticon.com/128/5393/5393061.png" alt="Profile" class="h-8 w-8 rounded-full object-cover invert">
                            <span class="absolute -bottom-8 left-1/2 -translate-x-1/2 whitespace-nowrap rounded bg-gray-700 px-2 py-1 text-xs text-white opacity-0 transition-opacity group-hover:opacity-100">
                                <?= html_escape($username) ?>
                            </span>
                        </a>
                        <a href="#" class="logout-confirm group relative rounded-full p-1 bg-red-700 flex border-2 border-white items-center text-sm/6 font-semibold text-white hover:text-red-300" data-logout-url="<?= site_url('logout') ?>">
                            <img src="https://cdn-icons-png.flaticon.com/128/10609/10609328.png" alt="Logout" class="h-6 w-6 filter invert">
                            <span class="absolute -bottom-8 left-1/2 -translate-x-1/2 whitespace-nowrap rounded bg-gray-700 px-2 py-1 text-xs text-white opacity-0 transition-opacity group-hover:opacity-100">
                                Log out
                            </span>
                        </a>
                    <?php else : ?>
                        <a href="<?= site_url('login') ?>" class="text-lg/6 font-semibold text-white hover:text-blue-400">Log in</a>
                        <span class="text-white">|</span>
                        <a href="<?= site_url('register') ?>" class="text-lg/6 font-semibold text-white hover:text-blue-400">Register</span></a>
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

        <main class="flex-grow mt-20">
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

                            <form method="POST" action="<?= site_url('book/submit') ?>" class="space-y-5 flex-grow flex flex-col justify-between">
                                <div>
                                    <?= csrf_field() ?>

                                    <div>
                                        <label for="service_id" class="block text-xs font-medium text-gray-300 mb-1">Service</label>
                                        <select id="service_id" name="service_id" required class="w-full px-3 py-2 text-sm bg-blue-900/50 border border-white/10 rounded-lg focus:ring-1 focus:ring-indigo-400 focus:border-indigo-400 transition text-white placeholder-gray-400">
                                            <option value="" class="bg-blue-800 text-gray-400">Select Service</option>
                                            <?php foreach ($services as $service): ?>
                                                <option value="<?= html_escape($service['id']) ?>" <?= (repopulate('service_id') == $service['id']) ? 'selected' : '' ?> class="bg-blue-800 text-white">
                                                    <?= html_escape($service['name']) ?> ($<?= number_format($service['price'], 2) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="doctor_id" class="block text-xs font-medium text-gray-300 mb-1">Doctor</label>
                                        <select id="doctor_id" name="doctor_id" required class="w-full px-3 py-2 text-sm bg-blue-900/50 border border-white/10 rounded-lg focus:ring-1 focus:ring-indigo-400 focus:border-indigo-400 transition text-white placeholder-gray-400">
                                            <option value="" class="bg-blue-800 text-gray-400">Select Doctor</option>
                                            <?php foreach ($doctors as $doctor): ?>
                                                <option value="<?= html_escape($doctor['id']) ?>" <?= (repopulate('doctor_id') == $doctor['id']) ? 'selected' : '' ?> class="bg-blue-800 text-white">
                                                    Dr. <?= html_escape($doctor['name']) ?> (<?= html_escape($doctor['specialty']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="appointment_date" class="block text-xs font-medium text-gray-300 mb-1">Date</label>
                                        <input type="date" id="appointment_date" name="appointment_date" required
                                            value="<?= repopulate('appointment_date', date('Y-m-d')) ?>"
                                            min="<?= date('Y-m-d') ?>"
                                            class="w-full px-3 py-2 text-sm bg-blue-900/50 border border-white/10 rounded-lg focus:ring-1 focus:ring-indigo-400 focus:border-indigo-400 transition text-white placeholder-gray-400 appearance-none">
                                    </div>

                                    <div>
                                        <label for="time_slot" class="block text-xs font-medium text-gray-300 mb-1">Time</label>
                                        <select id="time_slot" name="time_slot" required class="w-full px-3 py-2 text-sm bg-blue-900/50 border border-white/10 rounded-lg focus:ring-1 focus:ring-indigo-400 focus:border-indigo-400 transition text-white placeholder-gray-400">
                                            <option value="" class="bg-blue-800 text-gray-400">Select Time</option>
                                            <?php foreach ($time_slots as $slot): ?>
                                                <option value="<?= html_escape($slot) ?>" <?= (repopulate('time_slot') == $slot) ? 'selected' : '' ?> class="bg-blue-800 text-white">
                                                    <?= date('g:i A', strtotime($slot)) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <p class="text-xs text-gray-400 mt-1">Times based on availability.</p>
                                    </div>
                                </div>

                                <div class="mt-auto pt-4">
                                    <button type="submit" class="w-full bg-indigo-500 text-white py-2.5 rounded-lg font-semibold text-sm hover:bg-indigo-400 transition duration-200 shadow-md shadow-indigo-500/30">
                                        Confirm Appointment
                                    </button>
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
                                        <div class="bg-blue-900/40 rounded-lg p-3 border border-white/10 flex items-center space-x-3 transition hover:shadow-sm hover:bg-blue-900/60">
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
                                        <div class="p-3 rounded-lg border border-white/10 hover:shadow-sm transition bg-blue-900/40 hover:bg-blue-900/60">
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

        <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
        </body>

        <!-- Logout confirmation modal -->
        <div id="logoutModal" class="fixed inset-0 z-50 hidden items-center justify-center">
            <div class="fixed inset-0 bg-black/60" tabindex="-1"></div>
            <div class="bg-blue-950/95 backdrop-blur-md text-white rounded-lg p-6 z-50 max-w-md mx-auto shadow-lg">
                <h3 class="text-lg font-semibold">Confirm Logout</h3>
                <p class="mt-2 text-sm text-gray-200">Are you sure you want to log out?</p>
                <div class="mt-5 flex justify-end gap-3">
                    <button id="cancelLogout" class="px-4 py-2 rounded bg-gray-700 hover:bg-gray-600">Cancel</button>
                    <button id="confirmLogout" class="px-4 py-2 rounded bg-red-600 hover:bg-red-500 font-semibold">Log out</button>
                </div>
            </div>
        </div>

        <script>
            (function(){
                const modal = document.getElementById('logoutModal');
                const confirmBtn = document.getElementById('confirmLogout');
                const cancelBtn = document.getElementById('cancelLogout');
                let targetLogoutUrl = null;

                function showModal(url){
                    targetLogoutUrl = url || '<?= site_url('logout') ?>';
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }

                function hideModal(){
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    targetLogoutUrl = null;
                }

                document.querySelectorAll('.logout-confirm').forEach(el => {
                    el.addEventListener('click', function(e){
                        e.preventDefault();
                        const url = this.getAttribute('data-logout-url') || this.dataset.logoutUrl || '<?= site_url('logout') ?>';
                        showModal(url);
                    });
                });

                cancelBtn.addEventListener('click', function(){
                    hideModal();
                });

                confirmBtn.addEventListener('click', function(){
                    if (targetLogoutUrl) {
                        window.location.href = targetLogoutUrl;
                    } else {
                        window.location.href = '<?= site_url('logout') ?>';
                    }
                });

                // Close modal on Escape
                document.addEventListener('keydown', function(e){
                    if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                        hideModal();
                    }
                });
            })();
        </script>

        </html>
</body>

</html>