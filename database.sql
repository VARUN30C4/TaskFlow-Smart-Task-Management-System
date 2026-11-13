-- =====================================================================
-- TaskFlow – Smart Task Management System
-- Technical Assignment for IT Internship at RTIH Amaravati Foundation
-- Database Schema and Sample Data
-- Database Name: taskflow_db
-- =====================================================================

-- 1. Create Database if not exists
CREATE DATABASE IF NOT EXISTS `taskflow_db` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `taskflow_db`;

-- ---------------------------------------------------------------------
-- 2. Table structure for table `users`
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `tasks`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 3. Table structure for table `tasks`
-- ---------------------------------------------------------------------
CREATE TABLE `tasks` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `status` ENUM('Pending', 'In Progress', 'Completed') NOT NULL DEFAULT 'Pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_tasks_user_id` (`user_id`),
  INDEX `idx_tasks_status` (`status`),
  INDEX `idx_tasks_title` (`title`),
  CONSTRAINT `fk_tasks_user` 
    FOREIGN KEY (`user_id`) 
    REFERENCES `users` (`id`) 
    ON DELETE CASCADE 
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 4. Safe Sample Data for Development and Testing
-- 
-- Default Password for sample accounts is: Password123!
-- (Pre-hashed with PHP password_hash / standard BCRYPT algorithm)
-- ---------------------------------------------------------------------
INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `created_at`) VALUES
(1, 'John Doe', 'john.doe@example.com', '$2y$10$x6twu0nq5PTwoJ1hxi9As.wTM56kGlLkxzAP8qWE3UXe.wfd//5MG', '2026-11-01 09:00:00'),
(2, 'Jane Smith', 'jane.smith@example.com', '$2y$10$x6twu0nq5PTwoJ1hxi9As.wTM56kGlLkxzAP8qWE3UXe.wfd//5MG', '2026-11-02 10:30:00');

-- Reset Auto-Increment for users
ALTER TABLE `users` AUTO_INCREMENT = 3;

-- Sample tasks for User 1 (John Doe) and User 2 (Jane Smith)
INSERT INTO `tasks` (`id`, `user_id`, `title`, `description`, `status`, `created_at`) VALUES
(1, 1, 'Complete RTIH Internship Technical Assignment', 'Develop complete Core PHP Task Tracker with Level 1, 2, and 3 requirements including security audits.', 'In Progress', '2026-11-10 10:15:00'),
(2, 1, 'Set up Apache & MySQL on XAMPP', 'Configure localhost port 80 and MySQL port 3306 for local testing environment.', 'Completed', '2026-11-10 11:30:00'),
(3, 1, 'Implement CSRF and XSS Security Protection', 'Ensure all state-changing POST forms use random_bytes CSRF tokens and output is escaped with htmlspecialchars.', 'Completed', '2026-11-11 14:00:00'),
(4, 1, 'Design Responsive Bootstrap 5 UI', 'Build mobile-friendly dashboard with status badges, interactive modals, and summary statistics cards.', 'Pending', '2026-11-12 16:45:00'),
(5, 2, 'Prepare Weekly Sprint Planning Presentation', 'Slide deck covering upcoming deliverables, feature backlogs, and milestone reviews for RTIH.', 'Pending', '2026-11-12 09:00:00'),
(6, 2, 'Conduct Authorization Boundary Security Review', 'Verify that logged-in users cannot access or mutate tasks belonging to other user accounts via IDOR.', 'In Progress', '2026-11-12 14:20:00');

-- Reset Auto-Increment for tasks
ALTER TABLE `tasks` AUTO_INCREMENT = 7;
