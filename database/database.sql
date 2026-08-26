-- LuxuryStay Database Schema
-- Online Accommodation Booking & Management System - Sri Lanka

CREATE DATABASE IF NOT EXISTS luxurystay CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE luxurystay;

-- Admins table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Property owners table
CREATE TABLE owners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(20),
    address VARCHAR(255) NULL,
    profile_image VARCHAR(255) NULL,
    password VARCHAR(255) NOT NULL,
    company_name VARCHAR(150),
    status ENUM('active','suspended','pending') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Customers (users) table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    status ENUM('active','suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Password reset tokens
CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL,
    token VARCHAR(255) NOT NULL,
    role ENUM('user','owner','admin') NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Amenities master list
CREATE TABLE amenities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    icon VARCHAR(50) DEFAULT 'bi-check'
);

-- Properties
CREATE TABLE properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    address VARCHAR(255) NOT NULL,
    city VARCHAR(100) NULL,
    province VARCHAR(100) NULL,
    district VARCHAR(100) NOT NULL,
    property_type ENUM('Hotel','Villa','Resort','Guest House') NOT NULL,
    map_iframe TEXT,
    contact_phone VARCHAR(30) NULL,
    contact_email VARCHAR(150) NULL,
    latitude DECIMAL(10,8) NULL,
    longitude DECIMAL(11,8) NULL,
    policies TEXT,
    featured TINYINT(1) DEFAULT 0,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    deleted_at DATETIME NULL,
    avg_rating DECIMAL(3,2) DEFAULT 0,
    review_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES owners(id) ON DELETE CASCADE
);

-- Property images
CREATE TABLE property_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_primary TINYINT(1) DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

-- Property amenities junction
CREATE TABLE property_amenities (
    property_id INT NOT NULL,
    amenity_id INT NOT NULL,
    PRIMARY KEY (property_id, amenity_id),
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (amenity_id) REFERENCES amenities(id) ON DELETE CASCADE
);

-- Rooms
CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price_per_night DECIMAL(12,2) NOT NULL,
    weekend_price DECIMAL(12,2) NULL,
    max_guests INT NOT NULL DEFAULT 2,
    inventory INT NOT NULL DEFAULT 1,
    bed_type VARCHAR(50),
    status ENUM('active','inactive') DEFAULT 'active',
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

-- Room amenities junction
CREATE TABLE room_amenities (
    room_id INT NOT NULL,
    amenity_id INT NOT NULL,
    PRIMARY KEY (room_id, amenity_id),
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (amenity_id) REFERENCES amenities(id) ON DELETE CASCADE
);

-- Room images
CREATE TABLE room_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

-- Room availability (blocked dates or custom pricing)
CREATE TABLE room_availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    date DATE NOT NULL,
    is_available TINYINT(1) DEFAULT 1,
    custom_price DECIMAL(12,2) NULL,
    UNIQUE KEY unique_room_date (room_id, date),
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

-- Bookings
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    property_id INT NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    guests INT NOT NULL DEFAULT 1,
    total_amount DECIMAL(12,2) NOT NULL,
    status ENUM('pending','confirmed','cancelled','completed') DEFAULT 'pending',
    payment_status ENUM('pending','paid','refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    special_requests TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

-- Reviews
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    property_id INT NOT NULL,
    booking_id INT,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL
);

-- Notifications
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    owner_id INT NULL,
    admin_id INT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50) DEFAULT 'info',
    is_read TINYINT(1) DEFAULT 0,
    link VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Special offers
CREATE TABLE offers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    discount_percent DECIMAL(5,2),
    valid_from DATE,
    valid_to DATE,
    status ENUM('active','inactive') DEFAULT 'active',
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL
);

-- Recently viewed properties
CREATE TABLE recently_viewed (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    property_id INT NOT NULL,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    UNIQUE KEY unique_view (user_id, property_id)
);

-- Featured destinations
CREATE TABLE featured_destinations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    district VARCHAR(100) NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    image_path VARCHAR(255),
    sort_order INT DEFAULT 0
);

-- Sample data

-- Default admin (password: password)
INSERT INTO admins (name, email, password) VALUES
('System Admin', 'admin@luxurystay.lk', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36MMrOH2');

-- Amenities
INSERT INTO amenities (name, icon) VALUES
('WiFi', 'bi-wifi'),
('Pool', 'bi-water'),
('AC', 'bi-snow'),
('Parking', 'bi-car-front'),
('Breakfast', 'bi-cup-hot'),
('Beach access', 'bi-umbrella'),
('Spa', 'bi-heart-pulse'),
('Restaurant', 'bi-shop'),
('Gym', 'bi-bicycle'),
('Room Service', 'bi-bell');

-- Sample owners (password = first name + 123)
INSERT INTO owners (name, email, phone, profile_image, password, company_name, status) VALUES
('Kamal Perera', 'kamalperera@luxurystay.lk', '+94771234567', 'assets/images/default-avatar.svg', '$2y$10$sEqmxhuJalyeHyDIiB0A2.0ufBNTtgdHqomPHSiYGcIfExxdMA05O', 'Ceylon Hospitality Group', 'active'),
('Nimal Fernando', 'nimalfernando@luxurystay.lk', '+94772345678', 'assets/images/default-avatar.svg', '$2y$10$HbOdqDJMZ35kstb3tLb6G.v1IUy.xkQ00KM6SwfhwVYciF4mXeXfS', 'Paradise Resorts Ltd', 'active');

-- Sample users (password = first name + 123)
INSERT INTO users (name, email, phone, profile_image, password) VALUES
('John Silva', 'johnsilva@luxurystay.lk', '+94773456789', 'assets/images/default-avatar.svg', '$2y$10$oLFHGj6pVZeZ/1bHsM6xq.KPFS6J6RYo/aT8IGRLzxFXwwR2TIgGi'),
('Sarah Jayawardena', 'sarahjayawardena@luxurystay.lk', '+94774567890', 'assets/images/default-avatar.svg', '$2y$10$QjS2SZaZNeCNvbhtplGJn.mvb86eErvgPRug37VFEuQJqVwLngBSi');

-- Featured destinations
INSERT INTO featured_destinations (district, title, description, image_path, sort_order) VALUES
('Colombo', 'Colombo City', 'Experience the vibrant capital with luxury hotels and colonial charm.', 'assets/images/destinations/217408319.webp', 1),
('Kandy', 'Kandy Hills', 'Sacred city surrounded by misty mountains and tea plantations.', 'assets/images/destinations/249275219.webp', 2),
('Galle', 'Galle Fort', 'UNESCO heritage coastal fortress with boutique stays.', 'assets/images/destinations/249378359.jpg', 3),
('Mirissa', 'Mirissa Beach', 'Golden beaches and whale watching on the south coast.', 'assets/images/destinations/490937419.jpg', 4),
('Ella', 'Ella Mountains', 'Scenic hill country with iconic Nine Arch Bridge views.', 'assets/images/destinations/490951426.jpg', 5),
('Nuwara Eliya', 'Little England', 'Cool climate tea country with colonial elegance.', 'assets/images/destinations/814248519.jpg', 6);

-- Sample properties
INSERT INTO properties (owner_id, name, description, address, district, property_type, map_iframe, policies, featured, status, avg_rating, review_count) VALUES
(1, 'Cinnamon Grand Colombo', 'Iconic 5-star luxury hotel in the heart of Colombo offering world-class dining, spa, and panoramic city views.', '77 Galle Road, Colombo 03', 'Colombo', 'Hotel', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3960.798!2d79.848!3d6.927!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1', 'Check-in 2PM, Check-out 12PM. No smoking in rooms.', 1, 'approved', 4.80, 120),
(1, 'Heritance Kandalama', 'Geoffrey Bawa masterpiece nestled against a cliff overlooking the Kandalama reservoir.', 'Kandalama, Dambulla', 'Dambulla', 'Resort', 'https://www.google.com/maps/embed?pb=!1m18', 'Eco-friendly resort. Children welcome.', 1, 'approved', 4.90, 85),
(2, 'Jetwing Lighthouse Galle', 'Stunning clifftop resort within Galle Fort with infinity pool and ocean views.', 'Dadella, Galle', 'Galle', 'Resort', 'https://www.google.com/maps/embed?pb=!1m18', 'Beach access. Pets not allowed.', 1, 'approved', 4.70, 95),
(2, 'Cape Weligama', 'Exclusive cliff-top villa resort on the southern tip with private coves.', 'Weligama Bay, Weligama', 'Matara', 'Villa', 'https://www.google.com/maps/embed?pb=!1m18', 'Minimum 2 nights. All-inclusive options.', 1, 'approved', 4.95, 60),
(1, '98 Acres Resort Ella', 'Boutique resort on a tea estate with breathtaking Ella Gap views.', 'Ella, Badulla', 'Ella', 'Resort', 'https://www.google.com/maps/embed?pb=!1m18', 'Hill country weather - bring warm clothes.', 0, 'approved', 4.85, 70),
(2, 'Mirissa Hills Guest House', 'Charming family-run guest house steps from Mirissa beach.', 'Mirissa Road, Mirissa', 'Mirissa', 'Guest House', 'https://www.google.com/maps/embed?pb=!1m18', 'Breakfast included. Quiet hours 10PM-7AM.', 0, 'approved', 4.50, 40);

-- Property amenities
INSERT INTO property_amenities (property_id, amenity_id) VALUES
(1,1),(1,2),(1,3),(1,4),(1,5),(1,8),(1,9),
(2,1),(2,2),(2,3),(2,6),(2,7),(2,8),
(3,1),(3,2),(3,3),(3,6),(3,7),(3,8),
(4,1),(4,2),(4,3),(4,5),(4,6),(4,7),
(5,1),(5,3),(5,5),(5,8),
(6,1),(6,5),(6,6);

-- Property images
INSERT INTO property_images (property_id, image_path, is_primary, sort_order) VALUES
(1, 'uploads/properties/1/106858444.jpg', 1, 0),
(1, 'uploads/properties/1/e93eb03e.avif', 0, 1),
(1, 'uploads/properties/1/images (1).jpg', 0, 2),
(1, 'uploads/properties/1/images (2).jpg', 0, 3),
(1, 'uploads/properties/1/images.jpg', 0, 4),
(1, 'uploads/properties/1/image_b6d0d60b2a.jpg', 0, 5),
(1, 'uploads/properties/1/unnamed.webp', 0, 6),
(2, 'uploads/properties/img_6a340ff4af41e5.00617472.webp', 1, 0),
(3, 'uploads/properties/img_6a3488d1276ed4.86291310.webp', 1, 0),
(4, 'uploads/properties/img_6a348a92ab12b8.17141509.jpg', 1, 0),
(5, 'uploads/properties/img_6a348ae3118da0.49067607.jpg', 1, 0),
(6, 'uploads/properties/img_6a348b119e0d32.36687006.jpg', 1, 0);

-- Rooms
INSERT INTO rooms (property_id, name, description, price_per_night, max_guests, bed_type) VALUES
(1, 'Deluxe King Room', 'Spacious room with city view and king bed.', 45000.00, 2, 'King'),
(1, 'Executive Suite', 'Luxury suite with lounge and butler service.', 85000.00, 3, 'King'),
(2, 'Panoramic Room', 'Lake view room with private balcony.', 55000.00, 2, 'Queen'),
(2, 'Suite with Plunge Pool', 'Premium suite with private plunge pool.', 120000.00, 2, 'King'),
(3, 'Ocean View Room', 'Elegant room overlooking the Indian Ocean.', 65000.00, 2, 'King'),
(4, 'Cape Suite', 'Clifftop suite with private terrace.', 180000.00, 2, 'King'),
(5, 'Tea Estate Cottage', 'Cozy cottage surrounded by tea fields.', 35000.00, 2, 'Double'),
(6, 'Beach View Double', 'Comfortable double room with sea breeze.', 12000.00, 2, 'Double');

-- Offers
INSERT INTO offers (property_id, title, description, discount_percent, valid_from, valid_to, status) VALUES
(1, 'Weekend Special', '15% off weekend stays in Colombo', 15.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 90 DAY), 'active'),
(6, 'Beach Getaway', '20% off 3+ night stays at Mirissa', 20.00, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 60 DAY), 'active');

-- Sample bookings (for dashboard charts & testing)
INSERT INTO bookings (user_id, room_id, property_id, check_in, check_out, guests, total_amount, status, payment_status, payment_method) VALUES
(1, 1, 1, DATE_ADD(CURDATE(), INTERVAL 7 DAY), DATE_ADD(CURDATE(), INTERVAL 10 DAY), 2, 135000.00, 'confirmed', 'paid', 'card'),
(1, 7, 5, DATE_ADD(CURDATE(), INTERVAL -30 DAY), DATE_ADD(CURDATE(), INTERVAL -27 DAY), 2, 105000.00, 'completed', 'paid', 'bank'),
(2, 8, 6, DATE_ADD(CURDATE(), INTERVAL 14 DAY), DATE_ADD(CURDATE(), INTERVAL 16 DAY), 2, 24000.00, 'pending', 'paid', 'card');

-- Sample approved review
INSERT INTO reviews (user_id, property_id, booking_id, rating, comment, status) VALUES
(1, 5, 2, 5, 'Absolutely stunning views of Ella Gap. Tea estate atmosphere was magical!', 'approved');

-- Update property 5 rating after review
UPDATE properties SET avg_rating = 5.00, review_count = 1 WHERE id = 5;

-- Sample bookings (for dashboard charts & testing)
INSERT INTO bookings (user_id, room_id, property_id, check_in, check_out, guests, total_amount, status, payment_status, payment_method) VALUES
(1, 1, 1, DATE_ADD(CURDATE(), INTERVAL 7 DAY), DATE_ADD(CURDATE(), INTERVAL 10 DAY), 2, 135000.00, 'confirmed', 'paid', 'card'),
(1, 7, 5, DATE_ADD(CURDATE(), INTERVAL -30 DAY), DATE_ADD(CURDATE(), INTERVAL -28 DAY), 2, 70000.00, 'completed', 'paid', 'bank'),
(2, 3, 3, DATE_ADD(CURDATE(), INTERVAL 14 DAY), DATE_ADD(CURDATE(), INTERVAL 16 DAY), 2, 130000.00, 'pending', 'paid', 'card');

-- Sample approved reviews
INSERT INTO reviews (user_id, property_id, booking_id, rating, comment, status) VALUES
(1, 1, 2, 5, 'Exceptional service and stunning city views. Highly recommended!', 'approved'),
(1, 5, NULL, 5, 'The Ella Gap view from the cottage was breathtaking.', 'approved');
