-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 05, 2026 at 05:47 AM
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
-- Database: `youruni_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(80) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password_hash`, `created_at`, `last_login_at`) VALUES
(1, 'Loop', '$2y$10$R.a5kQJwfJPYVfZiFHfxb.QGj.HYMzKJ.tGqnut62SJHEdrwClxFG', '2026-10-17 12:42:11', '2026-11-05 03:07:43');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `university_name` varchar(255) NOT NULL,
  `major` varchar(100) NOT NULL,
  `zone` varchar(100) NOT NULL,
  `min_marks` int(11) NOT NULL DEFAULT 0,
  `image_url` varchar(255) DEFAULT 'photos/default.jpg',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `page_link` varchar(255) DEFAULT '#'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `opportunities`
--

CREATE TABLE `opportunities` (
  `id` int(10) UNSIGNED NOT NULL,
  `slug` varchar(120) NOT NULL,
  `title` varchar(180) NOT NULL,
  `icon` varchar(32) NOT NULL DEFAULT '?',
  `description` text NOT NULL,
  `university_examples` varchar(255) NOT NULL DEFAULT '',
  `page_link` varchar(255) NOT NULL DEFAULT '#',
  `css_class` varchar(40) NOT NULL DEFAULT 'card-cs',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `opportunities`
--

INSERT INTO `opportunities` (`id`, `slug`, `title`, `icon`, `description`, `university_examples`, `page_link`, `css_class`, `sort_order`, `is_active`, `created_at`) VALUES
(1, 'engineering', 'Engineering & Tech', '⚙️', 'Designing, building, and maintaining structures, machines, and technological systems.', 'YTU • MTU • UTYCC', 'Engineering.html', 'card-eng', 1, 1, '2026-10-17 12:41:22'),
(2, 'computer-science', 'Computer Science & IT', '💻', 'Software development, artificial intelligence, cybersecurity, and data science.', 'UIT • UCSY • MIIT', 'Computerscience.html', 'card-cs', 2, 1, '2026-10-17 12:41:22'),
(3, 'medicine', 'Medicine & Healthcare', '⚕️', 'Clinical practice, surgical medicine, nursing, public health, and pharmacy.', 'UM1 • UM2 • UMM', 'medicine.html', 'card-med', 3, 1, '2026-10-17 12:41:22'),
(4, 'business', 'Business & Management', '📊', 'Financial management, digital marketing, economics, and business administration.', 'Yangon Eco • Meiktila Eco', 'business.html', 'card-biz', 4, 1, '2026-10-17 12:41:22'),
(5, 'foreign-languages', 'Foreign Languages', '🌐', 'International relations, translation, diplomacy, and global business trade.', 'YUFL • MUFL', 'language.html', 'card-lang', 5, 1, '2026-10-17 12:41:22'),
(6, 'law', 'Law & Governance', '⚖️', 'Legal frameworks, public policy, human rights, and corporate law compliance.', 'Yangon University • Mandalay University', 'Law.html', 'card-law', 6, 1, '2026-10-17 12:41:22'),
(7, 'agriculture', 'Agriculture & Forestry', '🌱', 'Agritech, animal husbandry, forest management, and environmental conservation.', 'YAU • UVS • UFES', 'agriculture.html', 'card-agri', 7, 1, '2026-10-17 12:41:22'),
(8, 'education', 'Education & Pedagogy', '📚', 'Teacher training, curriculum development, and educational administration.', 'Education universities • Sagaing education', 'education.html', 'card-edu', 8, 1, '2026-10-17 12:41:22'),
(9, 'arts-culture', 'Arts & Culture', '🎨', 'Creative disciplines including visual arts, traditional performance, theater, and heritage.', 'NUAC Yangon • NUAC Mandalay', 'arts.html', 'card-art', 9, 1, '2026-10-17 12:41:22'),
(10, 'arts-science', 'Arts & Science', '🔬', 'Explore sciences and humanities including physics, chemistry, history, and psychology.', 'Yangon University • Mandalay University', 'arts-science.html', 'card-science', 10, 1, '2026-10-17 12:41:22'),
(11, 'defense', 'Defense & Military', '🛡️', 'Specialized military training, including command, engineering, and healthcare pathways.', 'DSA • DSTA • DSMA', 'defense.html', 'card-defense', 11, 1, '2026-10-17 12:41:22');

-- --------------------------------------------------------

--
-- Table structure for table `specializations`
--

CREATE TABLE `specializations` (
  `id` int(10) UNSIGNED NOT NULL,
  `opportunity_id` int(10) UNSIGNED NOT NULL,
  `slug` varchar(120) NOT NULL,
  `title` varchar(180) NOT NULL,
  `title_my` varchar(180) DEFAULT NULL,
  `description` text NOT NULL,
  `description_my` text DEFAULT NULL,
  `work_context` text DEFAULT NULL,
  `work_context_my` text DEFAULT NULL,
  `icon` varchar(32) NOT NULL DEFAULT '?',
  `badge_text` varchar(80) NOT NULL DEFAULT 'Major / Specialization',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `specializations`
--

INSERT INTO `specializations` (`id`, `opportunity_id`, `slug`, `title`, `title_my`, `description`, `description_my`, `work_context`, `work_context_my`, `icon`, `badge_text`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'civil-engineering', 'Civil Engineering', NULL, 'Plan, design, and supervise the construction of essential public infrastructure, including highways, bridges, water systems, and large buildings.', NULL, 'Major construction firms (e.g., Shwe Taung, Max Myanmar), urban planning departments, and civil engineering consultancies.', NULL, '🏗️', '🔥 High Demand', 1, 1, '2026-10-17 12:41:22', '2026-10-17 12:41:22'),
(2, 1, 'electronic-engineering', 'Electronic Engineering', NULL, 'Design and develop electronic circuits, microprocessors, and embedded systems for consumer electronics, medical devices, and control systems.', NULL, 'Semiconductor companies, consumer electronics manufacturers, telecommunications firms, and embedded systems R&D labs.', NULL, '🔌', '⚡ Hardware', 2, 1, '2026-10-17 12:41:22', '2026-10-17 12:41:22'),
(3, 1, 'electrical-power-engineering', 'Electrical Power Engineering', NULL, 'Generate, transmit, and distribute electrical power. Design high-voltage grids, renewable energy systems, and industrial electrical networks.', NULL, 'Power distribution boards (e.g., YESC, EPC), renewable energy corporations, and industrial manufacturing plants.', NULL, '⚡', '💡 Essential', 3, 1, '2026-10-17 12:41:22', '2026-10-17 12:41:22'),
(4, 1, 'mechanical-engineering', 'Mechanical Engineering', NULL, 'Apply physics and materials science to design, analyze, and manufacture mechanical systems, engines, HVAC units, and heavy machinery.', NULL, 'Automotive assembly lines, industrial manufacturing facilities, energy sector plants, and heavy equipment engineering firms.', NULL, '⚙️', '🛠️ Core Role', 4, 1, '2026-10-17 12:41:22', '2026-10-17 12:41:22'),
(5, 1, 'telecommunication-engineering', 'Telecommunication Engineering', NULL, 'Design and manage communication networks, including fiber optics, satellite transmission, 5G cellular towers, and broad-scale internet infrastructure.', NULL, 'Telecom operators (ATOM, MPT, Ooredoo), internet service providers, and network infrastructure firms.', NULL, '📡', '📶 Connectivity', 5, 1, '2026-10-17 12:41:22', '2026-10-17 12:41:22'),
(6, 1, 'computer-engineering-and-it', 'Computer Engineering & IT', NULL, 'Bridge hardware and software by designing computing architecture, developing enterprise software systems, and managing robust IT networks.', NULL, 'Tech startups, corporate IT departments, fintech platforms, software development agencies, and data centers.', NULL, '💻', '🔥 High Demand', 6, 1, '2026-10-17 12:41:22', '2026-10-17 12:41:22'),
(7, 1, 'mechatronic-engineering', 'Mechatronic Engineering', NULL, 'Combine mechanical, electronic, and software engineering to develop automated manufacturing lines, smart robotics, and autonomous systems.', NULL, 'Advanced manufacturing plants, robotics startups, aerospace tech firms, and industrial automation R&D labs.', NULL, '🤖', '🚀 Future Tech', 7, 1, '2026-10-17 12:41:22', '2026-10-17 12:41:22'),
(8, 1, 'chemical-engineering', 'Chemical Engineering', NULL, 'Apply chemistry and engineering principles to design processes for producing fuels, pharmaceuticals, food products, and advanced materials.', NULL, 'Oil and gas corporations, pharmaceutical manufacturers, environmental agencies, and chemical processing plants.', NULL, '🧪', '🏭 Industrial', 8, 1, '2026-10-17 12:41:22', '2026-10-17 12:41:22'),
(9, 1, 'food-engineering', 'Food Engineering', NULL, 'Design industrial processes for food production, focusing on preservation, packaging, quality control, and large-scale nutritional extraction.', NULL, 'Food and beverage manufacturing plants, agricultural processing facilities, and Fast-Moving Consumer Goods (FMCG) conglomerates.', NULL, '🥫', '🌾 FMCG', 9, 1, '2026-10-17 12:41:22', '2026-10-17 12:41:22'),
(10, 1, 'textile-engineering', 'Textile Engineering', NULL, 'Engineer fibers, yarns, and fabrics. Develop new textile machinery processes, sustainable fabrics, and quality control systems for garment production.', NULL, 'Garment manufacturing hubs, synthetic fiber production plants, and textile quality assurance laboratories.', NULL, '🧵', '👕 Manufacturing', 10, 1, '2026-10-17 12:41:22', '2026-10-17 12:41:22'),
(11, 1, 'mining-engineering', 'Mining Engineering', NULL, 'Plan and execute the safe, efficient extraction of minerals and resources from the earth using underground and open-pit mining techniques.', NULL, 'Mining operations, resource exploration companies, geological testing firms, and explosive engineering agencies.', NULL, '⛏️', '⛰️ Resource', 11, 1, '2026-10-17 12:41:22', '2026-10-17 12:41:22'),
(12, 1, 'petroleum-engineering', 'Petroleum Engineering', NULL, 'Design methods for extracting oil and natural gas from reservoirs deep underground, optimizing drilling equipment and recovery processes.', NULL, 'Energy corporations, offshore drilling rigs, petroleum refineries, and pipeline management firms.', NULL, '🛢️', '⛰️ Resource', 12, 1, '2026-10-17 12:41:22', '2026-10-17 12:41:22'),
(13, 1, 'metallurgical-engineering-and-materials-science', 'Metallurgical Engineering & Materials Science', NULL, 'Study the physical and chemical behavior of metallic elements, develop new alloys, and test composites for structural or electronic applications.', NULL, 'Steel and metal refineries, manufacturing R&D labs, semiconductor firms, and aerospace developers.', NULL, '🔬', '🧪 Research', 13, 1, '2026-10-17 12:41:22', '2026-10-17 12:41:22'),
(14, 1, 'architecture', 'Architecture', NULL, 'Blend art and engineering to design functional, safe, and aesthetically pleasing buildings, focusing on spatial planning and environmental impact.', NULL, 'Architectural design studios, urban planning firms, real estate development companies, and independent practices.', NULL, '🏛️', '🎨 Creative Design', 14, 1, '2026-10-17 12:41:22', '2026-10-17 12:41:22'),
(15, 1, 'naval-architecture', 'Naval Architecture', NULL, 'Design the hull form, structure, and internal layout of ships and marine vessels, ensuring stability, buoyancy, and hydrodynamic efficiency.', NULL, 'Shipyards, maritime design consultancies, naval defense contractors, and offshore engineering firms.', NULL, '🚢', 'Design & Hydrodynamics', 15, 1, '2026-10-17 12:41:22', '2026-10-17 12:41:22'),
(16, 1, 'marine-engineering', 'Marine Engineering', NULL, 'Oversee the design, installation, and maintenance of a vessel\'s internal operational systems, including steering, ventilation, and power generation.', NULL, 'Commercial shipping companies, cruise lines, maritime regulatory agencies, and vessel maintenance facilities.', NULL, '⚙️', 'Systems Operation', 16, 1, '2026-10-17 12:41:22', '2026-10-17 12:41:22'),
(17, 1, 'port-and-harbour-engineering', 'Port and Harbour Engineering', NULL, 'Design and construct coastal infrastructure such as breakwaters, jetties, cargo terminals, and dry docks to facilitate safe maritime transport and logistics.', NULL, 'Port authorities, coastal construction firms, global logistics hubs, and heavy civil engineering consultancies.', NULL, '🏗️', 'Infrastructure', 17, 1, '2026-10-17 12:41:22', '2026-10-17 12:41:22'),
(18, 1, 'river-and-coastal-engineering', 'River and Coastal Engineering', NULL, 'Manage coastal erosion, flood defenses, and river navigability by designing sustainable marine structures and executing comprehensive dredging plans.', NULL, 'Environmental agencies, government water resource departments, dredging companies, and marine surveying firms.', NULL, '🌊', 'Environmental', 18, 1, '2026-10-17 12:41:22', '2026-10-17 12:41:22'),
(19, 1, 'marine-electrical-and-electronics-engineering', 'Marine Electrical & Electronics Engineering', NULL, 'Develop and maintain complex marine power distribution networks, automated control systems, and highly calibrated onboard navigation equipment.', NULL, 'Marine technology manufacturers, ship repair yards, offshore wind farms, and maritime electronics suppliers.', NULL, '🔌', 'Control Systems', 19, 1, '2026-10-17 12:41:22', '2026-10-17 12:41:22'),
(20, 1, 'marine-mechanical-engineering', 'Marine Mechanical Engineering', NULL, 'Focus on the mechanical components of marine vessels, maintaining and optimizing heavy machinery, main engines, compressors, and hydraulic systems.', NULL, 'Engine manufacturing plants, commercial fleet maintenance crews, offshore oil and gas rigs, and maritime defense sectors.', NULL, '🔧', 'Machinery', 20, 1, '2026-10-17 12:41:22', '2026-10-17 12:41:22'),
(21, 1, 'nautical-science', 'Nautical Science', NULL, 'Master ship navigation, meteorology, cargo handling, and maritime law to safely operate and command commercial vessels across international waters.', NULL, 'Merchant navy fleets, international shipping corporations, maritime training academies, and harbor pilotage services.', NULL, '🧭', 'Navigation & Command', 21, 1, '2026-10-17 12:41:22', '2026-10-17 12:41:22'),
(22, 1, 'propulsion-and-flight-vehicles', 'Propulsion and Flight Vehicles', NULL, 'Design, test, and optimize aerodynamic structures and propulsion engines (jet, turboprop, and rocket) to achieve maximum thrust and fuel efficiency.', NULL, 'Aerospace manufacturing companies, defense contractors, aviation research laboratories, and national space agencies.', NULL, '🚀', 'Aerospace Dynamics', 22, 1, '2026-10-17 12:41:22', '2026-10-17 12:41:22'),
(23, 1, 'avionics', 'Avionics', NULL, 'Engineer the electronic systems used in aircraft and spacecraft, including communications, radar, flight control algorithms, and collision avoidance systems.', NULL, 'Aircraft production facilities, airline maintenance hubs, defense technology firms, and UAV (drone) manufacturing startups.', NULL, '✈️', 'Aviation Electronics', 23, 1, '2026-10-17 12:41:22', '2026-10-17 12:41:22'),
(24, 1, 'electrical-systems-and-instrumentation', 'Electrical Systems & Instrumentation', NULL, 'Design the power generation architectures, complex wiring networks, and highly calibrated sensors required to monitor and control aerospace vehicle performance.', NULL, 'Aerospace integration facilities, satellite development labs, aviation testing centers, and military technology organizations.', NULL, '🛰️', 'Flight Systems', 24, 1, '2026-10-17 12:41:22', '2026-10-17 12:41:22'),
(25, 1, 'fuel-and-propellant-engineering', 'Fuel and Propellant Engineering', NULL, 'Formulate and test advanced solid and liquid propellants, managing combustion stability, energy density, and the safe storage of volatile materials.', NULL, 'Propulsion test facilities, specialized chemical manufacturing plants, space exploration agencies, and missile defense programs.', NULL, '🛢️', 'Chemical Engineering', 25, 1, '2026-10-17 12:41:22', '2026-10-17 12:41:22'),
(26, 1, 'space-systems-engineering', 'Space Systems Engineering', NULL, 'Integrate complex subsystems for satellites, planetary probes, and spacecraft, ensuring survivability and mission success in the extreme environment of outer space.', NULL, 'Satellite communications companies, national space programs, commercial spaceflight corporations, and orbital research institutes.', NULL, '🌌', 'Orbital Tech', 26, 1, '2026-10-17 12:41:22', '2026-10-17 12:41:22'),
(27, 1, 'precision-engineering', 'Precision Engineering', NULL, 'Precision-focused engineering, measurement and manufacturing technologies.', NULL, 'Potential pathways include precision manufacturing, metrology, tooling and computer-controlled production. Confirm the exact program name and curriculum with the university before relying on this listing.', NULL, '🎯', 'Verification pending', 27, 1, '2026-10-17 12:41:22', '2026-10-17 12:41:22');

-- --------------------------------------------------------

--
-- Table structure for table `specialization_universities`
--

CREATE TABLE `specialization_universities` (
  `id` int(10) UNSIGNED NOT NULL,
  `specialization_id` int(10) UNSIGNED NOT NULL,
  `uni_id` int(11) NOT NULL,
  `status` enum('offered','not_offered','unverified') NOT NULL DEFAULT 'unverified',
  `notes` text DEFAULT NULL,
  `source_url` varchar(500) DEFAULT NULL,
  `last_verified_at` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `specialization_universities`
--

INSERT INTO `specialization_universities` (`id`, `specialization_id`, `uni_id`, `status`, `notes`, `source_url`, `last_verified_at`, `created_at`, `updated_at`) VALUES
(1, 1, 18, 'not_offered', 'Project owner explicitly noted UTYCC does not offer Civil Engineering; re-check against the current official program list before relying on this.', NULL, NULL, '2026-10-17 12:41:22', '2026-10-17 12:41:22'),
(2, 27, 18, 'unverified', 'Requested starter association. This is not confirmed; verify against an official UTYCC program source before presenting it as offered.', NULL, NULL, '2026-10-17 12:41:22', '2026-10-17 12:41:22');

-- --------------------------------------------------------

--
-- Table structure for table `uni`
--

CREATE TABLE `uni` (
  `id` int(11) NOT NULL,
  `uni_name` varchar(255) NOT NULL,
  `major` varchar(100) NOT NULL,
  `region` varchar(100) NOT NULL,
  `min_marks` int(11) NOT NULL DEFAULT 0,
  `admission_status` enum('known','pass_based','unknown') NOT NULL DEFAULT 'unknown',
  `image_path` varchar(255) NOT NULL,
  `page` varchar(255) NOT NULL,
  `popularity` int(11) DEFAULT 100,
  `source_url` varchar(500) DEFAULT NULL,
  `last_verified_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `uni`
--

INSERT INTO `uni` (`id`, `uni_name`, `major`, `region`, `min_marks`, `admission_status`, `image_path`, `page`, `popularity`, `source_url`, `last_verified_at`) VALUES
(1, 'University of Medicine 1', 'Medicine', 'Yangon', 460, 'known', 'photos/medicine1.jpg', 'UM1.html', 1, NULL, NULL),
(2, 'University of Medicine 2', 'Medicine', 'Yangon', 460, 'known', 'photos/medicine2.jpg', 'UM2.html', 2, NULL, NULL),
(3, 'University of Medicine, Mandalay', 'Medicine', 'Mandalay', 460, 'known', 'photos/medicine.jpg', 'UMM.html', 3, NULL, NULL),
(4, 'University of Medicine, Magway', 'Medicine', 'Magway', 460, 'known', 'photos/magwaymedicine.jpg', 'UMMAGWAY.html', 4, NULL, NULL),
(5, 'University of Medicine, Taunggyi', 'Medicine', 'Shan', 460, 'known', 'photos/medicinetauggyi.jpg', 'UMTAUNGGYI.html', 5, NULL, NULL),
(6, 'University of Dental Medicine, Yangon', 'Medicine', 'Yangon', 460, 'known', 'photos/dentalyangon.jpg', 'DentalYangon.html', 20, NULL, NULL),
(7, 'University of Dental Medicine, Mandalay', 'Medicine', 'Mandalay', 460, 'known', 'photos/dentalmandalay.jpg', 'DentalMandalay.html', 21, NULL, NULL),
(8, 'University of Pharmacy, Yangon', 'Medicine', 'Yangon', 450, 'known', 'photos/pharmacyyangon.jpg', 'PharmacyYangon.html', 22, NULL, NULL),
(9, 'University of Pharmacy, Mandalay', 'Medicine', 'Mandalay', 450, 'known', 'photos/pharmacymandalay.jpg', 'PharmacyMandalay.html', 23, NULL, NULL),
(10, 'University of Medical Technology, Yangon', 'Medicine', 'Yangon', 440, 'known', 'photos/meditechyangon.jpg', 'MedTechYangon.html', 24, NULL, NULL),
(11, 'University of Medical Technology, Mandalay', 'Medicine', 'Mandalay', 440, 'known', 'photos/meditechmandalay.jpg', 'MedTechMandalay.html', 25, NULL, NULL),
(12, 'University of Nursing, Yangon', 'Medicine', 'Yangon', 0, 'unknown', 'photos/nurseyangon.jpg', 'NursingYangon.html', 26, NULL, NULL),
(13, 'University of Nursing, Mandalay', 'Medicine', 'Mandalay', 0, 'unknown', 'photos/nursemandalay.jpg', 'NursingMandalay.html', 27, NULL, NULL),
(14, 'University of Community Health, Magway', 'Medicine', 'Magway', 400, 'known', 'photos/community.jpg', 'HealthMagway.html', 25, NULL, NULL),
(15, 'University of Traditional Medicine', 'Medicine', 'Mandalay', 380, 'known', 'photos/trad.jpg', 'TraMedicMandalay.html', 25, NULL, NULL),
(16, 'Yangon Technological University', 'Engineering', 'Yangon', 450, 'known', 'photos/ytu.jpg', 'YTU.html', 7, NULL, NULL),
(17, 'Mandalay Technological University', 'Engineering', 'Mandalay', 450, 'known', 'photos/mtu.jpg', 'MTU.html', 8, NULL, NULL),
(18, 'University of Technology: Yadanarpon Cyber City', 'Engineering', 'Mandalay', 420, 'known', 'photos/utycc.jpg', 'UTYCC.html', 6, NULL, NULL),
(19, 'Naypyitaw State Polytechnic University', 'Engineering & Computer Science', 'Naypyitaw', 450, 'known', 'photos/nspu.jpg', 'NSPU.html', 9, NULL, NULL),
(20, 'Myanmar Aerospace Engineering University', 'Engineering', 'Mandalay', 400, 'known', 'photos/plane.jpg', 'Plane.html', 10, NULL, NULL),
(21, 'Myanmar Maritime University', 'Engineering', 'Yangon', 420, 'known', 'photos/mmu.jpg', 'MMU.html', 15, NULL, NULL),
(22, 'University of Information Technology', 'Computer Science', 'Yangon', 450, 'known', 'photos/uit.jpg', 'UIT.html', 12, NULL, NULL),
(23, 'Myanmar Institute of Information Technology', 'Computer Science', 'Mandalay', 450, 'known', 'photos/miit.jpg', 'MIIT.html', 11, NULL, NULL),
(24, 'University of Computer Studies, Yangon', 'Computer Science', 'Yangon', 450, 'known', 'photos/ucsy.jpg', 'UCSY.html', 13, NULL, NULL),
(25, 'University of Computer Studies, Mandalay', 'Computer Science', 'Mandalay', 450, 'known', 'photos/ucsm.jpg', 'UCSM.html', 14, NULL, NULL),
(26, 'West Yangon Technological University', 'Engineering', 'Yangon', 400, 'known', 'photos/wytu.jpg', 'WYTU.html', 33, NULL, NULL),
(27, 'Technological University, Thanlyin', 'Engineering', 'Yangon', 400, 'known', 'photos/ttu.jpg', 'TUThanlyin.html', 36, NULL, NULL),
(28, 'Technological University, Mandalay', 'Engineering', 'Mandalay', 400, 'known', 'photos/tum.jpg', 'TUMandalay.html', 37, NULL, NULL),
(29, 'Technological University, Myitkyina', 'Engineering', 'Kachin', 400, 'known', 'photos/tukachin.jpg', 'TUMyitkyina.html', 38, NULL, NULL),
(30, 'Technological University, Monywa', 'Engineering', 'Sagaing', 400, 'known', 'photos/sagaingtu.jpg', 'TUMonywa.html', 39, NULL, NULL),
(31, 'Technological University, Kalay', 'Engineering', 'Sagaing', 400, 'known', 'photos/kalay.jpg', 'TUKalay.html', 40, NULL, NULL),
(32, 'Technological University, Kyaukse', 'Engineering', 'Mandalay', 400, 'known', 'photos/ktu.jpg', 'TUKyaukse.html', 41, NULL, NULL),
(33, 'Technological University, Meiktila', 'Engineering', 'Mandalay', 400, 'known', 'photos/mmtu.jpg', 'TUMeiktila.html', 42, NULL, NULL),
(34, 'Technological University, Yamethin', 'Engineering', 'Mandalay', 400, 'known', 'photos/ymethin.jpg', 'TUYamethin.html', 43, NULL, NULL),
(35, 'Technological University, Magway', 'Engineering', 'Magway', 400, 'known', 'photos/bjhtoo.jpg', 'TUMagway.html', 44, NULL, NULL),
(36, 'Technological University, Pakokku', 'Engineering', 'Magway', 400, 'known', 'photos/pakku.jpg', 'TUPakokku.html', 45, NULL, NULL),
(37, 'Technological University, Taunggyi', 'Engineering', 'Shan', 400, 'known', 'photos/tauggyitu.jpg', 'TUTaunggyi.html', 46, NULL, NULL),
(38, 'Technological University, Loikaw', 'Engineering', 'Kayah', 400, 'known', 'photos/kayahtu.jpg', 'TULoikaw.html', 47, NULL, NULL),
(39, 'Technological University, Sittwe', 'Engineering', 'Rakhine', 400, 'known', 'photos/sittway.jpg', 'TUSittway.html', 48, NULL, NULL),
(40, 'Technological University, Pyay', 'Engineering', 'Bago', 400, 'known', 'photos/pyaytu.jpg', 'TUPyay.html', 49, NULL, NULL),
(41, 'Technological University, Hmawbi', 'Engineering', 'Yangon', 400, 'known', 'photos/hmawbi.jpg', 'TUHmawbi.html', 50, NULL, NULL),
(42, 'Technological University, Pathein', 'Engineering', 'Ayeyarwady', 400, 'known', 'photos/patheintu.jpg', 'TUPathein.html', 51, NULL, NULL),
(43, 'Technological University, Hinthada', 'Engineering', 'Ayeyarwady', 400, 'known', 'photos/hinthada.jpg', 'TUHinthada.html', 52, NULL, NULL),
(44, 'Technological University, Hpa-an', 'Engineering', 'Kayin', 400, 'known', 'photos/kayin.jpg', 'TUHpaan.html', 53, NULL, NULL),
(45, 'Technological University, Mawlamyine', 'Engineering', 'Mon', 400, 'known', 'photos/mon.jpg', 'TUMawlamyine.html', 54, NULL, NULL),
(46, 'Technological University, Dawei', 'Engineering', 'Tanintharyi', 400, 'known', 'photos/dawei.jpg', 'TUDawei.html', 55, NULL, NULL),
(47, 'Technological University, Myeik', 'Engineering', 'Tanintharyi', 400, 'known', 'photos/myeik.jpg', 'TUMyeik.html', 56, NULL, NULL),
(48, 'Technological University, Lashio', 'Engineering', 'Shan', 400, 'known', 'photos/lashiotu.jpg', 'TULashio.html', 57, NULL, NULL),
(50, 'University of Computer Studies, Monywa', 'Computer Science', 'Sagaing', 320, 'known', 'photos/monywa.jpg', 'CUMonywa.html', 59, NULL, NULL),
(51, 'University of Computer Studies, Kalay', 'Computer Science', 'Sagaing', 320, 'known', 'photos/kalaycu.jpg', 'CUKalay.html', 60, NULL, NULL),
(52, 'University of Computer Studies, Meiktila', 'Computer Science', 'Mandalay', 320, 'known', 'photos/meik.jpg', 'CUMeiktila.html', 61, NULL, NULL),
(53, 'University of Computer Studies, Magway', 'Computer Science', 'Magway', 320, 'known', 'photos/ucsmagway.jpg', 'CUMagway.html', 62, NULL, NULL),
(54, 'University of Computer Studies, Pakokku', 'Computer Science', 'Magway', 320, 'known', 'photos/ucsbago.jpg', 'CUPakokku.html', 63, NULL, NULL),
(55, 'University of Computer Studies, Taunggyi', 'Computer Science', 'Shan', 320, 'known', 'photos/shanuc.jpg', 'CUTaunggyi.html', 64, NULL, NULL),
(56, 'University of Computer Studies, Loikaw', 'Computer Science', 'Kayah', 320, 'known', 'photos/loikaw.jpg', 'CULoikaw.html', 65, NULL, NULL),
(57, 'University of Computer Studies, Taungoo', 'Computer Science', 'Bago', 320, 'known', 'photos/taucs.jpg', 'CUTaungoo.html', 66, NULL, NULL),
(58, 'University of Computer Studies, Sittwe', 'Computer Science', 'Rakhine', 320, 'known', 'photos/sitt.jpg', 'CUSittwe.html', 67, NULL, NULL),
(59, 'University of Computer Studies, Pyay', 'Computer Science', 'Bago', 320, 'known', 'photos/pyaycu.jpg', 'CUPyay.html', 68, NULL, NULL),
(60, 'University of Computer Studies, Pathein', 'Computer Science', 'Ayeyarwady', 320, 'known', 'photos/pat.jpg', 'CUPathein.html', 69, NULL, NULL),
(61, 'University of Computer Studies, Hinthada', 'Computer Science', 'Ayeyarwady', 320, 'known', 'photos/hinthadacu.jpg', 'CUHinthada.html', 70, NULL, NULL),
(62, 'University of Computer Studies, Hpa-an', 'Computer Science', 'Kayin', 320, 'known', 'photos/kayincu.jpg', 'CUHpaan.html', 71, NULL, NULL),
(63, 'University of Computer Studies, Thaton', 'Computer Science', 'Mon', 320, 'known', 'photos/tha.jpg', 'CUThaton.html', 72, NULL, NULL),
(64, 'University of Computer Studies, Bhamo', 'Computer Science', 'Kachin', 320, 'known', 'photos/bhamo.jpg', 'CUBhamo.html', 73, NULL, NULL),
(65, 'University of Computer Studies, Lashio', 'Computer Science', 'Shan', 320, 'known', 'photos/lashio.jpg', 'CULashio.html', 74, NULL, NULL),
(66, 'University of Computer Studies, Dawei', 'Computer Science', 'Tanintharyi', 320, 'known', 'photos/dawe.png', 'CUDawei.html', 75, NULL, NULL),
(67, 'University of Computer Studies, Myeik', 'Computer Science', 'Tanintharyi', 320, 'known', 'photos/myeikcu.jpg', 'CUMyeik.html', 76, NULL, NULL),
(69, 'University of Computer Studies, Maubin', 'Computer Science', 'Ayeyarwady', 320, 'known', 'photos/ma.jpg', 'CUMaubin.html', 78, NULL, NULL),
(71, 'University of Yangon', 'Arts & Science,Law', 'Yangon', 450, 'known', 'photos/yu.jpg', 'YU.html', 81, NULL, NULL),
(72, 'University of Mandalay', 'Arts & Science,Law', 'Mandalay', 450, 'known', 'photos/mu.jpg', 'MU.html', 81, NULL, NULL),
(73, 'Dagon University', 'Arts & Science,Law', 'Yangon', 0, 'unknown', 'photos/dagon.jpg', 'Dagon.html', 81, NULL, NULL),
(74, 'East Yangon University', 'Arts & Science,Law', 'Yangon', 0, 'unknown', 'photos/eyu.jpg', 'EastYangon.html', 81, NULL, NULL),
(75, 'West Yangon University', 'Arts & Science,Law', 'Yangon', 0, 'unknown', 'photos/wyu.jpg', 'wyu.html', 82, NULL, NULL),
(76, 'Yadanabon University', 'Arts & Science,Law', 'Mandalay', 0, 'unknown', 'photos/yadanabon.jpg', 'yadanabon.html', 81, NULL, NULL),
(77, 'Yangon University of Economics(Hlaing)', 'Business & Economics', 'Yangon', 380, 'known', 'photos/yueco.jpg', 'yueco.html', 79, NULL, NULL),
(78, 'Meiktila University of Economics', 'Business & Economics', 'Mandalay', 400, 'known', 'photos/mteco.jpg', 'Meikeco.html', 80, NULL, NULL),
(79, 'Monywa University of Economics', 'Business & Economics', 'Sagaing', 400, 'known', 'photos/monywaeco.jpg', 'monywaeco.html', 80, NULL, NULL),
(80, 'Yangon University of Education', 'Education', 'Yangon', 420, 'known', 'photos/yuoe.jpg', 'YangonEdu.html', 101, NULL, NULL),
(81, 'Sagaing University of Education', 'Education', 'Sagaing', 420, 'known', 'photos/suoe.jpg', 'suoe.html', 101, NULL, NULL),
(82, 'Yezin Agricultural University', 'Agriculture', 'Naypyitaw', 380, 'known', 'photos/yau.jpg', 'YAU.html', 19, NULL, NULL),
(83, 'University of Veterinary Science, Yezin', 'Veterinary Science', 'Naypyitaw', 400, 'known', 'photos/uvs.jpg', 'UVS.html', 19, NULL, NULL),
(84, 'University of Forestry and Environmental Science', 'Forestry', 'Naypyitaw', 0, 'unknown', 'photos/ufes.jpg', 'ufes.html', 19, NULL, NULL),
(85, 'National University of Arts and Culture, Yangon', 'Arts & Culture', 'Yangon', 0, 'unknown', 'photos/nuac.jpg', 'nuac.html', 102, NULL, NULL),
(86, 'National University of Arts and Culture, Mandalay', 'Arts & Culture', 'Mandalay', 0, 'unknown', 'photos/nuacm.jpg', 'nuacm.html', 102, NULL, NULL),
(87, 'Co-operative University, Thanlyin', 'Business & Economics', 'Yangon', 0, 'unknown', 'photos/coop.jpg', 'CoThanlyin.html', 81, NULL, NULL),
(88, 'Co-operative University, Sagaing', 'Business & Economics', 'Sagaing', 0, 'unknown', 'photos/coops.jpg', 'CoSagaing.html', 81, NULL, NULL),
(89, 'Mawlamyine University', 'Arts & Science,Law', 'Mon', 340, 'known', 'photos/mlm.jpg', 'MawlamyineUni.html', 81, NULL, NULL),
(90, 'Taunggyi University', 'Arts & Science,Law', 'Shan', 0, 'unknown', 'photos/tgyi.jpg', 'TaunggyiUni.html', 82, NULL, NULL),
(91, 'Sittwe University', 'Arts & Science,Law', 'Rakhine', 0, 'unknown', 'photos/sittwe.jpg', 'SittweUni.html', 82, NULL, NULL),
(92, 'Pathein University', 'Arts & Science,Law', 'Ayeyarwady', 0, 'unknown', 'photos/pathein.jpg', 'PatheinUni.html', 82, NULL, NULL),
(93, 'Pyay University', 'Arts & Science,Law', 'Bago', 0, 'unknown', 'photos/pyay.jpg', 'PyayUni.html', 82, NULL, NULL),
(94, 'Myitkyina University', 'Arts & Science,Law', 'Kachin', 340, 'known', 'photos/myitkyina.jpg', 'MyitkyinaUni.html', 82, NULL, NULL),
(95, 'Yangon University of Economics(Ywathagyi)', 'Business & Economics', 'Yangon', 350, 'known', 'photos/yeco.jpg', 'yeco.html', 79, NULL, NULL),
(96, 'Technological University,Sagaing', 'Engineering', 'Sagaing', 400, 'known', 'photos/tsagaingu.jpg', 'TUSagaing.html', 34, NULL, NULL),
(97, 'Technological Unviersity,Taungoo', 'Engineering', 'Bago', 350, 'known', 'photos/tagtu.jpg', 'TUTaungoo.html', 35, NULL, NULL),
(98, 'University of Computer Studies,Myitkyina', 'Computer Science', 'Kachin', 320, 'known', 'photos/kachincu.jpg', 'CUMyitkyina.html', 79, NULL, NULL),
(99, 'Yangon University of Foreign Languages', 'Foreign Languages', 'Yangon', 380, 'known', 'photos/yufl.jpg', 'YUFL.html', 16, NULL, NULL),
(100, 'Mandalay University of Foreign Languages', 'Foreign Languages', 'Mandalay', 380, 'known', 'photos/mufl.jpg', 'MUFL.html', 17, NULL, NULL),
(101, 'Naypyitaw State Academy', 'Medicine,Law,Business,Arts & Education,Communication', 'Naypyitaw', 0, 'unknown', 'photos/nsa.jpg', 'NSA.html', 18, NULL, NULL),
(102, 'Polytechnic University,Kyaing tong', 'Engineering & Computer Science', 'Mon', 400, 'known', 'photos/monpu.jpg', 'PUKyaingTong.html', 28, NULL, NULL),
(103, 'Polytechnic University, Panglong', 'Engineering & Computer Science', 'Shan', 400, 'known', 'photos/pangpu.jpg', 'PUPanglong.html', 29, NULL, NULL),
(104, 'Polytechnic University, Myeik', 'Engineering & Computer Science', 'Tanintharyi', 400, 'known', 'photos/meikpu.jpg', 'PUMyeik.html', 30, NULL, NULL),
(105, 'Polytechnic University,Dawei', 'Engineering & Computer Science', 'Tanintharyi', 400, 'known', 'photos/puda.jpg', 'PUDawei.html', 31, NULL, NULL),
(106, ' Polytechnic University, Maubin', 'Engineering & Computer Science', 'Ayeyarwady', 400, 'known', 'photos/mau.jpg', 'PUMaubin.html', 32, NULL, NULL),
(108, 'Magway University', 'Arts & Science,Law', 'Magway', 0, 'unknown', 'photos/magwayUni.jpg', 'MagwayUni.html', 81, NULL, NULL),
(109, 'Technological University,Bhamo', 'Engineering', 'Kachin', 400, 'known', 'photos/bhamotu.jpg', 'TUBhamo.html', 45, NULL, NULL),
(111, 'Technological University,Maubin', 'Engineering', 'Ayeyarwady', 400, 'known', 'photos/maubin.jpg', 'TUMaubin.html', 45, NULL, NULL),
(112, 'University of Computer Studies(Mandalay)', 'Computer Science', 'Mandalay', 320, 'known', 'photos/ucsmm.jpg', 'CUMandalay.html', 60, NULL, NULL),
(113, 'Defence Services Academy', '', 'Mandalay', 0, 'unknown', 'photos/DSA.jpg', 'DSA.html', 105, NULL, NULL),
(114, 'Defence Services Institute of Nursing and Paramedical Science', 'Medicine', 'Yangon', 0, 'unknown', 'photos/minp.jpg', 'minp.html', 105, NULL, NULL),
(115, 'Defence Services Medical Academy', 'Medicine', 'Yangon', 0, 'unknown', 'photos/DD.jpg', 'DMA.html', 105, NULL, NULL),
(116, 'Defence Services Technological Academy', 'Engineering', 'Mandalay', 0, 'unknown', 'photos/dsta.jpg', 'dsta.html', 105, NULL, NULL),
(117, 'Hinthada University', 'Arts & Science,Law', 'Ayeyarwady', 0, 'unknown', 'photos/HinthadaUni.jpg', 'HinthadaUni.html', 100, NULL, NULL),
(118, 'Hpa-an University', 'Arts & Science,Law', 'Kayin', 0, 'unknown', 'photos/HpaanUni.jpg', 'HpaanUni.html', 100, NULL, NULL),
(119, 'Kalay University', 'Arts & Science,Law', 'Sagaing', 0, 'unknown', 'photos/KalayUni.jpg', 'KalayUni.html', 100, NULL, NULL),
(120, 'Kyaing Tong University', 'Arts & Science,Law', 'Shan', 0, 'unknown', 'photos/kyaingtonUni.jpg', 'KyaingtongUni.html', 100, NULL, NULL),
(121, 'Kyaukse University', 'Arts & Science,Law', 'Mandalay', 0, 'unknown', 'photos/kyaukseUni.jpg', 'KyaukseUni.html', 100, NULL, NULL),
(122, 'Lashio University', 'Arts & Science,Law', 'Shan', 0, 'unknown', 'photos/lashioUni.jpg', 'LashioUni.html', 100, NULL, NULL),
(123, 'Loikaw University', 'Arts & Science,Law', 'Kayar', 0, 'unknown', 'photos/loikawUni.jpg', 'LoikawUni.html', 100, NULL, NULL),
(124, 'Maubin University', 'Arts & Science,Law', 'Ayeyarwaday', 0, 'unknown', 'photos/maubinUni.jpg', 'MaubinUni.html', 100, NULL, NULL),
(125, 'Meiktila University', 'Arts & Science,Law', 'Mandalay', 0, 'unknown', 'photos/meiktilaUni.jpg', 'MeiktilaUni.html', 100, NULL, NULL),
(126, 'Monywa University', 'Arts & Science,Law', 'Sagaing', 0, 'unknown', 'photos/monywaUni.jpg', 'MonywaUni.html', 100, NULL, NULL),
(127, 'Myeik University', 'Arts & Science,Law', 'Tanintharyi', 0, 'unknown', 'photos/myeikUni.jpg', 'MyeikUni.html', 100, NULL, NULL),
(128, 'Pakokku University', 'Arts & Science,Law', 'Bago', 0, 'unknown', 'photos/pkku.jpg', 'PakokkuUni.html', 100, NULL, NULL),
(129, 'Panglong University', 'Arts & Science,Law', 'Shan', 0, 'unknown', 'photos/panglongUni.jpg', 'PanglongUni.html', 100, NULL, NULL),
(130, 'Sagaing University', 'Arts & Science,Law', 'Sagaing', 0, 'unknown', 'photos/sagaingUni.jpg', 'SagaingUni.html', 100, NULL, NULL),
(131, 'Shwebo University ', 'Arts & Science,Law', 'Sagaing', 0, 'unknown', 'photos/shweboUni.jpg', 'ShweboUni.html', 100, NULL, NULL),
(132, 'Taungoo University', 'Arts & Science,Law', 'Bago', 0, 'unknown', 'photos/taungooUni.jpg', 'TaungooUni.html', 100, NULL, NULL),
(133, 'Yenangyaung University', 'Arts & Science,Law', 'Magway', 0, 'unknown', 'photos/yenaungUni.jpg', 'YenaungUni.html', 100, NULL, NULL),
(134, 'Taunggoke University', 'Arts & Science,Law', 'Rakhine', 0, 'unknown', 'photos/taungookeUni.jpg', 'TaungookeUni.html', 100, NULL, NULL),
(135, 'Bago University', 'Arts & Science,Law', 'Bago', 0, 'unknown', 'photos/BagoUni.jpg', 'BagoUni.html', 100, NULL, NULL),
(136, 'Bhamo University', 'Arts & Science,Law', 'Yangon', 0, 'unknown', 'photos/BhamoUni.jpg', 'BhamoUni.html', 100, NULL, NULL),
(137, 'Dawei University', 'Arts & Science,Law', 'Tanintharyi', 0, 'unknown', 'photos/DaweiUni.jpg', 'DaweiUni.html', 100, NULL, NULL),
(138, 'University of Public Health, Yangon', 'Medicine', 'Yangon', 0, 'unknown', 'photos/publichealth.jpg', 'PublicHealth.html', 25, NULL, NULL),
(139, 'Yangon University of Distance Education', 'Arts & Science,Law', 'Yangon', 0, 'unknown', 'photos/yude.jpg', 'YUDE.html', 103, NULL, NULL),
(140, 'Mandalay University of Distance Education', 'Arts & Science,Law', 'Mandalay', 0, 'unknown', 'photos/muode.jpg', 'MUDOE.html', 103, NULL, NULL),
(141, 'Polytechnic University,Myitkyina', 'Engineering & Computer Science', 'Kachin', 400, 'known', 'photos/pukachin.jpg', 'PUMyitkyina.html', 30, NULL, NULL),
(142, 'Hakha University', 'Arts & Science,Law', 'Chin', 240, 'unknown', 'photos/hakha.jpg', 'HakhaUni.html', 100, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `university_programs`
--

CREATE TABLE `university_programs` (
  `id` int(10) UNSIGNED NOT NULL,
  `uni_id` int(11) NOT NULL,
  `program_key` varchar(120) NOT NULL,
  `program_name` varchar(180) NOT NULL,
  `status` enum('offered','not_offered','unverified') NOT NULL DEFAULT 'unverified',
  `notes` text DEFAULT NULL,
  `source_url` varchar(500) DEFAULT NULL,
  `last_verified_at` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `university_programs`
--

INSERT INTO `university_programs` (`id`, `uni_id`, `program_key`, `program_name`, `status`, `notes`, `source_url`, `last_verified_at`, `created_at`, `updated_at`) VALUES
(1, 18, 'civil-engineering', 'Civil Engineering', 'not_offered', 'Added from the project owner\'s explicit correction. Re-check against official current program lists before publication.', NULL, NULL, '2026-10-17 12:41:22', '2026-10-17 12:41:22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_courses_created` (`id`);

--
-- Indexes for table `opportunities`
--
ALTER TABLE `opportunities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_opportunities_active_order` (`is_active`,`sort_order`);

--
-- Indexes for table `specializations`
--
ALTER TABLE `specializations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_specializations_category_order` (`opportunity_id`,`is_active`,`sort_order`);

--
-- Indexes for table `specialization_universities`
--
ALTER TABLE `specialization_universities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_specialization_university` (`specialization_id`,`uni_id`),
  ADD KEY `idx_specialization_status` (`specialization_id`,`status`),
  ADD KEY `fk_su_uni` (`uni_id`);

--
-- Indexes for table `uni`
--
ALTER TABLE `uni`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_uni_name` (`uni_name`),
  ADD KEY `idx_uni_major` (`major`),
  ADD KEY `idx_uni_region` (`region`),
  ADD KEY `idx_uni_admission` (`admission_status`,`min_marks`);

--
-- Indexes for table `university_programs`
--
ALTER TABLE `university_programs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_uni_program` (`uni_id`,`program_key`),
  ADD KEY `idx_program_key_status` (`program_key`,`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `opportunities`
--
ALTER TABLE `opportunities`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `specializations`
--
ALTER TABLE `specializations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `specialization_universities`
--
ALTER TABLE `specialization_universities`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `uni`
--
ALTER TABLE `uni`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143;

--
-- AUTO_INCREMENT for table `university_programs`
--
ALTER TABLE `university_programs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `specializations`
--
ALTER TABLE `specializations`
  ADD CONSTRAINT `fk_specializations_opportunity` FOREIGN KEY (`opportunity_id`) REFERENCES `opportunities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `specialization_universities`
--
ALTER TABLE `specialization_universities`
  ADD CONSTRAINT `fk_su_specialization` FOREIGN KEY (`specialization_id`) REFERENCES `specializations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_su_uni` FOREIGN KEY (`uni_id`) REFERENCES `uni` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `university_programs`
--
ALTER TABLE `university_programs`
  ADD CONSTRAINT `fk_programs_uni` FOREIGN KEY (`uni_id`) REFERENCES `uni` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
