<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');
$LAVA = lava_instance();
$username = $LAVA->session->userdata('username');
$current_role = $LAVA->session->userdata('role'); // Staff role

// Variables passed from Staff::dashboard() - Make sure controller provides these
$all_users = $all_users ?? []; // Should contain only 'user' role accounts
$total_patients = $total_patients ?? 0;
$total_appointments = $total_appointments ?? 0;

// Fetch staff details (similar to how admin details are fetched for admin dashboard)
// This requires fetching the logged-in staff's details in the Staff controller
$staff_details = $staff_details ?? []; // Assuming controller passes this
$staff_full_name = $staff_details['full_name'] ?? $username;
$staff_email = $staff_details['email'] ?? 'No Email Set';


// Fetch flash messages if any
$flash_message = $LAVA->session->flashdata('success_message') ?? $LAVA->session->flashdata('error_message');

// Helper function (can be shared or duplicated if not using includes)
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
    <title>Staff Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            /* Using Admin styles for consistency */
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

        /* Match Admin font */
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
                <a href="<?= site_url('staff/dashboard') ?>" title="Dentalcare Home" class="flex items-center justify-center h-12 w-12 mb-8 rounded-full bg-blue-500 text-white shadow-md">
                    <img src="https://cdn-icons-png.flaticon.com/128/3914/3914549.png" alt="Dentalcare Logo" class="w-6 h-6 invert">
                </a>

                <nav class="space-y-4">
                    <a href="<?= site_url('staff/dashboard') ?>" title="Dashboard"
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
                    <h1 class="text-8xl font-extrabold text-gray-900">Dashboard</h1>
                    <p class="text-lg text-gray-600 mt-1">Welcome back, <?= html_escape($staff_full_name) ?>. Here's a summary for staff.</p>
                </header>

                <?php if ($flash_message): ?>
                    <div class="p-4 mb-6 rounded-lg <?= $LAVA->session->flashdata('success_message') ? 'bg-green-100 text-green-700 border-green-300' : 'bg-red-100 text-red-700 border-red-300' ?> border shadow-sm" role="alert">
                        <strong class="font-bold"><?= $LAVA->session->flashdata('success_message') ? 'Success!' : 'Error!' ?></strong>
                        <span><?= html_escape($flash_message) ?></span>
                    </div>
                <?php endif; ?>
                <?php // display_validation_errors($errors); // Usually no forms here, but can add if needed 
                ?>

                <section class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-10">
                    <div class="flex min-h-[20em] flex-col justify-between gap-[0.5em] rounded-[1.5em] bg-[#E0F2FE] p-[1.5em] text-[#0369A1] shadow-[0px_4px_16px_0px_rgba(0,0,0,0.1)] transition hover:shadow-lg">
                        <div class="flex h-fit w-full items-start justify-between">
                            <div class="flex flex-col items-start justify-center">
                                <p class="text-[1rem] font-semibold uppercase tracking-wider">Total Patients</p>
                                <p class="text-[8em] font-extrabold mt-1"><?= html_escape($total_patients) ?></p>
                            </div>
                            <div class="text-4xl opacity-80">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="h-[1px] w-full rounded-full bg-[hsla(206,90%,50%,0.2)]"></div>
                        <p class="text-[0.75rem] font-light text-sky-600">All registered patient accounts.</p>
                    </div>

                    <div class="flex min-h-[20em] flex-col justify-between gap-[0.5em] rounded-[1.5em] bg-[#FEF3C7] p-[1.5em] text-[#B45309] shadow-[0px_4px_16px_0px_rgba(0,0,0,0.1)] transition hover:shadow-lg">
                        <div class="flex h-fit w-full items-start justify-between">
                            <div class="flex flex-col items-start justify-center">
                                <p class="text-[1rem] font-semibold uppercase tracking-wider">Total Bookings</p>
                                <p class="text-[8em] font-extrabold mt-1"><?= html_escape($total_appointments) ?></p>
                            </div>
                            <div class="text-4xl opacity-80">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                        </div>
                        <div class="h-[1px] w-full rounded-full bg-[hsla(39,90%,40%,0.2)]"></div>
                        <p class="text-[0.75rem] font-light text-amber-700">All appointment records.</p>
                    </div>
                </section>

                <section class="bg-white p-6 sm:p-8 rounded-xl shadow-lg border border-gray-200">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Registered Patient Accounts</h2>
                    <p class="text-gray-600 mb-4 text-sm">
                        View registered patient accounts for reference.
                    </p>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Full Name</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Username</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (!empty($all_users)): ?>
                                    <?php foreach ($all_users as $user): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-4 text-sm font-medium text-gray-900"><?= html_escape($user['id']) ?></td>
                                            <td class="px-3 py-4 text-sm text-gray-600"><?= html_escape($user['full_name'] ?? 'N/A') ?></td>
                                            <td class="px-3 py-4 text-sm text-gray-600"><?= html_escape($user['email'] ?? 'N/A') ?></td>
                                            <td class="px-3 py-4 text-sm text-gray-600"><?= html_escape($user['username']) ?></td>
                                            <td class="px-3 py-4">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    <?= html_escape(ucfirst($user['role'])) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="px-3 py-4 text-center text-gray-500">No patient accounts found.</td>
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

    <script>
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
    </script>
</body>

</html>