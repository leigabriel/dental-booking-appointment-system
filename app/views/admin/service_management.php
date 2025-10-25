<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');
$LAVA = lava_instance();
$username = $LAVA->session->userdata('username');
$current_role = $LAVA->session->userdata('role');

// Data from Management::services()
$services = $services ?? [];
$services_list_json = $services_list_json ?? '[]';
$errors = $errors ?? [];
$post_data = $post_data ?? [];

$flash_message = $LAVA->session->flashdata('success_message') ?? $LAVA->session->flashdata('error_message');

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
    <title>Manage Services</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
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
    </style>
</head>

<body class="bg-gray-100">

    <div class="flex min-h-screen">

        <aside class="w-20 bg-blue-900 text-gray-300 p-3 flex flex-col items-center justify-between shadow-2xl sticky top-0 h-screen z-20">
            <div>
                <a href="<?= site_url('admin/dashboard') ?>" title="Dentalcare Home" class="flex items-center justify-center h-12 w-12 mb-8 rounded-full bg-blue-500 text-white shadow-md">
                    <img src="https://cdn-icons-png.flaticon.com/128/3914/3914549.png" alt="Dentalcare Logo" class="w-6 h-6 invert">
                </a>

                <nav class="space-y-4">
                    <a href="<?= site_url('admin/dashboard') ?>" title="Dashboard"
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
        </aside>

        <div class="flex-1 flex flex-col lg:flex-row">

            <main class="flex-1 p-6 sm:p-10 overflow-y-auto h-screen">
                <header class="mb-10">
                    <h1 class="text-8xl font-extrabold text-gray-900">Manage Services</h1>
                    <p class="text-lg text-gray-600 mt-1">Add, edit, and delete clinic services.</p>
                </header>

                <?php if ($flash_message): ?>
                    <div class="p-4 mb-6 rounded-lg <?= $LAVA->session->flashdata('success_message') ? 'bg-green-100 text-green-700 border-green-300' : 'bg-red-100 text-red-700 border-red-300' ?> border shadow-sm" role="alert">
                        <strong class="font-bold"><?= $LAVA->session->flashdata('success_message') ? 'Success!' : 'Error!' ?></strong>
                        <span><?= html_escape($flash_message) ?></span>
                    </div>
                <?php endif; ?>
                <?php display_validation_errors($errors); ?>


                <section class="bg-white p-6 sm:p-8 rounded-xl shadow-lg border border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">Service List</h2>
                        <button type="button" onclick="openModal('add')"
                            class="px-5 py-2 bg-[--primary-color] text-white text-sm font-semibold rounded-lg shadow-md hover:bg-[--primary-hover] transition-colors">
                            <i class="fas fa-plus mr-1"></i> Add New Service
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Service Name</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price (PHP)</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration (Mins)</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (!empty($services)): ?>
                                    <?php foreach ($services as $service): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-4 text-sm font-medium text-gray-900"><?= html_escape($service['id']) ?></td>
                                            <td class="px-3 py-4 text-sm text-gray-600"><?= html_escape($service['name']) ?></td>
                                            <td class="px-3 py-4 text-sm text-gray-600"><?= html_escape(number_format($service['price'], 2)) ?></td>
                                            <td class="px-3 py-4 text-sm text-gray-600"><?= html_escape($service['duration_mins']) ?></td>
                                            <td class="px-3 py-4 text-sm space-x-2 whitespace-nowrap">
                                                <button
                                                    class="text-blue-600 hover:text-blue-800 font-medium"
                                                    onclick="openModal('edit', <?= html_escape($service['id']) ?>)">
                                                    Edit
                                                </button>
                                                <button type="button"
                                                    onclick="openDeleteModal('<?= site_url('management/service_delete/' . $service['id']) ?>', '<?= html_escape($service['name']) ?>')"
                                                    class="text-red-600 hover:text-red-800 font-medium ml-2">
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="px-3 py-4 text-center text-gray-500">No services found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>

            <aside class="w-full lg:w-80 bg-white p-6 shadow-2xl border-l border-gray-200 overflow-y-auto h-screen sticky top-0">
                <div class="flex flex-col items-center text-center">
                    <div class="w-24 h-24 rounded-full bg-[--sidebar-bg] text-white flex items-center justify-center mb-4 ring-4 ring-offset-2 ring-[--primary-color]">
                        <i class="fas fa-user-shield text-5xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900"><?= html_escape($username) ?></h2>
                    <p class="text-sm font-semibold text-[--primary-color] uppercase"><?= html_escape(ucfirst($current_role)) ?></p>
                </div>
            </aside>
        </div>
    </div>

    <div id="service-modal" class="modal fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4" onclick="closeModal(event)">
        <div class="bg-white w-full max-w-lg p-6 rounded-xl shadow-2xl" onclick="event.stopPropagation()">
            <h2 id="modal-title" class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2"></h2>
            <form id="service-form" method="POST" action="">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="form-id">
                <div class="space-y-4">
                    <div>
                        <label for="form-name" class="block text-sm font-medium text-gray-700">Service Name</label>
                        <input type="text" id="form-name" name="name" required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[--primary-color] focus:border-[--primary-color]">
                    </div>
                    <div>
                        <label for="form-price" class="block text-sm font-medium text-gray-700">Price (PHP)</label>
                        <input type="number" step="0.01" id="form-price" name="price" required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[--primary-color] focus:border-[--primary-color]">
                    </div>
                    <div>
                        <label for="form-duration" class="block text-sm font-medium text-gray-700">Duration (in minutes)</label>
                        <input type="number" id="form-duration" name="duration_mins" required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[--primary-color] focus:border-[--primary-color]">
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition">Cancel</button>
                    <button type="submit" id="form-submit-button" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Save Service</button>
                </div>
            </form>
        </div>
    </div>

    <div id="delete-confirmation-modal"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4"
        onclick="closeDeleteModal(event)">

        <div class="bg-white w-full max-w-sm p-6 rounded-lg shadow-xl"
            onclick="event.stopPropagation()">

            <div class="text-center mb-4">
                <span class="inline-block p-6 rounded-full bg-red-100 text-red-600">
                    <i class="fas fa-exclamation-triangle text-2xl"></i>
                </span>
            </div>

            <h3 class="text-lg font-semibold text-center text-gray-800 mb-2">Confirm Deletion</h3>

            <p id="delete-modal-message" class="text-sm text-gray-600 text-center mb-6">
                Are you sure you want to delete this item? This action cannot be undone.
            </p>

            <div class="flex justify-center gap-4">
                <button type="button"
                    onclick="closeDeleteModal()"
                    class="px-5 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 font-medium text-sm">
                    Cancel
                </button>
                <a id="confirm-delete-button"
                    href="#"
                    class="px-5 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 font-medium text-sm shadow-sm">
                    Delete
                </a>
            </div>
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

        const servicesData = <?= $services_list_json ?>;
        const addUrl = "<?= site_url('management/service_add_update') ?>";
        const updateUrlBase = "<?= site_url('management/service_add_update') ?>/";

        function openModal(mode, serviceId = null) {
            form.reset();
            formId.value = '';
            document.querySelectorAll('.p-3.mb-4.rounded-lg.bg-red-100').forEach(el => el.remove());

            if (mode === 'add') {
                modalTitle.textContent = "Add New Service";
                form.action = addUrl;
                formSubmitButton.textContent = "Add Service";
            } else if (mode === 'edit' && servicesData[serviceId]) {
                const service = servicesData[serviceId];
                modalTitle.textContent = `Edit Service: ${service.name}`;
                form.action = `${updateUrlBase}${service.id}`;
                formId.value = service.id;
                formName.value = service.name;
                formPrice.value = service.price;
                formDuration.value = service.duration_mins;
                formSubmitButton.textContent = "Save Changes";
            }
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }

        function closeModal(event = null) {
            if (!event || event.target.id === 'service-modal') {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        }

        <?php if (!empty($errors) && !empty($post_data)): ?>
            <?php if (isset($post_data['id']) && !empty($post_data['id'])): ?>
                openModal('edit', <?= html_escape($post_data['id']) ?>);
                // Repopulate form with failed edit data
                formName.value = '<?= html_escape($post_data['name'] ?? '') ?>';
                formPrice.value = '<?= html_escape($post_data['price'] ?? '') ?>';
                formDuration.value = '<?= html_escape($post_data['duration_mins'] ?? '') ?>';
            <?php else: ?>
                openModal('add');
                // Repopulate form with failed add data
                formName.value = '<?= html_escape($post_data['name'] ?? '') ?>';
                formPrice.value = '<?= html_escape($post_data['price'] ?? '') ?>';
                formDuration.value = '<?= html_escape($post_data['duration_mins'] ?? '') ?>';
            <?php endif; ?>
        <?php endif; ?>

        const deleteModal = document.getElementById('delete-confirmation-modal');
        const deleteModalMessage = document.getElementById('delete-modal-message');
        const confirmDeleteButton = document.getElementById('confirm-delete-button');
        // const deleteModalContent = deleteModal.querySelector('div[onclick*="stopPropagation"]'); // No longer needed for animation

        function openDeleteModal(deleteUrl, itemName) {
            // Update the message and button link
            deleteModalMessage.innerHTML = `Are you sure you want to delete "<strong>${itemName}</strong>"? <br>This action cannot be undone.`;
            confirmDeleteButton.href = deleteUrl;

            // Show the modal directly
            deleteModal.classList.remove('hidden');
            deleteModal.classList.add('flex');
            document.body.style.overflow = 'hidden'; // Prevent background scroll
        }

        function closeDeleteModal(event = null) {
            // Only close if clicking the overlay or Cancel button
            if (!event || event.target.id === 'delete-confirmation-modal' || event.target.closest('button[onclick*="closeDeleteModal"]')) {
                // Hide the modal directly
                deleteModal.classList.add('hidden');
                deleteModal.classList.remove('flex');
                document.body.style.overflow = ''; // Restore scroll
            }
        }
    </script>
</body>

</html>