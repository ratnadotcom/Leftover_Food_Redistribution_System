CREATE DATABASE IF NOT EXISTS leftover_food_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE leftover_food_db;

-- 1. USERS TABLE (for login/authentication)
CREATE TABLE Users (
    user_id         INT PRIMARY KEY AUTO_INCREMENT,
    name            VARCHAR(100) NOT NULL,
    email           VARCHAR(100) NOT NULL UNIQUE,
    password        VARCHAR(255) NOT NULL,          -- store hashed password
    role            ENUM('admin','donor','receiver') NOT NULL DEFAULT 'donor',
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 2. DONORS TABLE
CREATE TABLE Donors (
    donor_id        INT PRIMARY KEY AUTO_INCREMENT,
    user_id         INT NOT NULL,
    name            VARCHAR(100) NOT NULL,
    contact         VARCHAR(15) NOT NULL UNIQUE,
    email           VARCHAR(100) NOT NULL UNIQUE,
    address         TEXT,
    donor_type      ENUM('restaurant','individual','event') NOT NULL DEFAULT 'individual',
    registration_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

-- 3. RECEIVERS TABLE
CREATE TABLE Receivers (
    receiver_id     INT PRIMARY KEY AUTO_INCREMENT,
    user_id         INT NOT NULL,
    name            VARCHAR(100) NOT NULL,
    contact         VARCHAR(15) NOT NULL UNIQUE,
    email           VARCHAR(100) NOT NULL UNIQUE,
    address         TEXT,
    receiver_type   ENUM('NGO','individual') NOT NULL DEFAULT 'individual',
    registration_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

-- 4. FOOD_ITEMS TABLE
CREATE TABLE Food_Items (
    food_id         INT PRIMARY KEY AUTO_INCREMENT,
    donor_id        INT NOT NULL,
    food_name       VARCHAR(100) NOT NULL,
    quantity        INT NOT NULL CHECK (quantity > 0),
    unit            VARCHAR(20) DEFAULT 'kg',       -- kg, packets, plates
    prepared_time   DATETIME NOT NULL,
    expiry_time     DATETIME NOT NULL,
    status          ENUM('available','reserved','distributed') NOT NULL DEFAULT 'available',
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES Donors(donor_id) ON DELETE CASCADE
);

-- 5. REQUESTS TABLE
CREATE TABLE Requests (
    request_id      INT PRIMARY KEY AUTO_INCREMENT,
    receiver_id     INT NOT NULL,
    food_id         INT NOT NULL,
    request_quantity INT NOT NULL CHECK (request_quantity > 0),
    request_time    DATETIME DEFAULT CURRENT_TIMESTAMP,
    status          ENUM('pending','approved','rejected','completed') NOT NULL DEFAULT 'pending',
    FOREIGN KEY (receiver_id) REFERENCES Receivers(receiver_id) ON DELETE CASCADE,
    FOREIGN KEY (food_id)     REFERENCES Food_Items(food_id)   ON DELETE CASCADE
);

-- 6. DELIVERIES TABLE
CREATE TABLE Deliveries (
    delivery_id     INT PRIMARY KEY AUTO_INCREMENT,
    request_id      INT NOT NULL UNIQUE,            -- 1:1 with Requests
    delivery_person VARCHAR(100) NOT NULL,
    contact         VARCHAR(15),
    pickup_time     DATETIME,
    delivery_time   DATETIME,
    status          ENUM('assigned','in_progress','completed') NOT NULL DEFAULT 'assigned',
    FOREIGN KEY (request_id) REFERENCES Requests(request_id) ON DELETE CASCADE
);

-- SAMPLE DATA
INSERT INTO Users (name, email, password, role) VALUES
('Admin User',      'admin@food.com',    MD5('admin123'),    'admin'),
('Rahim Donor',     'rahim@gmail.com',   MD5('rahim123'),    'donor'),
('Karim Donor',     'karim@gmail.com',   MD5('karim123'),    'donor'),
('Ngo Bangladesh',  'ngo@bd.org',        MD5('ngo123'),      'receiver'),
('Fatima Begum',    'fatima@gmail.com',  MD5('fatima123'),   'receiver');

-- Donors
INSERT INTO Donors (user_id, name, contact, email, address, donor_type) VALUES
(2, 'Rahim Donor',  '01711000001', 'rahim@gmail.com',  'Narayanganj, Dhaka', 'restaurant'),
(3, 'Karim Donor',  '01711000002', 'karim@gmail.com',  'Motijheel, Dhaka',   'individual');

-- Receivers
INSERT INTO Receivers (user_id, name, contact, email, address, receiver_type) VALUES
(4, 'Ngo Bangladesh', '01811000001', 'ngo@bd.org',       'Mirpur, Dhaka',     'NGO'),
(5, 'Fatima Begum',   '01811000002', 'fatima@gmail.com', 'Demra, Dhaka',      'individual');

-- Food Items
INSERT INTO Food_Items (donor_id, food_name, quantity, unit, prepared_time, expiry_time, status) VALUES
(1, 'Biriyani',       30, 'plates', NOW(), DATE_ADD(NOW(), INTERVAL 6 HOUR),  'available'),
(1, 'Roti & Curry',   50, 'plates', NOW(), DATE_ADD(NOW(), INTERVAL 8 HOUR),  'available'),
(2, 'Rice & Dal',     10, 'kg',     NOW(), DATE_ADD(NOW(), INTERVAL 12 HOUR), 'available');

-- Requests
INSERT INTO Requests (receiver_id, food_id, request_quantity, status) VALUES
(1, 1, 20, 'approved'),
(2, 3,  5, 'pending');

-- Deliveries
INSERT INTO Deliveries (request_id, delivery_person, contact, pickup_time, delivery_time, status) VALUES
(1, 'Raju Delivery', '01911000001', NOW(), DATE_ADD(NOW(), INTERVAL 2 HOUR), 'assigned');

