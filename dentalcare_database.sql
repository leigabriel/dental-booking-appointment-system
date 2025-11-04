DROP DATABASE IF EXISTS dentalcare_database;
CREATE DATABASE dentalcare_database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dentalcare_database;

CREATE TABLE users (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at DATETIME DEFAULT NULL,
    password VARCHAR(255) NULL,
    role ENUM('admin', 'staff', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE doctors (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    specialty VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_specialty (specialty)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE services (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    duration_mins INT(11) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE appointments (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) UNSIGNED NOT NULL,
    doctor_id INT(11) UNSIGNED NOT NULL,
    service_id INT(11) UNSIGNED NOT NULL,
    appointment_date DATE NOT NULL,
    time_slot TIME NOT NULL,
    payment_method ENUM('gcash', 'paypal', 'clinic') DEFAULT 'clinic',
    payment_status ENUM('unpaid', 'pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_reference VARCHAR(255) NULL,
    paid_at TIMESTAMP NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'declined') NOT NULL DEFAULT 'pending',
    decline_message TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE RESTRICT,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE RESTRICT,
    
    UNIQUE KEY unique_booking (doctor_id, appointment_date, time_slot),
    INDEX idx_user_id (user_id),
    INDEX idx_doctor_id (doctor_id),
    INDEX idx_service_id (service_id),
    INDEX idx_appointment_date (appointment_date),
    INDEX idx_status (status),
    INDEX idx_payment_status (payment_status),
    INDEX idx_payment_method (payment_method)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO doctors (name, specialty, email) VALUES 
('Dr. Michael Anderson', 'General Dentistry', 'dr.anderson@dentalcare.com'),
('Dr. Sarah Thompson', 'Orthodontics', 'dr.thompson@dentalcare.com'),
('Dr. James Rodriguez', 'Oral and Maxillofacial Surgery', 'dr.rodriguez@dentalcare.com'),
('Dr. Emily Chen', 'Pediatric Dentistry', 'dr.chen@dentalcare.com'),
('Dr. Robert Martinez', 'Periodontics', 'dr.martinez@dentalcare.com'),
('Dr. Jennifer Wilson', 'Endodontics', 'dr.wilson@dentalcare.com'),
('Dr. David Lee', 'Prosthodontics', 'dr.lee@dentalcare.com'),
('Dr. Maria Garcia', 'Cosmetic Dentistry', 'dr.garcia@dentalcare.com'),
('Dr. Christopher Brown', 'Oral Pathology', 'dr.brown@dentalcare.com'),
('Dr. Amanda Taylor', 'Dental Implantology', 'dr.taylor@dentalcare.com');

INSERT INTO services (name, price, duration_mins) VALUES 
('Dental Cleaning and Prophylaxis', 1500.00, 30),
('Tooth Extraction (Simple)', 2500.00, 30),
('Tooth Extraction (Surgical)', 5000.00, 60),
('Dental Filling (Composite)', 2000.00, 45),
('Root Canal Treatment', 8000.00, 90),
('Teeth Whitening', 6000.00, 60),
('Dental Crown Installation', 12000.00, 120),
('Braces Installation (Metal)', 35000.00, 90),
('Dental Implant', 45000.00, 120),
('Oral Surgery Consultation', 1000.00, 30);

INSERT INTO `users` (`id`, `username`, `full_name`, `email`, `email_verified_at`, `password`, `role`, `created_at`) VALUES
-- example admin account
-- username/email: admin or admin@dentalcare.com
-- password: admin@2025
(1, 'admin', 'Administrator', 'admin@dentalcare.com', NULL, '$2y$10$o01xgUutpzACMlQCLAqbruPigWaQi7RkwLH1W7vZW/uVPmZEOFyG6', 'admin', '2025-11-04 10:36:34');