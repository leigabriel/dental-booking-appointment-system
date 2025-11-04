# DENTALCARE - Dental Booking & Appointment System

<p align="center">
    <img src="./public/img/dentalcare-health.png" alt="Dentalcare App Icon" height="400" width="800"/>
</p>

<p align="center">
    <strong>A comprehensive web-based dental clinic management system with integrated payment processing and real-time appointment scheduling</strong>
</p>

---

## Table of Contents

- [Introduction](#-introduction)
- [Features](#-features)
- [Technology Stack](#️-technology-stack)
- [Framework](#-framework)
- [CDN & Libraries](#-cdn--libraries)
- [Integrations](#-integrations)
- [Installation](#-installation)
- [License](#-license)

---

## Introduction

**DENTALCARE** is a modern, full-featured dental clinic management system built with PHP and the LavaLust MVC Framework. The system streamlines appointment scheduling, patient management, and payment processing with an intuitive interface designed for dental clinics of all sizes.

### Why DENTALCARE?

- **Role-Based Access Control** - Separate interfaces for Patients, Staff, and Administrators
- **Real-Time Booking** - Prevent double-bookings with live time slot validation
- **Integrated Payments** - Support for GCash, PayPal, and Pay at Clinic options
- **Responsive Design** - Modern UI built with Tailwind CSS
- **Interactive Maps** - Leaflet.js integration for clinic location display
- **Booking Restrictions** - Smart daily limits (5 appointments/day) and same-time prevention

---

## Features

### Authentication & User Management
- **Secure Login System** - Username or email-based authentication
- **User Registration** - New patient account creation with validation
- **Role Management** - Admin, Staff, and Patient roles with distinct permissions
- **Profile Management** - Users can update personal information and view appointment history

### Appointment Booking System
- **Card-Based Service Selection** - Interactive, horizontal-scrollable service/doctor cards
- **Real-Time Availability** - Live time slot checking to prevent double-bookings
- **Daily Booking Limits** - Maximum 5 appointments per day per user
- **Smart Time Blocking** - Automatically disable booked time slots for selected doctors
- **Appointment Receipt Cards** - Visual confirmation with receipt-style design
- **Booking History** - Complete appointment history with clear all functionality

### Payment Processing
- **Multiple Payment Methods:**
  - **GCash** - PayMongo API integration (sandbox & production)
  - **PayPal** - PayPal REST API integration (sandbox & production)
  - **Pay at Clinic** - On-site payment option
- **Payment Status Tracking** - Real-time payment status (Paid, Unpaid, Pending)
- **Payment References** - Automatic reference number generation
- **Sandbox Testing** - Complete sandbox environment for payment testing

### Doctor Management (Admin)
- **CRUD Operations** - Create, Read, Update, Delete doctors
- **Doctor Profiles** - Name, specialization, availability, contact information
- **Image Upload** - Doctor profile pictures
- **Service Assignment** - Link doctors to specific services

### Service Management (Admin)
- **CRUD Operations** - Full service management capabilities
- **Service Details** - Name, description, duration, pricing
- **Image Gallery** - Service illustrations and photos
- **Service Categories** - Organize services by type

### Admin Dashboard
- **Statistics Overview** - Total appointments, patients, doctors, services
- **Chart Visualizations** - Chart.js powered analytics
- **Appointment Management** - Confirm, decline, or cancel appointments
- **User Management** - Create Admin/Staff accounts, manage user roles
- **Payment Monitoring** - View payment methods and statuses

### Staff Dashboard
- **Appointment Monitoring** - Real-time appointment tracking
- **Booking Actions** - Confirm or cancel patient appointments
- **Patient List** - View all registered patients
- **Read-Only Access** - View doctors and services (no edit permissions)

### Patient Features
- **User Profile** - Personal information management
- **Appointment Booking** - Easy-to-use booking interface
- **Receipt Cards** - Confirmed appointments displayed as visual receipts
- **Booking History Table** - Tabular view of all appointments
- **Clear History** - Remove completed/cancelled appointments
- **Payment Display** - View payment method and status for each booking

### UI/UX Enhancements
- **Modern Design** - Gradient backgrounds, smooth animations, shadow effects
- **Horizontal Scrolling** - Snap-scroll service/doctor card selection
- **Receipt-Style Cards** - Tear-line effects using radial gradients
- **SVG Icons** - Professional payment method logos (no emojis)
- **Responsive Layout** - Mobile-first design approach
- **Modal Dialogs** - Confirmation modals for critical actions
- **Status Badges** - Color-coded status indicators
- **Interactive Elements** - Hover effects, active states, transitions

---

## Technology Stack

| Component | Technology | Version/Details |
|-----------|-----------|----------------|
| **Backend Language** | PHP | 7.4+ |
| **Framework** | LavaLust MVC | v4.4.0 |
| **Database** | MySQL/MariaDB | 5.7+ / 10.3+ |
| **Frontend CSS** | Tailwind CSS | v3.x (CDN) |
| **JavaScript** | Vanilla JS | ES6+ |
| **Charts** | Chart.js | v4.4.1 |
| **Maps** | Leaflet.js | v1.9+ |
| **Dependency Manager** | Composer | v2.x |

### Database Schema

The system uses four primary tables:

1. **`users`** - Patient, staff, and admin accounts
2. **`doctors`** - Doctor profiles and information
3. **`services`** - Dental services offered
4. **`appointments`** - Booking records with payment details

**Payment Columns Added:**
- `payment_method` - ENUM('gcash', 'paypal', 'clinic')
- `payment_status` - ENUM('paid', 'unpaid', 'pending')
- `payment_reference` - VARCHAR(255)
- `paid_at` - DATETIME

---

## 🚀 Framework

### LavaLust PHP MVC Framework (v4.4.0)

<p align="center">
    <img width="200" height="300" src="https://lavalust.netlify.app/_images/logo.png">
</p>

**LavaLust** is a lightweight Web Framework using the MVC (Model-View-Controller) pattern, designed for developers who need a simple yet powerful toolkit for building full-featured web applications with PHP.

#### Key Features:
- **MVC Architecture** - Clean separation of concerns
- **Object-Oriented Approach** - Modern PHP coding standards
- **Built-in Routing** - Flexible URL routing system
- **Database Abstraction** - Easy database operations with LAVA_Query_Builder
- **Security Features** - XSS filtering, CSRF protection, input validation
- **Helper Functions** - Reduce repetitive coding tasks
- **Session Management** - Secure session handling
- **Form Validation** - Built-in validation rules
- **File Upload** - Secure file upload handling

#### Documentation
📚 [LavaLust Official Documentation](https://lavalust.netlify.app)

> **Note:** If using PLDT internet, you may need to use Google DNS (8.8.8.8) to access the Netlify-hosted documentation.

#### Framework Structure in This Project:

```
app/
├── config/          # Configuration files (database, routes, etc.)
├── controllers/     # Business logic (Auth, Booking, Payment, etc.)
├── models/          # Database models (User, Appointment, Doctor, Service)
├── views/           # HTML templates (admin, auth, booking, staff)
├── helpers/         # Custom helper functions
└── libraries/       # Custom libraries

scheme/              # LavaLust core files
├── database/        # Database drivers
├── kernel/          # Core framework classes
├── libraries/       # Framework libraries
└── helpers/         # Framework helper functions
```

---

## 📦 CDN & Libraries

### CSS Frameworks & Styling

| Library | Version | Purpose | CDN Link |
|---------|---------|---------|----------|
| **Tailwind CSS** | v3.x | Utility-first CSS framework | `https://cdn.tailwindcss.com` |
| **Tailwind Browser** | v4 | Browser-based Tailwind | `https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4` |
| **Tailwind+ Elements** | v1 | Extended Tailwind components | `https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1` |
| **Leaflet CSS** | v1.9+ | Map styling | Local `/public/dist/leaflet.css` |

### JavaScript Libraries

| Library | Version | Purpose | CDN Link |
|---------|---------|---------|----------|
| **Chart.js** | v4.4.1 | Data visualization & charts | `https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js` |
| **Leaflet.js** | v1.9+ | Interactive maps | Local `/public/dist/leaflet.js` |
| **Elfsight Platform** | Latest | Widget platform | `https://elfsightcdn.com/platform.js` |

### PHP Libraries (Composer)

| Package | Purpose |
|---------|---------|
| **firebase/php-jwt** | JWT token handling |
| **google/apiclient** | Google API integration |
| **guzzlehttp/guzzle** | HTTP client for API requests |
| **monolog/monolog** | Logging library |
| **phpseclib/phpseclib** | Security library |
| **vlucas/phpdotenv** | Environment variable management |

---

## 🔗 Integrations

### PayMongo API (GCash)

**Purpose:** Process GCash payments via PayMongo's Source API

**Integration Type:** REST API

**Endpoints Used:**
- `POST /v1/sources` - Create payment source
- Webhook callbacks for payment confirmation

**Features:**
- Sandbox testing environment
- Automatic redirect to GCash payment page
- Success/failure callback handling
- Payment reference tracking

**Setup:**
1. Create account at [PayMongo Dashboard](https://dashboard.paymongo.com/)
2. Get API keys (Public & Secret Key)
3. Configure in `app/controllers/Payment.php`
4. Test with sandbox credentials

**Documentation:** [PayMongo API Docs](https://developers.paymongo.com/docs)

---

### PayPal REST API

**Purpose:** Process PayPal payments

**Integration Type:** REST API (v2)

**Endpoints Used:**
- `POST /v2/checkout/orders` - Create order
- `POST /v2/checkout/orders/{id}/capture` - Capture payment

**Features:**
- Sandbox testing environment
- OAuth 2.0 authentication
- Automatic order creation
- Payment capture on approval

**Setup:**
1. Create account at [PayPal Developer](https://developer.paypal.com/)
2. Create sandbox application
3. Get Client ID & Secret
4. Configure in `app/controllers/Payment.php`
5. Test with sandbox accounts

**Documentation:** [PayPal REST API Reference](https://developer.paypal.com/api/rest/)

---

### Leaflet.js Mapping

**Purpose:** Display clinic location on landing page

**Integration Type:** JavaScript Library

**Features:**
- Interactive map display
- Custom marker for clinic location
- Zoom controls
- Pan functionality

**Implementation:**
- Loaded locally from `/public/dist/`
- Configured in `app/views/user_landing.php`
- Custom marker icons included

---

### Chart.js Analytics

**Purpose:** Visualize appointment statistics on admin dashboard

**Integration Type:** JavaScript Library (CDN)

**Chart Types Used:**
- Bar charts - Appointment counts
- Line charts - Trends over time
- Pie charts - Service distribution

**Features:**
- Responsive charts
- Interactive tooltips
- Color-coded datasets
- Real-time data rendering

---

### Tailwind CSS Framework

**Purpose:** Utility-first CSS styling

**Integration Type:** CDN

**Features Used:**
- Flexbox utilities
- Grid system
- Color palettes
- Spacing utilities
- Responsive breakpoints
- Gradient backgrounds
- Shadow effects
- Transition animations

**Custom Configuration:**
- Badge components
- Card layouts
- Modal dialogs
- Button variants
---

## Installation

### Prerequisites

- **PHP 7.4+** with extensions: PDO, MySQLi, JSON, MBString, OpenSSL
- **MySQL 5.7+** or **MariaDB 10.3+**
- **Composer** for dependency management
- **Web Server** (Apache/Nginx)
- **Git** for cloning repository

### Step-by-Step Installation

1. **Clone the Repository**
   ```bash
   git clone https://github.com/leigabriel/dental-booking-appointment-system.git
   cd dental-booking-appointment-system
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Database Setup**
   - Create a new MySQL database:
     ```sql
     CREATE DATABASE dentalcare_db;
     ```
   - Import the database schema:
     ```bash
     mysql -u root -p dentalcare_db < dentalcare_database.sql
     ```
   - Run payment migration (if not already in main SQL):
     ```bash
     mysql -u root -p dentalcare_db < migration_add_payment_columns.sql
     ```

4. **Configure Database Connection**
   - Open `app/config/database.php`
   - Update credentials:
     ```php
     $config['hostname'] = 'localhost';
     $config['username'] = 'your_username';
     $config['password'] = 'your_password';
     $config['database'] = 'dentalcare_db';
     ```

5. **Configure Payment APIs** (Optional - for payment features)
   - **PayMongo (GCash):**
     - Sign up at [PayMongo Dashboard](https://dashboard.paymongo.com/)
     - Get API keys
     - Update in `app/controllers/Payment.php`:
       ```php
       private $paymongo_secret_key = 'sk_test_your_secret_key';
       private $paymongo_public_key = 'pk_test_your_public_key';
       ```
   
   - **PayPal:**
     - Sign up at [PayPal Developer](https://developer.paypal.com/)
     - Create sandbox app
     - Update in `app/controllers/Payment.php`:
       ```php
       private $paypal_client_id = 'your_client_id';
       private $paypal_secret = 'your_secret';
       ```

6. **Set Base URL**
   - Open `app/config/config.php`
   - Set your base URL:
     ```php
     $config['base_url'] = 'http://localhost/dental-booking-appointment-system/';
     ```

7. **Set Permissions** (Linux/Mac)
   ```bash
   chmod -R 755 runtime/
   chmod -R 777 runtime/session/
   chmod -R 755 public/
   ```

8. **Start Development Server**
   - **Option 1 - PHP Built-in Server:**
     ```bash
     php -S localhost:8000
     ```
   - **Option 2 - Laragon/XAMPP/WAMP:**
     - Copy project to `htdocs` or `www` folder
     - Access via browser

9. **Access the Application**
   - Open browser: `http://localhost:8000` or your configured URL
   - Default admin credentials (if seeded):
     - Username: `admin`
     - Password: `admin123`

### Testing Payment Integration

1. **GCash (PayMongo Sandbox):**
   - Use test card: `4120 0000 0000 0007`
   - Any future expiry date
   - Any CVV

2. **PayPal Sandbox:**
   - Create test accounts in PayPal Developer Dashboard
   - Use sandbox credentials for testing

📖 **Full Setup Guide:** See `PAYMENT_SETUP_GUIDE.md` for detailed payment integration instructions.

---

## Usage

### User Roles & Access

| Role | Access Level | Features |
|------|-------------|----------|
| **Patient** | User Dashboard | Book appointments, manage profile, view history |
| **Staff** | Staff Dashboard | Monitor appointments, confirm/cancel bookings, view patients |
| **Admin** | Admin Dashboard | Full system control, user management, statistics |

### Key Workflows

**Patient Booking Flow:**
1. Register/Login → 2. Select Service → 3. Choose Doctor → 4. Pick Date/Time → 5. Select Payment → 6. Confirm Booking

**Admin Management Flow:**
1. Login → 2. View Dashboard Stats → 3. Manage Doctors/Services → 4. Review Appointments → 5. Create Staff Accounts

**Payment Processing Flow:**
1. Select Payment Method → 2. Redirect to Gateway → 3. Complete Payment → 4. Return to Site → 5. View Receipt

---

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## Changelog

See `changelog.txt` for version history and updates.

---

## Troubleshooting

**Common Issues:**

1. **Database Connection Failed**
   - Check credentials in `app/config/database.php`
   - Verify MySQL service is running
   - Ensure database exists

2. **Payment Not Working**
   - Verify API keys are correct
   - Check sandbox/production mode
   - Review error logs in `runtime/logs/`

3. **Session Errors**
   - Clear `runtime/session/` folder
   - Check folder permissions (777)

4. **Routing Issues**
   - Check `.htaccess` file exists
   - Verify `mod_rewrite` is enabled (Apache)
   - Set correct base URL in config

---

## Support

For issues, questions, or suggestions:
- **GitHub Issues:** [Create an Issue](https://github.com/leigabriel/dental-booking-appointment-system/issues)
- **Email:** Contact repository owner

---

## Acknowledgments

- **LavaLust Framework** by Ronald M. Marasigan
- **Tailwind CSS** for amazing utility classes
- **Chart.js** for beautiful charts
- **Leaflet.js** for interactive maps
- **PayMongo** for Philippine payment processing
- **PayPal** for international payment support

---

## Additional Resources

- [LavaLust Documentation](https://lavalust.netlify.app)
- [LavaLust YouTube Tutorials](https://youtube.com/ronmarasigan)
- [Tailwind CSS Docs](https://tailwindcss.com/docs)
- [Chart.js Docs](https://www.chartjs.org/docs/latest/)
- [Leaflet.js Docs](https://leafletjs.com/reference.html)
- [PayMongo API Docs](https://developers.paymongo.com/docs)
- [PayPal API Docs](https://developer.paypal.com/api/rest/)

---

## 📄 License

**MIT License**

Copyright (c) 2020 Ronald M. Marasigan (LavaLust Framework)  
Copyright (c) 2025 DENTALCARE Project Contributors

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

**THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.**

---

<p align="center">
    Made with ❤️ for dental clinics worldwide
</p>

<p align="center">
    <strong>⭐ Star this repository if you find it helpful!</strong>
</p>

