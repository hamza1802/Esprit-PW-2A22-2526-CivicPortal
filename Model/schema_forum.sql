-- Forum Module Database Schema
-- Run this SQL in phpMyAdmin or MySQL CLI to set up the forum tables.
-- Assumes the 'users' table already exists with a user_id primary key.

CREATE DATABASE IF NOT EXISTS civicportal;
USE civicportal;

CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'citizen',
    email VARCHAR(255) UNIQUE NOT NULL
);

-- Seed default users (matches AppModel session data)
INSERT IGNORE INTO users (user_id, name, role, email) VALUES
(1, 'John Citizen', 'citizen', 'john@example.com'),
(2, 'Alice Worker', 'worker', 'alice@cityhall.gov'),
(3, 'Admin User', 'admin', 'admin@cityhall.gov');

CREATE TABLE IF NOT EXISTS forum_posts (
    post_id    INT PRIMARY KEY AUTO_INCREMENT,
    user_id    INT NOT NULL,
    title      VARCHAR(255) NOT NULL,
    content    TEXT NOT NULL,
    category   VARCHAR(100) NOT NULL,
    status     ENUM('open', 'closed', 'pinned') NOT NULL DEFAULT 'open',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS forum_comments (
    comment_id INT PRIMARY KEY AUTO_INCREMENT,
    post_id    INT NOT NULL,
    user_id    INT NOT NULL,
    content    TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES forum_posts(post_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);
