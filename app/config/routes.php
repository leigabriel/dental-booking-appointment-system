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

// --- Authentication Routes ---
$router->get('/login', 'Auth::login');
$router->post('/login/submit', 'Auth::login_submit');

$router->get('/register', 'Auth::register');
$router->post('/register/submit', 'Auth::register_submit');

$router->get('/logout', 'Auth::logout');

// --- Role-Based Dashboards (Protected) ---
// Admin: Full Access (only role 'admin' can view)
$router->get('/admin/dashboard', function () {
  $LAVA = lava_instance();
  // Must load helper and library inside closure if not globally autoloaded
  $LAVA->call->helper('url');
  $LAVA->call->library('session');

  if ($LAVA->session->userdata('role') === 'admin') {
    // Forward the request to the dedicated Admin Controller method
    $LAVA->call->controller('Admin', 'dashboard');
  } else {
    redirect('login');
  }
});

// Staff: Limited Access (roles 'admin' OR 'staff' can view)
$router->get('/staff/dashboard', function () {
  $role = lava_instance()->session->userdata('role');
  if ($role === 'admin' || $role === 'staff') {
    lava_instance()->call->view('staff/dashboard');
  } else {
    redirect('login');
  }
});

// --- User Landing Page / Default Route ---
// This is the public landing page for patients/users.
$router->get('/', function () {
  lava_instance()->call->view('user_landing');
});

// You can safely remove the original: $router->get('/', 'Welcome::index');