-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 27, 2025 at 12:27 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

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
  `teacher_id` int(11) DEFAULT NULL,
  `total_marks` decimal(10,2) NOT NULL DEFAULT 100.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assignments`
--

INSERT INTO `assignments` (`id`, `title`, `description`, `subject_id`, `class_id`, `session_id`, `due_date`, `teacher_id`, `total_marks`) VALUES
(18, 'Climate Change and Its Effects on Nigeria', 'Write an essay of 300–500 words discussing the causes and effects of climate change on Nigeria’s environment and economy. Highlight at least two causes and three major effects, and suggest possible solutions. Use well-organized paragraphs, topic sentences, and supporting details.', 6, 4, 2, '2025-04-30', 2, 100.00),
(19, 'Balancing Chemical Equations', 'Balance the following chemical equations:\r\nH₂ + O₂ → H₂O\r\nFe + O₂ → Fe₂O₃\r\nC₃H₈ + O₂ → CO₂ + H₂O\r\nNa + H₂O → NaOH + H₂', 4, 4, 2, '2025-04-30', 2, 100.00),
(20, 'First Assignment', 'What is 22 + 21', 1, 4, 2, '2025-04-30', 2, 100.00),
(21, 'first ', 'what is a noun', 2, 4, 2, '2025-04-30', 2, 100.00),
(22, '2', 'evaluate X2 + 5x + 6 = 0', 2, 4, 2, '2025-04-30', 2, 100.00);

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
  `remarks` text DEFAULT NULL,
  `percentage` decimal(5,2) DEFAULT NULL,
  `achieved_marks` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`id`, `submission_id`, `score`, `remarks`, `percentage`, `achieved_marks`) VALUES
(43, 47, 78, 'Strong points: The student correctly identifies causes and effects of climate change in Nigeria, fulfilling the prompt\'s requirements regarding quantity. The suggested solutions are relevant and appropriate. The answer demonstrates a basic understanding of the topic. Areas to improve: The essay lacks depth of analysis; connections between causes, effects, and solutions are not thoroughly explored.  The writing is simplistic and lacks supporting details to substantiate the claims.  While grammatically correct, it lacks sophisticated vocabulary and sentence structure. Completeness is partially lacking due to the lack of detail. Originality is minimal. One specific suggestion:  Expand on each point by providing specific examples and data to support the claims (e.g., statistics on deforestation rates, specific instances of extreme weather events, details on government policies).', 78.00, 78.00),
(44, 48, 87, 'Strong points: All equations are correctly balanced. Explanations are provided for each equation.  Areas to improve: Explanations could be more detailed, showing the step-by-step balancing process. Grammar and clarity could be slightly improved in some explanations. One specific suggestion: Show the step-by-step balancing process for each equation (e.g., start with balancing the most complex molecule, then proceed to balance the rest).  This would enhance the clarity and completeness of the answer.', 87.00, 87.00),
(45, 49, 67, 'Strong points: The student correctly identifies burning fossil fuels and deforestation as causes of climate change in Nigeria and provides three relevant effects. The suggested solutions are also appropriate.  Areas to improve: The essay lacks depth and detailed explanation.  The structure is weak; it\'s essentially a list rather than a well-organized essay with developed paragraphs and topic sentences.  The word count is far below the minimum.  Grammar and clarity could be improved. Completeness is lacking due to insufficient detail and explanation. Originality is also lacking; the answer is quite generic. One specific suggestion: Expand on each cause and effect with specific examples, statistics, and detailed explanations to create a more comprehensive and persuasive essay.  For example, instead of simply stating \"agricultural losses,\" discuss specific crops affected, the extent of yield reduction, and the socio-economic consequences.', 67.00, 67.00),
(46, 50, 90, 'Strong points: All chemical equations are correctly balanced. Explanations, while brief, are generally accurate and demonstrate understanding of the balancing process. Grammar and punctuation are mostly correct. Areas to improve: Explanations could be more detailed and systematic (e.g., explicitly stating the steps taken to balance each element).  The explanation for the last equation is less clear than the others. Originality is not applicable to this question.  One specific suggestion:  Improve the explanations by explicitly listing the number of atoms of each element on both sides of the equation *before* and *after* balancing, showing the step-by-step process for balancing each element.', 90.00, 90.00),
(53, 57, 90, 'Correct answer.  [Correct calculation] [No work shown, lacks completeness] [Show your working steps to demonstrate understanding e.g., 22 + 20 = 42, 42 + 1 = 43]', 90.00, 90.00),
(54, 58, 70, 'Strong points: Correctly identifies the basic definition of a noun. Areas to improve: Lacks completeness and sophistication; grammar could be improved. One specific suggestion:  Expand the definition to include abstract nouns (e.g., ideas, concepts) and give examples of each type of noun.', 70.00, 70.00),
(55, 59, 70, 'Strong points: Correctly identified the solutions x = -2 and x = -3. Areas to improve: Inaccurate factoring process; lacks explanation and shows incomplete working. One specific suggestion: Show all steps clearly and explain the factoring method used (e.g., using the quadratic formula or explaining the logic behind finding factors that add up to 5 and multiply to 6).', 70.00, 70.00);

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
(5, 'Biology'),
(6, 'Geography');

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
(47, 8, 18, 'Climate change refers to long-term changes in the Earth\'s climate, particularly due to human activities such as burning fossil fuels and deforestation. In Nigeria, it has significant effects:\r\n\r\nCauses:\r\n\r\nGreenhouse Gas Emissions: The burning of fossil fuels for energy, transportation, and industrial activities increases carbon dioxide (CO₂) in the atmosphere.\r\n\r\nDeforestation: The widespread clearing of forests for agriculture and urbanization reduces the Earth\'s ability to absorb CO₂, exacerbating climate change.\r\n\r\nEffects:\r\n\r\nExtreme Weather Patterns: Nigeria faces more frequent and intense floods, droughts, and heatwaves. These disrupt agricultural production and lead to food insecurity.\r\n\r\nRising Sea Levels: Coastal cities like Lagos are at risk of flooding due to rising sea levels, which threaten infrastructure and livelihoods.\r\n\r\nLoss of Biodiversity: Habitat destruction and changing climates endanger wildlife species, particularly in Nigeria’s national parks and reserves.\r\n\r\nSolutions:\r\n\r\nRenewable Energy Sources: Promoting the use of wind, solar, and hydroelectric power can reduce CO₂ emissions.\r\n\r\nReforestation: Planting trees and reducing deforestation can help absorb carbon and mitigate environmental damage.\r\n\r\nGovernment Regulations: Policies such as carbon taxation, pollution control, and promoting energy efficiency can reduce environmental harm.', '2025-04-08 22:32:20'),
(48, 8, 19, 'H₂ + O₂ → H₂O\r\nBalanced equation: 2H₂ + O₂ → 2H₂O\r\nExplanation: The number of atoms on both sides must be equal. There are 4 hydrogen atoms and 2 oxygen atoms on both sides.\r\n\r\nFe + O₂ → Fe₂O₃\r\nBalanced equation: 4Fe + 3O₂ → 2Fe₂O₃\r\nExplanation: Four iron (Fe) atoms and three oxygen (O₂) molecules balance both sides of the equation.\r\n\r\nC₃H₈ + O₂ → CO₂ + H₂O\r\nBalanced equation: C₃H₈ + 5O₂ → 3CO₂ + 4H₂O\r\nExplanation: Balance carbon (C), hydrogen (H), and oxygen (O) atoms on both sides.\r\n\r\nNa + H₂O → NaOH + H₂\r\nBalanced equation: 2Na + 2H₂O → 2NaOH + H₂\r\nExplanation: There are two sodium (Na) atoms, two oxygen atoms, and two hydrogen atoms on both sides.', '2025-04-08 22:32:20'),
(49, 12, 18, 'Climate change is largely driven by human activities, particularly the burning of fossil fuels, which increases the concentration of greenhouse gases in the atmosphere. In Nigeria, this results in several adverse effects:\r\n\r\nCauses:\r\n\r\nBurning of Fossil Fuels: Nigeria’s reliance on petroleum and coal contributes significantly to greenhouse gas emissions.\r\n\r\nDeforestation for Agriculture: Widespread deforestation for farming reduces the country’s capacity to absorb CO₂.\r\n\r\nEffects:\r\n\r\nAgricultural Losses: Prolonged droughts and erratic rainfall patterns disrupt farming, causing crop failure and food shortages.\r\n\r\nIncreased Flooding: Coastal regions, especially Lagos, are more susceptible to flooding due to rising sea levels.\r\n\r\nWater Scarcity: Climate change leads to a reduction in freshwater availability as rainfall patterns change and water sources dry up.\r\n\r\nSolutions:\r\n\r\nDiversified Energy Sources: Investing in renewable energy like wind and solar power will reduce Nigeria’s carbon footprint.\r\n\r\nSustainable Land Management: Encouraging agroforestry and sustainable farming practices can help preserve ecosystems.', '2025-04-08 22:32:20'),
(50, 12, 19, 'H₂ + O₂ → H₂O\r\nBalanced equation: 2H₂ + O₂ → 2H₂O\r\nExplanation: To balance hydrogen, two molecules of H₂ are required for every O₂ molecule.\r\n\r\nFe + O₂ → Fe₂O₃\r\nBalanced equation: 4Fe + 3O₂ → 2Fe₂O₃\r\nExplanation: To balance iron, four Fe atoms are needed to form two molecules of Fe₂O₃.\r\n\r\nC₃H₈ + O₂ → CO₂ + H₂O\r\nBalanced equation: C₃H₈ + 5O₂ → 3CO₂ + 4H₂O\r\nExplanation: Balancing carbon, hydrogen, and oxygen atoms results in three molecules of CO₂ and four molecules of H₂O.\r\n\r\nNa + H₂O → NaOH + H₂\r\nBalanced equation: 2Na + 2H₂O → 2NaOH + H₂\r\nExplanation: Two sodium atoms and two water molecules balance the reaction.', '2025-04-08 22:32:20'),
(57, 9, 20, '43', '2025-04-08 22:32:20'),
(58, 10, 21, 'a noun is a name of person place animal or things', '2025-04-08 22:32:20'),
(59, 10, 22, 'x2 + 2x+3x+6\r\n(x2 + 2x) (3X+6)\r\nx(x+2)3(x+2)=0\r\n(X+2)(X+3)=0\r\nX+2=0 or X+3=0\r\nx=-2, X=-3\r\n-2, -3', '2025-04-08 22:32:20');

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
  `section_id` int(11) DEFAULT NULL,
  `created_at` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `full_name`, `email`, `class_id`, `section_id`, `created_at`) VALUES
(1, 'admin1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Admin User', 'admin@example.com', NULL, NULL, ''),
(2, 'teacher1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'Teacher One', 'teacher1@example.com', 1, 1, ''),
(3, 'teacher2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'Teacher Two', 'teacher2@example.com', 2, 2, ''),
(4, 'student1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Student One', 'student1@example.com', 1, 1, ''),
(5, 'student2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Student Two', 'student2@example.com', 1, 1, ''),
(6, 'student3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Student Three', 'student3@example.com', 2, 2, ''),
(7, 'student4', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Student Four', 'student4@example.com', 2, 2, ''),
(8, 'student5', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Student Five', 'student5@example.com', 4, 1, ''),
(9, 'Chidera', '$2y$10$m6b5hwnHjwlOyu/WY9coEu8jPhmmubFImL472/8rL7zLNLITXqPHO', 'student', 'Chidera Okafor', 'ChideraOkafor@gmail.com', 4, 1, '2025-02-20 22:22:46'),
(10, 'Adebayo', '$2y$10$DOraE/MAEYnWYyR0rO15tOkAcRb/jRzKjluaLBBW2jAAIFSKGk7bC', 'student', 'Adebayo Adeyemi', 'AdebayoAdeyemi@gmail.com', 4, 1, '2025-02-20 22:22:46'),
(11, 'Zainab', '$2y$10$5Pe73vgwE37X67XMpFnsYO96.omTj/18MgZlwO58rQ20o3U81uoWe', 'student', 'Zainab Abubakar', 'ZainabAbubakar@gmail.com', 4, 1, '2025-02-20 22:22:46'),
(12, 'Ngozi', '$2y$10$2tBRNVb0XcuS3IoiNmL4d.ho0enptRN92NKpDVE4kwNQOM3UrviAC', 'student', 'Ngozi Eze', 'NgoziEze@gmail.com', 4, 1, '2025-02-20 22:22:46');

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
(16, 1, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(17, 4, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(18, 2, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(19, 1, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(20, 2, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(21, 4, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(22, 8, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(23, 2, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(24, 2, 'create_assignment', 'Created assignment: hi with total marks: 10', '2025-02-21 19:20:31'),
(25, 8, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(26, 2, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(27, 1, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(28, 4, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(29, 8, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(30, 2, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(31, 8, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(32, 2, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(33, 1, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(34, 2, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(35, 2, 'create_assignment', 'Created assignment: first assignment with total marks: 10', '2025-02-21 19:20:31'),
(36, 8, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(37, 2, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(38, 2, 'create_assignment', 'Created assignment: Quasi molestias plac with total marks: 10', '2025-02-21 19:20:31'),
(39, 8, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(40, 2, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(41, 4, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(42, 4, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(43, 8, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(44, 2, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(45, 8, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(46, 2, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(47, 1, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(48, 2, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(49, 8, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(50, 2, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(51, 1, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(52, 2, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(53, 12, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(54, 2, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(55, 9, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(56, 10, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(57, 9, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(58, 10, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(59, 2, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(60, 10, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(61, 2, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(62, 10, 'login', 'User logged in from ::1', '2025-02-20 22:44:00'),
(63, 2, 'login', 'User logged in from ::1', '2025-02-20 22:44:00');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `submissions`
--
ALTER TABLE `submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

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
