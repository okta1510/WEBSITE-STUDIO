-- Manual Database Creation Script
-- Copy and paste this entire script into phpMyAdmin SQL tab

-- Create Database
CREATE DATABASE IF NOT EXISTS studio_foto_cekrek;
USE studio_foto_cekrek;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('customer', 'admin', 'photographer') DEFAULT 'customer',
    profile_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

-- Photo Packages Table
CREATE TABLE IF NOT EXISTS packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    duration_hours INT NOT NULL,
    max_photos INT,
    includes TEXT,
    category ENUM('wedding', 'portrait', 'event', 'family', 'corporate') NOT NULL,
    image VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Reservations Table
CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    package_id INT NOT NULL,
    photographer_id INT,
    reservation_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    location VARCHAR(255),
    special_requests TEXT,
    total_amount DECIMAL(10,2) NOT NULL,
    number_of_people INT DEFAULT 1,
    status ENUM('pending', 'confirmed', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('unpaid', 'partial', 'paid', 'refunded') DEFAULT 'unpaid',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE RESTRICT,
    FOREIGN KEY (photographer_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Payments Table
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'bank_transfer', 'credit_card', 'e_wallet') NOT NULL,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    transaction_id VARCHAR(100),
    notes TEXT,
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE CASCADE
);

-- Gallery Table
CREATE TABLE IF NOT EXISTS gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    image_path VARCHAR(255) NOT NULL,
    category ENUM('wedding', 'portrait', 'event', 'family', 'corporate') NOT NULL,
    photographer_id INT,
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (photographer_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Schedule Table
CREATE TABLE IF NOT EXISTS schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    photographer_id INT NOT NULL,
    date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    is_available BOOLEAN DEFAULT TRUE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (photographer_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_photographer_datetime (photographer_id, date, start_time)
);

-- Settings Table
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);



-- Insert Default Admin User (password: password)
INSERT INTO users (username, email, password, full_name, role) VALUES 
('admin', 'admin@studiofotocekrek.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin');

-- Insert Sample Packages
INSERT INTO packages (name, description, price, duration_hours, max_photos, includes, category) VALUES 
('Wedding Basic', 'Paket foto pernikahan dasar dengan 4 jam sesi foto', 2500000, 4, 100, 'Foto digital, 1 album, makeup artist', 'wedding'),
('Wedding Premium', 'Paket foto pernikahan premium dengan 8 jam sesi foto', 5000000, 8, 300, 'Foto digital, 2 album, makeup artist, video highlight', 'wedding'),
('Portrait Session', 'Sesi foto portrait profesional', 500000, 2, 50, 'Foto digital, editing profesional', 'portrait'),
('Family Package', 'Paket foto keluarga', 750000, 2, 75, 'Foto digital, 1 album mini', 'family'),
('Corporate Event', 'Dokumentasi acara perusahaan', 1500000, 4, 200, 'Foto digital, dokumentasi lengkap', 'corporate');

-- Insert Default Settings
INSERT INTO settings (setting_key, setting_value, description) VALUES 
('studio_name', 'Studio Foto Cekrek', 'Nama studio foto'),
('studio_address', 'Jl. Contoh No. 123, Jakarta', 'Alamat studio'),
('studio_phone', '021-12345678', 'Nomor telepon studio'),
('studio_email', 'info@studiofotocekrek.com', 'Email studio'),
('booking_advance_days', '7', 'Minimal hari booking di muka'),
('working_hours_start', '08:00', 'Jam buka studio'),
('working_hours_end', '21:00', 'Jam tutup studio');
