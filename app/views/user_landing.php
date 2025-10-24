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
                    <a href="#services" class="text-white hover:text-[--primary-color] px-3 py-1 rounded-md text-sm font-medium transition">Services</a>
                    <a href="#contacts" class="text-white hover:text-[--primary-color] px-3 py-1 rounded-md text-sm font-medium transition">Contact</a>
                    <a href="<?= site_url('book') ?>" class="text-white hover:text-[--primary-color] px-3 py-1 rounded-md text-sm font-medium transition">Book</a>
                </div>
            </div>
        </nav>
    </div>

    <section id="hero"
        class="relative min-h-screen rounded-b-3xl flex items-center justify-center p-8 overflow-hidden 
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

    <section id="about" class="min-h-screen bg-white flex flex-col items-center justify-center p-8">
        <div class="max-w-7xl text-center mb-16">
            <h2 class="text-4xl md:text-5xl font-extrabold text-gray-800 mb-2">
                About DENTALCARE
            </h2>
            <p class="text-xl text-gray-600">
                Personalized and High-Quality Dental Care
            </p>
        </div>

        <div class="flex flex-col md:flex-row items-start md:items-center justify-center space-y-10 md:space-y-0 md:space-x-12 max-w-6xl w-full">

            <div class="flex-shrink-0 w-full md:w-1/3 text-center relative">

                <div class="w-24 h-24 p-4 mb-4 mx-auto rounded-full border-2 border-dashed border-teal-200 bg-teal-50 flex items-center justify-center">
                    <span class="absolute -top-3 left-1/2 transform -translate-x-1/2 bg-teal-500 text-white w-8 h-8 flex items-center justify-center rounded-full text-sm font-bold shadow-lg">1</span>
                    <img src="https://cdn-icons-png.flaticon.com/128/7215/7215609.png" alt="Commitment Icon" class="w-12 h-12">
                </div>

                <h3 class="text-xl font-bold text-gray-800 mb-2 mt-4">Our Commitment</h3>
                <p class="text-gray-600 text-sm md:text-base px-2">
                    We provide personalized and high-quality dental care in a welcoming environment, using the latest technology.
                </p>
            </div>

            <div class="hidden md:block flex-1 border-t-2 border-dashed border-gray-300 -translate-y-12"></div>


            <div class="flex-shrink-0 w-full md:w-1/3 text-center relative">
                <div class="w-24 h-24 p-4 mb-4 mx-auto rounded-full border-2 border-dashed border-teal-200 bg-teal-50 flex items-center justify-center">
                    <span class="absolute -top-3 left-1/2 transform -translate-x-1/2 bg-teal-500 text-white w-8 h-8 flex items-center justify-center rounded-full text-sm font-bold shadow-lg">2</span>
                    <img src="https://cdn-icons-png.flaticon.com/128/2278/2278049.png" alt="Years of Service Icon" class="w-12 h-12">
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2 mt-4">Years of Service</h3>
                <p class="text-gray-600 text-sm md:text-base px-2">
                    Since opening our doors in 2015, we've served thousands of happy patients, achieving healthy and confident smiles.
                </p>
            </div>

            <div class="hidden md:block flex-1 border-t-2 border-dashed border-gray-300 -translate-y-12"></div>


            <div class="flex-shrink-0 w-full md:w-1/3 text-center relative">
                <div class="w-24 h-24 p-4 mb-4 mx-auto rounded-full border-2 border-dashed border-teal-200 bg-teal-50 flex items-center justify-center">
                    <span class="absolute -top-3 left-1/2 transform -translate-x-1/2 bg-teal-500 text-white w-8 h-8 flex items-center justify-center rounded-full text-sm font-bold shadow-lg">3</span>
                    <img src="https://cdn-icons-png.flaticon.com/128/2902/2902055.png" alt="Philosophy Icon" class="w-12 h-12">
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2 mt-4">Our Philosophy</h3>
                <p class="text-gray-600 text-sm md:text-base px-2">
                    We prioritize patient education and preventative care above all else to ensure lasting oral health.
                </p>
            </div>
        </div>
        <div class="mt-20 max-w-4xl w-full text-center">
            <p class="text-lg text-gray-700 leading-relaxed border-t pt-8 border-gray-200">
                DENTALCARE is dedicated to being your partner in oral health. Our experienced team utilizes state-of-the-art technology to deliver comprehensive general, cosmetic, and preventative care. We believe a beautiful, healthy smile is key to overall wellness, and we strive to make every visit comfortable, informative, and focused on achieving your best possible smile.
            </p>
        </div>
    </section>

    <section id="services" class="min-h-screen bg-gray-50 flex flex-col justify-center items-center p-8 py-24">
        <div class="max-w-7xl w-full text-center mb-16">
            <p class="text-xl text-teal-600 font-semibold mb-2">Our Care</p>
            <h2 class="text-4xl md:text-5xl font-extrabold text-gray-800">
                More than just a visit
            </h2>
            <p class="text-xl text-gray-600 mt-3">Explore the full range of dental services we offer.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-8 max-w-7xl w-full">

            <div class="bg-blue-400 p-6 md:p-8 rounded-2xl shadow-xl transition transform hover:shadow-2xl hover:-translate-y-1 border border-gray-100">
                <div class="w-16 h-16 mb-4 flex items-center justify-center rounded-xl bg-blue-100 border border-blue-200 mx-auto">
                    <img src="https://cdn-icons-png.flaticon.com/128/18448/18448032.png" alt="Exam & Cleaning Icon" class="w-12 h-12">
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2 mt-4 text-center">Comprehensive Exam & Cleaning</h3>
                <p class="text-gray-600 text-center text-sm">Complete check-up, professional cleaning, and preventative care for optimal oral health.</p>
            </div>

            <div class="bg-red-400 p-6 md:p-8 rounded-2xl shadow-xl transition transform hover:shadow-2xl hover:-translate-y-1 border border-gray-100">
                <div class="w-16 h-16 mb-4 flex items-center justify-center rounded-xl bg-pink-100 border border-pink-200 mx-auto">
                    <img src="https://cdn-icons-png.flaticon.com/128/13502/13502961.png" alt="Whitening Icon" class="w-12 h-12">
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2 mt-4 text-center">Professional Teeth Whitening</h3>
                <p class="text-gray-600 text-center text-sm">Safe and effective in-office treatment to brighten your smile several shades.</p>
            </div>

            <div class="bg-green-400 p-6 md:p-8 rounded-2xl shadow-xl transition transform hover:shadow-2xl hover:-translate-y-1 border border-gray-100">
                <div class="w-16 h-16 mb-4 flex items-center justify-center rounded-xl bg-green-100 border border-green-200 mx-auto">
                    <img src="https://cdn-icons-png.flaticon.com/128/11306/11306480.png" alt="Filling Icon" class="w-12 h-12">
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2 mt-4 text-center">Simple Amalgam/Composite Filling</h3>
                <p class="text-gray-600 text-center text-sm">Restoration of a single tooth to repair minor decay or damage with tooth-colored material.</p>
            </div>

            <div class="bg-yellow-400 p-6 md:p-8 rounded-2xl shadow-xl transition transform hover:shadow-2xl hover:-translate-y-1 border border-gray-100">
                <div class="w-16 h-16 mb-4 flex items-center justify-center rounded-xl bg-red-100 border border-red-200 mx-auto">
                    <img src="https://cdn-icons-png.flaticon.com/128/11377/11377162.png" alt="Extraction Icon" class="w-12 h-12">
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2 mt-4 text-center">Simple Tooth Extraction</h3>
                <p class="text-gray-600 text-center text-sm">Gentle removal of a single, non-surgical tooth that cannot be saved.</p>
            </div>

            <div class="bg-pink-400 p-6 md:p-8 rounded-2xl shadow-xl transition transform hover:shadow-2xl hover:-translate-y-1 border border-gray-100">
                <div class="w-16 h-16 mb-4 flex items-center justify-center rounded-xl bg-yellow-100 border border-yellow-200 mx-auto">
                    <img src="https://cdn-icons-png.flaticon.com/128/11377/11377181.png" alt="Root Canal Icon" class="w-12 h-12">
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2 mt-4 text-center">Root Canal Treatment (Anterior)</h3>
                <p class="text-gray-600 text-center text-sm">Treatment to save an infected or damaged front tooth by removing the nerve and pulp.</p>
            </div>

        </div>

        <div class="mt-16 text-center">
            <?php if ($is_logged_in): ?>
                <a href="<?= site_url('/book') ?>" class="inline-block px-10 py-4 text-xl font-bold rounded-xl bg-teal-600 text-white shadow-xl hover:bg-teal-700 transition duration-300 transform hover:scale-105">
                    Schedule Your Visit Now
                </a>
                <p class="text-base text-gray-500 mt-3">You are logged in as <?= html_escape($username) ?>.</p>
            <?php else: ?>
                <a href="<?= site_url('login') ?>" class="inline-block px-10 py-4 text-xl font-bold rounded-xl bg-blue-600 text-white shadow-xl hover:bg-blue-700 transition duration-300 transform hover:scale-105">
                    Login to Book Appointment
                </a>
                <p class="text-base text-gray-500 mt-3">New patients, please register first to access online booking.</p>
            <?php endif; ?>
        </div>
    </section>

    <section id="contacts" class="bg-gray-50 flex flex-col justify-center items-center p-8 py-24">
        <div class="max-w-7xl w-full">

            <div class="text-center mb-16">
                <p class="text-xl text-teal-600 font-semibold mb-2">Get In Touch</p>
                <h2 class="text-4xl md:text-5xl font-extrabold text-gray-800">Contact Us</h2>
                <p class="text-xl text-gray-600 mt-3">We're here to answer your questions. Get in touch via phone or visit our clinic.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">

                <div class="bg-white p-10 rounded-2xl shadow-xl border-t-4 border-teal-500 space-y-8">
                    <h3 class="text-3xl font-bold text-gray-800 mb-6">Clinic Information</h3>

                    <div class="flex items-start">
                        <div class="w-12 h-12 flex items-center justify-center rounded-full bg-blue-100 text-blue-600 flex-shrink-0 mr-5">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.717 21 3 14.283 3 6V5z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xl font-semibold text-gray-800">Call Us</h4>
                            <p class="text-lg text-blue-600 font-semibold font-mono">(555) DENT-CARE</p>
                            <p class="text-sm text-gray-500">For immediate assistance.</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="w-12 h-12 flex items-center justify-center rounded-full bg-teal-100 text-teal-600 flex-shrink-0 mr-5">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xl font-semibold text-gray-800">Location</h4>
                            <p class="text-gray-600 text-lg">Naujan, Oriental Mindoro, 5204</p>
                            <a href="#location-map" class="text-sm text-blue-500 hover:text-blue-700 font-medium">View on Map</a>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="w-12 h-12 flex items-center justify-center rounded-full bg-pink-100 text-pink-600 flex-shrink-0 mr-5">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xl font-semibold text-gray-800">Hours</h4>
                            <p class="text-gray-600">Mon - Fri: 8:00 AM - 5:00 PM</p>
                            <p class="text-gray-600">Sat: 9:00 AM - 1:00 PM</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-10 rounded-2xl shadow-xl border-t-4 border-blue-600">
                    <h3 class="text-3xl font-bold text-gray-800 mb-6">Send Us a Message</h3>
                    <form action="#" method="POST" class="space-y-5">
                        <div>
                            <label for="contact-name" class="block text-sm font-medium text-gray-700">Full Name</label>
                            <input type="text" name="name" id="contact-name" required class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-teal-500 focus:border-teal-500 transition">
                        </div>

                        <div>
                            <label for="contact-email" class="block text-sm font-medium text-gray-700">Email Address</label>
                            <input type="email" name="email" id="contact-email" required class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-teal-500 focus:border-teal-500 transition">
                        </div>

                        <div>
                            <label for="contact-message" class="block text-sm font-medium text-gray-700">Message</label>
                            <textarea name="message" id="contact-message" rows="5" required class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-teal-500 focus:border-teal-500 transition"></textarea>
                        </div>

                        <div>
                            <button typeG="submit" class="w-full inline-block px-10 py-3 text-lg font-bold rounded-lg bg-blue-600 text-white shadow-xl hover:bg-blue-700 transition duration-300 transform hover:scale-[1.01]">
                                Send an Email
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div id="location-map" class="pt-24">
                <h2 class="text-4xl font-bold text-gray-800 mb-6 border-b-2 border-gray-300 pb-2">
                    Our Location
                </h2>
                <p class="text-gray-700 text-lg mb-8">
                    Find us easily! Our clinic is located in Naujan, Oriental Mindoro, easily accessible by public transport.
                </p>

                <div id="clinicMap" class="mb-10"></div>
            </div>

        </div>
    </section>

    <footer class="bg-blue-700 text-white pt-16 pb-8" id="contact-footer">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 md:grid-cols-4 gap-10 border-b border-gray-200 pb-10">

                <div class="space-y-4">
                    <h3 class="text-2xl font-bold ">DENTALCARE</h3>
                    <p class="text-sm text-gray-300">
                        Committed to providing personalized and high-quality dental care in a comfortable and welcoming environment.
                    </p>
                    <div class="flex items-center space-x-2 text-sm text-gray-300">
                        <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.717 21 3 14.283 3 6V5z"></path>
                        </svg>
                        <span class="font-mono">0963-405-5941 DENTALCARE</span>
                    </div>
                </div>

                <div>
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-3 text-sm">
                        <li><a href="#about" class="text-gray-300 hover:text-white transition">About Us</a></li>
                        <li><a href="#services" class="text-gray-300 hover:text-white transition">Our Services</a></li>
                        <li><a href="#location-map" class="text-gray-300 hover:text-white transition">Find Us</a></li>
                        <li><a href="<?= site_url('/book') ?>" class="text-gray-300 hover:text-white transition">Book</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-lg font-semibold mb-4">Patient Center</h4>
                    <ul class="space-y-3 text-sm">
                        <li><a href="<?= site_url('/book') ?>" class="text-gray-300 hover:text-white transition">Book Appointment</a></li>
                        <li><a href="<?= site_url('login') ?>" class="text-gray-300 hover:text-white transition">Patient Login</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition">FAQs</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-white transition">Privacy Policy</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-lg font-semibold mb-4">Location & Hours</h4>
                    <address class="space-y-3 text-sm not-italic">
                        <p class="text-gray-300">Naujan, Oriental Mindoro, 5204</p>
                        <p class="text-gray-300">Mon - Fri: 8:00 AM - 5:00 PM</p>
                        <p class="text-gray-300">Sat: 9:00 AM - 1:00 PM</p>
                    </address>
                </div>
            </div>

            <div class="mt-8 pt-4 flex flex-col md:flex-row justify-between items-center text-sm text-gray-300">
                <p>&copy; 2025 DENTALCARE. All rights reserved.</p>
                <div class="flex space-x-4 mt-4 md:mt-0">
                    <a href="#" class="text-gray-400 hover:text-teal-400 transition">
                        <img src="https://cdn-icons-png.flaticon.com/128/174/174855.png" alt="Instagram Icon" class="w-6 h-6 rounded-md">
                    </a>
                    <a href="#" class="text-gray-400 hover:text-teal-400 transition">
                        <img src="https://cdn-icons-png.flaticon.com/128/5968/5968764.png" alt="Facebook Icon" class="w-6 h-6 rounded-md">
                    </a>
                    <a href="#" class="text-gray-400 hover:text-teal-400 transition">
                        <img src="https://cdn-icons-png.flaticon.com/128/5968/5968830.png" alt="Twitter/X Icon" class="w-6 h-6 rounded-md">
                    </a>
                </div>
            </div>
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

            // Updated mapContainer ID to match the merged section's ID
            const mapContainer = document.getElementById('contacts');
            const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        map.invalidateSize();
                        // Changed observer target to match the updated mapContainer variable
                        observer.unobserve(mapContainer);
                    }
                });
            }, {
                rootMargin: '100px 0px'
            });

            // Observe the map container
            observer.observe(mapContainer);

            // Fallback timeout
            setTimeout(function() {
                map.invalidateSize();
            }, 500);
        });
    </script>
</body>

</html>