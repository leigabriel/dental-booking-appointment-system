<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');
$LAVA = lava_instance();
$doctors = $doctors ?? [];
$services = $services ?? [];
$errors = $errors ?? [];

// Helper function to display session flash messages
$flash_message = $LAVA->session->flashdata('success_message') ?? $LAVA->session->flashdata('error_message');
$is_success = $LAVA->session->flashdata('success_message') ? true : false;

// Helper to display validation errors
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

// Helper to repopulate form fields (accesses global scope data passed from controller)
function repopulate($key, $default = '')
{
    global $data;
    if (!isset($data) || !is_array($data)) {
        $data = [];
    }
    return html_escape($data[$key] ?? $default);
}

// List of available times (simple example)
$time_slots = ['09:00:00', '10:00:00', '11:00:00', '13:00:00', '14:00:00', '15:00:00'];
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
            --primary-color: #0EA5E9;
            /* Tailwind Sky-500 */
            --primary-hover: #0284C7;
            --accent-light: #38BDF8;
            --neutral-dark: #1F2937;
        }

        body {
            font-family: 'JetBrains Mono', monospace;
        }

        /* Style for the two-column scheduling form */
        .schedule-grid {
            grid-template-columns: 2fr 3fr;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen p-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-lg border border-gray-200 mb-8">
            <h1 class="text-3xl font-extrabold text-[--primary-color]">
                Schedule Your Visit
            </h1>
            <a href="<?= site_url('/') ?>" class="text-sm text-gray-600 hover:text-[--primary-color] font-medium">
                ← Back to DENTALCARE Home
            </a>
        </div>

        <?php if ($flash_message): ?>
            <div class="p-4 mb-6 rounded-lg <?= $is_success ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?> border <?= $is_success ? 'border-green-300' : 'border-red-300' ?>">
                <?= html_escape($flash_message) ?>
            </div>
        <?php endif; ?>

        <?php display_validation_errors($errors); ?>

        <div class="grid grid-cols-1 lg:schedule-grid gap-10 bg-white p-8 rounded-xl shadow-lg border border-gray-200">

            <div class="space-y-6">
                <h2 class="text-2xl font-bold text-gray-800 border-b pb-2">Available Services</h2>
                <p class="text-gray-600 text-sm">Please review the services and their duration before scheduling.</p>

                <?php if (!empty($services)): ?>
                    <?php foreach ($services as $service): ?>
                        <div class="p-4 rounded-lg border border-gray-200 hover:shadow-md transition">
                            <h3 class="text-xl font-semibold text-[--primary-color]"><?= html_escape($service['name']) ?></h3>
                            <p class="text-sm text-gray-700">Duration: <?= html_escape($service['duration_mins']) ?> mins</p>
                            <p class="text-md font-bold text-gray-800">Price: $<?= number_format($service['price'], 2) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-500">No services are currently available for booking.</p>
                <?php endif; ?>
            </div>

            <div class="bg-gray-50 p-6 rounded-lg shadow-inner">
                <h2 class="text-2xl font-bold text-gray-800 border-b pb-2 mb-4">Book Your Slot</h2>

                <form method="POST" action="<?= site_url('book/submit') ?>" class="space-y-4">
                    <?= csrf_field() ?>

                    <div>
                        <label for="service_id" class="block text-sm font-medium text-gray-700 mb-1">1. Choose Service</label>
                        <select id="service_id" name="service_id" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[--primary-color] focus:border-transparent transition">
                            <option value="">-- Select a Service --</option>
                            <?php foreach ($services as $service): ?>
                                <option value="<?= html_escape($service['id']) ?>"
                                    <?= (isset($service_id) && repopulate('service_id') == $service['id']) ? 'selected' : '' ?>>
                                    <?= html_escape($service['name']) ?> ($<?= number_format($service['price'], 2) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="doctor_id" class="block text-sm font-medium text-gray-700 mb-1">2. Choose Doctor</label>
                        <select id="doctor_id" name="doctor_id" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[--primary-color] focus:border-transparent transition">
                            <option value="">-- Select a Doctor --</option>
                            <?php foreach ($doctors as $doctor): ?>
                                <option value="<?= html_escape($doctor['id']) ?>"
                                    <?= (isset($doctor_id) && repopulate('doctor_id') == $doctor['id']) ? 'selected' : '' ?>>
                                    Dr. <?= html_escape($doctor['name']) ?> (<?= html_escape($doctor['specialty']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="appointment_date" class="block text-sm font-medium text-gray-700 mb-1">3. Select Date</label>
                        <input type="date" id="appointment_date" name="appointment_date" required
                            value="<?= repopulate('appointment_date') ?>"
                            min="<?= date('Y-m-d') ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[--primary-color] focus:border-transparent transition">
                    </div>

                    <div>
                        <label for="time_slot" class="block text-sm font-medium text-gray-700 mb-1">4. Select Time</label>
                        <select id="time_slot" name="time_slot" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[--primary-color] focus:border-transparent transition">
                            <option value="">-- Select Time Slot --</option>
                            <?php foreach ($time_slots as $slot): ?>
                                <option value="<?= html_escape($slot) ?>"
                                    <?= (isset($time_slot) && repopulate('time_slot') == $slot) ? 'selected' : '' ?>>
                                    <?= date('g:i A', strtotime($slot)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Times are based on doctor availability.</p>
                    </div>

                    <button type="submit"
                        class="w-full bg-[--primary-color] text-white py-2.5 rounded-lg font-semibold hover:bg-[--primary-hover] transition duration-200 shadow-md shadow-sky-300/50 mt-6">
                        Confirm Appointment
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>