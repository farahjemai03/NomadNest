-- NomadNest Database Schema
-- Run this in phpMyAdmin > SQL tab, or via MySQL command line
-- Created for Laragon (MySQL 8+)

CREATE DATABASE IF NOT EXISTS nomadnest CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nomadnest;

-- ============================================================
-- USERS
-- role: 'member' = regular user, 'host' = space owner
-- ============================================================
CREATE TABLE users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)  NOT NULL,
    email       VARCHAR(150)  NOT NULL UNIQUE,
    password    VARCHAR(255)  NOT NULL,
    role        ENUM('member','host') NOT NULL DEFAULT 'member',
    city        VARCHAR(100)  DEFAULT NULL,
    bio         TEXT          DEFAULT NULL,
    tags        VARCHAR(255)  DEFAULT NULL,
    avatar      VARCHAR(255)  DEFAULT NULL,
    status      ENUM('open','busy','offline') DEFAULT 'open',
    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- SPACES
-- ============================================================
CREATE TABLE spaces (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    host_id             INT           NOT NULL,
    name                VARCHAR(150)  NOT NULL,
    type                ENUM('private_office','nomad_desk','meeting_room') NOT NULL,
    city                VARCHAR(100)  NOT NULL,
    address             VARCHAR(255)  NOT NULL,
    description         TEXT          DEFAULT NULL,
    price_per_day       DECIMAL(8,2)  NOT NULL,
    availability_status ENUM('available','limited','full') DEFAULT 'available',
    image               VARCHAR(255)  DEFAULT NULL,
    rating              DECIMAL(3,2)  DEFAULT 0.00,
    created_at          TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (host_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- AMENITIES  (one row per amenity per space)
-- ============================================================
CREATE TABLE amenities (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    space_id INT          NOT NULL,
    name     VARCHAR(100) NOT NULL,
    FOREIGN KEY (space_id) REFERENCES spaces(id) ON DELETE CASCADE
);

-- ============================================================
-- BOOKINGS
-- ============================================================
CREATE TABLE bookings (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT           NOT NULL,
    space_id     INT           NOT NULL,
    date_start   DATE          NOT NULL,
    date_end     DATE          NOT NULL,
    seats        INT           NOT NULL DEFAULT 1,
    total_price  DECIMAL(10,2) NOT NULL,
    status       ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
    created_at   TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
    FOREIGN KEY (space_id) REFERENCES spaces(id) ON DELETE CASCADE
);

-- ============================================================
-- REVIEWS
-- ============================================================
CREATE TABLE reviews (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT  NOT NULL,
    space_id   INT  NOT NULL,
    rating     TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment    TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
    FOREIGN KEY (space_id) REFERENCES spaces(id) ON DELETE CASCADE
);

-- ============================================================
-- MESSAGES
-- ============================================================
CREATE TABLE messages (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    sender_id   INT  NOT NULL,
    receiver_id INT  NOT NULL,
    body        TEXT NOT NULL,
    is_read     TINYINT(1) DEFAULT 0,
    created_at  TIMESTAMP  DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id)   REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- SUBSCRIPTIONS
-- ============================================================
CREATE TABLE subscriptions (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL UNIQUE,
    plan       ENUM('free','pro','business') DEFAULT 'free',
    price      DECIMAL(8,2) DEFAULT 0.00,
    active     TINYINT(1)   DEFAULT 1,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================================
-- CONNECTIONS  (member networking)
-- ============================================================
CREATE TABLE connections (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    from_user  INT NOT NULL,
    to_user    INT NOT NULL,
    status     ENUM('pending','accepted') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (from_user) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (to_user)   REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_connection (from_user, to_user)
);

-- ============================================================
-- SEED DATA  (sample content so the site isn't empty)
-- ============================================================

-- Sample users (passwords are all "password123" hashed with bcrypt)
INSERT INTO users (name, email, password, role, city, bio, tags, status) VALUES
('Léa Moreau',   'lea@nomadnest.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', 'Paris',       'Product designer who hops between Paris and Lisbon.',  'UI,Design Systems,Figma',         'open'),
('Daniel Okafor','daniel@nomadnest.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', 'Berlin',      'Indie founder building SaaS tools.',                  'SaaS,Growth,No-code',             'open'),
('Priya Shah',   'priya@nomadnest.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'member', 'Lisbon',      'ML engineer obsessed with LLMs.',                     'LLMs,Python,RAG',                 'busy'),
('Camille Laurent','camille@nomadnest.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','host','Paris',       'Host of The Lantern Loft.',                           NULL,                              'open'),
('Marcus Hill',  'marcus@nomadnest.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'host',   'New York',    'Brand strategist and co-working host.',               'Branding,Copy,Naming',            'open');

-- Sample spaces
INSERT INTO spaces (host_id, name, type, city, address, description, price_per_day, availability_status, rating) VALUES
(4, 'The Lantern Loft',  'private_office', 'Paris',      '12 Rue de la République, 75011', 'A bright industrial loft with reclaimed wood, glass partitions and a curated coffee bar.',      45.00, 'available', 4.90),
(4, 'Atelier Nord',      'nomad_desk',     'Berlin',     'Brunnenstraße 9, 10119',          'Raw concrete meets warm lighting in this Mitte favourite.',                                     22.00, 'limited',   4.70),
(4, 'Casa Mareta',       'private_office', 'Lisbon',     'R. da Boavista 84, 1200',         'Rooftop terrace, espresso bar and the best light in Lisbon.',                                   35.00, 'available', 4.80),
(5, 'Forge & Foam',      'meeting_room',   'New York',   '121 Wythe Ave, Brooklyn',         'Industrial meeting room with a projector wall and whiteboards.',                                89.00, 'full',      4.60),
(5, 'Kyoto Koan',        'nomad_desk',     'Tokyo',      '2-Chome Daikanyama, Shibuya',     'A silent sanctuary for deep work, with a tea bar and standing desks.',                          28.00, 'available', 4.90),
(5, 'Mercado Hub',       'private_office', 'Mexico City','Calle Orizaba 219, Roma Norte',   'Vibrant co-working with bike storage, pet-friendly policy and a coffee counter.',               30.00, 'limited',   4.70);

-- Amenities
INSERT INTO amenities (space_id, name) VALUES
(1,'WiFi'),(1,'Coffee'),(1,'Standing Desk'),(1,'24/7 Access'),
(2,'WiFi'),(2,'Coffee'),(2,'Projector'),(2,'Phone Booths'),
(3,'WiFi'),(3,'Rooftop'),(3,'Coffee'),(3,'Locker'),
(4,'WiFi'),(4,'Projector'),(4,'Whiteboard'),(4,'Coffee'),
(5,'WiFi'),(5,'Tea Bar'),(5,'Silent Zone'),(5,'Standing Desk'),
(6,'WiFi'),(6,'Coffee'),(6,'Bike Storage'),(6,'Pet Friendly');

-- Sample bookings
INSERT INTO bookings (user_id, space_id, date_start, date_end, seats, total_price, status) VALUES
(1, 1, '2026-05-12', '2026-05-12', 1, 49.00,  'confirmed'),
(1, 2, '2026-05-15', '2026-05-15', 1, 26.00,  'pending'),
(1, 3, '2026-05-20', '2026-05-20', 1, 39.00,  'confirmed'),
(1, 4, '2026-04-29', '2026-04-29', 1, 93.00,  'cancelled'),
(2, 5, '2026-05-18', '2026-05-19', 2, 60.00,  'confirmed');

-- Sample reviews
INSERT INTO reviews (user_id, space_id, rating, comment) VALUES
(1, 1, 5, 'Best espresso in the 11th. Will be back every week.'),
(2, 2, 5, 'Quiet phone booths actually work! Rare find in Berlin.'),
(3, 2, 4, 'WiFi flaked once but staff fixed it fast.'),
(1, 3, 5, 'The rooftop at sunset is unreal. Booked again immediately.');

-- Subscriptions
INSERT INTO subscriptions (user_id, plan, price, active) VALUES
(1, 'pro',      89.00,  1),
(2, 'free',     0.00,   1),
(3, 'business', 249.00, 1);

-- Sample messages
INSERT INTO messages (sender_id, receiver_id, body) VALUES
(1, 4, 'Hi Camille! Is the Lantern Loft available on the 25th?'),
(4, 1, 'Hey Léa! Yes it is, go ahead and book through the site.'),
(2, 3, 'Priya, want to co-work at Atelier Nord next week?');
UPDATE spaces SET image = 'https://images.unsplash.com/photo-1600508774634-4e11d34730e2?w=600&q=80' WHERE id = 1;
UPDATE spaces SET image = 'https://images.unsplash.com/photo-1531973576160-7125cd663d86?q=80&w=870&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D' WHERE id = 2;
UPDATE spaces SET image = 'https://images.unsplash.com/photo-1527192491265-7e15c55b1ed2?w=600&q=80' WHERE id = 3;
UPDATE spaces SET image = 'https://images.unsplash.com/photo-1517502884422-41eaead166d4?w=600&q=80' WHERE id = 4;
UPDATE spaces SET image = 'https://images.unsplash.com/photo-1527192491265-7e15c55b1ed2?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8Y293b3JraW5nJTIwc3BhY2V8ZW58MHx8MHx8fDA%3D' WHERE id = 5;
UPDATE spaces SET image = 'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?w=600&q=80' WHERE id = 6;
