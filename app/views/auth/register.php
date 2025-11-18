<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');

// Helper to display validation errors from the Form_validation library
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

// Define image URLs
$hide_pass_icon = 'https://cdn-icons-png.flaticon.com/128/2767/2767146.png';
$show_pass_icon = 'https://cdn-icons-png.flaticon.com/128/709/709612.png';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DENTALCARE: Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-hover: #1d4ed8;
        }

        body {
            font-family: 'Inter', sans-serif;
            font-weight: 500;
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

        ::-webkit-scrollbar {
            width: 2px;
        }

        ::-webkit-scrollbar-track {
            background: #212631;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            border-radius: 10px;
        }
    </style>
</head>

<body class="relative isolate bg-blue-950 flex justify-center items-center min-h-screen">
    <div aria-hidden="true" class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80">
        <div style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" class="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-30 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]"></div>
    </div>
    <div class="flex flex-col md:flex-row w-full max-w-5xl bg-white rounded-2xl overflow-hidden shadow-2xl border-4 border-gray-200">

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
                    <p class="text-lg font-semibold opacity-80 mb-2">Your smile, our priority</p>
                    <h1 class="text-4xl font-extrabold leading-snug">Join the DENTALCARE<br>community today</h1>
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

        <!-- RIGHT PANEL -->
        <div class="md:w-1/2 w-full p-8 flex flex-col justify-center">
            <h1 class="text-3xl font-extrabold text-gray-900 mb-1">Create your Account</h1>
            <p class="text-gray-500 text-sm mb-6">Register to access our dental booking and care services.</p>

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
                    <div class="relative">
                        <input type="password" id="password_reg" name="password" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[--primary-color] focus:border-transparent transition pr-10"
                            placeholder="********">
                        <button type="button" data-target="password_reg" class="toggle-password absolute inset-y-0 right-0 flex items-center px-3 text-gray-600 hover:text-[--primary-hover] focus:outline-none">
                            <img class="h-5 w-5 eye-icon-reg" src="<?= $hide_pass_icon ?>" alt="Toggle Visibility">
                        </button>
                    </div>
                </div>

                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <div class="relative">
                        <input type="password" id="confirm_password_reg" name="confirm_password" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[--primary-color] focus:border-transparent transition pr-10"
                            placeholder="********">
                        <button type="button" data-target="confirm_password_reg" class="toggle-password absolute inset-y-0 right-0 flex items-center px-3 text-gray-600 hover:text-[--primary-hover] focus:outline-none">
                            <img class="h-5 w-5 eye-icon-reg" src="<?= $hide_pass_icon ?>" alt="Toggle Visibility">
                        </button>
                    </div>
                </div>

                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Select Role</label>
                    <select id="role" name="role" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[--primary-color] focus:border-transparent transition">
                        <option value="user">User</option>
                        <option value="doctor">Doctor</option>
                        <option value="staff">Staff</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <!-- Specialty field for doctors -->
                <div id="specialty-field" style="display: none;">
                    <label for="specialty" class="block text-sm font-medium text-gray-700 mb-1">Specialty</label>
                    <input type="text" id="specialty" name="specialty"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[--primary-color] focus:border-transparent transition"
                        placeholder="e.g., General Dentistry, Orthodontics">
                </div>

                <button type="submit"
                    class="w-full bg-[--primary-color] text-white py-2.5 rounded-lg font-semibold hover:bg-[--primary-hover] transition duration-200 shadow-md shadow-blue-300/50">
                    Register
                </button>
            </form>

            <div class="text-center mt-6 text-sm">
                Already have an account?
                <a href="<?= site_url('login') ?>" class="text-[--primary-color] hover:underline font-medium transition">
                    Login
                </a>
            </div>

            <div class="flex items-center my-4">
                <hr class="flex-grow border-gray-300">
                <span class="text-gray-400 text-xs px-2">OR</span>
                <hr class="flex-grow border-gray-300">
            </div>

            <div class="flex justify-center space-x-3">
                <a href="/auth/google_login" type="button" class="flex items-center space-x-2 border border-gray-300 px-4 py-2 rounded-lg hover:bg-gray-50 transition text-sm">
                    <img src="https://cdn-icons-png.flaticon.com/128/2991/2991148.png" class="w-4 h-4" alt="Google"> <span>Login with Google</span>
                </a>
            </div>
        </div>
    </div>
    <div aria-hidden="true" class="absolute inset-x-0 top-[calc(100%-13rem)] -z-10 transform-gpu overflow-hidden blur-3xl sm:top-[calc(100%-30rem)]">
        <div style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" class="relative left-[calc(50%+3rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-30 sm:left-[calc(50%+36rem)] sm:w-[50.1875rem]"></div>
    </div>
    <script>
        const showIconUrl = '<?= $show_pass_icon ?>';
        const hideIconUrl = '<?= $hide_pass_icon ?>';

        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(toggleButton => {
            toggleButton.addEventListener('click', function(e) {
                const targetId = this.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);
                const eyeIcon = this.querySelector('.eye-icon-reg');
                const isPassword = passwordInput.getAttribute('type') === 'password';

                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                eyeIcon.src = isPassword ? showIconUrl : hideIconUrl;
            });
        });

        // Show/hide specialty field based on role selection
        const roleSelect = document.getElementById('role');
        const specialtyField = document.getElementById('specialty-field');
        const specialtyInput = document.getElementById('specialty');

        roleSelect.addEventListener('change', function() {
            if (this.value === 'doctor') {
                specialtyField.style.display = 'block';
                specialtyInput.required = true;
            } else {
                specialtyField.style.display = 'none';
                specialtyInput.required = false;
            }
        });
    </script>

    </script>
</body>

</html>