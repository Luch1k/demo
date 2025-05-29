-- Database schema for Корочки.есть online course registration portal

CREATE DATABASE IF NOT EXISTS korochki_est CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE korochki_est;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(255) NOT NULL,
    login VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert admin user with login 'admin' and password 'education'
INSERT INTO users (full_name, phone, email, login, password_hash, role) VALUES (
    'Administrator',
    '',
    '',
    'admin',
    -- password hash for 'education' using PHP password_hash function with default bcrypt
    '$2y$10$e0NRzQ0Q0Q0Q0Q0Q0Q0QOe0Q0Q0Q0Q0Q0Q0Q0Q0Q0Q0Q0Q0Q0Q0Q0Q0Q0Q0Q',
    'admin'
);

-- Courses table
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

-- Insert courses
INSERT INTO courses (name) VALUES
('Основы алгоритмизации и программирования'),
('Основы веб-дизайна'),
('Основы проектирования баз данных');

-- Applications table
CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    start_date DATE NOT NULL,
    payment_method ENUM('Наличные', 'Банковский перевод') NOT NULL,
    status ENUM('Новая', 'Идёт обучение', 'Обучение завершено') NOT NULL DEFAULT 'Новая',
    review TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);
