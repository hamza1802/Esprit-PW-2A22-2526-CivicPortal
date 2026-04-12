3eja
the3eja
•
GNX

This is the start of the #projet-web private channel. 
3eja [kdot],  — 3/28/2026 11:02 PM
https://www.canva.com/design/DAHFRfq5cJI/71vVwcQU4UV551O5mVOvCw/edit?utm_content=DAHFRfq5cJI&utm_campaign=designshare&utm_medium=link2&utm_source=sharebutton
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CivicPortal - Problem Statement</title>

the website.html
7 KB
3eja [kdot],  — 3/28/2026 11:51 PM
CRUD per Student
Every student implements the full Create, Read, Update, Delete cycle on their 2 tables.
Student 1 — User Management

Create: register a new user, create a role
Read: list all users, view user profile, list roles
Update: edit user info, change role, activate/deactivate account
Delete: remove a user, delete a role

Student 2 — Service Requests

Create: citizen submits a new request, uploads a document
Read: view request list, view request details, view attached documents
Update: worker changes request status (pending → approved/rejected)
Delete: cancel a request, remove an uploaded document

Student 3 — Appointments

Create: admin creates available slots, citizen books an appointment
Read: view available slots, view booked appointments, appointment history
Update: reschedule an appointment, mark a slot as unavailable
Delete: cancel an appointment, remove a time slot

Student 4 — Complaints & Feedback

Create: citizen files a complaint, citizen submits a star rating
Read: view all complaints, view complaint details, view ratings per service
Update: admin updates complaint status (open → resolved)
Delete: admin removes an invalid complaint, citizen deletes a rating

Student 5 — Admin Dashboard

Create: system generates an audit log entry, creates a notification
Read: view full activity log, filter logs by user/action, view notifications
Update: mark notification as read, filter and export log data
Delete: clear old notifications, archive audit logs
3eja [kdot],  — 3/29/2026 5:18 PM
CRUD per Student
Every student implements the full Create, Read, Update, Delete cycle on their 2 tables.
Student 1 — User Management

Create: register a new user, create a role
Read: list all users, view user profile, list roles
Update: edit user info, change role, activate/deactivate account
Delete: remove a user, delete a role

Student 2 — Service Requests

Create: citizen submits a new request, uploads a document
Read: view request list, view request details, view attached documents
Update: worker changes request status (pending → approved/rejected)
Delete: cancel a request, remove an uploaded document

Student 3 — Appointments

Create: admin creates available slots, citizen books an appointment
Read: view available slots, view booked appointments, appointment history
Update: reschedule an appointment, mark a slot as unavailable
Delete: cancel an appointment, remove a time slot

Student 4 — Complaints & Feedback

Create: citizen files a complaint, citizen submits a star rating
Read: view all complaints, view complaint details, view ratings per service
Update: admin updates complaint status (open → resolved)
Delete: admin removes an invalid complaint, citizen deletes a rating

Student 5 — Admin Dashboard

Create: system generates an audit log entry, creates a notification
Read: view full activity log, filter logs by user/action, view notifications
Update: mark notification as read, filter and export log data
Delete: clear old notifications, archive audit logs
dragonlollll — 3/29/2026 5:33 PM
h
Image
3eja [kdot],  — 3/29/2026 5:53 PM
ena appointments
w amen user
nikorwahkom @ytsurk @!     Amin @ilyesarf
!     Amin — 3/29/2026 6:36 PM
Image
@3eja
!     Amin — 3/29/2026 6:43 PM
Image
 [kdot], 
3eja [kdot],  — 3/29/2026 7:36 PM
.
dragonlollll — 3/30/2026 9:37 PM
Image
3eja [kdot],  — 3/30/2026 9:45 PM
-- ============================================
-- CivicPortal Database Schema
-- ============================================

-- Role table (no dependencies)
CREATE TABLE Role (

base de donne.sql
4 KB
dragonlollll — 3/30/2026 9:51 PM
Image
https://www.canva.com/design/DAG3uXH40tU/Wq9Af6yLE0n9SYlRH4AL5Q/edit
3eja [kdot],  — 3/30/2026 10:16 PM
hamza intro w group memvbers 
amine 4-5
hedi outro 
ilyes 8-13
amen 6-7
ilyesarf — 11:59 AM
Image
Image
forum_posts
post_id      INT          Primary Key, Auto Increment
user_id      INT          Foreign Key → users(user_id)
title        VARCHAR(255) Title of the discussion
content      TEXT         Body of the post
category     VARCHAR(100) e.g. Infrastructure, Health, Education
status       ENUM         'open', 'closed', 'pinned'
created_at   DATETIME     Timestamp of creation

forum_comments
comment_id   INT          Primary Key, Auto Increment
post_id      INT          Foreign Key → forum_posts(post_id)
user_id      INT          Foreign Key → users(user_id)
content      TEXT         The reply text
created_at   DATETIME     Timestamp of creation
!     Amin — 12:02 PM
CREATE TABLE IF NOT EXISTS transport (
  idTransport int(11) NOT NULL AUTO_INCREMENT,
  name varchar(255) NOT NULL,
  type varchar(100) NOT NULL,
  capacity int(11) NOT NULL,
  plateNumber varchar(255) DEFAULT NULL,
  status varchar(100) NOT NULL DEFAULT 'Active',
  PRIMARY KEY (idTransport)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===================== TRAJET TABLE =====================
CREATE TABLE IF NOT EXISTS trajet (
  idTrajet int(11) NOT NULL AUTO_INCREMENT,
  departure varchar(255) NOT NULL,
  destination varchar(255) NOT NULL,
  idTransport int(11) NOT NULL,
  departureTime datetime NOT NULL,
  price decimal(10,3) NOT NULL DEFAULT 0.000,
  PRIMARY KEY (idTrajet),
  KEY fk_trajet_transport (idTransport),
  CONSTRAINT fk_trajet_transport FOREIGN KEY (idTransport) REFERENCES transport (idTransport) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===================== TICKET TABLE =====================
CREATE TABLE IF NOT EXISTS ticket (
  idTicket int(11) NOT NULL AUTO_INCREMENT,
  ref varchar(50) NOT NULL,
  citizenName varchar(255) NOT NULL,
  idTrajet int(11) NOT NULL,
  issuedAt datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status varchar(50) NOT NULL DEFAULT 'Valid',
  PRIMARY KEY (idTicket),
  KEY fk_ticket_trajet (idTrajet),
  CONSTRAINT fk_ticket_trajet FOREIGN KEY (idTrajet) REFERENCES trajet (idTrajet) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
ytsurk — 12:04 PM
Slm
3eja [kdot],  — 12:05 PM
ija 3asba
ytsurk — 12:05 PM
Nguedo baz cnx bel sql zeda
Menich aal pc
3eja [kdot],  — 12:05 PM
yfz
dragonlollll — 12:07 PM
-- Create the users table first
CREATE TABLE user (
    id              INT             NOT NULL AUTO_INCREMENT,
    username        VARCHAR(50)     NOT NULL UNIQUE,
    email           VARCHAR(100)    NOT NULL UNIQUE,
    password_hash   VARCHAR(255)    NOT NULL,
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);

-- Create the profile table (linked to user) 
CREATE TABLE profile (
    id              INT             NOT NULL AUTO_INCREMENT,
    user_id         INT             NOT NULL UNIQUE,
    first_name      VARCHAR(50),
    last_name       VARCHAR(50),
    bio             TEXT,
    avatar_url      VARCHAR(255),
    phone_number    VARCHAR(20),
    date_of_birth   DATE,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
);
ytsurk — 12:14 PM
chabeb f ena voice chat ntoma yekhi?
3eja [kdot],  — 12:16 PM
romdhan
3asba
ytsurk — 12:18 PM
CREATE TABLE requests (
    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,

    title VARCHAR(200) NOT NULL,
    description TEXT,

    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE documents (
    id INT AUTO_INCREMENT PRIMARY KEY,

    request_id INT NOT NULL,

    file_path VARCHAR(500) NOT NULL,

    -- type de document (important)
    type ENUM('identity', 'proof', 'photo', 'certificate', 'other') DEFAULT 'other',

    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (request_id) REFERENCES requests(id) ON DELETE CASCADE
);
3eja [kdot],  — 12:32 PM

<style>
#erd { padding: 1rem 0; }
#erd svg { width: 100%; }
.mermaid-label { font-family: var(--font-sans) !important; }
</style>

civicportal_full_erd.html
5 KB
 [kdot], 
ytsurk — 12:38 PM
what this
3eja [kdot],  — 12:40 PM
Image
ytsurk — 12:41 PM
shi
3eja [kdot],  — 12:42 PM
-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 11, 2026 at 01:41 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `civicportal`
--

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `type` enum('identity','proof','photo','certificate','other') NOT NULL DEFAULT 'other',
  `uploaded_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enrollment`
--

CREATE TABLE `enrollment` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `enrolled_at` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('confirmed','waitlisted','cancelled') NOT NULL DEFAULT 'confirmed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `forum_comments`
--

CREATE TABLE `forum_comments` (
  `comment_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `forum_posts`
--

CREATE TABLE `forum_posts` (
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `status` enum('open','closed','pinned') NOT NULL DEFAULT 'open',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `profile`
--

CREATE TABLE `profile` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

... (305 lines left)

civicportal.sql
11 KB
http://localhost/civicportal/View/FrontOffice/index.php
﻿
-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 11, 2026 at 01:41 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `civicportal`
--

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `type` enum('identity','proof','photo','certificate','other') NOT NULL DEFAULT 'other',
  `uploaded_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enrollment`
--

CREATE TABLE `enrollment` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `enrolled_at` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('confirmed','waitlisted','cancelled') NOT NULL DEFAULT 'confirmed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `forum_comments`
--

CREATE TABLE `forum_comments` (
  `comment_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `forum_posts`
--

CREATE TABLE `forum_posts` (
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `status` enum('open','closed','pinned') NOT NULL DEFAULT 'open',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `profile`
--

CREATE TABLE `profile` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `program`
--

CREATE TABLE `program` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `capacity` int(11) NOT NULL DEFAULT 0,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `status` enum('active','cancelled','full') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ticket`
--

CREATE TABLE `ticket` (
  `idTicket` int(11) NOT NULL,
  `ref` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `idTrajet` int(11) NOT NULL,
  `issuedAt` datetime NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) NOT NULL DEFAULT 'Valid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `trajet`
--

CREATE TABLE `trajet` (
  `idTrajet` int(11) NOT NULL,
  `departure` varchar(255) NOT NULL,
  `destination` varchar(255) NOT NULL,
  `idTransport` int(11) NOT NULL,
  `departureTime` datetime NOT NULL,
  `price` decimal(10,3) NOT NULL DEFAULT 0.000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transport`
--

CREATE TABLE `transport` (
  `idTransport` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(100) NOT NULL,
  `capacity` int(11) NOT NULL,
  `plateNumber` varchar(255) DEFAULT NULL,
  `status` varchar(100) NOT NULL DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('citizen','agent','admin') NOT NULL DEFAULT 'citizen',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`request_id`);

--
-- Indexes for table `enrollment`
--
ALTER TABLE `enrollment`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user_program` (`user_id`,`program_id`),
  ADD KEY `program_id` (`program_id`);

--
-- Indexes for table `forum_comments`
--
ALTER TABLE `forum_comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `forum_posts`
--
ALTER TABLE `forum_posts`
  ADD PRIMARY KEY (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `profile`
--
ALTER TABLE `profile`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `program`
--
ALTER TABLE `program`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ticket`
--
ALTER TABLE `ticket`
  ADD PRIMARY KEY (`idTicket`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idTrajet` (`idTrajet`);

--
-- Indexes for table `trajet`
--
ALTER TABLE `trajet`
  ADD PRIMARY KEY (`idTrajet`),
  ADD KEY `idTransport` (`idTransport`);

--
-- Indexes for table `transport`
--
ALTER TABLE `transport`
  ADD PRIMARY KEY (`idTransport`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enrollment`
--
ALTER TABLE `enrollment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `forum_comments`
--
ALTER TABLE `forum_comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `forum_posts`
--
ALTER TABLE `forum_posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `profile`
--
ALTER TABLE `profile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `program`
--
ALTER TABLE `program`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ticket`
--
ALTER TABLE `ticket`
  MODIFY `idTicket` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `trajet`
--
ALTER TABLE `trajet`
  MODIFY `idTrajet` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transport`
--
ALTER TABLE `transport`
  MODIFY `idTransport` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `enrollment`
--
ALTER TABLE `enrollment`
  ADD CONSTRAINT `enrollment_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollment_ibfk_2` FOREIGN KEY (`program_id`) REFERENCES `program` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `forum_comments`
--
ALTER TABLE `forum_comments`
  ADD CONSTRAINT `forum_comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `forum_posts` (`post_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `forum_posts`
--
ALTER TABLE `forum_posts`
  ADD CONSTRAINT `forum_posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `profile`
--
ALTER TABLE `profile`
  ADD CONSTRAINT `profile_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ticket`
--
ALTER TABLE `ticket`
  ADD CONSTRAINT `ticket_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ticket_ibfk_2` FOREIGN KEY (`idTrajet`) REFERENCES `trajet` (`idTrajet`) ON DELETE CASCADE;

--
-- Constraints for table `trajet`
--
ALTER TABLE `trajet`
  ADD CONSTRAINT `trajet_ibfk_1` FOREIGN KEY (`idTransport`) REFERENCES `transport` (`idTransport`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
