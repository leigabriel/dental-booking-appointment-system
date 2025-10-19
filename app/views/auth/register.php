<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');
// Helper to display validation errors from the Form_validation library
function display_validation_errors($errors)
{
    if (!empty($errors)) {
        // Tailwind error styling
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
    <title>DENTALCARE: Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* CRITICAL CHANGE: Set primary color to Blue */
        :root {
            --primary-color: #2563eb;
            /* Tailwind Blue-600 */
            --primary-hover: #1d4ed8;
            /* Tailwind Blue-700 */
        }

        body {
            font-family: 'JetBrains Mono', monospace;
        }
    </style>
</head>

<body class="bg-gray-100 flex justify-center items-center min-h-screen p-4">
    <div class="form-container w-full max-w-sm bg-white p-8 rounded-xl shadow-2xl border border-gray-200">
        <h1 class="text-3xl font-extrabold text-left text-[--primary-color] mb-6">
            Welcome to DENTALCARE <br> <br>
            <p class="text-xl"> Register an Account </p>
        </h1>

        <?php display_validation_errors($errors ?? []); ?>

        <form method="POST" action="<?= site_url('register/submit') ?>" class="space-y-4">
            <?= csrf_field() ?>

            <div>
                <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                <input type="text" id="full_name" name="full_name" value="<?= html_escape($full_name ?? '') ?>" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[--primary-color] focus:border-transparent transition"
                    placeholder="Enter your full name">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" id="email" name="email" value="<?= html_escape($email ?? '') ?>" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[--primary-color] focus:border-transparent transition"
                    placeholder="user@example.com">
            </div>

            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <input type="text" id="username" name="username" value="<?= html_escape($username ?? '') ?>" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[--primary-color] focus:border-transparent transition"
                    placeholder="Enter your desired username">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" id="password" name="password" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[--primary-color] focus:border-transparent transition"
                    placeholder="••••••••">
            </div>

            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[--primary-color] focus:border-transparent transition"
                    placeholder="••••••••">
            </div>

            <div hidden>
                <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Select Role</label>
                <select id="role" name="role" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[--primary-color] focus:border-transparent transition">
                    <option value="user">Patient/User</option>
                    <option value="staff">Staff</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <button type="submit"
                class="w-full bg-[--primary-color] text-white py-2.5 rounded-lg font-semibold hover:bg-[--primary-hover] transition duration-200 shadow-md shadow-blue-300/50">
                Register
            </button>
        </form>

        <div class="text-center mt-6 text-sm">
            Already have an account?
            <a href="<?= site_url('login') ?>" class="underline text-blue-600 hover:text-blue-800 font-medium transition">
                Login
            </a>
        </div>
    </div>
</body>

</html>