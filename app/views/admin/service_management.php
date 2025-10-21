<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');
$LAVA = lava_instance();
// Retrieve the current user's role
$role = $LAVA->session->userdata('role');

$services = $services ?? [];
// services_list_json is set in Management::services() for JS lookup
$services_list_json = $services_list_json ?? '[]';

// Repopulate logic for failed form submission (used inside the modal for re-opening)
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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Services</title>
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
                Service Management
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
                <span>Add New Service</span>
            </button>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Current Services</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration (Mins)</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (!empty($services)): ?>
                            <?php foreach ($services as $s): ?>
                                <tr>
                                    <td class="px-3 py-4 text-sm font-medium text-gray-900"><?= html_escape($s['id']) ?></td>
                                    <td class="px-3 py-4 text-sm text-gray-600"><?= html_escape($s['name']) ?></td>
                                    <td class="px-3 py-4 text-sm text-gray-600">$<?= html_escape(number_format($s['price'], 2)) ?></td>
                                    <td class="px-3 py-4 text-sm text-gray-600"><?= html_escape($s['duration_mins']) ?></td>
                                    <td class="px-3 py-4 text-sm space-x-3">
                                        <button
                                            class="text-blue-600 hover:text-blue-800"
                                            onclick="openModal('edit', <?= html_escape(json_encode($s)) ?>)">
                                            Edit
                                        </button>
                                        <?php if ($role === 'admin'): ?>
                                            <a href="<?= site_url('management/service_delete/' . $s['id']) ?>" onclick="return confirm('Are you sure you want to delete service: <?= html_escape($s['name']) ?>?')" class="text-red-600 hover:text-red-800">Delete</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-3 py-4 text-center text-gray-500">No services defined.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="service-modal" class="modal fixed inset-0 bg-gray-900 bg-opacity-75 hidden items-center justify-center z-50 p-4" onclick="closeModal(event)">
        <div class="bg-white w-full max-w-lg p-6 rounded-xl shadow-2xl" onclick="event.stopPropagation()">

            <h2 id="modal-title" class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2"></h2>

            <?php
            if (!empty($errors)):
                // Display validation errors if form submission failed
                echo '<h3 class="text-lg font-semibold text-red-600">Validation Failed:</h3>';
                display_errors($errors);
            endif; ?>

            <form id="service-form" method="POST" action="<?= site_url('management/service_add_update') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="form-id" value="<?= html_escape($post_data['id'] ?? '') ?>">

                <div class="space-y-4">
                    <div>
                        <label for="form-name" class="block text-sm font-medium text-gray-700">Service Name</label>
                        <input type="text" id="form-name" name="name" required
                            value="<?= html_escape($post_data['name'] ?? '') ?>"
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[--primary-color] focus:border-[--primary-color]">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="form-price" class="block text-sm font-medium text-gray-700">Price (USD)</label>
                            <input type="number" step="0.01" id="form-price" name="price" required
                                value="<?= html_escape($post_data['price'] ?? '') ?>"
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[--primary-color] focus:border-[--primary-color]">
                        </div>
                        <div>
                            <label for="form-duration" class="block text-sm font-medium text-gray-700">Duration (Minutes)</label>
                            <input type="number" id="form-duration" name="duration_mins" required
                                value="<?= html_escape($post_data['duration_mins'] ?? '') ?>"
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[--primary-color] focus:border-[--primary-color]">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition">Cancel</button>
                    <button type="submit" id="form-submit-button" class="px-4 py-2 bg-[--primary-color] text-white rounded-lg hover:bg-[--primary-hover] transition">Save Service</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        const modal = document.getElementById('service-modal');
        const form = document.getElementById('service-form');
        const modalTitle = document.getElementById('modal-title');
        const formId = document.getElementById('form-id');
        const formName = document.getElementById('form-name');
        const formPrice = document.getElementById('form-price');
        const formDuration = document.getElementById('form-duration');
        const formSubmitButton = document.getElementById('form-submit-button');

        // Parse service data passed from PHP for quick lookup
        const servicesData = <?= $services_list_json ?>;

        function openModal(mode, service = {}) {
            // Reset form fields and validation artifacts
            form.reset();

            // Move any existing validation error block back into the modal
            const errorBlock = document.querySelector('.bg-red-100.text-red-700');
            if (errorBlock && !modal.contains(errorBlock)) {
                form.prepend(errorBlock);
            }

            if (mode === 'add') {
                modalTitle.textContent = "Add New Service";
                form.action = "<?= site_url('management/service_add_update') ?>";
                formId.value = '';
                formSubmitButton.textContent = "Add Service";

                // Clear any leftover data in modal fields from a failed POST if modal opened manually
                formName.value = '';
                formPrice.value = '';
                formDuration.value = '';

            } else if (mode === 'edit') {
                const data = typeof service === 'object' && service.id ? service : servicesData[service.id];

                if (!data) {
                    // This handles cases where data is missing but the modal is asked to open
                    // If modal is opened manually, use the lookup; if by POST failure, use the passed object.
                    alert('Error: Service data not found.');
                    return;
                }

                modalTitle.textContent = `Edit Service: ${data.name}`;
                form.action = `<?= site_url('management/service_add_update') ?>/${data.id}`;
                formId.value = data.id;

                // Populate fields with current or old POST data
                formName.value = data.name;
                // Use parseFloat and toFixed(2) to handle price formatting if necessary, otherwise use the raw value
                formPrice.value = parseFloat(data.price).toFixed(2);
                formDuration.value = data.duration_mins;
                formSubmitButton.textContent = "Save Changes";
            }

            // Show modal
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }

        function closeModal(event = null) {
            // Only close if click is on the background or called directly
            if (!event || event.target.id === 'service-modal') {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        }
    </script>
</body>

</html>