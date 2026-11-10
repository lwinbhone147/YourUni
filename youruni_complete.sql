-- YourUni consolidated database (single import file)
-- BACK UP AND REMOVE/RENAME old databases before importing if you want a clean replacement.
-- This creates youruni_db and puts both the homepage cards and catalogue in that database.
CREATE DATABASE IF NOT EXISTS youruni_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE youruni_db;
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS specialization_universities;
DROP TABLE IF EXISTS specializations;
DROP TABLE IF EXISTS university_programs;
DROP TABLE IF EXISTS opportunities;
DROP TABLE IF EXISTS admin_users;
DROP TABLE IF EXISTS enrollments;
DROP TABLE IF EXISTS users; -- Retire the obsolete plaintext sample user table.
DROP TABLE IF EXISTS uni;
DROP TABLE IF EXISTS courses;

CREATE TABLE courses (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  university_name VARCHAR(255) NOT NULL,
  major VARCHAR(100) NOT NULL,
  zone VARCHAR(100) NOT NULL,
  min_marks INT NOT NULL DEFAULT 0,
  image_url VARCHAR(255) DEFAULT 'photos/default.jpg',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  page_link VARCHAR(255) DEFAULT '#',
  INDEX idx_courses_created (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `courses` (`id`, `title`, `university_name`, `major`, `zone`, `min_marks`, `image_url`, `created_at`, `page_link`) VALUES
(2, 'Top 1', 'Yangon Technological University', 'Engineering', 'Yangon', 450, 'photos/1791599863_6ac9a4f7e3ac2.jpg', '2026-10-10 02:37:43', 'YTU.html');

CREATE TABLE uni (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  uni_name VARCHAR(255) NOT NULL,
  major VARCHAR(100) NOT NULL,
  region VARCHAR(100) NOT NULL,
  min_marks INT NOT NULL DEFAULT 0,
  admission_status ENUM('known','pass_based','unknown') NOT NULL DEFAULT 'unknown',
  image_path VARCHAR(255) NOT NULL,
  page VARCHAR(255) NOT NULL,
  popularity INT DEFAULT 100,
  source_url VARCHAR(500) DEFAULT NULL,
  last_verified_at DATE DEFAULT NULL,
  INDEX idx_uni_name (uni_name),
  INDEX idx_uni_major (major),
  INDEX idx_uni_region (region),
  INDEX idx_uni_admission (admission_status, min_marks)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `uni` (`id`, `uni_name`, `major`, `region`, `min_marks`, `image_path`, `page`, `popularity`) VALUES
(1, 'University of Medicine 1', 'Medicine', 'Yangon', 460, 'photos/medicine1.jpg', 'UM1.html', 1),
(2, 'University of Medicine 2', 'Medicine', 'Yangon', 460, 'photos/medicine2.jpg', 'UM2.html', 2),
(3, 'University of Medicine, Mandalay', 'Medicine', 'Mandalay', 460, 'photos/medicine.jpg', 'UMM.html', 3),
(4, 'University of Medicine, Magway', 'Medicine', 'Magway', 460, 'photos/magwaymedicine.jpg', 'UMMAGWAY.html', 4),
(5, 'University of Medicine, Taunggyi', 'Medicine', 'Shan', 460, 'photos/medicinetauggyi.jpg', 'UMTAUNGGYI.html', 5),
(6, 'University of Dental Medicine, Yangon', 'Medicine', 'Yangon', 460, 'photos/dentalyangon.jpg', 'DentalYangon.html', 20),
(7, 'University of Dental Medicine, Mandalay', 'Medicine', 'Mandalay', 460, 'photos/dentalmandalay.jpg', 'DentalMandalay.html', 21),
(8, 'University of Pharmacy, Yangon', 'Medicine', 'Yangon', 450, 'photos/pharmacyyangon.jpg', 'pharmacyyangon.html', 22),
(9, 'University of Pharmacy, Mandalay', 'Medicine', 'Mandalay', 450, 'photos/pharmacymandalay.jpg', 'pharmacymandalay.html', 23),
(10, 'University of Medical Technology, Yangon', 'Medicine', 'Yangon', 440, 'photos/meditechyangon.jpg', 'MedTechYangon.html', 24),
(11, 'University of Medical Technology, Mandalay', 'Medicine', 'Mandalay', 440, 'photos/meditechmandalay.jpg', 'MedTechMandalay.html', 25),
(12, 'University of Nursing, Yangon', 'Medicine', 'Yangon', 0, 'photos/nurseyangon.jpg', 'NursingYangon.html', 26),
(13, 'University of Nursing, Mandalay', 'Medicine', 'Mandalay', 0, 'photos/nursemandalay.jpg', 'NursingMandalay.html', 27),
(14, 'University of Community Health, Magway', 'Medicine', 'Magway', 400, 'photos/community.jpg', 'HealthMagway.html', 25),
(15, 'University of Traditional Medicine', 'Medicine', 'Mandalay', 380, 'photos/trad.jpg', 'TraMedicMandalay.html', 25),
(16, 'Yangon Technological University', 'Engineering', 'Yangon', 450, 'photos/ytu.jpg', 'YTU.html', 7),
(17, 'Mandalay Technological University', 'Engineering', 'Mandalay', 450, 'photos/mtu.jpg', 'mtu.html', 8),
(18, 'University of Technology: Yadanarpon Cyber City', 'Engineering', 'Mandalay', 420, 'photos/utycc.jpg', 'utycc.html', 6),
(19, 'Naypyitaw State Polytechnic University', 'Engineering & Computer Science', 'Naypyitaw', 450, 'photos/nspu.jpg', 'nspu.html', 9),
(20, 'Myanmar Aerospace Engineering University', 'Engineering', 'Mandalay', 400, 'photos/plane.jpg', 'plane.html', 10),
(21, 'Myanmar Maritime University', 'Engineering', 'Yangon', 420, 'photos/mmu.jpg', 'MMU.html', 15),
(22, 'University of Information Technology', 'Computer Science', 'Yangon', 450, 'photos/uit.jpg', 'UIT.html', 12),
(23, 'Myanmar Institute of Information Technology', 'Computer Science', 'Mandalay', 450, 'photos/miit.jpg', 'MIIT.html', 11),
(24, 'University of Computer Studies, Yangon', 'Computer Science', 'Yangon', 450, 'photos/ucsy.jpg', 'UCSY.html', 13),
(25, 'University of Computer Studies, Mandalay', 'Computer Science', 'Mandalay', 450, 'photos/ucsm.jpg', 'UCSM.html', 14),
(26, 'West Yangon Technological University', 'Engineering', 'Yangon', 400, 'photos/wytu.jpg', 'wytu.html', 33),
(27, 'Technological University, Thanlyin', 'Engineering', 'Yangon', 400, 'photos/ttu.jpg', 'TUThanlyin.html', 36),
(28, 'Technological University, Mandalay', 'Engineering', 'Mandalay', 400, 'photos/tum.jpg', 'TUMandalay.html', 37),
(29, 'Technological University, Myitkyina', 'Engineering', 'Kachin', 400, 'photos/tukachin.jpg', 'TUMyitkyina.html', 38),
(30, 'Technological University, Monywa', 'Engineering', 'Sagaing', 400, 'photos/sagaingtu.jpg', 'TUMonywa.html', 39),
(31, 'Technological University, Kalay', 'Engineering', 'Sagaing', 400, 'photos/kalay.jpg', 'TUKalay.html', 40),
(32, 'Technological University, Kyaukse', 'Engineering', 'Mandalay', 400, 'photos/ktu.jpg', 'TUKyaukse.html', 41),
(33, 'Technological University, Meiktila', 'Engineering', 'Mandalay', 400, 'photos/mmtu.jpg', 'TUMeiktila.html', 42),
(34, 'Technological University, Yamethin', 'Engineering', 'Mandalay', 400, 'photos/ymethin.jpg', 'TUYamethin.html', 43),
(35, 'Technological University, Magway', 'Engineering', 'Magway', 400, 'photos/bjhtoo.jpg', 'TUMagway.html', 44),
(36, 'Technological University, Pakokku', 'Engineering', 'Magway', 400, 'photos/pakku.jpg', 'TUPakokku.html', 45),
(37, 'Technological University, Taunggyi', 'Engineering', 'Shan', 400, 'photos/tauggyitu.jpg', 'TUTaunggyi.html', 46),
(38, 'Technological University, Loikaw', 'Engineering', 'Kayah', 400, 'photos/kayahtu.jpg', 'TULoikaw.html', 47),
(39, 'Technological University, Sittwe', 'Engineering', 'Rakhine', 400, 'photos/sittway.jpg', 'TUSittway.html', 48),
(40, 'Technological University, Pyay', 'Engineering', 'Bago', 400, 'photos/pyaytu.jpg', 'TUPyay.html', 49),
(41, 'Technological University, Hmawbi', 'Engineering', 'Yangon', 400, 'photos/hmawbi.jpg', 'TUHmawbi.html', 50),
(42, 'Technological University, Pathein', 'Engineering', 'Ayeyarwady', 400, 'photos/patheintu.jpg', 'TUPathein.html', 51),
(43, 'Technological University, Hinthada', 'Engineering', 'Ayeyarwady', 400, 'photos/hinthada.jpg', 'TUHinthada.html', 52),
(44, 'Technological University, Hpa-an', 'Engineering', 'Kayin', 400, 'photos/kayin.jpg', 'TUHpaan.html', 53),
(45, 'Technological University, Mawlamyine', 'Engineering', 'Mon', 400, 'photos/mon.jpg', 'TUMawlamyine.html', 54),
(46, 'Technological University, Dawei', 'Engineering', 'Tanintharyi', 400, 'photos/dawei.jpg', 'TUDawei.html', 55),
(47, 'Technological University, Myeik', 'Engineering', 'Tanintharyi', 400, 'photos/myeik.jpg', 'TUMyeik.html', 56),
(48, 'Technological University, Lashio', 'Engineering', 'Shan', 400, 'photos/lashiotu.jpg', 'TULashio.html', 57),
(49, 'Technological University, Panglong', 'Engineering', 'Shan', 400, 'photos/panglong.jpg', 'TUPanglong.html', 58),
(50, 'University of Computer Studies, Monywa', 'Computer Science', 'Sagaing', 320, 'photos/monywa.jpg', 'CUMonywa.html', 59),
(51, 'University of Computer Studies, Kalay', 'Computer Science', 'Sagaing', 320, 'photos/kalaycu.jpg', 'CUKalay.html', 60),
(52, 'University of Computer Studies, Meiktila', 'Computer Science', 'Mandalay', 320, 'photos/meik.jpg', 'CUMeiktila.html', 61),
(53, 'University of Computer Studies, Magway', 'Computer Science', 'Magway', 320, 'photos/ucsmagway.jpg', 'CUMagway.html', 62),
(54, 'University of Computer Studies, Pakokku', 'Computer Science', 'Magway', 320, 'photos/ucsbago.jpg', 'CUPakokku.html', 63),
(55, 'University of Computer Studies, Taunggyi', 'Computer Science', 'Shan', 320, 'photos/shanuc.jpg', 'CUTaunggyi.html', 64),
(56, 'University of Computer Studies, Loikaw', 'Computer Science', 'Kayah', 320, 'photos/loikaw.jpg', 'CULoikaw.html', 65),
(57, 'University of Computer Studies, Taungoo', 'Computer Science', 'Bago', 320, 'photos/taucs.jpg', 'CUTaungoo.html', 66),
(58, 'University of Computer Studies, Sittwe', 'Computer Science', 'Rakhine', 320, 'photos/sitt.jpg', 'CUSittwe.html', 67),
(59, 'University of Computer Studies, Pyay', 'Computer Science', 'Bago', 320, 'photos/pyaycu.jpg', 'CUPyay.html', 68),
(60, 'University of Computer Studies, Pathein', 'Computer Science', 'Ayeyarwady', 320, 'photos/pat.jpg', 'CUPathein.html', 69),
(61, 'University of Computer Studies, Hinthada', 'Computer Science', 'Ayeyarwady', 320, 'photos/hinthadacu.jpg', 'CUHinthada.html', 70),
(62, 'University of Computer Studies, Hpa-an', 'Computer Science', 'Kayin', 320, 'photos/kayincu.jpg', 'CUHpaan.html', 71),
(63, 'University of Computer Studies, Thaton', 'Computer Science', 'Mon', 320, 'photos/tha.jpg', 'CUThaton.html', 72),
(64, 'University of Computer Studies, Bhamo', 'Computer Science', 'Kachin', 320, 'photos/bhamo.jpg', 'CUBhamo.html', 73),
(65, 'University of Computer Studies, Lashio', 'Computer Science', 'Shan', 320, 'photos/lashio.jpg', 'CULashio.html', 74),
(66, 'University of Computer Studies, Dawei', 'Computer Science', 'Tanintharyi', 320, 'photos/dawe.png', 'CUDawei.html', 75),
(67, 'University of Computer Studies, Myeik', 'Computer Science', 'Tanintharyi', 320, 'photos/myeikcu.jpg', 'CUMyeik.html', 76),
(68, 'University of Computer Studies, Panglong', 'Computer Science', 'Shan', 320, 'photos/pang.jpg', 'CUPanglong.html', 77),
(69, 'University of Computer Studies, Maubin', 'Computer Science', 'Ayeyarwady', 320, 'photos/ma.jpg', 'CUMaubin.html', 78),
(71, 'University of Yangon', 'Arts & Science,Law', 'Yangon', 450, 'photos/yu.jpg', 'YU.html', 81),
(72, 'University of Mandalay', 'Arts & Science,Law', 'Mandalay', 450, 'photos/mu.jpg', 'MU.html', 81),
(73, 'Dagon University', 'Arts & Science,Law', 'Yangon', 0, 'photos/dagon.jpg', 'Dagon.html', 81),
(74, 'East Yangon University', 'Arts & Science,Law', 'Yangon', 0, 'photos/eyu.jpg', 'EastYangon.html', 81),
(75, 'West Yangon University', 'Arts & Science,Law', 'Yangon', 0, 'photos/wyu.jpg', 'wyu.html', 82),
(76, 'Yadanabon University', 'Arts & Science,Law', 'Mandalay', 0, 'photos/yadanabon.jpg', 'yadanabon.html', 81),
(77, 'Yangon University of Economics(Hlaing)', 'Business & Economics', 'Yangon', 380, 'photos/yueco.jpg', 'yueco.html', 79),
(78, 'Meiktila University of Economics', 'Business & Economics', 'Mandalay', 400, 'photos/mteco.jpg', 'Meikeco.html', 80),
(79, 'Monywa University of Economics', 'Business & Economics', 'Sagaing', 400, 'photos/monywaeco.jpg', 'monywaeco.html', 80),
(80, 'Yangon University of Education', 'Education', 'Yangon', 420, 'photos/yuoe.jpg', 'YangonEdu.html', 101),
(81, 'Sagaing University of Education', 'Education', 'Sagaing', 420, 'photos/suoe.jpg', 'suoe.html', 101),
(82, 'Yezin Agricultural University', 'Agriculture', 'Naypyitaw', 380, 'photos/yau.jpg', 'YAU.html', 19),
(83, 'University of Veterinary Science, Yezin', 'Veterinary Science', 'Naypyitaw', 400, 'photos/uvs.jpg', 'UVS.html', 19),
(84, 'University of Forestry and Environmental Science', 'Forestry', 'Naypyitaw', 0, 'photos/ufes.jpg', 'ufes.html', 19),
(85, 'National University of Arts and Culture, Yangon', 'Arts & Culture', 'Yangon', 0, 'photos/nuac.jpg', 'nuac.html', 102),
(86, 'National University of Arts and Culture, Mandalay', 'Arts & Culture', 'Mandalay', 0, 'photos/nuacm.jpg', 'nuacm.html', 102),
(87, 'Co-operative University, Thanlyin', 'Business & Economics', 'Yangon', 0, 'photos/coop.jpg', 'CoThanlyin.html', 81),
(88, 'Co-operative University, Sagaing', 'Business & Economics', 'Sagaing', 0, 'photos/coops.jpg', 'CoSagaing.html', 81),
(89, 'Mawlamyine University', 'Arts & Science,Law', 'Mon', 340, 'photos/mlm.jpg', 'MawlamyineUni.html', 81),
(90, 'Taunggyi University', 'Arts & Science,Law', 'Shan', 0, 'photos/tgyi.jpg', 'TaunggyiUni.html', 82),
(91, 'Sittwe University', 'Arts & Science,Law', 'Rakhine', 0, 'photos/sittwe.jpg', 'SittweUni.html', 82),
(92, 'Pathein University', 'Arts & Science,Law', 'Ayeyarwady', 0, 'photos/pathein.jpg', 'PatheinUni.html', 82),
(93, 'Pyay University', 'Arts & Science,Law', 'Bago', 0, 'photos/pyay.jpg', 'PyayUni.html', 82),
(94, 'Myitkyina University', 'Arts & Science,Law', 'Kachin', 340, 'photos/myitkyina.jpg', 'MyitkyinaUni.html', 82),
(95, 'Yangon University of Economics(Ywathagyi)', 'Business & Economics', 'Yangon', 350, 'photos/yeco.jpg', 'yeco.html', 79),
(96, 'Technological University,Sagaing', 'Engineering', 'Sagaing', 400, 'photos/tsagaingu.jpg', 'TUSagaing.html', 34),
(97, 'Technological Unviersity,Taungoo', 'Engineering', 'Bago', 350, 'photos/tagtu.jpg\r\n', 'TUTaungoo.html', 35),
(98, 'University of Computer Studies,Myitkyina', 'Computer Science', 'Kachin', 320, 'photos/kachincu.jpg', 'CUMyitkyina.html', 79),
(99, 'Yangon University of Foreign Languages', 'Foreign Languages', 'Yangon', 380, 'photos/yufl.jpg', 'YUFL.html', 16),
(100, 'Mandalay University of Foreign Languages', 'Foreign Languages', 'Mandalay', 380, 'photos/mufl.jpg', 'MUFL.html', 17),
(101, 'Naypyitaw State Academy', 'Medicine,Law,Business,Arts & Education,Communication', 'Naypyitaw', 0, 'photos/nsa.jpg', 'NSA.html', 18),
(102, 'Polytechnic University,Kyaing tong', 'Engineering & Computer Science', 'Mon', 400, 'photos/monpu.jpg', 'PUKyaingTong.html', 28),
(103, 'Polytechnic University, Panglong', 'Engineering & Computer Science', 'Shan', 400, 'photos/pangpu.jpg', 'PUPanglong.html', 29),
(104, 'Polytechnic University, Myeik', 'Engineering & Computer Science', 'Tanintharyi', 400, 'photos/meikpu.jpg', 'PUMyeik.html', 30),
(105, 'Polytechnic University,Dawei', 'Engineering & Computer Science', 'Tanintharyi', 400, 'photos/puda.jpg', 'PUDawei.html', 31),
(106, ' Polytechnic University, Maubin', 'Engineering & Computer Science', 'Ayeyarwady', 400, 'photos/mau.jpg', 'PUMaubin.html', 32),
(108, 'Magway University', 'Arts & Science,Law', 'Magway', 0, 'photos/magwayUni.jpg', 'MagwayUni.html', 81),
(109, 'Technological University,Bhamo', 'Engineering', 'Kachin', 400, 'photos/bhamotu.jpg\n', 'TUBhamo.html\r\n', 45),
(110, 'Technological University,Kyaing Tong', 'Engineering', 'Mon', 400, 'photos/kktu.jpg', 'TUKyaingTong.html\r\n', 45),
(111, 'Technological University,Maubin', 'Engineering', 'Ayeyarwady', 400, 'photos/maubin.jpg', 'TUMaubin.html\r\n', 45),
(112, 'University of Computer Studies(Mandalay)', 'Computer Science', 'Mandalay', 320, 'photos/ucsmm.jpg\r\n', 'CUMandalay.html', 60),
(113, 'Defence Services Academy', '', 'Mandalay', 0, 'photos/DSA.jpg', 'DSA.html', 105),
(114, 'Defence Services Institute of Nursing and Paramedical Science', 'Medicine', 'Yangon', 0, 'photos/minp.jpg', 'minp.html', 105),
(115, 'Defence Services Medical Academy', 'Medicine', 'Yangon', 0, 'photos/DD.jpg', 'DMA.html', 105),
(116, 'Defence Services Technological Academy', 'Engineering', 'Mandalay', 0, 'photos/dsta.jpg', 'dsta.html', 105),
(117, 'Hinthada University', 'Arts & Science,Law', 'Ayeyarwady', 0, 'photos/HinthadaUni.jpg', 'HinthadaUni.html', 100),
(118, 'Hpa-an University', 'Arts & Science,Law', 'Kayin', 0, 'photos/HpaanUni.jpg', 'HpaanUni.html', 100),
(119, 'Kalay University', 'Arts & Science,Law', 'Sagaing', 0, 'photos/KalayUni.jpg', 'KalayUni.html', 100),
(120, 'Kyaing Tong University', 'Arts & Science,Law', 'Shan', 0, 'photos/kyaingtonUni.jpg', 'KyaingtongUni.html', 100),
(121, 'Kyaukse University', 'Arts & Science,Law', 'Mandalay', 0, 'photos/kyaukseUni.jpg', 'KyaukseUni.html', 100),
(122, 'Lahio University', 'Arts & Science,Law', 'Shan', 0, 'photos/lashioUni.jpg', 'LashioUni.html', 100),
(123, 'Loikaw University', 'Arts & Science,Law', 'Kayar', 0, 'photos/loikawUni.jpg', 'LoikawUni.html', 100),
(124, 'Maubin University', 'Arts & Science,Law', 'Ayeyarwaday', 0, 'photos/maubinUni.jpg', 'MaubinUni.html', 100),
(125, 'Meiktila University', 'Arts & Science,Law', 'Mandalay', 0, 'photos/meiktilaUni.jpg', 'MeiktilaUni.html', 100),
(126, 'Monywa University', 'Arts & Science,Law', 'Sagaing', 0, 'photos/monywaUni.jpg', 'MonywaUni.html', 100),
(127, 'Myeik University', 'Arts & Science,Law', 'Tanintharyi', 0, 'photos/myeikUni.jpg', 'MyeikUni.html', 100),
(128, 'Pakokku University', 'Arts & Science,Law', 'Bago', 0, 'photos/pkku.jpg', 'PakokkuUni.html', 100),
(129, 'Panglong University', 'Arts & Science,Law', 'Shan', 0, 'photos/panglongUni.jpg', 'PanglongUni.html', 100),
(130, 'Sagaing University', 'Arts & Science,Law', 'Sagaing', 0, 'photos/sagaingUni.jpg', 'SagaingUni.html', 100),
(131, 'Shwebo University ', 'Arts & Science,Law', 'Sagaing', 0, 'photos/shweboUni.jpg', 'ShweboUni.html', 100),
(132, 'Taungoo University', 'Arts & Science,Law', 'Bago', 0, 'photos/taungooUni.jpg', 'TaungooUni.html', 100),
(133, 'Yenangyaung University', 'Arts & Science,Law', 'Magway', 0, 'photos/yenaungUni.jpg', 'YenaungUni.html', 100),
(134, 'Taunggoke University', 'Arts & Science,Law', 'Rakhine', 0, 'photos/taungookeUni.jpg', 'TaungookeUni.html', 100),
(135, 'Bago University', 'Arts & Science,Law', 'Bago', 0, 'photos/BagoUni.jpg', 'BagoUni.html', 100),
(136, 'Bhamo University', 'Arts & Science,Law', 'Yangon', 0, 'photos/bhamoUni.jpg', 'BhamoUni.html\r\n', 100),
(137, 'Dawei University', 'Arts & Science,Law', 'Tanintharyi', 0, 'photos/DaweiUni.jpg', 'DaweiUni.html', 100),
(138, 'University of Public Health, Yangon', 'Medicine', 'Yangon', 0, 'photos/publichealth.jpg', 'PublicHealth.html', 25),
(139, 'Yangon University of Distance Education', 'Arts & Science,Law', 'Yangon', 0, 'photos/yude.jpg', 'YUDE.html', 103),
(140, 'Mandalay University of Distance Education', 'Arts & Science,Law', 'Mandalay', 0, 'photos/muode.jpg', 'MUDOE.html', 103),
(141, 'Polytechnic University,Myitkyina', 'Engineering & Computer Science', 'Kachin', 400, 'photos/pukachin.jpg', 'PUMyitkyina.html', 30),
(142, 'University of Mohnyin', 'Arts & Science,Law', 'Kachin', 0, 'photos/monyin.jpg', 'Mohnyin.html', 81);
UPDATE uni SET admission_status = CASE WHEN min_marks > 0 THEN 'known' ELSE 'unknown' END;
UPDATE uni SET page = 'TUYamethin.html' WHERE page = 'ymethin.html';
UPDATE uni SET page = 'PUMyitkyina.html' WHERE uni_name LIKE '%Polytechnic University%Myitkyina%';
-- Canonicalize database links to exact file casing for case-sensitive production hosts.
UPDATE uni SET page = 'UM1.html' WHERE id = 1;
UPDATE uni SET image_path = 'photos/medicine1.jpg' WHERE id = 1;
UPDATE uni SET page = 'UM2.html' WHERE id = 2;
UPDATE uni SET image_path = 'photos/medicine2.jpg' WHERE id = 2;
UPDATE uni SET page = 'UMM.html' WHERE id = 3;
UPDATE uni SET image_path = 'photos/medicine.jpg' WHERE id = 3;
UPDATE uni SET page = 'UMMAGWAY.html' WHERE id = 4;
UPDATE uni SET image_path = 'photos/magwaymedicine.jpg' WHERE id = 4;
UPDATE uni SET page = 'UMTAUNGGYI.html' WHERE id = 5;
UPDATE uni SET image_path = 'photos/medicinetauggyi.jpg' WHERE id = 5;
UPDATE uni SET page = 'DentalYangon.html' WHERE id = 6;
UPDATE uni SET image_path = 'photos/dentalyangon.jpg' WHERE id = 6;
UPDATE uni SET page = 'DentalMandalay.html' WHERE id = 7;
UPDATE uni SET image_path = 'photos/dentalmandalay.jpg' WHERE id = 7;
UPDATE uni SET page = 'PharmacyYangon.html' WHERE id = 8;
UPDATE uni SET image_path = 'photos/pharmacyyangon.jpg' WHERE id = 8;
UPDATE uni SET page = 'PharmacyMandalay.html' WHERE id = 9;
UPDATE uni SET image_path = 'photos/pharmacymandalay.jpg' WHERE id = 9;
UPDATE uni SET page = 'MedTechYangon.html' WHERE id = 10;
UPDATE uni SET image_path = 'photos/meditechyangon.jpg' WHERE id = 10;
UPDATE uni SET page = 'MedTechMandalay.html' WHERE id = 11;
UPDATE uni SET image_path = 'photos/meditechmandalay.jpg' WHERE id = 11;
UPDATE uni SET page = 'NursingYangon.html' WHERE id = 12;
UPDATE uni SET image_path = 'photos/nurseyangon.jpg' WHERE id = 12;
UPDATE uni SET page = 'NursingMandalay.html' WHERE id = 13;
UPDATE uni SET image_path = 'photos/nursemandalay.jpg' WHERE id = 13;
UPDATE uni SET page = 'HealthMagway.html' WHERE id = 14;
UPDATE uni SET image_path = 'photos/community.jpg' WHERE id = 14;
UPDATE uni SET page = 'TraMedicMandalay.html' WHERE id = 15;
UPDATE uni SET image_path = 'photos/trad.jpg' WHERE id = 15;
UPDATE uni SET page = 'YTU.html' WHERE id = 16;
UPDATE uni SET image_path = 'photos/ytu.jpg' WHERE id = 16;
UPDATE uni SET page = 'MTU.html' WHERE id = 17;
UPDATE uni SET image_path = 'photos/mtu.jpg' WHERE id = 17;
UPDATE uni SET page = 'UTYCC.html' WHERE id = 18;
UPDATE uni SET image_path = 'photos/utycc.jpg' WHERE id = 18;
UPDATE uni SET page = 'NSPU.html' WHERE id = 19;
UPDATE uni SET image_path = 'photos/nspu.jpg' WHERE id = 19;
UPDATE uni SET page = 'Plane.html' WHERE id = 20;
UPDATE uni SET image_path = 'photos/plane.jpg' WHERE id = 20;
UPDATE uni SET page = 'MMU.html' WHERE id = 21;
UPDATE uni SET image_path = 'photos/mmu.jpg' WHERE id = 21;
UPDATE uni SET page = 'UIT.html' WHERE id = 22;
UPDATE uni SET image_path = 'photos/uit.jpg' WHERE id = 22;
UPDATE uni SET page = 'MIIT.html' WHERE id = 23;
UPDATE uni SET image_path = 'photos/miit.jpg' WHERE id = 23;
UPDATE uni SET page = 'UCSY.html' WHERE id = 24;
UPDATE uni SET image_path = 'photos/ucsy.jpg' WHERE id = 24;
UPDATE uni SET page = 'UCSM.html' WHERE id = 25;
UPDATE uni SET image_path = 'photos/ucsm.jpg' WHERE id = 25;
UPDATE uni SET page = 'WYTU.html' WHERE id = 26;
UPDATE uni SET image_path = 'photos/wytu.jpg' WHERE id = 26;
UPDATE uni SET page = 'TUThanlyin.html' WHERE id = 27;
UPDATE uni SET image_path = 'photos/ttu.jpg' WHERE id = 27;
UPDATE uni SET page = 'TUMandalay.html' WHERE id = 28;
UPDATE uni SET image_path = 'photos/tum.jpg' WHERE id = 28;
UPDATE uni SET page = 'TUMyitkyina.html' WHERE id = 29;
UPDATE uni SET image_path = 'photos/tukachin.jpg' WHERE id = 29;
UPDATE uni SET page = 'TUMonywa.html' WHERE id = 30;
UPDATE uni SET image_path = 'photos/sagaingtu.jpg' WHERE id = 30;
UPDATE uni SET page = 'TUKalay.html' WHERE id = 31;
UPDATE uni SET image_path = 'photos/kalay.jpg' WHERE id = 31;
UPDATE uni SET page = 'TUKyaukse.html' WHERE id = 32;
UPDATE uni SET image_path = 'photos/ktu.jpg' WHERE id = 32;
UPDATE uni SET page = 'TUMeiktila.html' WHERE id = 33;
UPDATE uni SET image_path = 'photos/mmtu.jpg' WHERE id = 33;
UPDATE uni SET page = 'TUYamethin.html' WHERE id = 34;
UPDATE uni SET image_path = 'photos/ymethin.jpg' WHERE id = 34;
UPDATE uni SET page = 'TUMagway.html' WHERE id = 35;
UPDATE uni SET image_path = 'photos/bjhtoo.jpg' WHERE id = 35;
UPDATE uni SET page = 'TUPakokku.html' WHERE id = 36;
UPDATE uni SET image_path = 'photos/pakku.jpg' WHERE id = 36;
UPDATE uni SET page = 'TUTaunggyi.html' WHERE id = 37;
UPDATE uni SET image_path = 'photos/tauggyitu.jpg' WHERE id = 37;
UPDATE uni SET page = 'TULoikaw.html' WHERE id = 38;
UPDATE uni SET image_path = 'photos/kayahtu.jpg' WHERE id = 38;
UPDATE uni SET page = 'TUSittway.html' WHERE id = 39;
UPDATE uni SET image_path = 'photos/sittway.jpg' WHERE id = 39;
UPDATE uni SET page = 'TUPyay.html' WHERE id = 40;
UPDATE uni SET image_path = 'photos/pyaytu.jpg' WHERE id = 40;
UPDATE uni SET page = 'TUHmawbi.html' WHERE id = 41;
UPDATE uni SET image_path = 'photos/hmawbi.jpg' WHERE id = 41;
UPDATE uni SET page = 'TUPathein.html' WHERE id = 42;
UPDATE uni SET image_path = 'photos/patheintu.jpg' WHERE id = 42;
UPDATE uni SET page = 'TUHinthada.html' WHERE id = 43;
UPDATE uni SET image_path = 'photos/hinthada.jpg' WHERE id = 43;
UPDATE uni SET page = 'TUHpaan.html' WHERE id = 44;
UPDATE uni SET image_path = 'photos/kayin.jpg' WHERE id = 44;
UPDATE uni SET page = 'TUMawlamyine.html' WHERE id = 45;
UPDATE uni SET image_path = 'photos/mon.jpg' WHERE id = 45;
UPDATE uni SET page = 'TUDawei.html' WHERE id = 46;
UPDATE uni SET image_path = 'photos/dawei.jpg' WHERE id = 46;
UPDATE uni SET page = 'TUMyeik.html' WHERE id = 47;
UPDATE uni SET image_path = 'photos/myeik.jpg' WHERE id = 47;
UPDATE uni SET page = 'TULashio.html' WHERE id = 48;
UPDATE uni SET image_path = 'photos/lashiotu.jpg' WHERE id = 48;
UPDATE uni SET page = 'TUPanglong.html' WHERE id = 49;
UPDATE uni SET image_path = 'photos/panglong.jpg' WHERE id = 49;
UPDATE uni SET page = 'CUMonywa.html' WHERE id = 50;
UPDATE uni SET image_path = 'photos/monywa.jpg' WHERE id = 50;
UPDATE uni SET page = 'CUKalay.html' WHERE id = 51;
UPDATE uni SET image_path = 'photos/kalaycu.jpg' WHERE id = 51;
UPDATE uni SET page = 'CUMeiktila.html' WHERE id = 52;
UPDATE uni SET image_path = 'photos/meik.jpg' WHERE id = 52;
UPDATE uni SET page = 'CUMagway.html' WHERE id = 53;
UPDATE uni SET image_path = 'photos/ucsmagway.jpg' WHERE id = 53;
UPDATE uni SET page = 'CUPakokku.html' WHERE id = 54;
UPDATE uni SET image_path = 'photos/ucsbago.jpg' WHERE id = 54;
UPDATE uni SET page = 'CUTaunggyi.html' WHERE id = 55;
UPDATE uni SET image_path = 'photos/shanuc.jpg' WHERE id = 55;
UPDATE uni SET page = 'CULoikaw.html' WHERE id = 56;
UPDATE uni SET image_path = 'photos/loikaw.jpg' WHERE id = 56;
UPDATE uni SET page = 'CUTaungoo.html' WHERE id = 57;
UPDATE uni SET image_path = 'photos/taucs.jpg' WHERE id = 57;
UPDATE uni SET page = 'CUSittwe.html' WHERE id = 58;
UPDATE uni SET image_path = 'photos/sitt.jpg' WHERE id = 58;
UPDATE uni SET page = 'CUPyay.html' WHERE id = 59;
UPDATE uni SET image_path = 'photos/pyaycu.jpg' WHERE id = 59;
UPDATE uni SET page = 'CUPathein.html' WHERE id = 60;
UPDATE uni SET image_path = 'photos/pat.jpg' WHERE id = 60;
UPDATE uni SET page = 'CUHinthada.html' WHERE id = 61;
UPDATE uni SET image_path = 'photos/hinthadacu.jpg' WHERE id = 61;
UPDATE uni SET page = 'CUHpaan.html' WHERE id = 62;
UPDATE uni SET image_path = 'photos/kayincu.jpg' WHERE id = 62;
UPDATE uni SET page = 'CUThaton.html' WHERE id = 63;
UPDATE uni SET image_path = 'photos/tha.jpg' WHERE id = 63;
UPDATE uni SET page = 'CUBhamo.html' WHERE id = 64;
UPDATE uni SET image_path = 'photos/bhamo.jpg' WHERE id = 64;
UPDATE uni SET page = 'CULashio.html' WHERE id = 65;
UPDATE uni SET image_path = 'photos/lashio.jpg' WHERE id = 65;
UPDATE uni SET page = 'CUDawei.html' WHERE id = 66;
UPDATE uni SET image_path = 'photos/dawe.png' WHERE id = 66;
UPDATE uni SET page = 'CUMyeik.html' WHERE id = 67;
UPDATE uni SET image_path = 'photos/myeikcu.jpg' WHERE id = 67;
UPDATE uni SET page = 'CUPanglong.html' WHERE id = 68;
UPDATE uni SET image_path = 'photos/pang.jpg' WHERE id = 68;
UPDATE uni SET page = 'CUMaubin.html' WHERE id = 69;
UPDATE uni SET image_path = 'photos/ma.jpg' WHERE id = 69;
UPDATE uni SET page = 'YU.html' WHERE id = 71;
UPDATE uni SET image_path = 'photos/yu.jpg' WHERE id = 71;
UPDATE uni SET page = 'MU.html' WHERE id = 72;
UPDATE uni SET image_path = 'photos/mu.jpg' WHERE id = 72;
UPDATE uni SET page = 'Dagon.html' WHERE id = 73;
UPDATE uni SET image_path = 'photos/dagon.jpg' WHERE id = 73;
UPDATE uni SET page = 'EastYangon.html' WHERE id = 74;
UPDATE uni SET image_path = 'photos/eyu.jpg' WHERE id = 74;
UPDATE uni SET page = 'wyu.html' WHERE id = 75;
UPDATE uni SET image_path = 'photos/wyu.jpg' WHERE id = 75;
UPDATE uni SET page = 'yadanabon.html' WHERE id = 76;
UPDATE uni SET image_path = 'photos/yadanabon.jpg' WHERE id = 76;
UPDATE uni SET page = 'yueco.html' WHERE id = 77;
UPDATE uni SET image_path = 'photos/yueco.jpg' WHERE id = 77;
UPDATE uni SET page = 'Meikeco.html' WHERE id = 78;
UPDATE uni SET image_path = 'photos/mteco.jpg' WHERE id = 78;
UPDATE uni SET page = 'monywaeco.html' WHERE id = 79;
UPDATE uni SET image_path = 'photos/monywaeco.jpg' WHERE id = 79;
UPDATE uni SET page = 'YangonEdu.html' WHERE id = 80;
UPDATE uni SET image_path = 'photos/yuoe.jpg' WHERE id = 80;
UPDATE uni SET page = 'suoe.html' WHERE id = 81;
UPDATE uni SET image_path = 'photos/suoe.jpg' WHERE id = 81;
UPDATE uni SET page = 'YAU.html' WHERE id = 82;
UPDATE uni SET image_path = 'photos/yau.jpg' WHERE id = 82;
UPDATE uni SET page = 'UVS.html' WHERE id = 83;
UPDATE uni SET image_path = 'photos/uvs.jpg' WHERE id = 83;
UPDATE uni SET page = 'ufes.html' WHERE id = 84;
UPDATE uni SET image_path = 'photos/ufes.jpg' WHERE id = 84;
UPDATE uni SET page = 'nuac.html' WHERE id = 85;
UPDATE uni SET image_path = 'photos/nuac.jpg' WHERE id = 85;
UPDATE uni SET page = 'nuacm.html' WHERE id = 86;
UPDATE uni SET image_path = 'photos/nuacm.jpg' WHERE id = 86;
UPDATE uni SET page = 'CoThanlyin.html' WHERE id = 87;
UPDATE uni SET image_path = 'photos/coop.jpg' WHERE id = 87;
UPDATE uni SET page = 'CoSagaing.html' WHERE id = 88;
UPDATE uni SET image_path = 'photos/coops.jpg' WHERE id = 88;
UPDATE uni SET page = 'MawlamyineUni.html' WHERE id = 89;
UPDATE uni SET image_path = 'photos/mlm.jpg' WHERE id = 89;
UPDATE uni SET page = 'TaunggyiUni.html' WHERE id = 90;
UPDATE uni SET image_path = 'photos/tgyi.jpg' WHERE id = 90;
UPDATE uni SET page = 'SittweUni.html' WHERE id = 91;
UPDATE uni SET image_path = 'photos/sittwe.jpg' WHERE id = 91;
UPDATE uni SET page = 'PatheinUni.html' WHERE id = 92;
UPDATE uni SET image_path = 'photos/pathein.jpg' WHERE id = 92;
UPDATE uni SET page = 'PyayUni.html' WHERE id = 93;
UPDATE uni SET image_path = 'photos/pyay.jpg' WHERE id = 93;
UPDATE uni SET page = 'MyitkyinaUni.html' WHERE id = 94;
UPDATE uni SET image_path = 'photos/myitkyina.jpg' WHERE id = 94;
UPDATE uni SET page = 'yeco.html' WHERE id = 95;
UPDATE uni SET image_path = 'photos/yeco.jpg' WHERE id = 95;
UPDATE uni SET page = 'TUSagaing.html' WHERE id = 96;
UPDATE uni SET image_path = 'photos/tsagaingu.jpg' WHERE id = 96;
UPDATE uni SET page = 'TUTaungoo.html' WHERE id = 97;
UPDATE uni SET image_path = 'photos/tagtu.jpg' WHERE id = 97;
UPDATE uni SET page = 'CUMyitkyina.html' WHERE id = 98;
UPDATE uni SET image_path = 'photos/kachincu.jpg' WHERE id = 98;
UPDATE uni SET page = 'YUFL.html' WHERE id = 99;
UPDATE uni SET image_path = 'photos/yufl.jpg' WHERE id = 99;
UPDATE uni SET page = 'MUFL.html' WHERE id = 100;
UPDATE uni SET image_path = 'photos/mufl.jpg' WHERE id = 100;
UPDATE uni SET page = 'NSA.html' WHERE id = 101;
UPDATE uni SET image_path = 'photos/nsa.jpg' WHERE id = 101;
UPDATE uni SET page = 'PUKyaingTong.html' WHERE id = 102;
UPDATE uni SET image_path = 'photos/monpu.jpg' WHERE id = 102;
UPDATE uni SET page = 'PUPanglong.html' WHERE id = 103;
UPDATE uni SET image_path = 'photos/pangpu.jpg' WHERE id = 103;
UPDATE uni SET page = 'PUMyeik.html' WHERE id = 104;
UPDATE uni SET image_path = 'photos/meikpu.jpg' WHERE id = 104;
UPDATE uni SET page = 'PUDawei.html' WHERE id = 105;
UPDATE uni SET image_path = 'photos/puda.jpg' WHERE id = 105;
UPDATE uni SET page = 'PUMaubin.html' WHERE id = 106;
UPDATE uni SET image_path = 'photos/mau.jpg' WHERE id = 106;
UPDATE uni SET page = 'MagwayUni.html' WHERE id = 108;
UPDATE uni SET image_path = 'photos/magwayUni.jpg' WHERE id = 108;
UPDATE uni SET page = 'TUBhamo.html' WHERE id = 109;
UPDATE uni SET image_path = 'photos/bhamotu.jpg' WHERE id = 109;
UPDATE uni SET page = 'TUKyaingTong.html' WHERE id = 110;
UPDATE uni SET image_path = 'photos/kktu.jpg' WHERE id = 110;
UPDATE uni SET page = 'TUMaubin.html' WHERE id = 111;
UPDATE uni SET image_path = 'photos/maubin.jpg' WHERE id = 111;
UPDATE uni SET page = 'CUMandalay.html' WHERE id = 112;
UPDATE uni SET image_path = 'photos/ucsmm.jpg' WHERE id = 112;
UPDATE uni SET page = 'DSA.html' WHERE id = 113;
UPDATE uni SET image_path = 'photos/DSA.jpg' WHERE id = 113;
UPDATE uni SET page = 'minp.html' WHERE id = 114;
UPDATE uni SET image_path = 'photos/minp.jpg' WHERE id = 114;
UPDATE uni SET page = 'DMA.html' WHERE id = 115;
UPDATE uni SET image_path = 'photos/DD.jpg' WHERE id = 115;
UPDATE uni SET page = 'dsta.html' WHERE id = 116;
UPDATE uni SET image_path = 'photos/dsta.jpg' WHERE id = 116;
UPDATE uni SET page = 'HinthadaUni.html' WHERE id = 117;
UPDATE uni SET image_path = 'photos/HinthadaUni.jpg' WHERE id = 117;
UPDATE uni SET page = 'HpaanUni.html' WHERE id = 118;
UPDATE uni SET image_path = 'photos/HpaanUni.jpg' WHERE id = 118;
UPDATE uni SET page = 'KalayUni.html' WHERE id = 119;
UPDATE uni SET image_path = 'photos/KalayUni.jpg' WHERE id = 119;
UPDATE uni SET page = 'KyaingtongUni.html' WHERE id = 120;
UPDATE uni SET image_path = 'photos/kyaingtonUni.jpg' WHERE id = 120;
UPDATE uni SET page = 'KyaukseUni.html' WHERE id = 121;
UPDATE uni SET image_path = 'photos/kyaukseUni.jpg' WHERE id = 121;
UPDATE uni SET page = 'LashioUni.html' WHERE id = 122;
UPDATE uni SET image_path = 'photos/lashioUni.jpg' WHERE id = 122;
UPDATE uni SET page = 'LoikawUni.html' WHERE id = 123;
UPDATE uni SET image_path = 'photos/loikawUni.jpg' WHERE id = 123;
UPDATE uni SET page = 'MaubinUni.html' WHERE id = 124;
UPDATE uni SET image_path = 'photos/maubinUni.jpg' WHERE id = 124;
UPDATE uni SET page = 'MeiktilaUni.html' WHERE id = 125;
UPDATE uni SET image_path = 'photos/meiktilaUni.jpg' WHERE id = 125;
UPDATE uni SET page = 'MonywaUni.html' WHERE id = 126;
UPDATE uni SET image_path = 'photos/monywaUni.jpg' WHERE id = 126;
UPDATE uni SET page = 'MyeikUni.html' WHERE id = 127;
UPDATE uni SET image_path = 'photos/myeikUni.jpg' WHERE id = 127;
UPDATE uni SET page = 'PakokkuUni.html' WHERE id = 128;
UPDATE uni SET image_path = 'photos/pkku.jpg' WHERE id = 128;
UPDATE uni SET page = 'PanglongUni.html' WHERE id = 129;
UPDATE uni SET image_path = 'photos/panglongUni.jpg' WHERE id = 129;
UPDATE uni SET page = 'SagaingUni.html' WHERE id = 130;
UPDATE uni SET image_path = 'photos/sagaingUni.jpg' WHERE id = 130;
UPDATE uni SET page = 'ShweboUni.html' WHERE id = 131;
UPDATE uni SET image_path = 'photos/shweboUni.jpg' WHERE id = 131;
UPDATE uni SET page = 'TaungooUni.html' WHERE id = 132;
UPDATE uni SET image_path = 'photos/taungooUni.jpg' WHERE id = 132;
UPDATE uni SET page = 'YenaungUni.html' WHERE id = 133;
UPDATE uni SET image_path = 'photos/yenaungUni.jpg' WHERE id = 133;
UPDATE uni SET page = 'TaungookeUni.html' WHERE id = 134;
UPDATE uni SET image_path = 'photos/taungookeUni.jpg' WHERE id = 134;
UPDATE uni SET page = 'BagoUni.html' WHERE id = 135;
UPDATE uni SET image_path = 'photos/BagoUni.jpg' WHERE id = 135;
UPDATE uni SET page = 'BhamoUni.html' WHERE id = 136;
UPDATE uni SET image_path = 'photos/BhamoUni.jpg' WHERE id = 136;
UPDATE uni SET page = 'DaweiUni.html' WHERE id = 137;
UPDATE uni SET image_path = 'photos/DaweiUni.jpg' WHERE id = 137;
UPDATE uni SET page = 'PublicHealth.html' WHERE id = 138;
UPDATE uni SET image_path = 'photos/publichealth.jpg' WHERE id = 138;
UPDATE uni SET page = 'YUDE.html' WHERE id = 139;
UPDATE uni SET image_path = 'photos/yude.jpg' WHERE id = 139;
UPDATE uni SET page = 'MUDOE.html' WHERE id = 140;
UPDATE uni SET image_path = 'photos/muode.jpg' WHERE id = 140;
UPDATE uni SET page = 'PUMyitkyina.html' WHERE id = 141;
UPDATE uni SET image_path = 'photos/myitkyina.jpg' WHERE id = 141;
UPDATE uni SET image_path = 'photos/pukachin.jpg' WHERE id = 141;
UPDATE courses SET page_link='YTU.html' WHERE LOWER(TRIM(page_link))='ytu.html';

CREATE TABLE admin_users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(80) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_login_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE opportunities (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(120) NOT NULL UNIQUE,
  title VARCHAR(180) NOT NULL,
  icon VARCHAR(32) NOT NULL DEFAULT '🎓',
  description TEXT NOT NULL,
  university_examples VARCHAR(255) NOT NULL DEFAULT '',
  page_link VARCHAR(255) NOT NULL DEFAULT '#',
  css_class VARCHAR(40) NOT NULL DEFAULT 'card-cs',
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_opportunities_active_order (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE specializations (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  opportunity_id INT UNSIGNED NOT NULL,
  slug VARCHAR(120) NOT NULL UNIQUE,
  title VARCHAR(180) NOT NULL,
  title_my VARCHAR(180) DEFAULT NULL,
  description TEXT NOT NULL,
  description_my TEXT DEFAULT NULL,
  work_context TEXT DEFAULT NULL,
  work_context_my TEXT DEFAULT NULL,
  icon VARCHAR(32) NOT NULL DEFAULT '🎓',
  badge_text VARCHAR(80) NOT NULL DEFAULT 'Major / Specialization',
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_specializations_category_order (opportunity_id, is_active, sort_order),
  CONSTRAINT fk_specializations_opportunity FOREIGN KEY (opportunity_id) REFERENCES opportunities(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE specialization_universities (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  specialization_id INT UNSIGNED NOT NULL,
  uni_id INT NOT NULL,
  status ENUM('offered','not_offered','unverified') NOT NULL DEFAULT 'unverified',
  notes TEXT DEFAULT NULL,
  source_url VARCHAR(500) DEFAULT NULL,
  last_verified_at DATE DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_specialization_university (specialization_id, uni_id),
  INDEX idx_specialization_status (specialization_id, status),
  CONSTRAINT fk_su_specialization FOREIGN KEY (specialization_id) REFERENCES specializations(id) ON DELETE CASCADE,
  CONSTRAINT fk_su_uni FOREIGN KEY (uni_id) REFERENCES uni(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE university_programs (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  uni_id INT NOT NULL,
  program_key VARCHAR(120) NOT NULL,
  program_name VARCHAR(180) NOT NULL,
  status ENUM('offered','not_offered','unverified') NOT NULL DEFAULT 'unverified',
  notes TEXT DEFAULT NULL,
  source_url VARCHAR(500) DEFAULT NULL,
  last_verified_at DATE DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_uni_program (uni_id, program_key),
  INDEX idx_program_key_status (program_key, status),
  CONSTRAINT fk_programs_uni FOREIGN KEY (uni_id) REFERENCES uni(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO opportunities (slug,title,icon,description,university_examples,page_link,css_class,sort_order,is_active) VALUES
('engineering','Engineering & Tech','⚙️','Designing, building, and maintaining structures, machines, and technological systems.','YTU • MTU • UTYCC','Engineering.html','card-eng',1,1),
('computer-science','Computer Science & IT','💻','Software development, artificial intelligence, cybersecurity, and data science.','UIT • UCSY • MIIT','Computerscience.html','card-cs',2,1),
('medicine','Medicine & Healthcare','⚕️','Clinical practice, surgical medicine, nursing, public health, and pharmacy.','UM1 • UM2 • UMM','medicine.html','card-med',3,1),
('business','Business & Management','📊','Financial management, digital marketing, economics, and business administration.','Yangon Eco • Meiktila Eco','business.html','card-biz',4,1),
('foreign-languages','Foreign Languages','🌐','International relations, translation, diplomacy, and global business trade.','YUFL • MUFL','language.html','card-lang',5,1),
('law','Law & Governance','⚖️','Legal frameworks, public policy, human rights, and corporate law compliance.','Yangon University • Mandalay University','Law.html','card-law',6,1),
('agriculture','Agriculture & Forestry','🌱','Agritech, animal husbandry, forest management, and environmental conservation.','YAU • UVS • UFES','agriculture.html','card-agri',7,1),
('education','Education & Pedagogy','📚','Teacher training, curriculum development, and educational administration.','Education universities • Sagaing education','education.html','card-edu',8,1),
('arts-culture','Arts & Culture','🎨','Creative disciplines including visual arts, traditional performance, theater, and heritage.','NUAC Yangon • NUAC Mandalay','arts.html','card-art',9,1),
('arts-science','Arts & Science','🔬','Explore sciences and humanities including physics, chemistry, history, and psychology.','Yangon University • Mandalay University','arts-science.html','card-science',10,1),
('defense','Defense & Military','🛡️','Specialized military training, including command, engineering, and healthcare pathways.','DSA • DSTA • DSMA','defense.html','card-defense',11,1);

-- Starter specialization list migrated from Engineering.html so these cards can be managed in the dashboard.
-- UTYCC's Precision Engineering association remains unverified until an official source is recorded.
INSERT INTO specializations (opportunity_id,slug,title,description,work_context,icon,badge_text,sort_order,is_active) SELECT id,'civil-engineering','Civil Engineering','Plan, design, and supervise the construction of essential public infrastructure, including highways, bridges, water systems, and large buildings.','Major construction firms (e.g., Shwe Taung, Max Myanmar), urban planning departments, and civil engineering consultancies.','🏗️','🔥 High Demand',1,1 FROM opportunities WHERE slug='engineering' ON DUPLICATE KEY UPDATE title=VALUES(title), description=VALUES(description), work_context=VALUES(work_context), icon=VALUES(icon), badge_text=VALUES(badge_text), sort_order=VALUES(sort_order), is_active=VALUES(is_active);
INSERT INTO specializations (opportunity_id,slug,title,description,work_context,icon,badge_text,sort_order,is_active) SELECT id,'electronic-engineering','Electronic Engineering','Design and develop electronic circuits, microprocessors, and embedded systems for consumer electronics, medical devices, and control systems.','Semiconductor companies, consumer electronics manufacturers, telecommunications firms, and embedded systems R&D labs.','🔌','⚡ Hardware',2,1 FROM opportunities WHERE slug='engineering' ON DUPLICATE KEY UPDATE title=VALUES(title), description=VALUES(description), work_context=VALUES(work_context), icon=VALUES(icon), badge_text=VALUES(badge_text), sort_order=VALUES(sort_order), is_active=VALUES(is_active);
INSERT INTO specializations (opportunity_id,slug,title,description,work_context,icon,badge_text,sort_order,is_active) SELECT id,'electrical-power-engineering','Electrical Power Engineering','Generate, transmit, and distribute electrical power. Design high-voltage grids, renewable energy systems, and industrial electrical networks.','Power distribution boards (e.g., YESC, EPC), renewable energy corporations, and industrial manufacturing plants.','⚡','💡 Essential',3,1 FROM opportunities WHERE slug='engineering' ON DUPLICATE KEY UPDATE title=VALUES(title), description=VALUES(description), work_context=VALUES(work_context), icon=VALUES(icon), badge_text=VALUES(badge_text), sort_order=VALUES(sort_order), is_active=VALUES(is_active);
INSERT INTO specializations (opportunity_id,slug,title,description,work_context,icon,badge_text,sort_order,is_active) SELECT id,'mechanical-engineering','Mechanical Engineering','Apply physics and materials science to design, analyze, and manufacture mechanical systems, engines, HVAC units, and heavy machinery.','Automotive assembly lines, industrial manufacturing facilities, energy sector plants, and heavy equipment engineering firms.','⚙️','🛠️ Core Role',4,1 FROM opportunities WHERE slug='engineering' ON DUPLICATE KEY UPDATE title=VALUES(title), description=VALUES(description), work_context=VALUES(work_context), icon=VALUES(icon), badge_text=VALUES(badge_text), sort_order=VALUES(sort_order), is_active=VALUES(is_active);
INSERT INTO specializations (opportunity_id,slug,title,description,work_context,icon,badge_text,sort_order,is_active) SELECT id,'telecommunication-engineering','Telecommunication Engineering','Design and manage communication networks, including fiber optics, satellite transmission, 5G cellular towers, and broad-scale internet infrastructure.','Telecom operators (ATOM, MPT, Ooredoo), internet service providers, and network infrastructure firms.','📡','📶 Connectivity',5,1 FROM opportunities WHERE slug='engineering' ON DUPLICATE KEY UPDATE title=VALUES(title), description=VALUES(description), work_context=VALUES(work_context), icon=VALUES(icon), badge_text=VALUES(badge_text), sort_order=VALUES(sort_order), is_active=VALUES(is_active);
INSERT INTO specializations (opportunity_id,slug,title,description,work_context,icon,badge_text,sort_order,is_active) SELECT id,'computer-engineering-and-it','Computer Engineering & IT','Bridge hardware and software by designing computing architecture, developing enterprise software systems, and managing robust IT networks.','Tech startups, corporate IT departments, fintech platforms, software development agencies, and data centers.','💻','🔥 High Demand',6,1 FROM opportunities WHERE slug='engineering' ON DUPLICATE KEY UPDATE title=VALUES(title), description=VALUES(description), work_context=VALUES(work_context), icon=VALUES(icon), badge_text=VALUES(badge_text), sort_order=VALUES(sort_order), is_active=VALUES(is_active);
INSERT INTO specializations (opportunity_id,slug,title,description,work_context,icon,badge_text,sort_order,is_active) SELECT id,'mechatronic-engineering','Mechatronic Engineering','Combine mechanical, electronic, and software engineering to develop automated manufacturing lines, smart robotics, and autonomous systems.','Advanced manufacturing plants, robotics startups, aerospace tech firms, and industrial automation R&D labs.','🤖','🚀 Future Tech',7,1 FROM opportunities WHERE slug='engineering' ON DUPLICATE KEY UPDATE title=VALUES(title), description=VALUES(description), work_context=VALUES(work_context), icon=VALUES(icon), badge_text=VALUES(badge_text), sort_order=VALUES(sort_order), is_active=VALUES(is_active);
INSERT INTO specializations (opportunity_id,slug,title,description,work_context,icon,badge_text,sort_order,is_active) SELECT id,'chemical-engineering','Chemical Engineering','Apply chemistry and engineering principles to design processes for producing fuels, pharmaceuticals, food products, and advanced materials.','Oil and gas corporations, pharmaceutical manufacturers, environmental agencies, and chemical processing plants.','🧪','🏭 Industrial',8,1 FROM opportunities WHERE slug='engineering' ON DUPLICATE KEY UPDATE title=VALUES(title), description=VALUES(description), work_context=VALUES(work_context), icon=VALUES(icon), badge_text=VALUES(badge_text), sort_order=VALUES(sort_order), is_active=VALUES(is_active);
INSERT INTO specializations (opportunity_id,slug,title,description,work_context,icon,badge_text,sort_order,is_active) SELECT id,'food-engineering','Food Engineering','Design industrial processes for food production, focusing on preservation, packaging, quality control, and large-scale nutritional extraction.','Food and beverage manufacturing plants, agricultural processing facilities, and Fast-Moving Consumer Goods (FMCG) conglomerates.','🥫','🌾 FMCG',9,1 FROM opportunities WHERE slug='engineering' ON DUPLICATE KEY UPDATE title=VALUES(title), description=VALUES(description), work_context=VALUES(work_context), icon=VALUES(icon), badge_text=VALUES(badge_text), sort_order=VALUES(sort_order), is_active=VALUES(is_active);
INSERT INTO specializations (opportunity_id,slug,title,description,work_context,icon,badge_text,sort_order,is_active) SELECT id,'textile-engineering','Textile Engineering','Engineer fibers, yarns, and fabrics. Develop new textile machinery processes, sustainable fabrics, and quality control systems for garment production.','Garment manufacturing hubs, synthetic fiber production plants, and textile quality assurance laboratories.','🧵','👕 Manufacturing',10,1 FROM opportunities WHERE slug='engineering' ON DUPLICATE KEY UPDATE title=VALUES(title), description=VALUES(description), work_context=VALUES(work_context), icon=VALUES(icon), badge_text=VALUES(badge_text), sort_order=VALUES(sort_order), is_active=VALUES(is_active);
INSERT INTO specializations (opportunity_id,slug,title,description,work_context,icon,badge_text,sort_order,is_active) SELECT id,'mining-engineering','Mining Engineering','Plan and execute the safe, efficient extraction of minerals and resources from the earth using underground and open-pit mining techniques.','Mining operations, resource exploration companies, geological testing firms, and explosive engineering agencies.','⛏️','⛰️ Resource',11,1 FROM opportunities WHERE slug='engineering' ON DUPLICATE KEY UPDATE title=VALUES(title), description=VALUES(description), work_context=VALUES(work_context), icon=VALUES(icon), badge_text=VALUES(badge_text), sort_order=VALUES(sort_order), is_active=VALUES(is_active);
INSERT INTO specializations (opportunity_id,slug,title,description,work_context,icon,badge_text,sort_order,is_active) SELECT id,'petroleum-engineering','Petroleum Engineering','Design methods for extracting oil and natural gas from reservoirs deep underground, optimizing drilling equipment and recovery processes.','Energy corporations, offshore drilling rigs, petroleum refineries, and pipeline management firms.','🛢️','⛰️ Resource',12,1 FROM opportunities WHERE slug='engineering' ON DUPLICATE KEY UPDATE title=VALUES(title), description=VALUES(description), work_context=VALUES(work_context), icon=VALUES(icon), badge_text=VALUES(badge_text), sort_order=VALUES(sort_order), is_active=VALUES(is_active);
INSERT INTO specializations (opportunity_id,slug,title,description,work_context,icon,badge_text,sort_order,is_active) SELECT id,'metallurgical-engineering-and-materials-science','Metallurgical Engineering & Materials Science','Study the physical and chemical behavior of metallic elements, develop new alloys, and test composites for structural or electronic applications.','Steel and metal refineries, manufacturing R&D labs, semiconductor firms, and aerospace developers.','🔬','🧪 Research',13,1 FROM opportunities WHERE slug='engineering' ON DUPLICATE KEY UPDATE title=VALUES(title), description=VALUES(description), work_context=VALUES(work_context), icon=VALUES(icon), badge_text=VALUES(badge_text), sort_order=VALUES(sort_order), is_active=VALUES(is_active);
INSERT INTO specializations (opportunity_id,slug,title,description,work_context,icon,badge_text,sort_order,is_active) SELECT id,'architecture','Architecture','Blend art and engineering to design functional, safe, and aesthetically pleasing buildings, focusing on spatial planning and environmental impact.','Architectural design studios, urban planning firms, real estate development companies, and independent practices.','🏛️','🎨 Creative Design',14,1 FROM opportunities WHERE slug='engineering' ON DUPLICATE KEY UPDATE title=VALUES(title), description=VALUES(description), work_context=VALUES(work_context), icon=VALUES(icon), badge_text=VALUES(badge_text), sort_order=VALUES(sort_order), is_active=VALUES(is_active);
INSERT INTO specializations (opportunity_id,slug,title,description,work_context,icon,badge_text,sort_order,is_active) SELECT id,'naval-architecture','Naval Architecture','Design the hull form, structure, and internal layout of ships and marine vessels, ensuring stability, buoyancy, and hydrodynamic efficiency.','Shipyards, maritime design consultancies, naval defense contractors, and offshore engineering firms.','🚢','Design & Hydrodynamics',15,1 FROM opportunities WHERE slug='engineering' ON DUPLICATE KEY UPDATE title=VALUES(title), description=VALUES(description), work_context=VALUES(work_context), icon=VALUES(icon), badge_text=VALUES(badge_text), sort_order=VALUES(sort_order), is_active=VALUES(is_active);
INSERT INTO specializations (opportunity_id,slug,title,description,work_context,icon,badge_text,sort_order,is_active) SELECT id,'marine-engineering','Marine Engineering','Oversee the design, installation, and maintenance of a vessel''s internal operational systems, including steering, ventilation, and power generation.','Commercial shipping companies, cruise lines, maritime regulatory agencies, and vessel maintenance facilities.','⚙️','Systems Operation',16,1 FROM opportunities WHERE slug='engineering' ON DUPLICATE KEY UPDATE title=VALUES(title), description=VALUES(description), work_context=VALUES(work_context), icon=VALUES(icon), badge_text=VALUES(badge_text), sort_order=VALUES(sort_order), is_active=VALUES(is_active);
INSERT INTO specializations (opportunity_id,slug,title,description,work_context,icon,badge_text,sort_order,is_active) SELECT id,'port-and-harbour-engineering','Port and Harbour Engineering','Design and construct coastal infrastructure such as breakwaters, jetties, cargo terminals, and dry docks to facilitate safe maritime transport and logistics.','Port authorities, coastal construction firms, global logistics hubs, and heavy civil engineering consultancies.','🏗️','Infrastructure',17,1 FROM opportunities WHERE slug='engineering' ON DUPLICATE KEY UPDATE title=VALUES(title), description=VALUES(description), work_context=VALUES(work_context), icon=VALUES(icon), badge_text=VALUES(badge_text), sort_order=VALUES(sort_order), is_active=VALUES(is_active);
INSERT INTO specializations (opportunity_id,slug,title,description,work_context,icon,badge_text,sort_order,is_active) SELECT id,'river-and-coastal-engineering','River and Coastal Engineering','Manage coastal erosion, flood defenses, and river navigability by designing sustainable marine structures and executing comprehensive dredging plans.','Environmental agencies, government water resource departments, dredging companies, and marine surveying firms.','🌊','Environmental',18,1 FROM opportunities WHERE slug='engineering' ON DUPLICATE KEY UPDATE title=VALUES(title), description=VALUES(description), work_context=VALUES(work_context), icon=VALUES(icon), badge_text=VALUES(badge_text), sort_order=VALUES(sort_order), is_active=VALUES(is_active);
INSERT INTO specializations (opportunity_id,slug,title,description,work_context,icon,badge_text,sort_order,is_active) SELECT id,'marine-electrical-and-electronics-engineering','Marine Electrical & Electronics Engineering','Develop and maintain complex marine power distribution networks, automated control systems, and highly calibrated onboard navigation equipment.','Marine technology manufacturers, ship repair yards, offshore wind farms, and maritime electronics suppliers.','🔌','Control Systems',19,1 FROM opportunities WHERE slug='engineering' ON DUPLICATE KEY UPDATE title=VALUES(title), description=VALUES(description), work_context=VALUES(work_context), icon=VALUES(icon), badge_text=VALUES(badge_text), sort_order=VALUES(sort_order), is_active=VALUES(is_active);
INSERT INTO specializations (opportunity_id,slug,title,description,work_context,icon,badge_text,sort_order,is_active) SELECT id,'marine-mechanical-engineering','Marine Mechanical Engineering','Focus on the mechanical components of marine vessels, maintaining and optimizing heavy machinery, main engines, compressors, and hydraulic systems.','Engine manufacturing plants, commercial fleet maintenance crews, offshore oil and gas rigs, and maritime defense sectors.','🔧','Machinery',20,1 FROM opportunities WHERE slug='engineering' ON DUPLICATE KEY UPDATE title=VALUES(title), description=VALUES(description), work_context=VALUES(work_context), icon=VALUES(icon), badge_text=VALUES(badge_text), sort_order=VALUES(sort_order), is_active=VALUES(is_active);
INSERT INTO specializations (opportunity_id,slug,title,description,work_context,icon,badge_text,sort_order,is_active) SELECT id,'nautical-science','Nautical Science','Master ship navigation, meteorology, cargo handling, and maritime law to safely operate and command commercial vessels across international waters.','Merchant navy fleets, international shipping corporations, maritime training academies, and harbor pilotage services.','🧭','Navigation & Command',21,1 FROM opportunities WHERE slug='engineering' ON DUPLICATE KEY UPDATE title=VALUES(title), description=VALUES(description), work_context=VALUES(work_context), icon=VALUES(icon), badge_text=VALUES(badge_text), sort_order=VALUES(sort_order), is_active=VALUES(is_active);
INSERT INTO specializations (opportunity_id,slug,title,description,work_context,icon,badge_text,sort_order,is_active) SELECT id,'propulsion-and-flight-vehicles','Propulsion and Flight Vehicles','Design, test, and optimize aerodynamic structures and propulsion engines (jet, turboprop, and rocket) to achieve maximum thrust and fuel efficiency.','Aerospace manufacturing companies, defense contractors, aviation research laboratories, and national space agencies.','🚀','Aerospace Dynamics',22,1 FROM opportunities WHERE slug='engineering' ON DUPLICATE KEY UPDATE title=VALUES(title), description=VALUES(description), work_context=VALUES(work_context), icon=VALUES(icon), badge_text=VALUES(badge_text), sort_order=VALUES(sort_order), is_active=VALUES(is_active);
INSERT INTO specializations (opportunity_id,slug,title,description,work_context,icon,badge_text,sort_order,is_active) SELECT id,'avionics','Avionics','Engineer the electronic systems used in aircraft and spacecraft, including communications, radar, flight control algorithms, and collision avoidance systems.','Aircraft production facilities, airline maintenance hubs, defense technology firms, and UAV (drone) manufacturing startups.','✈️','Aviation Electronics',23,1 FROM opportunities WHERE slug='engineering' ON DUPLICATE KEY UPDATE title=VALUES(title), description=VALUES(description), work_context=VALUES(work_context), icon=VALUES(icon), badge_text=VALUES(badge_text), sort_order=VALUES(sort_order), is_active=VALUES(is_active);
INSERT INTO specializations (opportunity_id,slug,title,description,work_context,icon,badge_text,sort_order,is_active) SELECT id,'electrical-systems-and-instrumentation','Electrical Systems & Instrumentation','Design the power generation architectures, complex wiring networks, and highly calibrated sensors required to monitor and control aerospace vehicle performance.','Aerospace integration facilities, satellite development labs, aviation testing centers, and military technology organizations.','🛰️','Flight Systems',24,1 FROM opportunities WHERE slug='engineering' ON DUPLICATE KEY UPDATE title=VALUES(title), description=VALUES(description), work_context=VALUES(work_context), icon=VALUES(icon), badge_text=VALUES(badge_text), sort_order=VALUES(sort_order), is_active=VALUES(is_active);
INSERT INTO specializations (opportunity_id,slug,title,description,work_context,icon,badge_text,sort_order,is_active) SELECT id,'fuel-and-propellant-engineering','Fuel and Propellant Engineering','Formulate and test advanced solid and liquid propellants, managing combustion stability, energy density, and the safe storage of volatile materials.','Propulsion test facilities, specialized chemical manufacturing plants, space exploration agencies, and missile defense programs.','🛢️','Chemical Engineering',25,1 FROM opportunities WHERE slug='engineering' ON DUPLICATE KEY UPDATE title=VALUES(title), description=VALUES(description), work_context=VALUES(work_context), icon=VALUES(icon), badge_text=VALUES(badge_text), sort_order=VALUES(sort_order), is_active=VALUES(is_active);
INSERT INTO specializations (opportunity_id,slug,title,description,work_context,icon,badge_text,sort_order,is_active) SELECT id,'space-systems-engineering','Space Systems Engineering','Integrate complex subsystems for satellites, planetary probes, and spacecraft, ensuring survivability and mission success in the extreme environment of outer space.','Satellite communications companies, national space programs, commercial spaceflight corporations, and orbital research institutes.','🌌','Orbital Tech',26,1 FROM opportunities WHERE slug='engineering' ON DUPLICATE KEY UPDATE title=VALUES(title), description=VALUES(description), work_context=VALUES(work_context), icon=VALUES(icon), badge_text=VALUES(badge_text), sort_order=VALUES(sort_order), is_active=VALUES(is_active);
INSERT INTO specializations (opportunity_id,slug,title,description,work_context,icon,badge_text,sort_order,is_active) SELECT id,'precision-engineering','Precision Engineering','Precision-focused engineering, measurement and manufacturing technologies.','Potential pathways include precision manufacturing, metrology, tooling and computer-controlled production. Confirm the exact program name and curriculum with the university before relying on this listing.','🎯','Verification pending',27,1 FROM opportunities WHERE slug='engineering' ON DUPLICATE KEY UPDATE title=VALUES(title), description=VALUES(description), work_context=VALUES(work_context), icon=VALUES(icon), badge_text=VALUES(badge_text), sort_order=VALUES(sort_order), is_active=1;
INSERT INTO specialization_universities (specialization_id,uni_id,status,notes) SELECT s.id,u.id,'not_offered','Project owner explicitly noted UTYCC does not offer Civil Engineering; re-check against the current official program list before relying on this.' FROM specializations s JOIN uni u ON LOWER(u.page)='utycc.html' WHERE s.slug='civil-engineering' ON DUPLICATE KEY UPDATE status=VALUES(status),notes=VALUES(notes);
INSERT INTO specialization_universities (specialization_id,uni_id,status,notes) SELECT s.id,u.id,'unverified','Requested starter association. This is not confirmed; verify against an official UTYCC program source before presenting it as offered.' FROM specializations s JOIN uni u ON LOWER(u.page)='utycc.html' WHERE s.slug='precision-engineering' ON DUPLICATE KEY UPDATE status=VALUES(status),notes=VALUES(notes);

-- Explicitly record the project requirement supplied by the owner: UTYCC does not offer Civil Engineering.
INSERT INTO university_programs (uni_id,program_key,program_name,status,notes)
SELECT id,'civil-engineering','Civil Engineering','not_offered','Added from the project owner''s explicit correction. Re-check against official current program lists before publication.'
FROM uni WHERE LOWER(page)='utycc.html' LIMIT 1;

SET FOREIGN_KEY_CHECKS=1;
