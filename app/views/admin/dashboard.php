<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');
$LAVA = lava_instance();
$username = $LAVA->session->userdata('username');
$current_role = $LAVA->session->userdata('role');

// Variables passed from Admin::dashboard() method
$total_users = $total_users ?? 0;
$total_staff = $total_staff ?? 0;
$total_admin = $total_admin ?? 0;
$all_users = $all_users ?? [];
$errors = $errors ?? [];
$post_data = $post_data ?? [];

// Define paths and helpers
$logo_img = 'https://cdn-icons-png.flaticon.com/128/11873/11873346.png';
$admin_img = 'https://cdn-icons-png.flaticon.com/128/9512/9512709.png';
$staff_img = 'https://cdn-icons-png.flaticon.com/128/3281/3281869.png';
$user_img = 'https://cdn-icons-png.flaticon.com/128/17701/17701286.png';
$booking_img = 'https://cdn-icons-png.flaticon.com/128/18669/18669653.png';
$doctors_img = 'https://cdn-icons-png.flaticon.com/128/2785/2785482.png';
$services_img = 'https://cdn-icons-png.flaticon.com/128/1041/1041898.png';

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
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary-color: #0EA5E9;
            --primary-hover: #0284C7;
        }

        body {
            font-family: 'JetBrains Mono', monospace;
        }

        .card-blue {
            background-color: #3B82F6;
            border-color: #2563EB;
        }

        .card-blue-hover:hover {
            background-color: #2563EB;
        }

        .modal {
            transition: opacity 0.25s ease;
        }
    </style>
</head>

<body class="bg-gray-100 p-6 sm:p-10 min-h-screen">
    <div class="max-w-7xl mx-auto">

        <header class="flex justify-between items-center bg-white p-6 rounded-xl shadow-2xl border border-gray-200 mb-10">
            <h1 class="text-4xl font-extrabold text-gray-900 flex items-center space-x-3">
                <span class="uppercase">DENTALCARE Admin: <?= html_escape($username) ?></span>
            </h1>
            <div class="text-right">
                <p class="text-lg font-bold text-[--primary-color]">Hello, <?= html_escape($username) ?></p>
                <p class="text-sm text-gray-500">Role: <span class="font-semibold text-red-600"><?= html_escape(ucfirst($current_role)) ?></span></p>
                <a href="<?= site_url('logout') ?>" class="mt-1 inline-block text-sm text-red-500 hover:text-red-700 font-medium transition">
                    Logout
                </a>
            </div>
        </header>

        <?php if ($flash_message): ?>
            <div class="p-4 mb-4 rounded-lg <?= $LAVA->session->flashdata('success_message') ? 'bg-green-100 text-green-700 border-green-300' : 'bg-red-100 text-red-700 border-red-300' ?> border">
                <?= html_escape($flash_message) ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-10">
            <div class="bg-white p-6 rounded-xl shadow-lg border-b-4 border-red-500 hover:shadow-xl transition duration-200">
                <p class="text-sm font-semibold text-red-600 uppercase tracking-wider mb-2">Total Admins</p>
                <div class="flex items-center justify-between">
                    <p class="text-4xl font-extrabold text-gray-900"><?= html_escape($total_admin) ?></p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-lg border-b-4 border-green-500 hover:shadow-xl transition duration-200">
                <p class="text-sm font-semibold text-green-600 uppercase tracking-wider mb-2">Total Staff</p>
                <div class="flex items-center justify-between">
                    <p class="text-4xl font-extrabold text-gray-900"><?= html_escape($total_staff) ?></p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-lg border-b-4 border-[--primary-color] hover:shadow-xl transition duration-200">
                <p class="text-sm font-semibold text-[--primary-color] uppercase tracking-wider mb-2">Total Users</p>
                <div class="flex items-center justify-between">
                    <p class="text-4xl font-extrabold text-gray-900"><?= html_escape($total_users) ?></p>
                </div>
            </div>
        </div>

        <div class="mb-10">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-3">Core Management Tools</h2>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

                <a href="<?= site_url('management/appointments') ?>" class="group block bg-gray-700 p-6 rounded-xl shadow-lg border border-gray-800 hover:bg-gray-800 transition duration-150">
                    <h3 class="text-xl font-extrabold text-white mb-2 flex items-center space-x-3">
                        <span>Manage Bookings</span>
                    </h3>
                    <p class="text-gray-300 group-hover:text-gray-100 transition">View, confirm, or cancel patient bookings.</p>
                </a>

                <a href="<?= site_url('management/doctors') ?>" class="group block card-blue p-6 rounded-xl shadow-lg border card-blue-hover transition duration-150">
                    <h3 class="text-xl font-extrabold text-white mb-2 flex items-center space-x-3">
                        <span>Manage Doctors</span>
                    </h3>
                    <p class="text-blue-100 group-hover:text-white transition">Add, view, and edit/delete doctor records.</p>
                </a>

                <a href="<?= site_url('management/services') ?>" class="group block bg-yellow-500 p-6 rounded-xl shadow-lg border border-yellow-600 hover:bg-yellow-600 transition duration-150">
                    <h3 class="text-xl font-extrabold text-white mb-2 flex items-center space-x-3">
                        <span>Manage Services</span>
                    </h3>
                    <p class="text-yellow-100 group-hover:text-white transition">Add, view, and edit/delete service offerings.</p>
                </a>

                <button type="button" onclick="openModal('add')" class="group block bg-red-600 p-6 rounded-xl shadow-lg border border-red-700 hover:bg-red-700 transition duration-150 text-white text-center">
                    <h3 class="text-xl font-extrabold mb-2 flex items-center justify-center space-x-3">
                        <span>Add/Edit Admin/Staff</span>
                    </h3>
                    <p class="text-red-200 group-hover:text-white transition text-sm">Create or modify Admin/Staff user accounts.</p>
                </button>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 border-b pb-3">All Registered Accounts</h2>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Full Name</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (!empty($all_users)): ?>
                            <?php foreach ($all_users as $user): ?>
                                <tr>
                                    <td class="px-3 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= html_escape($user['id']) ?></td>
                                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-600"><?= html_escape($user['username']) ?></td>
                                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-600"><?= html_escape($user['full_name'] ?? 'N/A') ?></td>
                                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-600"><?= html_escape($user['email'] ?? 'N/A') ?></td>
                                    <td class="px-3 py-4 whitespace-nowrap">
                                        <?php
                                        $role_class = match ($user['role']) {
                                            'admin' => 'bg-red-100 text-red-800',
                                            'staff' => 'bg-blue-100 text-blue-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                        ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $role_class ?>">
                                            <?= html_escape(ucfirst($user['role'])) ?>
                                        </span>
                                    </td>
                                    <td class="px-3 py-4 text-sm space-x-2 whitespace-nowrap">
                                        <?php if ($user['role'] === 'admin' || $user['role'] === 'staff'): ?>
                                            <button
                                                class="text-blue-600 hover:text-blue-800 font-medium"
                                                onclick="openModal('edit', {
                                                    id: '<?= $user['id'] ?>',
                                                    username: '<?= html_escape($user['username']) ?>',
                                                    full_name: '<?= html_escape($user['full_name']) ?>',
                                                    email: '<?= html_escape($user['email']) ?>',
                                                    role: '<?= $user['role'] ?>'
                                                })">
                                                Edit
                                            </button>
                                            <a href="<?= site_url('admin/admin_staff_delete/' . $user['id']) ?>"
                                                onclick="return confirm('WARNING: Delete <?= html_escape($user['username']) ?>? This cannot be undone.');"
                                                class="text-red-600 hover:text-red-800 font-medium ml-2">
                                                Delete
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-400">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= html_escape($user['created_at']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-3 py-4 text-center text-gray-500">No registered accounts found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="user-modal" class="modal fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50 p-4" onclick="closeModal(event)">
        <div class="bg-white w-full max-w-lg p-6 rounded-xl shadow-2xl" onclick="event.stopPropagation()">

            <h2 id="modal-title" class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2"></h2>

            <form id="user-form" method="POST" action="">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="form-id">

                <div class="space-y-4">
                    <div>
                        <label for="form-full-name" class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" id="form-full-name" name="full_name" required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[--primary-color] focus:border-[--primary-color]">
                    </div>

                    <div>
                        <label for="form-email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="form-email" name="email" required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[--primary-color] focus:border-[--primary-color]">
                    </div>

                    <div id="username-field">
                        <label for="form-username" class="block text-sm font-medium text-gray-700">Username</label>
                        <input type="text" id="form-username" name="username" required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[--primary-color] focus:border-[--primary-color]">
                    </div>

                    <div>
                        <label for="form-role" class="block text-sm font-medium text-gray-700">Role</label>
                        <select id="form-role" name="role" required
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[--primary-color] focus:border-[--primary-color]">
                            <option value="staff">Staff</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <div>
                        <label for="form-password" class="block text-sm font-medium text-gray-700">Password <span id="password-hint" class="text-xs text-gray-500 ml-1"></span></label>
                        <input type="password" id="form-password" name="password"
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-[--primary-color] focus:border-[--primary-color]">
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition">Cancel</button>
                    <button type="submit" id="form-submit-button" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">Save User</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        const modal = document.getElementById('user-modal');
        const form = document.getElementById('user-form');
        const modalTitle = document.getElementById('modal-title');
        const formId = document.getElementById('form-id');
        const formFullName = document.getElementById('form-full-name');
        const formEmail = document.getElementById('form-email');
        const formUsername = document.getElementById('form-username');
        const usernameField = document.getElementById('username-field');
        const formRole = document.getElementById('form-role');
        const formPassword = document.getElementById('form-password');
        const passwordHint = document.getElementById('password-hint');
        const formSubmitButton = document.getElementById('form-submit-button');
        const siteUrl = "<?= site_url('admin/admin_staff_add_update') ?>";

        function openModal(mode, user = {}) {
            // Reset form and errors
            form.reset();
            formId.value = '';
            document.querySelectorAll('.p-3.mb-4.rounded-lg.bg-red-100').forEach(el => el.remove());

            if (mode === 'add') {
                modalTitle.textContent = "Add New Admin/Staff";
                form.action = siteUrl;

                // Show username field and require password
                usernameField.classList.remove('hidden');
                formUsername.required = true;
                formPassword.required = true;
                passwordHint.textContent = "(Required)";
                formSubmitButton.textContent = "Add User";

            } else if (mode === 'edit') {
                modalTitle.textContent = `Edit User: ${user.username}`;
                form.action = `${siteUrl}/${user.id}`;
                formId.value = user.id;

                // Hide username field, pre-fill other fields, and make password optional
                usernameField.classList.add('hidden');
                formUsername.required = false;
                formFullName.value = user.full_name;
                formEmail.value = user.email;
                formRole.value = user.role;
                formPassword.required = false;
                passwordHint.textContent = "(Optional - leave blank to keep current)";
                formSubmitButton.textContent = "Save Changes";
            }

            // Show modal
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden'); // Prevent scrolling
        }

        function closeModal(event = null) {
            // Check if the click was outside the modal content, or if called directly by a button
            if (!event || event.target.id === 'user-modal') {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        }
    </script>
</body>

</html>