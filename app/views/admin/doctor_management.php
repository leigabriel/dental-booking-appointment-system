<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');
$LAVA = lava_instance();
$username = $LAVA->session->userdata('username');
$current_role = $LAVA->session->userdata('role');

// Data from Management::doctors()
$doctors = $doctors ?? [];
$doctors_list_json = $doctors_list_json ?? '[]';
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
    <title>Manage Doctors</title>
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

                    <a href="<?= site_url('admin/calendar') ?>" title="Appointments"
                        class="flex items-center justify-center h-12 w-12 rounded-full transition-colors duration-200 relative group
                              <?php /* ACTIVE STATE for appointments.php: */ ?> bg-blue-500 text-white shadow-md <?php /* END ACTIVE */ ?>
                              <?php /* INACTIVE STATE for other pages: */ ?> text-gray-400 hover:bg-blue-600 hover:text-white <?php /* END INACTIVE */ ?>
                              ">
                        <img src="https://cdn-icons-png.flaticon.com/128/747/747310.png" alt="" class="w-6 h-6 invert">
                        <span class="absolute left-full ml-3 px-2 py-1 text-xs font-medium text-white bg-gray-900 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap -translate-x-2 group-hover:translate-x-0 pointer-events-none z-30">
                            Calendar
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
                    <h1 class="text-8xl font-extrabold text-gray-900">Manage Doctors</h1>
                    <p class="text-lg text-gray-600 mt-1">Add, edit, and delete doctor profiles.</p>
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
                        <h2 class="text-2xl font-bold text-gray-800">Doctor List</h2>
                        <button type="button" onclick="openModal('add')"
                            class="px-5 py-2 bg-[--primary-color] text-white text-sm font-semibold rounded-lg shadow-md hover:bg-[--primary-hover] transition-colors">
                            <i class="fas fa-plus mr-1"></i> Add New Doctor
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Specialty</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (!empty($doctors)): ?>
                                    <?php foreach ($doctors as $doctor): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-4 text-sm font-medium text-gray-900"><?= html_escape($doctor['id']) ?></td>
                                            <td class="px-3 py-4 text-sm text-gray-600"><?= html_escape($doctor['name']) ?></td>
                                            <td class="px-3 py-4 text-sm text-gray-600"><?= html_escape($doctor['specialty']) ?></td>
                                            <td class="px-3 py-4 text-sm text-gray-600"><?= html_escape($doctor['email']) ?></td>
                                            <td class="px-3 py-4 text-sm space-x-2 whitespace-nowrap">
                                                <button
                                                    class="text-blue-600 hover:text-blue-800 font-medium"
                                                    onclick="openModal('edit', <?= html_escape($doctor['id']) ?>)">
                                                    Edit
                                                </button>
                                                <?php if ($current_role === 'admin'): ?>
                                                    <button type="button"
                                                        onclick="openDeleteModal('<?= site_url('management/doctor_delete/' . $doctor['id']) ?>', '<?= html_escape($doctor['name']) ?>')"
                                                        class="text-red-600 hover:text-red-800 font-medium ml-2">
                                                        Delete
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="px-3 py-4 text-center text-gray-500">No doctors found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>

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

    <div id="doctor-modal" class="modal fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4" onclick="closeModal(event)">
        <div class="bg-white w-full max-w-lg p-6 rounded-xl shadow-2xl" onclick="event.stopPropagation()">
            <h2 id="modal-title" class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2"></h2>
            <form id="doctor-form" method="POST" action="">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="form-id">
                <div class="space-y-4">
                    <div>
                        <label for="form-name" class="block text-sm font-medium text-gray-700">Doctor Name</label>
                        <input type="text" id="form-name" name="name" required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[--primary-color] focus:border-[--primary-color]">
                    </div>
                    <div>
                        <label for="form-specialty" class="block text-sm font-medium text-gray-700">Specialty</label>
                        <input type="text" id="form-specialty" name="specialty" required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[--primary-color] focus:border-[--primary-color]">
                    </div>
                    <div>
                        <label for="form-email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="form-email" name="email" required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[--primary-color] focus:border-[--primary-color]">
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition">Cancel</button>
                    <button type="submit" id="form-submit-button" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Save Doctor</button>
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

        const doctorsData = <?= $doctors_list_json ?>;
        const addUrl = "<?= site_url('management/doctor_add_update') ?>";
        const updateUrlBase = "<?= site_url('management/doctor_add_update') ?>/";

        function openModal(mode, doctorId = null) {
            form.reset();
            formId.value = '';
            document.querySelectorAll('.p-3.mb-4.rounded-lg.bg-red-100').forEach(el => el.remove());

            if (mode === 'add') {
                modalTitle.textContent = "Add New Doctor";
                form.action = addUrl;
                formSubmitButton.textContent = "Add Doctor";
            } else if (mode === 'edit' && doctorsData[doctorId]) {
                const doctor = doctorsData[doctorId];
                modalTitle.textContent = `Edit Doctor: ${doctor.name}`;
                form.action = `${updateUrlBase}${doctor.id}`;
                formId.value = doctor.id;
                formName.value = doctor.name;
                formSpecialty.value = doctor.specialty;
                formEmail.value = doctor.email;
                formSubmitButton.textContent = "Save Changes";
            }
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }

        function closeModal(event = null) {
            if (!event || event.target.id === 'doctor-modal') {
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
                formSpecialty.value = '<?= html_escape($post_data['specialty'] ?? '') ?>';
                formEmail.value = '<?= html_escape($post_data['email'] ?? '') ?>';
            <?php else: ?>
                openModal('add');
                // Repopulate form with failed add data
                formName.value = '<?= html_escape($post_data['name'] ?? '') ?>';
                formSpecialty.value = '<?= html_escape($post_data['specialty'] ?? '') ?>';
                formEmail.value = '<?= html_escape($post_data['email'] ?? '') ?>';
            <?php endif; ?>
        <?php endif; ?>

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