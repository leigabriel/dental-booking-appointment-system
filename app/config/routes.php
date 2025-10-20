<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');
/**
 * ------------------------------------------------------------------
 * LavaLust - an opensource lightweight PHP MVC Framework
 * ------------------------------------------------------------------
 *
 * MIT License
 *
 * Copyright (c) 2020 Ronald M. Marasigan
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package LavaLust
 * @author Ronald M. Marasigan <ronald.marasigan@yahoo.com>
 * @since Version 1
 * @link https://github.com/ronmarasigan/LavaLust
 * @license https://opensource.org/licenses/MIT MIT License
 */

/*
| -------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------
| Here is where you can register web routes for your application.
|
|
*/

// $router->get('/', 'Welcome::index');

// Authentication Routes
$router->get('/login', 'Auth::login');
$router->post('/login/submit', 'Auth::login_submit');

$router->get('/register', 'Auth::register');
$router->post('/register/submit', 'Auth::register_submit');

$router->get('/logout', 'Auth::logout');

// --- APPOINTMENT BOOKING ROUTES ---
$router->get('/book', 'Booking::index');
$router->post('/book/submit', 'Booking::submit');

// -----------------------------------------------------------------
// --- ADMIN MANAGEMENT ROUTES (Handled by Management Controller) ---
// -----------------------------------------------------------------

// 1. Appointments Overview (View all bookings)
$router->get('/management/appointments', 'Management::appointments');

// NEW: Appointment Actions
$router->get('/management/appointment_confirm/(:num)', 'Management::appointment_confirm/$1')
  ->where_number('num');
$router->get('/management/appointment_cancel/(:num)', 'Management::appointment_cancel/$1')
  ->where_number('num');
  
// 2. Doctor CRUD
$router->get('/management/doctors', 'Management::doctors'); // Read: List all doctors
$router->match('/management/doctor_add_update', 'Management::doctor_add_update', 'GET|POST'); // Add new doctor
$router->match('/management/doctor_add_update/(:num)', 'Management::doctor_add_update/$1', 'GET|POST') // Update existing doctor
  ->where_number('num');
$router->get('/management/doctor_delete/(:num)', 'Management::doctor_delete/$1') // Delete doctor
  ->where_number('num');

// 3. Service CRUD
$router->get('/management/services', 'Management::services'); // Read: List all services
$router->match('/management/service_add_update', 'Management::service_add_update', 'GET|POST'); // Add new service
$router->match('/management/service_add_update/(:num)', 'Management::service_add_update/$1', 'GET|POST') // Update existing service
  ->where_number('num');
$router->get('/management/service_delete/(:num)', 'Management::service_delete/$1') // Delete service
  ->where_number('num');

// -----------------------------------------------------------------
// --- DASHBOARD ACCESS (Requires Authorization) ---
// -----------------------------------------------------------------

// Admin Dashboard Access
$router->get('/admin/dashboard', function () {
  $LAVA = lava_instance();
  $LAVA->call->helper('url');
  $LAVA->call->library('session');

  if ($LAVA->session->userdata('role') === 'admin') {
    $LAVA->call->controller('Admin', 'dashboard');
  } else {
    redirect('login');
  }
});

// Staff: Limited Access
$router->get('/staff/dashboard', function () {
  $LAVA = lava_instance();
  $LAVA->call->helper('url');
  $LAVA->call->library('session');

  $role = $LAVA->session->userdata('role');
  if ($role === 'admin' || $role === 'staff') {
    // This calls the controller that fetches the data.
    $LAVA->call->controller('Staff', 'dashboard');
  } else {
    redirect('login');
  }
});

// User Landing Page
// This is the public landing page for users.
$router->get('/', function () {
  lava_instance()->call->view('user_landing');
});

// User Profile Management Routes
$router->get('/profile', 'Auth::profile');
$router->post('/profile/update', 'Auth::profile_edit_submit');
$router->get('/profile/delete', 'Auth::profile_delete');

// --- NEW FEATURE ROUTES: APPOINTMENTS ---
$router->get('/book', 'Booking::index');
$router->post('/book/submit', 'Booking::submit');