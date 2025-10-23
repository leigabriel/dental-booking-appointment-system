<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');
$LAVA = lava_instance();
$is_logged_in = $LAVA->session->userdata('is_logged_in');
$username = $LAVA->session->userdata('username');
$role = $LAVA->session->userdata('role');

// Define the base path for local Leaflet assets
$leaflet_base_path = base_url() . PUBLIC_DIR . '/dist/';
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DENTALCARE</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="<?= $leaflet_base_path . 'leaflet.css' ?>" crossorigin="" />

    <style>
        :root {
            --primary-color: #2563eb;
            --primary-hover: #1d4ed8;
            --accent-light: #2e3be7ff;
            --neutral-dark: #1e3a8a;
        }

        body {
            font-family: 'JetBrains Mono', monospace;
        }

        .two-tier-nav {
            position: sticky;
            top: 0;
            z-index: 10;
        }

        #clinicMap {
            height: 400px;
            width: 100%;
            border-radius: 0.75rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            z-index: 1;
        }
    </style>
</head>

<body class="bg-white">

    <div class="two-tier-nav shadow-lg">
        <div class="bg-blue-800">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex justify-between items-center h-14">
                    <a href="#hero" class="text-xl font-extrabold flex items-center space-x-2">
                        <span class="text-3xl font-extrabold text-white">DENTALCARE</span>
                    </a>

                    <div class="flex items-center space-x-4">
                        <?php if ($is_logged_in): ?>
                            <span class="text-sm font-medium text-white hidden sm:inline">Hello, <?= html_escape($username) ?></span>

                            <?php if ($role === 'user'): ?>
                                <a href="<?= site_url('profile') ?>" class="px-3 py-1.5 text-sm font-semibold rounded-lg bg-gray-100 text-[--neutral-dark] hover:bg-gray-200 transition">
                                    My Profile
                                </a>
                            <?php endif; ?>

                            <a href="<?= site_url('logout') ?>" class="text-sm text-red-400 hover:text-red-300 font-medium">Logout</a>
                        <?php else: ?>
                            <a href="<?= site_url('login') ?>" class="px-3 py-1.5 text-sm font-semibold rounded-lg bg-[--primary-color] text-white hover:bg-[--primary-hover] transition shadow-md">
                                Login
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <nav class="bg-[--accent-light]">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex justify-end items-center h-10 space-x-6">
                    <a href="#about" class="text-white hover:text-[--primary-color] px-3 py-1 rounded-md text-sm font-medium transition">About Us</a>
                    <a href="#services" class="text-white hover:text-[--primary-color] px-3 py-1 rounded-md text-sm font-medium transition">Services / Booking</a>
                    <a href="#location" class="text-white hover:text-[--primary-color] px-3 py-1 rounded-md text-sm font-medium transition">Location</a>
                    <a href="#contact" class="text-white hover:text-[--primary-color] px-3 py-1 rounded-md text-sm font-medium transition">Contact</a>
                    <a href="<?= site_url('book') ?>" class="text-white hover:text-[--primary-color] px-3 py-1 rounded-md text-sm font-medium transition">Book</a>
                </div>
            </div>
        </nav>
    </div>

    <section id="hero"
        class="relative min-h-screen flex items-center justify-center p-8 overflow-hidden 
           bg-blue-700 text-white">

        <div class="absolute inset-0 flex items-center justify-center">
            <img src="<?php echo base_url() . PUBLIC_DIR . '/img/aa.png'; ?>"
                alt="Dental clinic background"
                class="object-contain max-w-[80%] max-h-[80%] opacity-100">
            <div class="absolute inset-0 bg-blue-300/20"></div>
        </div>


        <div class="relative max-w-7xl w-full text-center md:text-left flex flex-col justify-between pt-16 pb-12">
            <h1 class="text-8xl sm:text-7xl md:text-8xl font-extrabold leading-tight tracking-tight">
                HEALTHY TEETH,<br>HAPPY LIFE
            </h1>

            <div class="mt-12 flex flex-col md:flex-row justify-between gap-8">
                <div class="max-w-xs mx-auto md:mx-0 space-y-4">
                    <div class="flex items-center justify-center md:justify-start space-x-2">
                        <svg class="w-6 h-6 text-sky-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.43 1.05a11.001 11.001 0 006.84 6.84l1.05-2.43a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.948V19a2 2 0 01-2 2h-1C9.72 21 3 14.28 3 6V5z"></path>
                        </svg>
                        <span class="text-lg font-semibold text-sky-200">0963-405-5941</span>
                    </div>
                    <p class="text-sm text-gray-100">
                        Experience world-class dental care in a welcoming environment. Achieve the smile you deserve.
                    </p>
                </div>

                <div class="text-center md:text-right space-y-4">
                    <p class="text-sm font-semibold text-gray-200">5204 Naujan, Oriental Mindoro</p>
                    <a href="#about"
                        class="inline-flex items-center space-x-2 text-md font-semibold text-sky-300 hover:text-white transition">
                        <span>ABOUT DENTALCARE</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section id="about" class="min-h-screen bg-white flex items-center justify-center p-8">
        <div class="max-w-7xl">
            <h2 class="text-6xl font-bold text-[--primary-color] mb-6 border-b-2 border-gray-200 pb-2">
                About DENTALCARE
            </h2>
            <p class="text-gray-700 text-2xl mb-4">
                We are committed to providing personalized and high-quality dental care in a comfortable and welcoming environment. Our team of experienced dentists uses the latest technology to ensure the best results for your oral health.
            </p>
            <p class="text-gray-700 text-2xl">
                Since opening our doors in 2015, we've served thousands of happy patients, helping them achieve healthy and confident smiles. We prioritize patient education and preventative care above all else.
            </p>
        </div>
    </section>

    <section id="services" class="min-h-screen bg-blue-700 flex flex-col items-center p-8 pt-24">
        <h2 class="text-4xl font-bold text-white mb-12">Our Services & Booking</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-7xl w-full">
            <div class="bg-white p-6 rounded-xl shadow-lg border-t-4 border-[--accent-light] hover:shadow-xl transition">
                <h3 class="text-xl font-bold text-gray-800 mb-2">General Dentistry</h3>
                <p class="text-gray-600">Check-ups, cleanings, X-rays, and basic restorative procedures.</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-lg border-t-4 border-[--accent-light] hover:shadow-xl transition">
                <h3 class="text-xl font-bold text-gray-800 mb-2">Cosmetic Procedures</h3>
                <p class="text-gray-600">Teeth whitening, veneers, and smile design consultation.</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-lg border-t-4 border-[--accent-light] hover:shadow-xl transition">
                <h3 class="text-xl font-bold text-gray-800 mb-2">Orthodontics</h3>
                <p class="text-gray-600">Braces and Invisalign treatments for all ages.</p>
            </div>
        </div>

        <div class="mt-12 text-center">
            <p class="text-2xl font-semibold text-white mb-4">Ready to book?</p>
            <?php if ($is_logged_in): ?>
                <a href="<?= site_url('/book') ?>" class="inline-block px-8 py-3 text-lg font-bold rounded-lg bg-green-500 text-white hover:bg-green-600 transition shadow-lg">
                    Schedule Your Visit Now
                </a>
                <p class="text-sm text-gray-400 mt-2">You are logged in as <?= html_escape($username) ?>.</p>
            <?php else: ?>
                <a href="<?= site_url('login') ?>" class="inline-block px-8 py-3 text-lg font-bold rounded-lg bg-[--primary-color] text-white shadow-xl hover:bg-[--primary-hover] transition duration-300">
                    Login to Book
                </a>
                <p class="text-sm text-gray-500 mt-2">New patients, please register first.</p>
            <?php endif; ?>
        </div>
    </section>

    <section id="location" class="min-h-screen bg-gray-50 flex flex-col items-center p-8 pt-24">
        <div class="max-w-7xl w-full">
            <h2 class="text-4xl font-bold text-gray-800 mb-6 border-b-2 border-gray-300 pb-2">
                Our Location
            </h2>
            <p class="text-gray-700 text-lg mb-8">
                Find us easily! Our clinic is located in the heart of Cityville, easily accessible by public transport.
            </p>

            <div id="clinicMap" class="mb-10"></div>
        </div>
    </section>

    <section id="contact" class="min-h-screen bg-white flex items-center justify-center p-8">
        <div class="max-w-4xl w-full text-center">
            <h2 class="text-4xl font-bold text-[--primary-color] mb-6">Contact Us</h2>
            <p class="text-gray-700 text-lg mb-8">We're here to answer your questions. Get in touch via phone or visit our clinic.</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="p-6 bg-gray-50 rounded-xl shadow-md">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Call Us</h3>
                    <p class="text-blue-500 font-semibold">(555) 123-4567</p>
                </div>
                <div class="p-6 bg-gray-50 rounded-xl shadow-md">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Location</h3>
                    <p class="text-gray-600">123 Health Ave, Suite 101, Cityville</p>
                    <a href="#location" class="text-sm text-blue-500 hover:text-blue-700 font-medium">View on Map</a>
                </div>
                <div class="p-6 bg-gray-50 rounded-xl shadow-md">
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Hours</h3>
                    <p class="text-gray-600">Mon - Fri: 9:00 AM - 5:00 PM</p>
                </div>
            </div>

            <a href="mailto:info@dentalcare.com" class="mt-8 inline-block px-6 py-3 text-sm font-semibold rounded-lg bg-gray-800 text-white hover:bg-gray-700 transition">
                Send an Email
            </a>
        </div>
    </section>

    <footer class="bg-gray-900 text-white p-6 text-center">
        <p>&copy; <?= date('Y') ?> DENTALCARE Booking System. All rights reserved.</p>
        <div class="mt-2 text-sm text-gray-400">
            <a href="#" class="hover:text-white mx-2">Privacy Policy</a> |
            <a href="#" class="hover:text-white mx-2">Terms of Service</a>
        </div>
    </footer>

    <script src="<?= $leaflet_base_path . 'leaflet.js' ?>" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            delete L.Icon.Default.prototype._getIconUrl;

            L.Icon.Default.mergeOptions({
                iconRetinaUrl: '<?= $leaflet_base_path ?>images/marker-icon-2x.png',
                iconUrl: '<?= $leaflet_base_path ?>images/marker-icon.png',
                shadowUrl: '<?= $leaflet_base_path ?>images/marker-shadow.png',
            });

            // Naujan, Oriental Mindoro
            const clinicLat = 13.2500;
            const clinicLng = 121.2500;
            const clinicZoom = 13;

            // Initialize the map
            const map = L.map('clinicMap').setView([clinicLat, clinicLng], clinicZoom);

            // Add the tile layer (OpenStreetMap)
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

            // Add a marker at the clinic location
            const marker = L.marker([clinicLat, clinicLng]).addTo(map);

            // Add a popup to the marker
            marker.bindPopup("<b>DENTALCARE Clinic</b><br>5204 Naujan, Oriental Mindoro").openPopup();

            const mapContainer = document.getElementById('location');
            const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        map.invalidateSize();
                        observer.unobserve(mapContainer);
                    }
                });
            }, {
                rootMargin: '100px 0px'
            });

            observer.observe(mapContainer);

            setTimeout(function() {
                map.invalidateSize();
            }, 500);
        });
    </script>
</body>

</html>