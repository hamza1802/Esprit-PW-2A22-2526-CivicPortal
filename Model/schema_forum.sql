-- Forum Module Database Schema
-- Run this SQL in phpMyAdmin or MySQL CLI to set up the forum tables.
-- Requires the 'users' table from civicportal.sql (PK: id, column: username).

CREATE DATABASE IF NOT EXISTS civicportal;
USE civicportal;

-- Forum tables rely on the users table defined in civicportal.sql.
-- If you're setting up forum-only, ensure users exists first:
--
--   CREATE TABLE IF NOT EXISTS users (
--       id INT PRIMARY KEY AUTO_INCREMENT,
--       username VARCHAR(50) NOT NULL,
--       email VARCHAR(100) NOT NULL UNIQUE,
--       password_hash VARCHAR(255) NOT NULL,
--       role ENUM('citizen','agent','admin') NOT NULL DEFAULT 'citizen',
--       is_active TINYINT(1) NOT NULL DEFAULT 1,
--       created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
--   );

CREATE TABLE IF NOT EXISTS forum_posts (
    post_id    INT PRIMARY KEY AUTO_INCREMENT,
    user_id    INT NOT NULL,
    title      VARCHAR(255) NOT NULL,
    content    TEXT NOT NULL,
    category   VARCHAR(100) NOT NULL,
    status     ENUM('open', 'closed', 'pinned') NOT NULL DEFAULT 'open',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS forum_comments (
    comment_id INT PRIMARY KEY AUTO_INCREMENT,
    post_id    INT NOT NULL,
    user_id    INT NOT NULL,
    content    TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES forum_posts(post_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
