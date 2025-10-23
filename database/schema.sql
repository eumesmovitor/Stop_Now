-- StopNow Database Schema

CREATE DATABASE IF NOT EXISTS stopnow CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE stopnow;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    cpf VARCHAR(14) UNIQUE,
    profile_image VARCHAR(255),
    is_verified BOOLEAN DEFAULT FALSE,
    verification_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB;

-- Parking spots table
CREATE TABLE IF NOT EXISTS parking_spots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    address VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(50) NOT NULL,
    zip_code VARCHAR(10) NOT NULL,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    price_daily DECIMAL(10, 2) NOT NULL,
    price_weekly DECIMAL(10, 2),
    price_monthly DECIMAL(10, 2),
    price_annual DECIMAL(10, 2),
    spot_type ENUM('covered', 'uncovered', 'garage', 'street') NOT NULL,
    is_covered BOOLEAN DEFAULT FALSE,
    has_security BOOLEAN DEFAULT FALSE,
    has_camera BOOLEAN DEFAULT FALSE,
    has_lighting BOOLEAN DEFAULT FALSE,
    has_electric_charging BOOLEAN DEFAULT FALSE,
    has_smart_lock BOOLEAN DEFAULT FALSE,
    smart_lock_type VARCHAR(50),
    max_height DECIMAL(5, 2),
    max_width DECIMAL(5, 2),
    status ENUM('active', 'inactive', 'occupied') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_owner (owner_id),
    INDEX idx_city (city),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- Parking spot images
CREATE TABLE IF NOT EXISTS spot_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    spot_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (spot_id) REFERENCES parking_spots(id) ON DELETE CASCADE,
    INDEX idx_spot (spot_id)
) ENGINE=InnoDB;

-- Bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    spot_id INT NOT NULL,
    renter_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_days INT NOT NULL,
    price_per_day DECIMAL(10, 2) NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    service_fee DECIMAL(10, 2) NOT NULL,
    final_price DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('pix', 'credit_card', 'boleto') NOT NULL,
    payment_status ENUM('pending', 'escrow', 'released', 'refunded', 'cancelled') DEFAULT 'pending',
    booking_status ENUM('pending', 'confirmed', 'active', 'completed', 'cancelled') DEFAULT 'pending',
    access_code VARCHAR(10),
    qr_code VARCHAR(255),
    access_code_expires DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (spot_id) REFERENCES parking_spots(id) ON DELETE CASCADE,
    FOREIGN KEY (renter_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_spot (spot_id),
    INDEX idx_renter (renter_id),
    INDEX idx_dates (start_date, end_date),
    INDEX idx_status (booking_status)
) ENGINE=InnoDB;

-- Reviews table
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    reviewed_id INT NOT NULL,
    review_type ENUM('renter_to_owner', 'owner_to_renter') NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_booking (booking_id),
    INDEX idx_reviewed (reviewed_id)
) ENGINE=InnoDB;

-- Smart lock access logs
CREATE TABLE IF NOT EXISTS access_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    spot_id INT NOT NULL,
    user_id INT NOT NULL,
    access_type ENUM('qr_code', 'temp_code', 'remote_unlock') NOT NULL,
    access_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('success', 'failed', 'denied') NOT NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (spot_id) REFERENCES parking_spots(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_booking (booking_id),
    INDEX idx_spot (spot_id)
) ENGINE=InnoDB;

-- Insert sample data
INSERT INTO users (name, email, password, phone, cpf, is_verified, verification_date) VALUES
('João Silva', 'joao@email.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYzpLhJ7.K6', '(11) 98765-4321', '123.456.789-00', TRUE, NOW()),
('Maria Santos', 'maria@email.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYzpLhJ7.K6', '(11) 98765-4322', '123.456.789-01', TRUE, NOW()),
('Pedro Costa', 'pedro@email.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYzpLhJ7.K6', '(11) 98765-4323', '123.456.789-02', TRUE, NOW());
