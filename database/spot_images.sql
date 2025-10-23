CREATE TABLE IF NOT EXISTS spot_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    spot_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (spot_id) REFERENCES parking_spots(id) ON DELETE CASCADE
);