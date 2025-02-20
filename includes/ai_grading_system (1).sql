-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Feb 20, 2025 at 11:50 PM
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
-- Database: `ai_grading_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `subject_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `due_date` date NOT NULL,
  `teacher_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assignments`
--

INSERT INTO `assignments` (`id`, `title`, `description`, `subject_id`, `class_id`, `session_id`, `due_date`, `teacher_id`) VALUES
(1, 'Math Assignment 1', 'Solve the following problems.', 1, 1, 1, '2023-10-15', NULL),
(2, 'English Essay', 'Write an essay on your favorite book.', 2, 2, 1, '2023-10-20', NULL),
(3, 'Physics Lab Report', 'Submit your lab report for Experiment 3.', 3, 3, 1, '2023-10-25', NULL),
(4, 'Pariatur Enim minus', 'what is a noun', 2, 1, 1, '2019-12-27', NULL),
(5, 'Quo incidunt est i', 'what is noun', 2, 4, 2, '2015-12-22', 2),
(6, 'Quas nostrum sit qu', 'Esse sit aliqua La', 2, 2, 1, '2013-09-07', 2),
(7, 'Qui irure rerum iure', 'Qui duis nihil ipsa', 3, 2, 2, '1991-07-21', 2),
(8, 'new', 'what is 1+1', 1, 1, 1, '2025-02-28', 2),
(9, 'first assignment', 'Identify the adjective in the following sentence:\r\n\"The tall boy quickly ran across the busy street.\"', 2, 4, 2, '2025-02-28', 2),
(10, 'me', 'what is 1+1', 1, 4, 2, '2025-02-28', 2),
(11, 'Qui dignissimos iste', 'what i adjective', 2, 4, 2, '2025-02-28', 2),
(12, 'Fugit iste enim vol', 'Ut ratione laudantiu', 3, 3, 1, '2025-02-20', 2);

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `name`) VALUES
(1, 'JSS1'),
(2, 'JSS2'),
(3, 'SS1'),
(4, 'SS2');

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `submission_id` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`id`, `submission_id`, `score`, `remarks`) VALUES
(27, 32, 0, 'This answer is completely unacceptable.  You need to focus on answering the question, not using inappropriate language.  The question asked you to identify the adjective in the sentence.  \"Tall\" is the adjective describing the boy.  Review the definition of an adjective and practice identifying them in sentences.  Your response shows a complete lack of effort and understanding.  A passing grade requires a genuine attempt to answer the question correctly.'),
(28, 33, 10, 'Your answer is incorrect. A noun is a word that names a person, place, thing, or idea.  \"Action ward\" is not a grammatical term. To improve, review the definition of a noun and learn to identify different types of nouns (e.g., proper nouns, common nouns, concrete nouns, abstract nouns).'),
(30, 35, 91, 'Your answer is accurate and demonstrates a clear understanding of basic arithmetic.  You have correctly calculated 1 + 1 = 2.  Because this is such a fundamental problem, originality is not a significant factor to assess here. Your answer is grammatically correct and clearly presented. Therefore, the only deduction is a minor one in the accuracy portion, reflecting the simplicity of the question and the limited opportunity to demonstrate advanced mathematical skill.  For more complex mathematical problems, ensure you show your working, as this can help to improve clarity and aid in identifying any potential errors in your reasoning.'),
(31, 25, 0, 'answer is incurrect');

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`id`, `name`) VALUES
(1, 'Section A'),
(2, 'Section B');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `name`) VALUES
(1, '2022/2023'),
(2, '2023/2024');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `name`) VALUES
(1, 'Mathematics'),
(2, 'English'),
(3, 'Physics'),
(4, 'Chemistry'),
(5, 'Biology');

-- --------------------------------------------------------

--
-- Table structure for table `submissions`
--

CREATE TABLE `submissions` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `submission_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `submissions`
--

INSERT INTO `submissions` (`id`, `student_id`, `assignment_id`, `content`, `submission_date`) VALUES
(25, 4, 8, '1+1=3', '2025-02-13 20:11:15'),
(32, 8, 9, 'i dont know you shit', '2025-02-20 17:36:34'),
(33, 8, 5, 'Action ward', '2025-02-20 17:48:39'),
(35, 8, 10, '2', '2025-02-20 17:18:17');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','teacher','admin') NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `class_id` int(11) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `full_name`, `email`, `class_id`, `section_id`) VALUES
(1, 'admin1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Admin User', 'admin@example.com', NULL, NULL),
(2, 'teacher1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'Teacher One', 'teacher1@example.com', 1, 1),
(3, 'teacher2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'Teacher Two', 'teacher2@example.com', 2, 2),
(4, 'student1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Student One', 'student1@example.com', 1, 1),
(5, 'student2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Student Two', 'student2@example.com', 1, 1),
(6, 'student3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Student Three', 'student3@example.com', 2, 2),
(7, 'student4', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Student Four', 'student4@example.com', 2, 2),
(8, 'student5', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Student Five', 'student5@example.com', 4, 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_activity_logs`
--

CREATE TABLE `user_activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity_type` varchar(50) NOT NULL,
  `activity_details` text DEFAULT NULL,
  `timestamp` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_activity_logs`
--

INSERT INTO `user_activity_logs` (`id`, `user_id`, `activity_type`, `activity_details`, `timestamp`) VALUES
(1, 2, 'login', 'User logged in from ::1', '2025-02-20 16:47:16'),
(2, 4, 'login', 'User logged in from ::1', '2025-02-20 17:11:24'),
(3, 2, 'login', 'User logged in from ::1', '2025-02-20 17:26:02'),
(4, 1, 'login', 'User logged in from ::1', '2025-02-20 17:26:24'),
(5, 4, 'login', 'User logged in from ::1', '2025-02-20 17:28:36'),
(6, 8, 'login', 'User logged in from ::1', '2025-02-20 17:30:09'),
(7, 8, 'login', 'User logged in from 192.168.232.21', '2025-02-20 17:47:41'),
(8, 8, 'login', 'User logged in from ::1', '2025-02-20 17:48:04'),
(9, 2, 'login', 'User logged in from ::1', '2025-02-20 17:49:46'),
(10, 8, 'login', 'User logged in from ::1', '2025-02-20 17:55:04'),
(11, 2, 'login', 'User logged in from ::1', '2025-02-20 18:24:43'),
(12, 2, 'login', 'User logged in from ::1', '2025-02-20 22:14:22'),
(13, 2, 'login', 'User logged in from ::1', '2025-02-20 22:14:22'),
(14, 2, 'login', 'User logged in from ::1', '2025-02-20 22:14:42'),
(15, 1, 'login', 'User logged in from ::1', '2025-02-20 22:17:10'),
(16, 1, 'login', 'User logged in from ::1', '2025-02-20 22:44:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `session_id` (`session_id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `submission_id` (`submission_id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `submissions`
--
ALTER TABLE `submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `assignment_id` (`assignment_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `submissions`
--
ALTER TABLE `submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `assignments_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `assignments_ibfk_3` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`submission_id`) REFERENCES `submissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `submissions`
--
ALTER TABLE `submissions`
  ADD CONSTRAINT `submissions_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `submissions_ibfk_2` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  ADD CONSTRAINT `user_activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
