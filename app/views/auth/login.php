<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

function display_error($error)
{
    if (!empty($error)) {
        echo '<div class="p-3 mb-4 rounded-lg bg-red-100 text-red-700 border border-red-300">' . html_escape($error) . '</div>';
    }
}

function display_flashdata($session)
{
    $message = $session->flashdata('success_message') ?? $session->flashdata('error_message');
    $color_class = $session->flashdata('success_message') ? 'bg-green-100 text-green-700 border-green-300' : 'bg-red-100 text-red-700 border-red-300';
    if ($message) {
        echo '<div class="p-3 mb-4 rounded-lg border ' . $color_class . '">' . html_escape($message) . '</div>';
    }
}

$hide_pass_icon = 'https://cdn-icons-png.flaticon.com/128/2767/2767146.png';
$show_pass_icon = 'https://cdn-icons-png.flaticon.com/128/709/709612.png';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DENTALCARE: Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-hover: #1d4ed8;
        }

        body {
            font-family: 'Inter', 'JetBrains Mono', monospace;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #3b82f6, #1e3a8a, #2563eb);
            background-size: 200% 200%;
            animation: gradientShift 6s ease infinite;
        }

        @keyframes gradientShift {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }
    </style>
</head>

<body class="bg-gray-100 flex justify-center items-center min-h-screen">
    <div class="flex flex-col md:flex-row w-full max-w-5xl bg-white rounded-2xl overflow-hidden shadow-2xl border border-gray-200">

        <!-- LEFT PANEL -->
        <div class="relative gradient-bg flex flex-col justify-between text-white p-10 md:w-1/2 rounded-2xl md:rounded-none overflow-hidden">
            <!-- Background Image (Overlay) -->
            <div class="absolute inset-0">
                <img src="https://i.pinimg.com/736x/4b/a0/03/4ba003d85204accaec805a405947351a.jpg"
                    alt="Dental background"
                    class="w-full h-full object-cover opacity-100">
                <div class="absolute inset-0 bg-gradient-to-t from-blue-800/80 via-blue-700/70 to-blue-600/60"></div>
            </div>

            <!-- Content Layer -->
            <div class="relative z-10 flex flex-col justify-between h-full">
                <div>
                    <p class="text-sm opacity-80 mb-2">DENTALCARE</p>
                    <h1 class="text-4xl font-extrabold leading-snug">We Care About Your Teeth Health</h1>
                </div>

                <div class="mt-10 opacity-80 text-sm">
                    <p class="mb-2 font-medium">Trusted by</p>
                    <div class="flex space-x-4 opacity-90 text-xs">
                        <span class="bg-white/10 px-2 py-1 rounded">Colgate</span>
                        <span class="bg-white/10 px-2 py-1 rounded">Oral-B</span>
                        <span class="bg-white/10 px-2 py-1 rounded">Philips</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT PANEL (LOGIN FORM) -->
        <div class="md:w-1/2 w-full p-8 flex flex-col justify-center">
            <h1 class="text-3xl font-extrabold text-gray-900 mb-1">Get Started Now</h1>
            <p class="text-gray-500 text-sm mb-6">Please log in to your account to continue.</p>

            <?php display_flashdata(lava_instance()->session); ?>
            <?php display_error($error ?? null); ?>

            <form method="POST" action="<?= site_url('login/submit') ?>" class="space-y-4">
                <?= csrf_field() ?>

                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username or Email</label>
                    <input type="text" id="username" name="username"
                        value="<?= html_escape($username ?? '') ?>" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[--primary-color] focus:border-transparent transition"
                        placeholder="Enter username or email">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[--primary-color] focus:border-transparent transition pr-10"
                            placeholder="********">
                        <button type="button" id="togglePassword"
                            class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-600 hover:text-[--primary-hover] focus:outline-none">
                            <img id="eye-icon" class="h-5 w-5" src="<?= $hide_pass_icon ?>" alt="Toggle Visibility">
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" class="h-4 w-4 text-[--primary-color] border-gray-300 rounded">
                        <span class="text-gray-600">I agree to the <a href="#" class="text-[--primary-color] hover:underline">Terms & Privacy</a></span>
                    </label>
                    <a href="#" class="text-[--primary-color] hover:underline font-medium">Forgot Password?</a>
                </div>

                <button type="submit"
                    class="w-full bg-[--primary-color] text-white py-2.5 rounded-lg font-semibold hover:bg-[--primary-hover] transition duration-200 shadow-md shadow-blue-300/50">
                    Log in
                </button>

                <div class="text-center text-sm mt-2">
                    Don't have an account?
                    <a href="<?= site_url('register') ?>" class="text-[--primary-color] hover:underline font-medium">Register</a>
                </div>

                <div class="flex items-center my-4">
                    <hr class="flex-grow border-gray-300">
                    <span class="text-gray-400 text-xs px-2">OR</span>
                    <hr class="flex-grow border-gray-300">
                </div>

                <div class="flex justify-center space-x-3">
                    <button type="button" class="flex items-center space-x-2 border border-gray-300 px-4 py-2 rounded-lg hover:bg-gray-50 transition text-sm">
                        <img src="https://cdn-icons-png.flaticon.com/128/2991/2991148.png" class="w-4 h-4" alt="Google"> <span>Login with Google</span>
                    </button>
                    <button type="button" class="flex items-center space-x-2 border border-gray-300 px-4 py-2 rounded-lg hover:bg-gray-50 transition text-sm">
                        <img src="https://cdn-icons-png.flaticon.com/128/5968/5968764.png" class="w-4 h-4" alt="Apple"> <span>Login with Facebook</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const passwordInput = document.getElementById('password');
        const toggleButton = document.getElementById('togglePassword');
        const eyeIcon = document.getElementById('eye-icon');
        const showIconUrl = '<?= $show_pass_icon ?>';
        const hideIconUrl = '<?= $hide_pass_icon ?>';

        toggleButton.addEventListener('click', function(e) {
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            eyeIcon.src = isPassword ? showIconUrl : hideIconUrl;
            eyeIcon.alt = isPassword ? 'Hide Password' : 'Show Password';
        });
    </script>
</body>

</html>