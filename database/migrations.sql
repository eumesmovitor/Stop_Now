-- Migration file for StopNow new features
-- Run this after the main schema.sql

-- Notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    data JSON,
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Favorites table
CREATE TABLE IF NOT EXISTS favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    spot_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (spot_id) REFERENCES parking_spots(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, spot_id)
);

-- Messages table
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    subject VARCHAR(255),
    message TEXT NOT NULL,
    booking_id INT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL
);

-- Add indexes for better performance
-- Note: These indexes will be created only if they don't already exist
-- If you get duplicate key errors, the indexes already exist and can be safely ignored

-- Check existing indexes first (uncomment to run):
-- SELECT TABLE_NAME, INDEX_NAME, COLUMN_NAME FROM INFORMATION_SCHEMA.STATISTICS 
-- WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ('notifications', 'favorites', 'messages') 
-- AND INDEX_NAME LIKE 'idx_%' ORDER BY TABLE_NAME, INDEX_NAME;

CREATE INDEX idx_notifications_user_id ON notifications(user_id);
CREATE INDEX idx_notifications_type ON notifications(type);
CREATE INDEX idx_notifications_is_read ON notifications(is_read);
CREATE INDEX idx_notifications_created_at ON notifications(created_at);

CREATE INDEX idx_favorites_user_id ON favorites(user_id);
CREATE INDEX idx_favorites_spot_id ON favorites(spot_id);

CREATE INDEX idx_messages_sender_id ON messages(sender_id);
CREATE INDEX idx_messages_receiver_id ON messages(receiver_id);
CREATE INDEX idx_messages_booking_id ON messages(booking_id);
CREATE INDEX idx_messages_is_read ON messages(is_read);
CREATE INDEX idx_messages_created_at ON messages(created_at);

-- Add updated_at column to parking_spots if it doesn't exist
ALTER TABLE parking_spots ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Add status column to parking_spots if it doesn't exist
ALTER TABLE parking_spots ADD COLUMN IF NOT EXISTS status ENUM('active', 'inactive', 'occupied') DEFAULT 'active';

-- Add indexes for better search performance
CREATE INDEX IF NOT EXISTS idx_parking_spots_status ON parking_spots(status);
CREATE INDEX IF NOT EXISTS idx_parking_spots_city ON parking_spots(city);
CREATE INDEX IF NOT EXISTS idx_parking_spots_spot_type ON parking_spots(spot_type);
CREATE INDEX IF NOT EXISTS idx_parking_spots_price_daily ON parking_spots(price_daily);

-- Add admin column to users if it doesn't exist
ALTER TABLE users ADD COLUMN IF NOT EXISTS is_admin BOOLEAN DEFAULT FALSE;

-- Insert sample admin user (password: admin123)
INSERT IGNORE INTO users (name, email, password, phone, is_admin, is_verified, created_at) 
VALUES ('Administrador', 'admin@stopnow.com', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewdBPj4J8K8K8K8K', '11999999999', TRUE, TRUE, NOW());

-- Add some sample data for testing
INSERT IGNORE INTO parking_spots (owner_id, title, description, address, city, state, zip_code, price_daily, spot_type, is_covered, has_security, has_camera, has_lighting, has_electric_charging, has_smart_lock, status, created_at) 
VALUES 
(1, 'Vaga Coberta no Centro', 'Vaga coberta e segura no centro da cidade', 'Rua das Flores, 123', 'São Paulo', 'SP', '01234-567', 25.00, 'covered', TRUE, TRUE, TRUE, TRUE, FALSE, TRUE, 'active', NOW()),
(1, 'Garagem Residencial', 'Garagem em prédio residencial com segurança 24h', 'Av. Paulista, 1000', 'São Paulo', 'SP', '01310-100', 35.00, 'garage', TRUE, TRUE, TRUE, TRUE, TRUE, TRUE, 'active', NOW()),
(1, 'Vaga na Rua', 'Vaga descoberta na rua, fácil acesso', 'Rua Augusta, 456', 'São Paulo', 'SP', '01305-000', 15.00, 'street', FALSE, FALSE, FALSE, TRUE, FALSE, FALSE, 'active', NOW());
