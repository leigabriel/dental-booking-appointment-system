<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');
$LAVA = lava_instance();
// Ensure required variables are initialized
$doctors = $doctors ?? []; // Assuming this array is passed from the controller
$services = $services ?? [];
$errors = $errors ?? [];

// Helper function to display session flash messages
$flash_message = $LAVA->session->flashdata('success_message') ?? $LAVA->session->flashdata('error_message');
$is_success = $LAVA->session->flashdata('success_message') ? true : false;

// Helper to display validation errors (LavaLust style)
function display_validation_errors($errors)
{
    if (!empty($errors)) {
        echo '<div class="p-3 mb-4 rounded-lg bg-red-100 text-red-700 border border-red-300 font-semibold">';
        echo 'Please fix the following issues:';
        echo '<ul class="list-disc pl-5 mt-1 m-0 text-sm">';
        foreach ($errors as $error) {
            echo '<li>' . html_escape($error) . '</li>';
        }
        echo '</ul></div>';
    }
}

// Helper to repopulate form fields
function repopulate($key, $default = '')
{
    global $data; // Use the global $data array potentially passed to the view
    if (!isset($data) || !is_array($data)) {
        $data = []; // Initialize if not set
    }
    // Check if the key exists in POST data (from form submission) first, then in $data
    $value = $_POST[$key] ?? ($data[$key] ?? $default);
    return html_escape($value);
}


// List of available times
$time_slots = ['08:00:00', '09:00:00', '10:00:00', '11:00:00', '12:00:00', '13:00:00', '14:00:00', '15:00:00', '16:00:00', '17:00:00'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Appointment</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-hover: #1d4ed8;
            --accent-light: #2e3be7ff;
            --neutral-dark: #1e3a8a;
        }

        body {
            font-family: 'JetBrains Mono', monospace;
        }

        .two-tier-nav {
            position: sticky;
            top: 0;
            z-index: 50;
            /* Ensure nav stays on top */
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen">

    <div class="two-tier-nav shadow-lg">
        <div class="bg-blue-800">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex justify-between items-center h-14">
                    <a href="<?= site_url('/') ?>" class="text-xl font-extrabold flex items-center space-x-2">
                        <span class="text-3xl font-extrabold text-white">DENTALCARE</span>
                    </a>

                    <div class="flex items-center space-x-4">
                        <?php if ($LAVA->session->userdata('is_logged_in')): ?>
                            <span class="text-sm font-medium text-white hidden sm:inline">Hello, <?= html_escape($LAVA->session->userdata('username')) ?></span>

                            <?php if ($LAVA->session->userdata('role') === 'user'): ?>
                                <a href="<?= site_url('profile') ?>" class="px-3 py-1.5 text-sm font-semibold rounded-lg bg-gray-100 text-[--neutral-dark] hover:bg-gray-200 transition">
                                    My Profile
                                </a>
                            <?php endif; ?>

                            <a href="<?= site_url('logout') ?>" class="text-sm text-red-400 hover:text-red-300 font-medium">Logout</a>
                        <?php else: ?>
                            <a href="<?= site_url('login') ?>" class="px-3 py-1.5 text-sm font-semibold rounded-lg bg-[--primary-color] text-white hover:bg-[--primary-hover] transition shadow-md">
                                Login
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <nav class="bg-blue-500">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex justify-end items-center h-10 space-x-6">
                    <a href="/#" class="text-white hover:text-blue-300 px-3 py-1 rounded-md text-sm font-medium transition hover:underline">Home</a>
                    <a href="/#about" class="text-white hover:text-blue-300 px-3 py-1 rounded-md text-sm font-medium transition hover:underline">About Us</a>
                    <a href="/#services" class="text-white hover:text-blue-300 px-3 py-1 rounded-md text-sm font-medium transition hover:underline">Services</a>
                    <a href="/#contacts" class="text-white hover:text-blue-300 px-3 py-1 rounded-md text-sm font-medium transition hover:underline">Contact</a>
                    <a href="<?= site_url('book') ?>" class="text-white hover:text-blue-300 px-3 py-1 rounded-md text-sm font-medium transition hover:underline">Book</a>
                </div>
            </div>
        </nav>
    </div>

    <div class=" mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-lg border border-gray-200 mb-8">
            <h1 class="text-3xl font-extrabold text-[--primary-color]">
                Schedule Your Visit
            </h1>
            <a href="<?= site_url('/profile') ?>" class="text-sm text-gray-600 hover:text-[--primary-color] hover:underline font-medium">
                View Appointment
            </a>
        </div>

        <?php if ($flash_message): ?>
            <div class="p-4 mb-6 rounded-lg <?= $is_success ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?> border <?= $is_success ? 'border-green-300' : 'border-red-300' ?>">
                <?= html_escape($flash_message) ?>
            </div>
        <?php endif; ?>

        <?php display_validation_errors($errors); ?>

        <div class="grid grid-cols-5 grid-rows-5 gap-4">

            <div class="col-span-3 row-span-3 col-start-3 row-start-1 bg-white p-6 rounded-xl shadow-lg border border-gray-200">
                <div class="bg-gray-100 p-6 rounded-lg shadow-inner h-full flex flex-col">
                    <h2 class="text-2xl font-bold text-gray-800 border-b pb-2 mb-4">Book Your Slot</h2>

                    <form method="POST" action="<?= site_url('book/submit') ?>" class="space-y-4 flex-grow flex flex-col justify-between">
                        <div>
                            <?= csrf_field() ?>

                            <div>
                                <label for="service_id" class="block text-xs font-medium text-gray-700 mb-1">Service</label>
                                <select id="service_id" name="service_id" required
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-1 focus:ring-[--primary-color] focus:border-[--primary-color] transition">
                                    <option value="">Select Service</option>
                                    <?php foreach ($services as $service): ?>
                                        <option value="<?= html_escape($service['id']) ?>" <?= (repopulate('service_id') == $service['id']) ? 'selected' : '' ?>>
                                            <?= html_escape($service['name']) ?> ($<?= number_format($service['price'], 2) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label for="doctor_id" class="block text-xs font-medium text-gray-700 mb-1">Doctor</label>
                                <select id="doctor_id" name="doctor_id" required
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-1 focus:ring-[--primary-color] focus:border-[--primary-color] transition">
                                    <option value="">Select Doctor</option>
                                    <?php foreach ($doctors as $doctor): ?>
                                        <option value="<?= html_escape($doctor['id']) ?>" <?= (repopulate('doctor_id') == $doctor['id']) ? 'selected' : '' ?>>
                                            Dr. <?= html_escape($doctor['name']) ?> (<?= html_escape($doctor['specialty']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label for="appointment_date" class="block text-xs font-medium text-gray-700 mb-1">Date</label>
                                <input type="date" id="appointment_date" name="appointment_date" required
                                    value="<?= repopulate('appointment_date') ?>"
                                    min="<?= date('Y-m-d') ?>"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-1 focus:ring-[--primary-color] focus:border-[--primary-color] transition">
                            </div>

                            <div>
                                <label for="time_slot" class="block text-xs font-medium text-gray-700 mb-1">Time</label>
                                <select id="time_slot" name="time_slot" required
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-1 focus:ring-[--primary-color] focus:border-[--primary-color] transition">
                                    <option value="">Select Time</option>
                                    <?php foreach ($time_slots as $slot): ?>
                                        <option value="<?= html_escape($slot) ?>" <?= (repopulate('time_slot') == $slot) ? 'selected' : '' ?>>
                                            <?= date('g:i A', strtotime($slot)) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Times based on availability.</p>
                            </div>
                        </div>

                        <div class="mt-auto pt-4">
                            <button type="submit"
                                class="w-full bg-[--primary-color] text-white py-2 rounded-lg font-semibold text-sm hover:bg-[--primary-hover] transition duration-200 shadow-md shadow-sky-300/50">
                                Confirm Appointment
                            </button>
                        </div>

                    </form>
                </div>
            </div>

            <div class="row-span-3 col-start-1 row-start-1 bg-white p-6 rounded-xl shadow-lg border border-gray-200 space-y-4">
                <h2 class="text-2xl font-bold text-gray-800 border-b pb-2">Available Dentists</h2>
                <p class="text-gray-600 text-xs">Choose your preferred dental professional.</p>
                <div class="max-h-[400px] overflow-y-auto space-y-3 pr-2">
                    <?php if (!empty($doctors)): ?>
                        <?php foreach ($doctors as $doctor): ?>
                            <div class="bg-gray-50 rounded-lg p-3 border border-gray-100 flex items-center space-x-3 transition hover:shadow-sm">
                                <div>
                                    <h3 class="text-sm font-bold text-gray-800">Dr. <?= html_escape($doctor['name']) ?></h3>
                                    <p class="text-teal-600 font-semibold text-xs"><?= html_escape($doctor['specialty']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-gray-500 text-sm">No dentists currently available.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row-span-3 col-start-2 row-start-1 bg-white p-6 rounded-xl shadow-lg border border-gray-200 space-y-4">
                <h2 class="text-2xl font-bold text-gray-800 border-b pb-2">Available Services</h2>
                <p class="text-gray-600 text-xs">Review services and duration.</p>
                <div class="max-h-[400px] overflow-y-auto space-y-3 pr-2">
                    <?php if (!empty($services)): ?>
                        <?php foreach ($services as $service): ?>
                            <div class="p-3 rounded-lg border border-gray-100 hover:shadow-sm transition bg-gray-50">
                                <h3 class="text-sm font-semibold text-[--primary-color]"><?= html_escape($service['name']) ?></h3>
                                <p class="text-xs text-gray-700">Duration: <?= html_escape($service['duration_mins']) ?> mins</p>
                                <p class="text-xs font-bold text-gray-800">Price: $<?= number_format($service['price'], 2) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-gray-500 text-sm">No services are currently available.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-span-5 row-span-2 row-start-4 bg-white p-6 rounded-xl shadow-lg border border-gray-200 space-y-2">
                <h2 class="text-2xl font-bold text-gray-800 border-b pb-2">Dental Care Information</h2>
                <p class="text-gray-600 text-xl leading-relaxed">
                    At <span class="font-bold text-[--primary-color]">DentalCare</span>, we focus on providing high-quality dental services
                    tailored to your needs. From preventive care to advanced treatments, our experienced team ensures
                    your comfort and confidence every step of the way.
                </p>
            </div>

        </div>
    </div>
    </div>
</body>

</html>