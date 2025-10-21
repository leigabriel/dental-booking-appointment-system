<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');
$LAVA = lava_instance();
$role = $LAVA->session->userdata('role');

// Data variables
$doctors = $doctors ?? [];
// doctors_list_json is set in Management::doctors() for JS lookup
$doctors_list_json = $doctors_list_json ?? '[]';

$errors = $errors ?? [];
$post_data = $post_data ?? [];
$flash_message = $LAVA->session->flashdata('success_message') ?? $LAVA->session->flashdata('error_message');

function display_errors($errors)
{
    if (!empty($errors)) {
        echo '<div class="p-3 mb-4 rounded-lg bg-red-100 text-red-700 border border-red-300">';
        echo '<ul>';
        foreach ($errors as $error) {
            echo '<li>' . html_escape($error) . '</li>';
        }
        echo '</ul></div>';
    }
}

// Check if a validation failure occurred for the ADD form (ID is null)
$show_add_errors = !empty($errors) && empty($post_data['id']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Doctors</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary-color: #0EA5E9;
            --primary-hover: #0284C7;
        }

        body {
            font-family: 'JetBrains Mono', monospace;
        }

        .modal {
            transition: opacity 0.25s ease;
        }
    </style>
</head>

<body class="bg-gray-50 p-6 sm:p-10 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow-lg border border-gray-200 mb-8">
            <h1 class="text-3xl font-extrabold text-[--primary-color]">
                Doctor Management
            </h1>
            <a href="<?= site_url('admin/dashboard') ?>" class="text-sm text-gray-600 hover:text-[--primary-color] font-medium">
                ← Back to Dashboard
            </a>
        </div>

        <?php if ($flash_message): ?>
            <div class="p-4 mb-4 rounded-lg <?= $LAVA->session->flashdata('success_message') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                <?= html_escape($flash_message) ?>
            </div>
        <?php endif; ?>

        <div class="mb-6">
            <button type="button" onclick="openModal('add')" class="bg-green-500 text-white py-2.5 px-6 rounded-lg font-semibold hover:bg-green-600 transition flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <span>Add New Doctor</span>
            </button>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Current Doctors</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Specialty</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (!empty($doctors)): ?>
                            <?php foreach ($doctors as $d): ?>
                                <tr>
                                    <td class="px-3 py-4 text-sm font-medium text-gray-900"><?= html_escape($d['id']) ?></td>
                                    <td class="px-3 py-4 text-sm text-gray-600"><?= html_escape($d['name']) ?></td>
                                    <td class="px-3 py-4 text-sm text-gray-600"><?= html_escape($d['specialty']) ?></td>
                                    <td class="px-3 py-4 text-sm text-gray-600"><?= html_escape($d['email']) ?></td>
                                    <td class="px-3 py-4 text-sm space-x-3">
                                        <button
                                            class="text-blue-600 hover:text-blue-800"
                                            onclick="openModal('edit', <?= html_escape(json_encode($d)) ?>)">
                                            Edit
                                        </button>
                                        <?php if ($role === 'admin'): ?>
                                            <a href="<?= site_url('management/doctor_delete/' . $d['id']) ?>" onclick="return confirm('Are you sure you want to delete Dr. <?= html_escape($d['name']) ?>?')" class="text-red-600 hover:text-red-800">Delete</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-3 py-4 text-center text-gray-500">No doctors registered.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="doctor-modal" class="modal fixed inset-0 bg-gray-900 bg-opacity-75 hidden items-center justify-center z-50 p-4" onclick="closeModal(event)">
        <div class="bg-white w-full max-w-lg p-6 rounded-xl shadow-2xl" onclick="event.stopPropagation()">

            <h2 id="modal-title" class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2"></h2>

            <?php
            // If validation failed on a POST request, re-show errors inside the modal structure
            if (!empty($errors)):
                // Determine if this was an ADD or EDIT failure to set the modal title correctly
                $is_edit_fail = !empty($post_data['id']);
                $failed_title = $is_edit_fail ? "Edit Doctor" : "Add New Doctor";
            ?>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Automatically open the modal if validation errors are present
                        openModal('<?= $is_edit_fail ? 'edit' : 'add' ?>', <?= json_encode($post_data) ?>);
                    });
                </script>
                <h3 class="text-lg font-semibold text-red-600">Validation Failed:</h3>
                <?php display_errors($errors); ?>
            <?php endif; ?>

            <form id="doctor-form" method="POST" action="<?= site_url('management/doctor_add_update') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="form-id" value="<?= html_escape($post_data['id'] ?? '') ?>">

                <div class="space-y-4">
                    <div>
                        <label for="form-name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" id="form-name" name="name" required
                            value="<?= html_escape($post_data['name'] ?? '') ?>"
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[--primary-color] focus:border-[--primary-color]">
                    </div>

                    <div>
                        <label for="form-specialty" class="block text-sm font-medium text-gray-700">Specialty</label>
                        <input type="text" id="form-specialty" name="specialty" required
                            value="<?= html_escape($post_data['specialty'] ?? '') ?>"
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[--primary-color] focus:border-[--primary-color]">
                    </div>

                    <div>
                        <label for="form-email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="form-email" name="email" required
                            value="<?= html_escape($post_data['email'] ?? '') ?>"
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[--primary-color] focus:border-[--primary-color]">
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition">Cancel</button>
                    <button type="submit" id="form-submit-button" class="px-4 py-2 bg-[--primary-color] text-white rounded-lg hover:bg-[--primary-hover] transition">Save Doctor</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        const modal = document.getElementById('doctor-modal');
        const form = document.getElementById('doctor-form');
        const modalTitle = document.getElementById('modal-title');
        const formId = document.getElementById('form-id');
        const formName = document.getElementById('form-name');
        const formSpecialty = document.getElementById('form-specialty');
        const formEmail = document.getElementById('form-email');
        const formSubmitButton = document.getElementById('form-submit-button');

        // Parse doctor data passed from PHP for quick lookup
        const doctorsData = <?= $doctors_list_json ?>;

        function openModal(mode, doctor = {}) {
            // Reset form fields
            form.reset();

            // Clear any old, visible validation errors outside the form for a fresh start
            document.querySelectorAll('#doctor-modal .p-3.mb-4.rounded-lg.bg-red-100').forEach(el => el.remove());

            if (mode === 'add') {
                modalTitle.textContent = "Add New Doctor";
                form.action = "<?= site_url('management/doctor_add_update') ?>";
                formId.value = '';
                formSubmitButton.textContent = "Add Doctor";

            } else if (mode === 'edit') {
                const data = typeof doctor === 'object' && doctor.id ? doctor : doctorsData[doctor.id];

                if (!data) {
                    alert('Error: Doctor data not found.');
                    return;
                }

                modalTitle.textContent = `Edit Doctor: Dr. ${data.name}`;
                form.action = `<?= site_url('management/doctor_add_update') ?>/${data.id}`;
                formId.value = data.id;
                formName.value = data.name;
                formSpecialty.value = data.specialty;
                formEmail.value = data.email;
                formSubmitButton.textContent = "Save Changes";
            }

            // Show modal
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');

            // Move existing error block if present (for failed POST attempts)
            const errorBlock = document.querySelector('.bg-red-100.text-red-700');
            if (errorBlock && !modal.contains(errorBlock)) {
                form.prepend(errorBlock);
            }
        }

        function closeModal(event = null) {
            // Only close if click is on the background or called directly
            if (!event || event.target.id === 'doctor-modal') {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        }

        // Handle case where validation fails on a POST request and form needs to be re-opened
        <?php if (!empty($errors)): ?>
            document.addEventListener('DOMContentLoaded', function() {
                const initialData = <?= json_encode($post_data) ?>;
                const mode = initialData.id ? 'edit' : 'add';
                // Find and move the error block into the modal on page load
                const errorBlock = document.querySelector('.bg-red-100.text-red-700');
                if (errorBlock) {
                    errorBlock.remove(); // Temporarily remove from original position
                    form.prepend(errorBlock); // Prepend to the form inside the modal
                }
                openModal(mode, initialData);
            });
        <?php endif; ?>
    </script>
</body>

</html>