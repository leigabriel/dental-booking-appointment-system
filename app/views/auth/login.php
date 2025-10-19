<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');
// Helpers for displaying errors and session flash messages
function display_error($error)
{
    if (!empty($error)) {
        // Keeping error text red, but updating classes slightly for consistency
        echo '<div class="p-3 mb-4 rounded-lg bg-red-100 text-red-700 border border-red-300">' . html_escape($error) . '</div>';
    }
}
function display_flashdata($session)
{
    $message = $session->flashdata('success_message') ?? $session->flashdata('error_message');
    // Keeping success/error colors distinct
    $color_class = $session->flashdata('success_message') ? 'bg-green-100 text-green-700 border-green-300' : 'bg-red-100 text-red-700 border-red-300';
    if ($message) {
        echo '<div class="p-3 mb-4 rounded-lg border ' . $color_class . '">' . html_escape($message) . '</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DENTALCARE: Login</title>
    <!-- Tailwind CSS CDN for styling -->
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
            <p class="text-xl"> Login to Your Account </p>
        </h1>

        <?php display_flashdata(lava_instance()->session); ?>
        <?php display_error($error ?? null); ?>

        <form method="POST" action="<?= site_url('login/submit') ?>" class="space-y-4">
            <?= csrf_field() // Important: CSRF protection is highly recommended 
            ?>

            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <input type="text" id="username" name="username"
                    value="<?= html_escape($username ?? '') ?>" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[--primary-color] focus:border-transparent transition"
                    placeholder="Enter your username">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" id="password" name="password" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[--primary-color] focus:border-transparent transition"
                    placeholder="••••••••">
            </div>

            <button type="submit"
                class="w-full bg-[--primary-color] text-white py-2.5 rounded-lg font-semibold hover:bg-[--primary-hover] transition duration-200 shadow-md shadow-blue-300/50">
                Sign In
            </button>
        </form>

        <div class="text-center mt-6 text-sm">
            Don't have an account?
            <a href="<?= site_url('register') ?>" class="underline text-blue-600 hover:text-blue-800 font-medium transition">
                Register
            </a>
        </div>
    </div>
</body>

</html>