# DENTALCARE - Clinic Management System

This web application manages doctor appointments and patient records for a dental practice, built on the **LavaLust PHP MVC Framework (v4.4.0)**.

<p align="center">
    <img src="/favicon.ico" alt="Dentalcare App Icon" width="120"/>
</p>

## 🌟 Project Features

DENTALCARE features three core roles for streamlined operations:

* **Patient (`user`):** Secure login (username/email), schedule appointments for available Doctors/Services, and manage profile/bookings.
* **Staff (`staff`):** Dashboard for appointment monitoring, confirm/cancel bookings, and view all patient accounts. Read/Update access only for Doctors/Services.
* **Admin (`admin`):** Full dashboard statistics, complete CRUD control over Doctors and Services, and user account management (including Admin/Staff roles).

***

## 🛠️ Technology Stack

| Component | Technology | Details |
| :--- | :--- | :--- |
| **Backend** | PHP 7+ | Core language for the application logic. |
| **Framework** | **LavaLust MVC** (v4.4.0) | Provides MVC structure, routing, and database abstraction. |
| **Database** | MySQL | Data storage using `users`, `doctors`, `services`, and `appointments` tables. |
| **Frontend** | Tailwind CSS | Utility-first CSS framework for a modern, responsive interface. |
| **Mapping** | **Leaflet.js** | Used to display the clinic's location on the landing page. |

### Core Modules (Models & Controllers)

The application's business logic is structured around five primary database models and their corresponding controllers:

* **Authentication & Profile:** Handled by `Auth.php` and `UserModel.php`.
* **Appointment Booking:** Handled by `Booking.php` and `AppointmentModel.php`.
* **Management:** Handled by `Management.php`, `DoctorModel.php`, and `ServiceModel.php`.

***