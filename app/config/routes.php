<?php
defined('PREVENT_DIRECT_ACCESS') or exit('No direct script access allowed');
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

 $router->get('/', 'Welcome::index');

/*/ User Landing Page
$router->get('/', function () {
  lava_instance()->call->view('user_landing');
});*/

// Authentication Routes
$router->get('/login', 'Auth::login');
$router->post('/login/submit', 'Auth::login_submit');
$router->get('/register', 'Auth::register');
$router->post('/register/submit', 'Auth::register_submit');
$router->get('/logout', 'Auth::logout');

// APPOINTMENT BOOKING ROUTES
$router->get('/book', 'Booking::index');
$router->post('/book/submit', 'Booking::submit');

// ADMIN MANAGEMENT ROUTES

// NEW: Redirect base /management to appointments
$router->get('/management', function () {
  redirect('management/appointments');
});

// Admin/Staff Management
$router->post('/admin/admin_staff_add_update', 'Admin::admin_staff_add_update');
$router->post('/admin/admin_staff_add_update/{id}', 'Admin::admin_staff_add_update')
  ->where_number('id');
$router->get('/admin/admin_staff_delete/{id}', 'Admin::admin_staff_delete')
  ->where_number('id');

// Appointments Overview
$router->get('/management/appointments', 'Management::appointments');
$router->get('/management/appointment_confirm/{id}', 'Management::appointment_confirm')
  ->where_number('id');
$router->get('/management/appointment_cancel/{id}', 'Management::appointment_cancel')
  ->where_number('id');

// Doctor Management
$router->get('/management/doctors', 'Management::doctors');
$router->post('/management/doctor_add_update', 'Management::doctor_add_update');
$router->get('/management/doctor_edit/{id}', 'Management::doctor_edit')
  ->where_number('id');
$router->post('/management/doctor_add_update/{id}', 'Management::doctor_add_update')
  ->where_number('id');
$router->get('/management/doctor_delete/{id}', 'Management::doctor_delete')
  ->where_number('id');

// Service Management
$router->get('/management/services', 'Management::services');
$router->post('/management/service_add_update', 'Management::service_add_update');
$router->get('/management/service_edit/{id}', 'Management::service_edit')
  ->where_number('id');
$router->post('/management/service_add_update/{id}', 'Management::service_add_update')
  ->where_number('id');
$router->get('/management/service_delete/{id}', 'Management::service_delete')
  ->where_number('id');

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

// Staff Dashboard Access
$router->get('/staff/dashboard', function () {
  $LAVA = lava_instance();
  $LAVA->call->helper('url');
  $LAVA->call->library('session');

  $role = $LAVA->session->userdata('role');
  if ($role === 'admin' || $role === 'staff') {
    $LAVA->call->controller('Staff', 'dashboard');
  } else {
    redirect('login');
  }
});

// User Profile Management Routes
$router->get('/profile', 'Auth::profile');
$router->post('/profile/update', 'Auth::profile_edit_submit');
$router->get('/profile/delete', 'Auth::profile_delete');

// APPOINTMENTS
$router->get('/book', 'Booking::index');
$router->post('/book/submit', 'Booking::submit');