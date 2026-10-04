-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 04, 2026 at 04:42 PM
-- Server version: 8.0.30
-- PHP Version: 8.3.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kuesify`
--

-- --------------------------------------------------------

--
-- Table structure for table `ai_generations`
--

CREATE TABLE `ai_generations` (
  `id` bigint UNSIGNED NOT NULL,
  `organization_id` bigint UNSIGNED NOT NULL,
  `material_id` bigint UNSIGNED NOT NULL,
  `creator_id` bigint UNSIGNED NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `question_count` tinyint UNSIGNED NOT NULL,
  `difficulty` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `types` json NOT NULL,
  `failure_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ai_generations`
--

INSERT INTO `ai_generations` (`id`, `organization_id`, `material_id`, `creator_id`, `status`, `question_count`, `difficulty`, `types`, `failure_reason`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 2, 'review', 20, 'hard', '[\"multiple_choice\", \"true_false\"]', NULL, '2026-10-03 14:57:05', '2026-10-03 14:57:11');

-- --------------------------------------------------------

--
-- Table structure for table `ai_question_drafts`
--

CREATE TABLE `ai_question_drafts` (
  `id` bigint UNSIGNED NOT NULL,
  `ai_generation_id` bigint UNSIGNED NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prompt` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` json DEFAULT NULL,
  `correct_answer` text COLLATE utf8mb4_unicode_ci,
  `explanation` text COLLATE utf8mb4_unicode_ci,
  `points` int UNSIGNED NOT NULL DEFAULT '1000',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ai_question_drafts`
--

INSERT INTO `ai_question_drafts` (`id`, `ai_generation_id`, `type`, `prompt`, `options`, `correct_answer`, `explanation`, `points`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'true_false', 'Materi_05_Pengetahuan_Umum_Artificial_Intelligence mencakup pembahasan mengenai dasar-dasar kecerdasan buatan.', NULL, 'True', 'Materi tersebut secara umum membahas pengetahuan dasar mengenai Artificial Intelligence.', 1000, 'rejected', '2026-10-03 14:57:11', '2026-10-03 14:57:33'),
(2, 1, 'multiple_choice', 'Apa kepanjangan utama dari AI dalam konteks ilmu komputer?', '[\"Automated Integration\", \"Artificial Intelligence\", \"Advanced Interface\", \"Algorithmic Information\"]', 'Artificial Intelligence', 'AI secara universal merupakan singkatan dari Artificial Intelligence.', 1000, 'approved', '2026-10-03 14:57:11', '2026-10-03 14:57:37'),
(3, 1, 'true_false', 'Artificial Intelligence adalah teknologi yang hanya dapat diterapkan pada bidang industri manufaktur.', NULL, 'False', 'AI dapat diterapkan di berbagai bidang luas seperti kesehatan, keuangan, pendidikan, dan lain-lain.', 1000, 'approved', '2026-10-03 14:57:11', '2026-10-03 14:57:39'),
(4, 1, 'multiple_choice', 'Manakah di bawah ini yang merupakan cabang utama dari Artificial Intelligence?', '[\"Machine Learning\", \"Manual Processing\", \"Static Programming\", \"Basic Spreadsheet\"]', 'Machine Learning', 'Machine Learning adalah salah satu sub-bidang atau cabang utama dalam pengembangan AI.', 1000, 'approved', '2026-10-03 14:57:11', '2026-10-03 14:57:40'),
(5, 1, 'true_false', 'Machine Learning memungkinkan sistem komputer untuk belajar dari data tanpa harus secara eksplisit diprogram.', NULL, 'True', 'Definisi inti dari Machine Learning adalah kemampuan belajar otomatis berdasarkan data.', 1000, 'approved', '2026-10-03 14:57:11', '2026-10-03 14:57:42'),
(6, 1, 'multiple_choice', 'Apa fungsi utama dari Natural Language Processing (NLP) dalam AI?', '[\"Memproses gambar dan video\", \"Memahami dan memproses bahasa manusia\", \"Mengatur sirkuit listrik perangkat keras\", \"Menghitung rumus fisika kuantum\"]', 'Memahami dan memproses bahasa manusia', 'NLP berfokus pada interaksi antara komputer dan bahasa manusia.', 1000, 'approved', '2026-10-03 14:57:11', '2026-10-03 14:57:43'),
(7, 1, 'true_false', 'Deep Learning adalah sub-bidang dari Machine Learning yang menggunakan jaringan saraf tiruan (neural networks) dengan banyak lapisan.', NULL, 'True', 'Deep Learning memang menggunakan arsitektur deep neural networks.', 1000, 'approved', '2026-10-03 14:57:11', '2026-10-03 14:57:45'),
(8, 1, 'multiple_choice', 'Manakah teknologi AI yang paling sering digunakan untuk pengenalan wajah (facial recognition)?', '[\"Computer Vision\", \"Natural Language Processing\", \"Robotic Process Automation\", \"Database Management\"]', 'Computer Vision', 'Computer Vision adalah bidang AI yang memungkinkan komputer memproses dan memahami informasi visual.', 1000, 'approved', '2026-10-03 14:57:11', '2026-10-03 14:57:47'),
(9, 1, 'true_false', 'Artificial Intelligence sama sekali tidak membutuhkan data untuk dapat berfungsi secara optimal.', NULL, 'False', 'Sebagian besar sistem AI, terutama Machine Learning, sangat bergantung pada ketersediaan data pelatihan yang berkualitas.', 1000, 'approved', '2026-10-03 14:57:11', '2026-10-03 14:57:48'),
(10, 1, 'multiple_choice', 'Apa yang dimaksud dengan Artificial General Intelligence (AGI)?', '[\"AI yang hanya bisa bermain catur\", \"AI yang memiliki tingkat kecerdasan setara atau melebihi manusia di berbagai bidang\", \"AI yang khusus untuk administrasi perkantoran\", \"Sistem komputer tanpa algoritma\"]', 'AI yang memiliki tingkat kecerdasan setara atau melebihi manusia di berbagai bidang', 'AGI merujuk pada konsep kecerdasan buatan umum yang dapat menandingi fleksibilitas kognitif manusia.', 1000, 'approved', '2026-10-03 14:57:11', '2026-10-03 14:57:52'),
(11, 1, 'true_false', 'Algoritma pencarian (search algorithms) adalah salah satu metode klasik yang digunakan dalam sejarah perkembangan AI.', NULL, 'True', 'Pencarian heuristik dan algoritma pencarian adalah fondasi awal dalam pemecahan masalah AI klasik.', 1000, 'approved', '2026-10-03 14:57:11', '2026-10-03 14:57:51'),
(12, 1, 'multiple_choice', 'Manakah contoh penerapan AI dalam kehidupan sehari-hari?', '[\"Sistem rekomendasi pada layanan streaming\", \"Kalkulator saku standar\", \"Buku catatan fisik\", \"Kabel LAN jaringan\"]', 'Sistem rekomendasi pada layanan streaming', 'Layanan streaming menggunakan algoritma AI untuk merekomendasikan konten kepada pengguna.', 1000, 'approved', '2026-10-03 14:57:11', '2026-10-03 14:57:54'),
(13, 1, 'true_false', 'Etika dalam penggunaan AI (AI ethics) tidak perlu dibahas karena AI tidak memiliki dampak sosial.', NULL, 'False', 'Etika AI sangat penting untuk mencegah bias, pelanggaran privasi, dan dampak negatif lainnya.', 1000, 'approved', '2026-10-03 14:57:11', '2026-10-03 14:57:55'),
(14, 1, 'multiple_choice', 'Apa yang menjadi fokus utama dari bidang Reinforcement Learning?', '[\"Belajar melalui sistem penghargaan (reward) dan hukuman (penalty) dari interaksi lingkungan\", \"Menerjemahkan teks bahasa asing ke bahasa lokal\", \"Mengompres ukuran file gambar\", \"Membuat desain tata letak halaman web\"]', 'Belajar melalui sistem penghargaan (reward) dan hukuman (penalty) dari interaksi lingkungan', 'Reinforcement learning melatih agen untuk membuat keputusan melalui trial and error berbasis reward.', 1000, 'approved', '2026-10-03 14:57:11', '2026-10-03 14:58:00'),
(15, 1, 'true_false', 'Turing Test adalah salah satu metode historis untuk menguji kemampuan mesin menunjukkan perilaku cerdas yang tidak dapat dibedakan dari manusia.', NULL, 'True', 'Alan Turing mengusulkan tes ini untuk mengukur kecerdasan mesin.', 1000, 'approved', '2026-10-03 14:57:11', '2026-10-03 14:58:01'),
(16, 1, 'multiple_choice', 'Apa tantangan utama dalam pengembangan model Machine Learning?', '[\"Overfitting dan kualitas data yang buruk\", \"Ketersediaan listrik PLN yang stabil\", \"Warna perangkat keras komputer\", \"Jumlah tombol pada keyboard\"]', 'Overfitting dan kualitas data yang buruk', 'Overfitting (terlalu pas pada data latih) dan data yang buruk adalah masalah krusial dalam ML.', 1000, 'approved', '2026-10-03 14:57:11', '2026-10-03 14:58:03'),
(17, 1, 'true_false', 'Chatbot modern sebagian besar sudah memanfaatkan teknologi Large Language Models (LLMs).', NULL, 'True', 'Sebagian besar chatbot canggih saat ini ditenagai oleh model bahasa besar (LLMs).', 1000, 'approved', '2026-10-03 14:57:11', '2026-10-03 14:58:04'),
(18, 1, 'multiple_choice', 'Manakah komponen perangkat keras yang paling krusial untuk mempercepat pelatihan model Deep Learning berskala besar?', '[\"GPU (Graphics Processing Unit)\", \"Power Supply Unit\", \"Optical Drive\", \"Sound Card\"]', 'GPU (Graphics Processing Unit)', 'GPU sangat efisien dalam melakukan komputasi paralel yang dibutuhkan oleh Deep Learning.', 1000, 'approved', '2026-10-03 14:57:11', '2026-10-03 14:58:06'),
(19, 1, 'true_false', 'Artificial Narrow Intelligence (ANI) adalah jenis AI yang dirancang untuk menyelesaikan satu tugas spesifik saja.', NULL, 'True', 'Sebagian besar AI yang ada saat ini dikategorikan sebagai ANI (Narrow AI).', 1000, 'approved', '2026-10-03 14:57:11', '2026-10-03 14:58:07'),
(20, 1, 'multiple_choice', 'Apa dampak negatif potensial dari penggunaan AI yang tidak terkontrol?', '[\"Penyebaran informasi palsu (deepfake) dan bias algoritmik\", \"Peningkatan kecepatan internet secara global\", \"Penurunan suhu perangkat komputer secara drastis\", \"Pengurangan kapasitas penyimpanan harddisk\"]', 'Penyebaran informasi palsu (deepfake) dan bias algoritmik', 'Bias dan deepfake merupakan risiko nyata dari penyalahgunaan teknologi AI.', 1000, 'approved', '2026-10-03 14:57:11', '2026-10-03 14:58:09');

-- --------------------------------------------------------

--
-- Table structure for table `attempt_answers`
--

CREATE TABLE `attempt_answers` (
  `id` bigint UNSIGNED NOT NULL,
  `quiz_attempt_id` bigint UNSIGNED NOT NULL,
  `question_id` bigint UNSIGNED NOT NULL,
  `answer` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_correct` tinyint(1) DEFAULT NULL,
  `points_awarded` int UNSIGNED NOT NULL DEFAULT '0',
  `feedback` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attempt_answers`
--

INSERT INTO `attempt_answers` (`id`, `quiz_attempt_id`, `question_id`, `answer`, `is_correct`, `points_awarded`, `feedback`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Produsen', 1, 100, NULL, '2026-10-01 09:11:18', '2026-10-01 09:11:18'),
(2, 2, 3, 'Mematikan keran jika tidak dipakai kembali', 0, 1, NULL, '2026-10-01 09:11:49', '2026-10-03 15:20:40'),
(3, 3, 22, 'Penyebaran informasi palsu (deepfake) dan bias algoritmik', 1, 1000, NULL, '2026-10-03 15:23:11', '2026-10-03 15:23:13');

-- --------------------------------------------------------

--
-- Table structure for table `badges`
--

CREATE TABLE `badges` (
  `id` bigint UNSIGNED NOT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rarity` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'common',
  `criteria_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'completed_attempts',
  `criteria_value` int UNSIGNED NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `badges`
--

INSERT INTO `badges` (`id`, `key`, `name`, `description`, `rarity`, `criteria_type`, `criteria_value`, `created_at`, `updated_at`) VALUES
(1, 'first_attempt', 'Awakening of Novice', 'Selesaikan kuis pertama untuk memulai petualangan belajarmu.', 'common', 'completed_attempts', 1, '2026-10-01 06:57:45', '2026-10-01 06:57:45'),
(2, 'score_100', 'Centurion Scholar', 'Raih skor 100 mutlak dalam satu arena kuis.', 'rare', 'score', 100, '2026-10-01 06:57:45', '2026-10-01 06:57:45'),
(3, 'streak_3', 'Iron Will', 'Bangun tekad baja dengan streak belajar 3 hari beruntun.', 'rare', 'streak', 3, '2026-10-01 06:57:45', '2026-10-01 06:57:45'),
(4, 'quizzes_5', 'Trial Challenger', 'Taklukkan 5 sesi kuis latihan berbeda.', 'rare', 'completed_attempts', 5, '2026-10-01 06:57:45', '2026-10-01 06:57:45'),
(5, 'streak_7', 'Flame of Dedication', 'Pertahankan kobaran api streak selama 7 hari tanpa henti.', 'epic', 'streak', 7, '2026-10-01 06:57:45', '2026-10-01 06:57:45'),
(6, 'quizzes_15', 'Dungeon Conqueror', 'Selesaikan 15 sesi kuis dengan sukses.', 'epic', 'completed_attempts', 15, '2026-10-01 06:57:45', '2026-10-01 06:57:45'),
(7, 'xp_1000', 'Grand Scholar', 'Kumpulkan akumulasi 1.000 XP dari arena pengetahuan.', 'epic', 'xp', 1000, '2026-10-01 06:57:45', '2026-10-01 06:57:45'),
(8, 'perfect_score', 'Flawless Mastery', 'Selesaikan satu kuis dengan akurasi 100% tanpa kesalahan.', 'legendary', 'perfect_score', 1, '2026-10-01 06:57:45', '2026-10-01 06:57:45'),
(9, 'streak_14', 'Unyielding Vanguard', 'Jaga ketangguhan belajar selama 14 hari berturut-turut.', 'legendary', 'streak', 14, '2026-10-01 06:57:45', '2026-10-01 06:57:45'),
(10, 'xp_5000', 'High Archmage', 'Kumpulkan 5.000 XP dari berbagai kuis dan ekspedisi misi.', 'legendary', 'xp', 5000, '2026-10-01 06:57:45', '2026-10-01 06:57:45'),
(11, 'streak_30', 'Eternal Titan', 'Legenda hidup: Selesaikan kuis selama 30 hari penuh tanpa putus.', 'mythic', 'streak', 30, '2026-10-01 06:57:45', '2026-10-01 06:57:45'),
(12, 'xp_10000', 'Sovereign of Wisdom', 'Raih tahta tertinggi pengetahuan dengan 10.000 XP.', 'mythic', 'xp', 10000, '2026-10-01 06:57:45', '2026-10-01 06:57:45');

-- --------------------------------------------------------

--
-- Table structure for table `badge_awards`
--

CREATE TABLE `badge_awards` (
  `id` bigint UNSIGNED NOT NULL,
  `organization_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `badge_id` bigint UNSIGNED NOT NULL,
  `quiz_attempt_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `badge_awards`
--

INSERT INTO `badge_awards` (`id`, `organization_id`, `user_id`, `badge_id`, `quiz_attempt_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 1, '2026-10-01 09:11:20', '2026-10-01 09:11:20'),
(2, 1, 1, 2, 1, '2026-10-01 09:11:20', '2026-10-01 09:11:20'),
(5, 1, 1, 7, 3, '2026-10-03 15:23:14', '2026-10-03 15:23:14');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('kuesify-cache-5c785c036466adea360111aa28563bfd556b5fba', 'i:1;', 1791130838),
('kuesify-cache-5c785c036466adea360111aa28563bfd556b5fba:timer', 'i:1791130838;', 1791130838),
('kuesify-cache-da4b9237bacccdf19c0760cab7aec4a8359010b0', 'i:2;', 1791064672),
('kuesify-cache-da4b9237bacccdf19c0760cab7aec4a8359010b0:timer', 'i:1791064672;', 1791064672),
('kuesify-cache-live-leaderboard:1', 'a:2:{i:0;a:4:{s:2:\"id\";i:2;s:5:\"alias\";s:9:\"Mas anies\";s:10:\"avatar_key\";s:9:\"profile_7\";s:5:\"score\";i:290;}i:1;a:4:{s:2:\"id\";i:1;s:5:\"alias\";s:9:\"Mas anies\";s:10:\"avatar_key\";N;s:5:\"score\";i:250;}}', 1791127119),
('kuesify-cache-live-leaderboard:10', 'a:0:{}', 1791123890),
('kuesify-cache-live-leaderboard:11', 'a:0:{}', 1791123923),
('kuesify-cache-live-leaderboard:12', 'a:0:{}', 1791124802),
('kuesify-cache-live-leaderboard:13', 'a:1:{i:0;a:4:{s:2:\"id\";i:10;s:5:\"alias\";s:17:\"Demo unregistered\";s:10:\"avatar_key\";s:9:\"profile_1\";s:5:\"score\";i:0;}}', 1791125059),
('kuesify-cache-live-leaderboard:14', 'a:0:{}', 1791125324),
('kuesify-cache-live-leaderboard:15', 'a:0:{}', 1791126081),
('kuesify-cache-live-leaderboard:16', 'a:0:{}', 1791126785),
('kuesify-cache-live-leaderboard:17', 'a:0:{}', 1791126801),
('kuesify-cache-live-leaderboard:18', 'a:0:{}', 1791126935),
('kuesify-cache-live-leaderboard:19', 'a:0:{}', 1791127224),
('kuesify-cache-live-leaderboard:20', 'a:0:{}', 1791127295),
('kuesify-cache-live-leaderboard:21', 'a:1:{i:0;a:4:{s:2:\"id\";i:11;s:5:\"alias\";s:17:\"Demo unregistered\";s:10:\"avatar_key\";s:9:\"profile_6\";s:5:\"score\";i:170;}}', 1791128726),
('kuesify-cache-live-leaderboard:22', 'a:0:{}', 1791128806),
('kuesify-cache-live-leaderboard:23', 'a:0:{}', 1791128949),
('kuesify-cache-live-leaderboard:24', 'a:0:{}', 1791128955),
('kuesify-cache-live-leaderboard:25', 'a:1:{i:0;a:4:{s:2:\"id\";i:12;s:5:\"alias\";s:17:\"Demo unregistered\";s:10:\"avatar_key\";s:10:\"profile_10\";s:5:\"score\";i:120;}}', 1791129053),
('kuesify-cache-live-leaderboard:3', 'a:1:{i:0;a:4:{s:2:\"id\";i:4;s:5:\"alias\";s:9:\"Mas anies\";s:10:\"avatar_key\";s:9:\"profile_7\";s:5:\"score\";i:110;}}', 1791062180),
('kuesify-cache-live-leaderboard:4', 'a:0:{}', 1791062220),
('kuesify-cache-live-leaderboard:5', 'a:1:{i:0;a:4:{s:2:\"id\";i:5;s:5:\"alias\";s:17:\"Bagas Nurdiansyah\";s:10:\"avatar_key\";s:10:\"profile_11\";s:5:\"score\";i:700;}}', 1791062465),
('kuesify-cache-live-leaderboard:6', 'a:1:{i:0;a:4:{s:2:\"id\";i:6;s:5:\"alias\";s:17:\"Bagas Nurdiansyah\";s:10:\"avatar_key\";s:10:\"profile_11\";s:5:\"score\";i:600;}}', 1791062698),
('kuesify-cache-live-leaderboard:7', 'a:1:{i:0;a:4:{s:2:\"id\";i:7;s:5:\"alias\";s:17:\"Bagas Nurdiansyah\";s:10:\"avatar_key\";s:10:\"profile_11\";s:5:\"score\";i:580;}}', 1791063863),
('kuesify-cache-live-leaderboard:8', 'a:1:{i:0;a:4:{s:2:\"id\";i:8;s:5:\"alias\";s:17:\"Bagas Nurdiansyah\";s:10:\"avatar_key\";s:10:\"profile_11\";s:5:\"score\";i:150;}}', 1791064591),
('kuesify-cache-live-leaderboard:9', 'a:1:{i:0;a:4:{s:2:\"id\";i:9;s:5:\"alias\";s:17:\"Bagas Nurdiansyah\";s:10:\"avatar_key\";s:10:\"profile_11\";s:5:\"score\";i:11130;}}', 1791065436);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `theme_key` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `id` bigint UNSIGNED NOT NULL,
  `organization_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `organization_id`, `name`, `created_at`, `updated_at`) VALUES
(1, 1, 'Jokowi Hadir', '2026-10-03 13:33:38', '2026-10-03 13:33:38');

-- --------------------------------------------------------

--
-- Table structure for table `group_user`
--

CREATE TABLE `group_user` (
  `group_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `live_answers`
--

CREATE TABLE `live_answers` (
  `id` bigint UNSIGNED NOT NULL,
  `live_participant_id` bigint UNSIGNED NOT NULL,
  `question_id` bigint UNSIGNED NOT NULL,
  `answer` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_correct` tinyint(1) NOT NULL,
  `points_awarded` int UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `live_answers`
--

INSERT INTO `live_answers` (`id`, `live_participant_id`, `question_id`, `answer`, `is_correct`, `points_awarded`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Produsen', 1, 250, '2026-10-03 13:43:45', '2026-10-03 13:43:45'),
(2, 2, 2, 'true', 1, 290, '2026-10-03 13:55:45', '2026-10-03 13:55:45'),
(3, 3, 2, 'true', 1, 300, '2026-10-03 14:04:12', '2026-10-03 14:04:12'),
(4, 4, 1, 'Produsen', 1, 110, '2026-10-03 14:14:40', '2026-10-03 14:14:40'),
(5, 5, 1, 'Produsen', 1, 360, '2026-10-03 14:18:07', '2026-10-03 14:18:07'),
(6, 5, 2, 'true', 1, 340, '2026-10-03 14:18:16', '2026-10-03 14:18:16'),
(7, 6, 1, 'Produsen', 1, 290, '2026-10-03 14:21:32', '2026-10-03 14:21:32'),
(8, 6, 2, 'true', 1, 310, '2026-10-03 14:23:49', '2026-10-03 14:23:49'),
(9, 7, 1, 'Produsen', 1, 330, '2026-10-03 14:38:11', '2026-10-03 14:38:11'),
(10, 7, 2, 'true', 1, 250, '2026-10-03 14:39:44', '2026-10-03 14:39:44'),
(12, 8, 2, 'true', 1, 150, '2026-10-03 14:55:57', '2026-10-03 14:55:57'),
(13, 9, 22, 'Penyebaran informasi palsu (deepfake) dan bias algoritmik', 1, 1040, '2026-10-03 15:02:46', '2026-10-03 15:02:46'),
(14, 9, 20, 'GPU (Graphics Processing Unit)', 1, 1010, '2026-10-03 15:03:32', '2026-10-03 15:03:32'),
(15, 9, 19, 'True', 1, 1020, '2026-10-03 15:03:52', '2026-10-03 15:03:52'),
(16, 9, 18, 'Overfitting dan kualitas data yang buruk', 1, 1010, '2026-10-03 15:04:12', '2026-10-03 15:04:12'),
(17, 9, 15, 'False', 1, 1020, '2026-10-03 15:05:24', '2026-10-03 15:05:24'),
(18, 9, 14, 'Sistem rekomendasi pada layanan streaming', 1, 1000, '2026-10-03 15:05:43', '2026-10-03 15:05:43'),
(19, 9, 12, 'True', 1, 1010, '2026-10-03 15:05:59', '2026-10-03 15:05:59'),
(20, 9, 11, 'False', 1, 1000, '2026-10-03 15:06:18', '2026-10-03 15:06:18'),
(21, 9, 10, 'Computer Vision', 1, 1010, '2026-10-03 15:06:52', '2026-10-03 15:06:52'),
(22, 9, 9, 'True', 1, 1010, '2026-10-03 15:07:09', '2026-10-03 15:07:09'),
(23, 9, 8, 'Memproses gambar dan video', 0, 0, '2026-10-03 15:07:26', '2026-10-03 15:07:26'),
(24, 9, 7, 'True', 1, 1000, '2026-10-03 15:08:51', '2026-10-03 15:08:51'),
(25, 11, 1, 'Produsen', 1, 170, '2026-10-04 08:22:50', '2026-10-04 08:22:50'),
(26, 11, 2, 'false', 0, 0, '2026-10-04 08:25:16', '2026-10-04 08:25:16'),
(27, 12, 1, 'Produsen', 1, 120, '2026-10-04 08:50:20', '2026-10-04 08:50:20');

-- --------------------------------------------------------

--
-- Table structure for table `live_participants`
--

CREATE TABLE `live_participants` (
  `id` bigint UNSIGNED NOT NULL,
  `live_session_id` bigint UNSIGNED NOT NULL,
  `alias` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reconnect_token` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `score` int UNSIGNED NOT NULL DEFAULT '0',
  `kicked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `live_participants`
--

INSERT INTO `live_participants` (`id`, `live_session_id`, `alias`, `avatar_key`, `reconnect_token`, `score`, `kicked_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'Mas anies', NULL, 'b55ec865-e8eb-420c-9efc-2a85f8ea7601', 250, NULL, '2026-10-03 13:43:20', '2026-10-03 13:43:46'),
(2, 1, 'Mas anies', 'profile_7', '545022ad-4e55-4d2d-9015-bbb282ba9bd7', 290, NULL, '2026-10-03 13:55:03', '2026-10-03 13:55:45'),
(3, 2, 'Mas anies', 'profile_7', '0f87ad2c-1d4d-4fa1-a55d-5c9f3cf5352e', 300, NULL, '2026-10-03 13:59:34', '2026-10-03 14:04:12'),
(4, 3, 'Mas anies', 'profile_7', '88c6fc9b-edf1-4683-8c0a-d47f6d3dc9fa', 110, NULL, '2026-10-03 14:13:27', '2026-10-03 14:14:40'),
(5, 5, 'Bagas Nurdiansyah', 'profile_11', 'ea352b95-0209-4f87-be73-33db41a319dd', 700, NULL, '2026-10-03 14:17:52', '2026-10-03 14:18:16'),
(6, 6, 'Bagas Nurdiansyah', 'profile_11', '51f3b1ec-31be-425b-9788-610c34577f1f', 600, NULL, '2026-10-03 14:21:14', '2026-10-03 14:23:49'),
(7, 7, 'Bagas Nurdiansyah', 'profile_11', '7b9c3eb2-4ab4-4aef-bbfd-5048826b5fae', 580, NULL, '2026-10-03 14:35:36', '2026-10-03 14:39:44'),
(8, 8, 'Bagas Nurdiansyah', 'profile_11', '845e5f74-ade7-4a66-b841-30486eb9a3ba', 150, NULL, '2026-10-03 14:45:53', '2026-10-03 14:55:57'),
(9, 9, 'Bagas Nurdiansyah', 'profile_11', '1eda21a0-f489-46e3-995e-cd1cf525d9fc', 11130, NULL, '2026-10-03 15:02:36', '2026-10-03 15:08:51'),
(10, 13, 'Demo unregistered', 'profile_1', 'a8fc1c4a-236b-46e7-a78e-266ecde6f593', 0, NULL, '2026-10-04 07:43:36', '2026-10-04 07:43:36'),
(11, 21, 'Demo unregistered', 'profile_6', 'da9352ce-a637-4a4c-ace8-8cee6c19b2dc', 170, NULL, '2026-10-04 08:22:13', '2026-10-04 08:22:50'),
(12, 25, 'Demo unregistered', 'profile_10', '6f0bd872-a748-412a-b3cd-49ccdb82847c', 120, NULL, '2026-10-04 08:50:08', '2026-10-04 08:50:20');

-- --------------------------------------------------------

--
-- Table structure for table `live_sessions`
--

CREATE TABLE `live_sessions` (
  `id` bigint UNSIGNED NOT NULL,
  `organization_id` bigint UNSIGNED NOT NULL,
  `quiz_id` bigint UNSIGNED NOT NULL,
  `host_id` bigint UNSIGNED NOT NULL,
  `pin` char(6) COLLATE utf8mb4_unicode_ci NOT NULL,
  `broadcast_token` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lobby_locked` tinyint(1) NOT NULL DEFAULT '0',
  `question_duration` int UNSIGNED NOT NULL,
  `speed_multiplier` int UNSIGNED NOT NULL,
  `current_question_id` bigint UNSIGNED DEFAULT NULL,
  `question_started_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `live_sessions`
--

INSERT INTO `live_sessions` (`id`, `organization_id`, `quiz_id`, `host_id`, `pin`, `broadcast_token`, `status`, `lobby_locked`, `question_duration`, `speed_multiplier`, `current_question_id`, `question_started_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 2, '783419', '87ed15fc-44f3-4508-a906-36d1f59b241c', 'ended', 0, 30, 10, NULL, NULL, '2026-10-02 13:44:21', '2026-10-03 13:55:58'),
(2, 1, 1, 2, '352455', '675559b4-acef-4c28-b6a0-5bc818496900', 'ended', 0, 30, 10, NULL, NULL, '2026-10-03 13:58:41', '2026-10-03 14:04:25'),
(3, 1, 1, 2, '818045', '86615ed5-6179-4920-86a6-0c6776150f54', 'ended', 0, 30, 10, NULL, NULL, '2026-10-03 14:09:10', '2026-10-03 14:16:17'),
(4, 1, 1, 2, '612110', 'c272029e-915b-4c50-befd-b7dc687bd115', 'ended', 0, 30, 10, NULL, NULL, '2026-10-03 14:16:50', '2026-10-03 14:16:58'),
(5, 1, 1, 2, '683212', 'b1371feb-baf8-4bf2-8e7a-4d2183b623bf', 'ended', 0, 30, 10, NULL, NULL, '2026-10-03 14:17:04', '2026-10-03 14:18:28'),
(6, 1, 1, 2, '646865', 'a056c3f0-ad65-45f2-89e0-aa7dc641767f', 'ended', 0, 30, 10, NULL, NULL, '2026-10-03 14:20:42', '2026-10-03 14:24:48'),
(7, 1, 1, 2, '796793', '705420d0-fde1-48f2-9b1b-cd0e261e40c2', 'ended', 0, 30, 10, NULL, NULL, '2026-10-03 14:35:06', '2026-10-03 14:40:45'),
(8, 1, 1, 2, '930398', '8abcfbdc-bacb-4d2f-b731-cced8508ad46', 'ended', 0, 10, 10, NULL, NULL, '2026-10-03 14:45:22', '2026-10-03 14:56:21'),
(9, 1, 3, 2, '455821', 'cb59e757-c33e-485a-a0e4-595dda4b6feb', 'ended', 0, 10, 10, NULL, NULL, '2026-10-03 15:02:21', '2026-10-03 15:10:25'),
(10, 1, 3, 2, '577277', 'f883754c-d3ce-45ab-8065-12c4290fe25d', 'lobby', 0, 10, 10, NULL, NULL, '2026-10-04 07:24:40', '2026-10-04 07:24:40'),
(11, 1, 3, 2, '813236', '155996e1-fcd3-46e0-a33f-59a2d28eaddd', 'lobby', 0, 10, 10, NULL, NULL, '2026-10-04 07:25:13', '2026-10-04 07:25:13'),
(12, 1, 3, 2, '740651', 'bcbc8f29-f810-41a3-9589-eba446afa63f', 'ended', 0, 30, 10, NULL, NULL, '2026-10-04 07:32:05', '2026-10-04 07:39:55'),
(13, 1, 3, 2, '576330', '8c8e5c01-7c2f-4dec-9eb7-53fa4dec5408', 'ended', 0, 30, 10, NULL, NULL, '2026-10-04 07:38:46', '2026-10-04 07:44:09'),
(14, 1, 3, 2, '898390', '188fc737-5ef6-401b-984e-696f8f38055c', 'lobby', 0, 30, 10, NULL, NULL, '2026-10-04 07:48:34', '2026-10-04 07:48:34'),
(15, 1, 3, 2, '736559', '1c80d9a4-f6b1-43a2-b374-8b73b3317a9b', 'lobby', 0, 30, 10, NULL, NULL, '2026-10-04 08:01:11', '2026-10-04 08:01:11'),
(16, 1, 3, 2, '596878', '57f6c67e-728a-47a2-b1ed-d9c21a0bf2ed', 'ended', 0, 30, 10, NULL, NULL, '2026-10-04 08:09:58', '2026-10-04 08:13:02'),
(17, 1, 3, 2, '872483', '6dd6748b-c48f-4681-b673-81350fe6c05a', 'lobby', 0, 30, 10, NULL, NULL, '2026-10-04 08:13:11', '2026-10-04 08:13:11'),
(18, 1, 3, 2, '299931', 'e2daffe6-655a-4aab-99c9-62e3b89ff6c1', 'lobby', 0, 10, 10, NULL, NULL, '2026-10-04 08:15:25', '2026-10-04 08:15:25'),
(19, 1, 3, 2, '955861', '35cbd7cd-3bb2-4fa4-be6a-b18ef9da94cd', 'ended', 0, 30, 10, NULL, NULL, '2026-10-04 08:19:23', '2026-10-04 08:20:14'),
(20, 1, 3, 2, '816331', '0cae92aa-2890-440f-9e24-49405d305502', 'ended', 0, 30, 10, NULL, NULL, '2026-10-04 08:20:44', '2026-10-04 08:21:25'),
(21, 1, 1, 2, '309412', 'a9344f83-12b2-417b-81ca-63833eb5d011', 'ended', 0, 30, 10, NULL, NULL, '2026-10-04 08:21:30', '2026-10-04 08:45:20'),
(22, 1, 3, 2, '357300', 'dc933f9b-23ab-40cd-882d-8713a4368f29', 'lobby', 0, 10, 10, NULL, NULL, '2026-10-04 08:46:36', '2026-10-04 08:46:36'),
(23, 1, 8, 2, '190252', '948e7320-3213-4692-aedc-e85afd615337', 'ended', 0, 30, 10, NULL, NULL, '2026-10-04 08:48:22', '2026-10-04 08:48:59'),
(24, 1, 1, 2, '497165', 'e5b5c32e-2916-4aab-b793-6655762cee35', 'ended', 0, 30, 10, NULL, NULL, '2026-10-04 08:49:05', '2026-10-04 08:49:13'),
(25, 1, 1, 2, '462194', 'e8147503-556d-45e1-9395-41601b7c69ab', 'live', 0, 10, 10, 1, NULL, '2026-10-04 08:49:20', '2026-10-04 08:50:43');

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `id` bigint UNSIGNED NOT NULL,
  `organization_id` bigint UNSIGNED NOT NULL,
  `creator_id` bigint UNSIGNED NOT NULL,
  `disk` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` bigint UNSIGNED NOT NULL,
  `page_count` int UNSIGNED DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `visibility` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'organization',
  `version` int UNSIGNED NOT NULL DEFAULT '1',
  `extracted_text` longtext COLLATE utf8mb4_unicode_ci,
  `failure_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `materials`
--

INSERT INTO `materials` (`id`, `organization_id`, `creator_id`, `disk`, `path`, `original_name`, `mime_type`, `size`, `page_count`, `status`, `visibility`, `version`, `extracted_text`, `failure_reason`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 'local', 'materials/1/X7BJW0xyzHQ9MwX19jgCCA9I4H2hsasLUdLIvFkJ.pdf', 'Materi_05_Pengetahuan_Umum_Artificial_Intelligence.pdf', 'application/pdf', 191435, 11, 'extracted', 'organization', 1, 'Materi_05_Pengetahuan_Umum_Artificial_Intelligence', NULL, '2026-10-03 14:56:52', '2026-10-03 14:56:53');

-- --------------------------------------------------------

--
-- Table structure for table `material_checks`
--

CREATE TABLE `material_checks` (
  `id` bigint UNSIGNED NOT NULL,
  `organization_id` bigint UNSIGNED NOT NULL,
  `material_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `answer` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `confidence` tinyint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `material_notes`
--

CREATE TABLE `material_notes` (
  `id` bigint UNSIGNED NOT NULL,
  `organization_id` bigint UNSIGNED NOT NULL,
  `material_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `material_progresses`
--

CREATE TABLE `material_progresses` (
  `id` bigint UNSIGNED NOT NULL,
  `organization_id` bigint UNSIGNED NOT NULL,
  `material_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `read_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_09_15_170000_create_organizations_table', 1),
(5, '2026_09_15_170100_create_organization_user_table', 1),
(6, '2026_09_15_170200_create_quizzes_table', 1),
(7, '2026_09_15_171000_create_categories_table', 1),
(8, '2026_09_15_171100_create_tags_table', 1),
(9, '2026_09_15_171200_create_questions_table', 1),
(10, '2026_09_15_171300_create_question_tag_table', 1),
(11, '2026_09_15_172000_create_quiz_question_table', 1),
(12, '2026_09_15_173000_create_live_sessions_table', 1),
(13, '2026_09_15_173100_create_live_participants_table', 1),
(14, '2026_09_15_173200_create_live_answers_table', 1),
(15, '2026_09_15_174000_create_quiz_attempts_table', 1),
(16, '2026_09_15_174100_create_attempt_answers_table', 1),
(17, '2026_09_15_175000_add_delivery_rules_to_quizzes_table', 1),
(18, '2026_09_15_175100_add_feedback_to_attempt_answers_table', 1),
(19, '2026_09_15_180000_create_materials_table', 1),
(20, '2026_09_15_181000_create_ai_generations_table', 1),
(21, '2026_09_15_181100_create_ai_question_drafts_table', 1),
(22, '2026_09_15_182000_add_metadata_to_quizzes_table', 1),
(23, '2026_09_15_183000_create_gamification_tables', 1),
(24, '2026_09_15_184000_add_active_to_organization_user_table', 1),
(25, '2026_09_15_184100_create_groups_table', 1),
(26, '2026_09_15_185000_add_lobby_state_to_live_sessions', 1),
(27, '2026_09_15_186000_add_timezone_to_organizations_table', 1),
(28, '2026_09_15_187000_add_broadcast_token_to_live_sessions_table', 1),
(29, '2026_09_16_000000_add_avatar_key_to_users_table', 1),
(30, '2026_09_27_000001_create_group_user_table', 1),
(31, '2026_09_27_000002_create_notifications_table', 1),
(32, '2026_09_27_000003_add_hint_to_questions_table', 1),
(33, '2026_09_27_000004_add_locale_to_users_table', 1),
(34, '2026_09_27_000005_add_theme_key_to_categories_table', 1),
(35, '2026_09_27_000006_create_quiz_collaborator_table', 1),
(36, '2026_09_28_000001_add_visibility_to_materials_table', 1),
(37, '2026_09_28_000002_create_material_progresses_table', 1),
(38, '2026_09_28_000003_create_material_notes_table', 1),
(39, '2026_09_28_000004_create_material_checks_table', 1),
(40, '2026_09_28_000005_add_version_to_materials_table', 1),
(41, '2026_09_29_000001_add_allow_retry_to_quizzes_table', 1),
(42, '2026_09_29_000002_create_missions_tables', 1),
(43, '2026_09_29_000003_enhance_badges_table', 1),
(44, '2026_09_29_000004_link_badges_to_attempts', 1),
(45, '2026_09_29_000005_add_level_snapshot_to_xp_events', 1),
(46, '2026_09_16_010000_add_preferences_to_users_table', 2),
(47, '2026_10_03_204722_add_avatar_key_to_live_participants_table', 2);

-- --------------------------------------------------------

--
-- Table structure for table `missions`
--

CREATE TABLE `missions` (
  `id` bigint UNSIGNED NOT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kind` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `goal` int UNSIGNED NOT NULL,
  `reward_xp` int UNSIGNED NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `missions`
--

INSERT INTO `missions` (`id`, `key`, `kind`, `title`, `description`, `goal`, `reward_xp`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'daily_quiz_1', 'daily', 'Daily Bounty: Ujian Fajar', 'Selesaikan minimal satu kuis hari ini.', 1, 25, 1, '2026-10-01 06:57:45', '2026-10-01 06:57:45'),
(2, 'daily_score_80', 'daily', 'Daily Bounty: Presisi Tempur', 'Selesaikan kuis dengan skor minimal 80 hari ini.', 2, 50, 1, '2026-10-01 06:57:45', '2026-10-01 06:57:45'),
(3, 'daily_quiz_2', 'daily', 'Daily Bounty: Grinding Harian', 'Selesaikan dua sesi kuis dalam satu hari.', 2, 60, 1, '2026-10-01 06:57:45', '2026-10-01 06:57:45'),
(4, 'weekly_quiz_3', 'weekly', 'Weekly Raid: Ritme Petualang', 'Selesaikan tiga kuis minggu ini.', 3, 75, 1, '2026-10-01 06:57:45', '2026-10-01 06:57:45'),
(5, 'weekly_quiz_7', 'weekly', 'Weekly Raid: Marathon Sang Juara', 'Selesaikan tujuh sesi kuis dalam satu pekan.', 7, 200, 1, '2026-10-01 06:57:45', '2026-10-01 06:57:45'),
(6, 'weekly_perfect', 'weekly', 'Weekly Raid: Sentuhan Midas', 'Raih skor 100 sempurna pada kuis minggu ini.', 2, 150, 1, '2026-10-01 06:57:45', '2026-10-01 06:57:45'),
(7, 'learning_quiz_5', 'campaign', 'Quest: Penjelajah Pustaka', 'Selesaikan 5 kuis untuk membuka lencana Trial Challenger.', 5, 150, 1, '2026-10-01 06:57:45', '2026-10-01 06:57:45'),
(8, 'campaign_dungeon_15', 'campaign', 'Raid: Penakluk 15 Medan', 'Taklukkan 15 kuis untuk membuka lencana Epic Dungeon Conqueror.', 15, 350, 1, '2026-10-01 06:57:45', '2026-10-01 06:57:45'),
(9, 'campaign_dungeon_25', 'campaign', 'Grand Raid: 25 Dungeon Takluk', 'Taklukkan 25 kuis untuk membuka lencana Epic Dungeon Grandmaster.', 25, 600, 1, '2026-10-01 06:57:45', '2026-10-01 06:57:45'),
(10, 'campaign_flawless', 'campaign', 'Ascension: Ketepatan Tanpa Celah', 'Raih nilai 100 sempurna pada 2 kuis untuk membuka lencana Legendary Flawless Mastery.', 2, 500, 1, '2026-10-01 06:57:45', '2026-10-01 06:57:45'),
(11, 'campaign_titan_30', 'campaign', 'Apex Quest: Ketekunan Titan 30 Hari', 'Pertahankan streak 30 hari utuh untuk membuka gelar Mythic Eternal Titan.', 30, 2000, 1, '2026-10-01 06:57:45', '2026-10-01 06:57:45');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint UNSIGNED NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES
('0062afca-7ac0-4ec1-8a11-0e13dad199f2', 'App\\Notifications\\AttemptGraded', 'App\\Models\\User', 1, '{\"attempt_id\":2,\"quiz_id\":2,\"quiz_title\":\"Refleksi Lingkungan\",\"score\":0,\"message\":\"Nilai quiz Refleksi Lingkungan sudah tersedia.\"}', '2026-10-03 15:31:06', '2026-10-03 15:20:25', '2026-10-03 15:31:06'),
('91207e40-43a9-4f4d-8282-3798cddd3c58', 'App\\Notifications\\AttemptGraded', 'App\\Models\\User', 1, '{\"attempt_id\":2,\"quiz_id\":2,\"quiz_title\":\"Refleksi Lingkungan\",\"score\":\"1\",\"message\":\"Nilai quiz Refleksi Lingkungan sudah tersedia.\"}', '2026-10-03 15:22:49', '2026-10-03 15:20:40', '2026-10-03 15:22:49');

-- --------------------------------------------------------

--
-- Table structure for table `organizations`
--

CREATE TABLE `organizations` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `timezone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Asia/Jakarta',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `organizations`
--

INSERT INTO `organizations` (`id`, `name`, `slug`, `timezone`, `created_at`, `updated_at`) VALUES
(1, 'Kuesify Demo', 'kuesify-demo', 'Asia/Jakarta', '2026-10-01 06:57:37', '2026-10-01 06:57:37'),
(2, 'Bagas Nurdiansyah\'s Learning Space', 'bagas-nurdiansyah-5', 'Asia/Jakarta', '2026-10-03 13:20:00', '2026-10-03 13:20:00'),
(3, 'Default Org', 'default-org', 'Asia/Jakarta', '2026-10-04 07:52:59', '2026-10-04 07:52:59'),
(4, 'Bagas Nurdiansyah\'s Learning Space', 'bagas-nurdiansyah-7', 'Asia/Jakarta', '2026-10-04 09:19:38', '2026-10-04 09:19:38');

-- --------------------------------------------------------

--
-- Table structure for table `organization_user`
--

CREATE TABLE `organization_user` (
  `organization_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `organization_user`
--

INSERT INTO `organization_user` (`organization_id`, `user_id`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'participant', 1, '2026-10-01 06:57:37', '2026-10-01 06:57:37'),
(1, 2, 'creator', 1, '2026-10-01 06:57:37', '2026-10-01 06:57:37'),
(1, 3, 'organization_admin', 1, '2026-10-01 06:57:38', '2026-10-01 06:57:38'),
(1, 4, 'super_admin', 1, '2026-10-01 06:57:38', '2026-10-01 06:57:38'),
(1, 5, 'participant', 1, '2026-10-03 13:33:58', '2026-10-03 13:33:58'),
(2, 5, 'participant', 1, '2026-10-03 13:20:00', '2026-10-03 13:20:00'),
(4, 7, 'participant', 1, '2026-10-04 09:19:38', '2026-10-04 09:19:38');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` bigint UNSIGNED NOT NULL,
  `organization_id` bigint UNSIGNED NOT NULL,
  `creator_id` bigint UNSIGNED NOT NULL,
  `category_id` bigint UNSIGNED DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prompt` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` json DEFAULT NULL,
  `correct_answer` text COLLATE utf8mb4_unicode_ci,
  `explanation` text COLLATE utf8mb4_unicode_ci,
  `hint` text COLLATE utf8mb4_unicode_ci,
  `points` int UNSIGNED NOT NULL DEFAULT '1000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `organization_id`, `creator_id`, `category_id`, `type`, `prompt`, `options`, `correct_answer`, `explanation`, `hint`, `points`, `created_at`, `updated_at`) VALUES
(1, 1, 2, NULL, 'multiple_choice', 'Makhluk hidup yang membuat makanan sendiri disebut?', '[\"Produsen\", \"Konsumen\", \"Pengurai\"]', 'Produsen', NULL, NULL, 100, '2026-10-01 06:57:38', '2026-10-01 06:57:38'),
(2, 1, 2, NULL, 'true_false', 'Air menguap karena panas matahari.', '[\"true\", \"false\"]', 'true', NULL, NULL, 100, '2026-10-01 06:57:38', '2026-10-01 06:57:38'),
(3, 1, 2, NULL, 'essay', 'Jelaskan satu cara menghemat air di rumah.', NULL, NULL, NULL, NULL, 100, '2026-10-01 06:57:38', '2026-10-01 06:57:38'),
(4, 1, 2, NULL, 'multiple_choice', 'Apa kepanjangan utama dari AI dalam konteks ilmu komputer?', '[\"Automated Integration\", \"Artificial Intelligence\", \"Advanced Interface\", \"Algorithmic Information\"]', 'Artificial Intelligence', 'AI secara universal merupakan singkatan dari Artificial Intelligence.', NULL, 1000, '2026-10-03 14:57:37', '2026-10-03 14:57:37'),
(5, 1, 2, NULL, 'true_false', 'Artificial Intelligence adalah teknologi yang hanya dapat diterapkan pada bidang industri manufaktur.', NULL, 'False', 'AI dapat diterapkan di berbagai bidang luas seperti kesehatan, keuangan, pendidikan, dan lain-lain.', NULL, 1000, '2026-10-03 14:57:39', '2026-10-03 14:57:39'),
(6, 1, 2, NULL, 'multiple_choice', 'Manakah di bawah ini yang merupakan cabang utama dari Artificial Intelligence?', '[\"Machine Learning\", \"Manual Processing\", \"Static Programming\", \"Basic Spreadsheet\"]', 'Machine Learning', 'Machine Learning adalah salah satu sub-bidang atau cabang utama dalam pengembangan AI.', NULL, 1000, '2026-10-03 14:57:40', '2026-10-03 14:57:40'),
(7, 1, 2, NULL, 'true_false', 'Machine Learning memungkinkan sistem komputer untuk belajar dari data tanpa harus secara eksplisit diprogram.', NULL, 'True', 'Definisi inti dari Machine Learning adalah kemampuan belajar otomatis berdasarkan data.', NULL, 1000, '2026-10-03 14:57:42', '2026-10-03 14:57:42'),
(8, 1, 2, NULL, 'multiple_choice', 'Apa fungsi utama dari Natural Language Processing (NLP) dalam AI?', '[\"Memproses gambar dan video\", \"Memahami dan memproses bahasa manusia\", \"Mengatur sirkuit listrik perangkat keras\", \"Menghitung rumus fisika kuantum\"]', 'Memahami dan memproses bahasa manusia', 'NLP berfokus pada interaksi antara komputer dan bahasa manusia.', NULL, 1000, '2026-10-03 14:57:43', '2026-10-03 14:57:43'),
(9, 1, 2, NULL, 'true_false', 'Deep Learning adalah sub-bidang dari Machine Learning yang menggunakan jaringan saraf tiruan (neural networks) dengan banyak lapisan.', NULL, 'True', 'Deep Learning memang menggunakan arsitektur deep neural networks.', NULL, 1000, '2026-10-03 14:57:45', '2026-10-03 14:57:45'),
(10, 1, 2, NULL, 'multiple_choice', 'Manakah teknologi AI yang paling sering digunakan untuk pengenalan wajah (facial recognition)?', '[\"Computer Vision\", \"Natural Language Processing\", \"Robotic Process Automation\", \"Database Management\"]', 'Computer Vision', 'Computer Vision adalah bidang AI yang memungkinkan komputer memproses dan memahami informasi visual.', NULL, 1000, '2026-10-03 14:57:46', '2026-10-03 14:57:46'),
(11, 1, 2, NULL, 'true_false', 'Artificial Intelligence sama sekali tidak membutuhkan data untuk dapat berfungsi secara optimal.', NULL, 'False', 'Sebagian besar sistem AI, terutama Machine Learning, sangat bergantung pada ketersediaan data pelatihan yang berkualitas.', NULL, 1000, '2026-10-03 14:57:48', '2026-10-03 14:57:48'),
(12, 1, 2, NULL, 'true_false', 'Algoritma pencarian (search algorithms) adalah salah satu metode klasik yang digunakan dalam sejarah perkembangan AI.', NULL, 'True', 'Pencarian heuristik dan algoritma pencarian adalah fondasi awal dalam pemecahan masalah AI klasik.', NULL, 1000, '2026-10-03 14:57:51', '2026-10-03 14:57:51'),
(13, 1, 2, NULL, 'multiple_choice', 'Apa yang dimaksud dengan Artificial General Intelligence (AGI)?', '[\"AI yang hanya bisa bermain catur\", \"AI yang memiliki tingkat kecerdasan setara atau melebihi manusia di berbagai bidang\", \"AI yang khusus untuk administrasi perkantoran\", \"Sistem komputer tanpa algoritma\"]', 'AI yang memiliki tingkat kecerdasan setara atau melebihi manusia di berbagai bidang', 'AGI merujuk pada konsep kecerdasan buatan umum yang dapat menandingi fleksibilitas kognitif manusia.', NULL, 1000, '2026-10-03 14:57:52', '2026-10-03 14:57:52'),
(14, 1, 2, NULL, 'multiple_choice', 'Manakah contoh penerapan AI dalam kehidupan sehari-hari?', '[\"Sistem rekomendasi pada layanan streaming\", \"Kalkulator saku standar\", \"Buku catatan fisik\", \"Kabel LAN jaringan\"]', 'Sistem rekomendasi pada layanan streaming', 'Layanan streaming menggunakan algoritma AI untuk merekomendasikan konten kepada pengguna.', NULL, 1000, '2026-10-03 14:57:54', '2026-10-03 14:57:54'),
(15, 1, 2, NULL, 'true_false', 'Etika dalam penggunaan AI (AI ethics) tidak perlu dibahas karena AI tidak memiliki dampak sosial.', NULL, 'False', 'Etika AI sangat penting untuk mencegah bias, pelanggaran privasi, dan dampak negatif lainnya.', NULL, 1000, '2026-10-03 14:57:55', '2026-10-03 14:57:55'),
(16, 1, 2, NULL, 'multiple_choice', 'Apa yang menjadi fokus utama dari bidang Reinforcement Learning?', '[\"Belajar melalui sistem penghargaan (reward) dan hukuman (penalty) dari interaksi lingkungan\", \"Menerjemahkan teks bahasa asing ke bahasa lokal\", \"Mengompres ukuran file gambar\", \"Membuat desain tata letak halaman web\"]', 'Belajar melalui sistem penghargaan (reward) dan hukuman (penalty) dari interaksi lingkungan', 'Reinforcement learning melatih agen untuk membuat keputusan melalui trial and error berbasis reward.', NULL, 1000, '2026-10-03 14:58:00', '2026-10-03 14:58:00'),
(17, 1, 2, NULL, 'true_false', 'Turing Test adalah salah satu metode historis untuk menguji kemampuan mesin menunjukkan perilaku cerdas yang tidak dapat dibedakan dari manusia.', NULL, 'True', 'Alan Turing mengusulkan tes ini untuk mengukur kecerdasan mesin.', NULL, 1000, '2026-10-03 14:58:01', '2026-10-03 14:58:01'),
(18, 1, 2, NULL, 'multiple_choice', 'Apa tantangan utama dalam pengembangan model Machine Learning?', '[\"Overfitting dan kualitas data yang buruk\", \"Ketersediaan listrik PLN yang stabil\", \"Warna perangkat keras komputer\", \"Jumlah tombol pada keyboard\"]', 'Overfitting dan kualitas data yang buruk', 'Overfitting (terlalu pas pada data latih) dan data yang buruk adalah masalah krusial dalam ML.', NULL, 1000, '2026-10-03 14:58:03', '2026-10-03 14:58:03'),
(19, 1, 2, NULL, 'true_false', 'Chatbot modern sebagian besar sudah memanfaatkan teknologi Large Language Models (LLMs).', NULL, 'True', 'Sebagian besar chatbot canggih saat ini ditenagai oleh model bahasa besar (LLMs).', NULL, 1000, '2026-10-03 14:58:04', '2026-10-03 14:58:04'),
(20, 1, 2, NULL, 'multiple_choice', 'Manakah komponen perangkat keras yang paling krusial untuk mempercepat pelatihan model Deep Learning berskala besar?', '[\"GPU (Graphics Processing Unit)\", \"Power Supply Unit\", \"Optical Drive\", \"Sound Card\"]', 'GPU (Graphics Processing Unit)', 'GPU sangat efisien dalam melakukan komputasi paralel yang dibutuhkan oleh Deep Learning.', NULL, 1000, '2026-10-03 14:58:06', '2026-10-03 14:58:06'),
(21, 1, 2, NULL, 'true_false', 'Artificial Narrow Intelligence (ANI) adalah jenis AI yang dirancang untuk menyelesaikan satu tugas spesifik saja.', NULL, 'True', 'Sebagian besar AI yang ada saat ini dikategorikan sebagai ANI (Narrow AI).', NULL, 1000, '2026-10-03 14:58:07', '2026-10-03 14:58:07'),
(22, 1, 2, NULL, 'multiple_choice', 'Apa dampak negatif potensial dari penggunaan AI yang tidak terkontrol?', '[\"Penyebaran informasi palsu (deepfake) dan bias algoritmik\", \"Peningkatan kecepatan internet secara global\", \"Penurunan suhu perangkat komputer secara drastis\", \"Pengurangan kapasitas penyimpanan harddisk\"]', 'Penyebaran informasi palsu (deepfake) dan bias algoritmik', 'Bias dan deepfake merupakan risiko nyata dari penyalahgunaan teknologi AI.', NULL, 1000, '2026-10-03 14:58:09', '2026-10-03 14:58:09'),
(23, 3, 6, NULL, 'multiple_choice', 'Test Question?', '[\"A\", \"B\"]', 'A', NULL, NULL, 1000, '2026-10-04 07:54:17', '2026-10-04 07:54:17'),
(24, 3, 6, NULL, 'multiple_choice', 'Test Question?', '[\"A\", \"B\"]', 'A', NULL, NULL, 1000, '2026-10-04 07:55:09', '2026-10-04 07:55:09');

-- --------------------------------------------------------

--
-- Table structure for table `question_tag`
--

CREATE TABLE `question_tag` (
  `question_id` bigint UNSIGNED NOT NULL,
  `tag_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `id` bigint UNSIGNED NOT NULL,
  `organization_id` bigint UNSIGNED NOT NULL,
  `creator_id` bigint UNSIGNED NOT NULL,
  `category_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `visibility` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'private',
  `cover_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deadline_at` timestamp NULL DEFAULT NULL,
  `max_attempts` int UNSIGNED DEFAULT NULL,
  `allow_retry` tinyint(1) NOT NULL DEFAULT '0',
  `show_explanations` tinyint(1) NOT NULL DEFAULT '0',
  `settings` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quizzes`
--

INSERT INTO `quizzes` (`id`, `organization_id`, `creator_id`, `category_id`, `title`, `description`, `status`, `visibility`, `cover_image`, `deadline_at`, `max_attempts`, `allow_retry`, `show_explanations`, `settings`, `created_at`, `updated_at`) VALUES
(1, 1, 2, NULL, 'Kuis Pemanasan', 'Demo soal objektif untuk mode mandiri dan live.', 'published', 'organization', NULL, NULL, NULL, 0, 0, NULL, '2026-10-01 06:57:38', '2026-10-01 06:57:38'),
(2, 1, 2, NULL, 'Refleksi Lingkungan', 'Demo essay dengan penilaian manual.', 'published', 'organization', NULL, NULL, NULL, 0, 0, NULL, '2026-10-01 06:57:38', '2026-10-01 06:57:38'),
(3, 1, 2, NULL, 'Kuis ai', 'Deskripsi', 'published', 'public', NULL, '2026-10-09 22:01:00', 1, 1, 1, NULL, '2026-10-03 14:59:24', '2026-10-04 08:14:37'),
(4, 3, 6, NULL, 'Test Quiz', NULL, 'published', 'private', NULL, NULL, NULL, 0, 0, NULL, '2026-10-04 07:52:59', '2026-10-04 07:52:59'),
(5, 3, 6, NULL, 'Test Quiz', NULL, 'published', 'private', NULL, NULL, NULL, 0, 0, NULL, '2026-10-04 07:53:36', '2026-10-04 07:53:36'),
(6, 3, 6, NULL, 'Test Quiz', NULL, 'published', 'private', NULL, NULL, NULL, 0, 0, NULL, '2026-10-04 07:54:17', '2026-10-04 07:54:17'),
(7, 3, 6, NULL, 'Test Quiz', NULL, 'published', 'private', NULL, NULL, NULL, 0, 0, NULL, '2026-10-04 07:55:09', '2026-10-04 07:55:09'),
(8, 1, 2, NULL, 'Kuis ai 2', 'asdsdwqdqwqwdwq', 'published', 'public', NULL, '2026-10-07 15:28:00', 1, 0, 1, NULL, '2026-10-04 08:28:29', '2026-10-04 09:03:01');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_attempts`
--

CREATE TABLE `quiz_attempts` (
  `id` bigint UNSIGNED NOT NULL,
  `organization_id` bigint UNSIGNED NOT NULL,
  `quiz_id` bigint UNSIGNED NOT NULL,
  `participant_id` bigint UNSIGNED NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `score` int UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quiz_attempts`
--

INSERT INTO `quiz_attempts` (`id`, `organization_id`, `quiz_id`, `participant_id`, `status`, `score`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 'completed', 100, '2026-10-01 09:11:09', '2026-10-01 09:11:20'),
(2, 1, 2, 1, 'pending_review', 1, '2026-10-01 09:11:36', '2026-10-03 15:20:40'),
(3, 1, 3, 1, 'completed', 1000, '2026-10-03 15:23:06', '2026-10-03 15:23:14'),
(4, 1, 8, 1, 'completed', 0, '2026-10-04 08:29:55', '2026-10-04 08:30:50');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_collaborator`
--

CREATE TABLE `quiz_collaborator` (
  `quiz_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_question`
--

CREATE TABLE `quiz_question` (
  `quiz_id` bigint UNSIGNED NOT NULL,
  `question_id` bigint UNSIGNED NOT NULL,
  `position` int UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quiz_question`
--

INSERT INTO `quiz_question` (`quiz_id`, `question_id`, `position`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, NULL),
(1, 2, 2, NULL, NULL),
(2, 3, 1, NULL, NULL),
(3, 4, 18, NULL, NULL),
(3, 5, 17, NULL, NULL),
(3, 6, 15, NULL, NULL),
(3, 7, 16, NULL, NULL),
(3, 8, 14, NULL, NULL),
(3, 9, 13, NULL, NULL),
(3, 10, 12, NULL, NULL),
(3, 11, 11, NULL, NULL),
(3, 12, 10, NULL, NULL),
(3, 14, 9, NULL, NULL),
(3, 15, 8, NULL, NULL),
(3, 16, 7, NULL, NULL),
(3, 17, 6, NULL, NULL),
(3, 18, 5, NULL, NULL),
(3, 19, 4, NULL, NULL),
(3, 20, 3, NULL, NULL),
(3, 21, 2, NULL, NULL),
(3, 22, 1, NULL, NULL),
(4, 23, 1, NULL, NULL),
(6, 23, 1, NULL, NULL),
(7, 24, 1, NULL, NULL),
(8, 19, 1, NULL, NULL),
(8, 20, 2, NULL, NULL),
(8, 21, 3, NULL, NULL),
(8, 22, 4, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('qbBsPcPWziyfIKdYpQ3VSJMNPkznKg5009dZSjUr', 4, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJYcXBXSFZSTjdFc0Q0cHpWemVYWnZld3dnMnkzRnRHUHV1eUs4OVkwIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6NCwib3JnYW5pemF0aW9uX2lkIjoxfQ==', 1791131367),
('RxqNIv7VyBUSMXyK9ILqdoVsMfCcxi1M1yNwymsO', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJiY1ZaNDE4UGxFY1J5Mkc3eWZWRGQyWjJYSkYweWdrZEVsWW8yVEpHIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJsaXZlX3BhcnRpY2lwYW50Ijp7InNlc3Npb25faWQiOjI1LCJwYXJ0aWNpcGFudF9pZCI6MTIsInRva2VuIjoiNmYwYmQ4NzItYTc0OC00MTJhLWIzY2QtNDljY2RiODI4NDdjIn19', 1791129021);

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` bigint UNSIGNED NOT NULL,
  `organization_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar_key` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preferences` json DEFAULT NULL,
  `locale` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'id',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `avatar_key`, `preferences`, `locale`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Demo Participant', 'participant@kuesify.test', 'book', NULL, 'id', '2026-10-01 06:57:37', '$2y$12$CE20LvKYPUwLBDG7tESKQeihVmd5SiIdOXqQH8BHYIopFOLv620mu', 'Eg8TDHZeWWfx5tknm4KycEMEIqXCky7hYhxpngOQPPfZtJJ0GDIgARgi3CID', '2026-10-01 06:57:37', '2026-10-01 06:57:37'),
(2, 'Demo Creator', 'creator@kuesify.test', 'profile_3', NULL, 'id', '2026-10-01 06:57:37', '$2y$12$jMCvVfDMYTKS5T2iyB36t.1EuDFA12EXw6DxU5JsVB0L/XccXxk.e', 'cKcHbM16DN4BqIbmpJHhPTjylg9akrMrSM7asO61EQo69q4g0LmUMOPwrFaZ', '2026-10-01 06:57:37', '2026-10-03 13:40:03'),
(3, 'Demo Organization Admin', 'admin@kuesify.test', 'profile_8', NULL, 'id', '2026-10-01 06:57:38', '$2y$12$t2/qTfDG7jKTnRpRCNhtnenG/sBp1mj0DCwHG7T8kZJC8KyRxzGqO', 'OS7qC7Te8k7NlCmMzm3918GDnfBihfuzt5oxKZEjrLJmgdlz213BFR3VMWxP', '2026-10-01 06:57:38', '2026-10-03 13:32:55'),
(4, 'Demo Super Admin', 'superadmin@kuesify.test', 'profile_2', NULL, 'id', '2026-10-01 06:57:38', '$2y$12$vv3rJWJr5as0vYGMGDeB/eyiITigWxLwpm9trP/0A/uFsXSDVViRy', 'hBcgH6pHbBxcG10LeksHDvNo2vx2ERv4pmEWzErWmNlcYb32HQ1FT2ySFmuQ', '2026-10-01 06:57:38', '2026-10-03 13:34:44'),
(5, 'Bagas Nurdiansyah', 'bagasb65nurdiansyah777@gmail.com', 'profile_11', NULL, 'id', '2026-10-03 13:21:14', '$2y$12$983P9KAAHsFDjiJj.fyyj.VX0JplQ/l2GGKW0eBPREHJXI2AmWvkW', 'yxsZTfV16MxreyI2nSD4blZtieqUpGaqgFhOf46WbcLQE0fzMlFiptJtyuD0', '2026-10-03 13:20:00', '2026-10-03 13:21:55'),
(6, 'Test User', 'test@kuesify.com', NULL, NULL, 'id', NULL, '$2y$12$ujgqSQ3wDV7yNeuyaRpTJuEeFDgkrcGYIzCZ3RJ3VS0xZaNIValuu', NULL, '2026-10-04 07:52:24', '2026-10-04 07:52:24'),
(7, 'Bagas Nurdiansyah', 'owner@raciwon.com', NULL, NULL, 'id', NULL, '$2y$12$Id9VXHks1oDgNH95gMgmnuLwfRiGiIpcCigJK9t0au2rNEaPdDvJu', NULL, '2026-10-04 09:19:38', '2026-10-04 09:19:38');

-- --------------------------------------------------------

--
-- Table structure for table `user_missions`
--

CREATE TABLE `user_missions` (
  `id` bigint UNSIGNED NOT NULL,
  `organization_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `mission_id` bigint UNSIGNED NOT NULL,
  `period_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `progress` int UNSIGNED NOT NULL DEFAULT '0',
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_missions`
--

INSERT INTO `user_missions` (`id`, `organization_id`, `user_id`, `mission_id`, `period_key`, `progress`, `completed_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, '2026-10-01', 1, '2026-10-01 09:11:20', '2026-10-01 09:11:20', '2026-10-01 09:11:20'),
(2, 1, 1, 2, '2026-10-01', 1, NULL, '2026-10-01 09:11:20', '2026-10-01 09:11:20'),
(3, 1, 1, 3, '2026-10-01', 1, NULL, '2026-10-01 09:11:20', '2026-10-01 09:11:20'),
(4, 1, 1, 4, '2026-09-28', 3, '2026-10-04 08:30:50', '2026-10-01 09:11:20', '2026-10-04 08:30:50'),
(5, 1, 1, 5, '2026-09-28', 3, NULL, '2026-10-01 09:11:20', '2026-10-04 08:30:50'),
(6, 1, 1, 6, '2026-09-28', 0, NULL, '2026-10-01 09:11:20', '2026-10-01 09:11:20'),
(7, 1, 1, 7, 'lifetime', 3, NULL, '2026-10-01 09:11:20', '2026-10-04 08:30:50'),
(8, 1, 1, 8, 'lifetime', 3, NULL, '2026-10-01 09:11:20', '2026-10-04 08:30:50'),
(9, 1, 1, 9, 'lifetime', 3, NULL, '2026-10-01 09:11:20', '2026-10-04 08:30:50'),
(10, 1, 1, 10, 'lifetime', 0, NULL, '2026-10-01 09:11:20', '2026-10-01 09:11:20'),
(11, 1, 1, 11, 'lifetime', 1, NULL, '2026-10-01 09:11:20', '2026-10-01 09:11:20'),
(12, 1, 1, 1, '2026-10-04', 1, '2026-10-03 15:23:14', '2026-10-03 15:23:14', '2026-10-03 15:23:14'),
(13, 1, 1, 2, '2026-10-04', 1, NULL, '2026-10-03 15:23:14', '2026-10-03 15:23:14'),
(14, 1, 1, 3, '2026-10-04', 2, '2026-10-04 08:30:50', '2026-10-03 15:23:14', '2026-10-04 08:30:50');

-- --------------------------------------------------------

--
-- Table structure for table `user_progresses`
--

CREATE TABLE `user_progresses` (
  `id` bigint UNSIGNED NOT NULL,
  `organization_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `xp` int UNSIGNED NOT NULL DEFAULT '0',
  `level` int UNSIGNED NOT NULL DEFAULT '1',
  `streak` int UNSIGNED NOT NULL DEFAULT '0',
  `last_activity_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_progresses`
--

INSERT INTO `user_progresses` (`id`, `organization_id`, `user_id`, `xp`, `level`, `streak`, `last_activity_date`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1285, 2, 1, '2026-10-04', '2026-10-01 09:11:20', '2026-10-04 08:30:50');

-- --------------------------------------------------------

--
-- Table structure for table `xp_events`
--

CREATE TABLE `xp_events` (
  `id` bigint UNSIGNED NOT NULL,
  `organization_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `quiz_attempt_id` bigint UNSIGNED NOT NULL,
  `amount` int UNSIGNED NOT NULL,
  `level_before` int UNSIGNED DEFAULT NULL,
  `level_after` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `xp_events`
--

INSERT INTO `xp_events` (`id`, `organization_id`, `user_id`, `quiz_attempt_id`, `amount`, `level_before`, `level_after`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 100, 1, 1, '2026-10-01 09:11:20', '2026-10-01 09:11:20'),
(2, 1, 1, 3, 1000, 1, 2, '2026-10-03 15:23:14', '2026-10-03 15:23:14'),
(3, 1, 1, 4, 0, 2, 2, '2026-10-04 08:30:50', '2026-10-04 08:30:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ai_generations`
--
ALTER TABLE `ai_generations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ai_generations_organization_id_foreign` (`organization_id`),
  ADD KEY `ai_generations_material_id_foreign` (`material_id`),
  ADD KEY `ai_generations_creator_id_foreign` (`creator_id`);

--
-- Indexes for table `ai_question_drafts`
--
ALTER TABLE `ai_question_drafts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ai_question_drafts_ai_generation_id_foreign` (`ai_generation_id`);

--
-- Indexes for table `attempt_answers`
--
ALTER TABLE `attempt_answers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `attempt_answers_quiz_attempt_id_question_id_unique` (`quiz_attempt_id`,`question_id`),
  ADD KEY `attempt_answers_question_id_foreign` (`question_id`);

--
-- Indexes for table `badges`
--
ALTER TABLE `badges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `badges_key_unique` (`key`);

--
-- Indexes for table `badge_awards`
--
ALTER TABLE `badge_awards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `badge_awards_organization_id_user_id_badge_id_unique` (`organization_id`,`user_id`,`badge_id`),
  ADD KEY `badge_awards_user_id_foreign` (`user_id`),
  ADD KEY `badge_awards_badge_id_foreign` (`badge_id`),
  ADD KEY `badge_awards_quiz_attempt_id_foreign` (`quiz_attempt_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_slug_unique` (`slug`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `groups_organization_id_name_unique` (`organization_id`,`name`);

--
-- Indexes for table `group_user`
--
ALTER TABLE `group_user`
  ADD PRIMARY KEY (`group_id`,`user_id`),
  ADD KEY `group_user_user_id_foreign` (`user_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `live_answers`
--
ALTER TABLE `live_answers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `live_answers_live_participant_id_question_id_unique` (`live_participant_id`,`question_id`),
  ADD KEY `live_answers_question_id_foreign` (`question_id`);

--
-- Indexes for table `live_participants`
--
ALTER TABLE `live_participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `live_participants_reconnect_token_unique` (`reconnect_token`),
  ADD KEY `live_participants_live_session_id_foreign` (`live_session_id`);

--
-- Indexes for table `live_sessions`
--
ALTER TABLE `live_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `live_sessions_pin_unique` (`pin`),
  ADD UNIQUE KEY `live_sessions_broadcast_token_unique` (`broadcast_token`),
  ADD KEY `live_sessions_organization_id_foreign` (`organization_id`),
  ADD KEY `live_sessions_quiz_id_foreign` (`quiz_id`),
  ADD KEY `live_sessions_host_id_foreign` (`host_id`),
  ADD KEY `live_sessions_current_question_id_foreign` (`current_question_id`);

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `materials_organization_id_foreign` (`organization_id`),
  ADD KEY `materials_creator_id_foreign` (`creator_id`);

--
-- Indexes for table `material_checks`
--
ALTER TABLE `material_checks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `material_checks_material_id_user_id_unique` (`material_id`,`user_id`),
  ADD KEY `material_checks_user_id_foreign` (`user_id`),
  ADD KEY `material_checks_organization_id_user_id_index` (`organization_id`,`user_id`);

--
-- Indexes for table `material_notes`
--
ALTER TABLE `material_notes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `material_notes_material_id_user_id_unique` (`material_id`,`user_id`),
  ADD KEY `material_notes_user_id_foreign` (`user_id`),
  ADD KEY `material_notes_organization_id_user_id_index` (`organization_id`,`user_id`);

--
-- Indexes for table `material_progresses`
--
ALTER TABLE `material_progresses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `material_progresses_material_id_user_id_unique` (`material_id`,`user_id`),
  ADD KEY `material_progresses_user_id_foreign` (`user_id`),
  ADD KEY `material_progresses_organization_id_user_id_index` (`organization_id`,`user_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `missions`
--
ALTER TABLE `missions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `missions_key_unique` (`key`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indexes for table `organizations`
--
ALTER TABLE `organizations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `organizations_slug_unique` (`slug`);

--
-- Indexes for table `organization_user`
--
ALTER TABLE `organization_user`
  ADD PRIMARY KEY (`organization_id`,`user_id`),
  ADD KEY `organization_user_user_id_foreign` (`user_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `questions_organization_id_foreign` (`organization_id`),
  ADD KEY `questions_creator_id_foreign` (`creator_id`),
  ADD KEY `questions_category_id_foreign` (`category_id`);

--
-- Indexes for table `question_tag`
--
ALTER TABLE `question_tag`
  ADD PRIMARY KEY (`question_id`,`tag_id`),
  ADD KEY `question_tag_tag_id_foreign` (`tag_id`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quizzes_organization_id_foreign` (`organization_id`),
  ADD KEY `quizzes_creator_id_foreign` (`creator_id`),
  ADD KEY `quizzes_category_id_foreign` (`category_id`);

--
-- Indexes for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_attempts_organization_id_foreign` (`organization_id`),
  ADD KEY `quiz_attempts_quiz_id_foreign` (`quiz_id`),
  ADD KEY `quiz_attempts_participant_id_foreign` (`participant_id`);

--
-- Indexes for table `quiz_collaborator`
--
ALTER TABLE `quiz_collaborator`
  ADD PRIMARY KEY (`quiz_id`,`user_id`),
  ADD KEY `quiz_collaborator_user_id_foreign` (`user_id`);

--
-- Indexes for table `quiz_question`
--
ALTER TABLE `quiz_question`
  ADD PRIMARY KEY (`quiz_id`,`question_id`),
  ADD UNIQUE KEY `quiz_question_quiz_id_position_unique` (`quiz_id`,`position`),
  ADD KEY `quiz_question_question_id_foreign` (`question_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tags_organization_id_name_unique` (`organization_id`,`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `user_missions`
--
ALTER TABLE `user_missions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_missions_scope_unique` (`organization_id`,`user_id`,`mission_id`,`period_key`),
  ADD KEY `user_missions_user_id_foreign` (`user_id`),
  ADD KEY `user_missions_mission_id_foreign` (`mission_id`);

--
-- Indexes for table `user_progresses`
--
ALTER TABLE `user_progresses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_progresses_organization_id_user_id_unique` (`organization_id`,`user_id`),
  ADD KEY `user_progresses_user_id_foreign` (`user_id`);

--
-- Indexes for table `xp_events`
--
ALTER TABLE `xp_events`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `xp_events_quiz_attempt_id_unique` (`quiz_attempt_id`),
  ADD KEY `xp_events_organization_id_foreign` (`organization_id`),
  ADD KEY `xp_events_user_id_foreign` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ai_generations`
--
ALTER TABLE `ai_generations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ai_question_drafts`
--
ALTER TABLE `ai_question_drafts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `attempt_answers`
--
ALTER TABLE `attempt_answers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `badges`
--
ALTER TABLE `badges`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `badge_awards`
--
ALTER TABLE `badge_awards`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `live_answers`
--
ALTER TABLE `live_answers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `live_participants`
--
ALTER TABLE `live_participants`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `live_sessions`
--
ALTER TABLE `live_sessions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `material_checks`
--
ALTER TABLE `material_checks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `material_notes`
--
ALTER TABLE `material_notes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `material_progresses`
--
ALTER TABLE `material_progresses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `missions`
--
ALTER TABLE `missions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `organizations`
--
ALTER TABLE `organizations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user_missions`
--
ALTER TABLE `user_missions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `user_progresses`
--
ALTER TABLE `user_progresses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `xp_events`
--
ALTER TABLE `xp_events`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ai_generations`
--
ALTER TABLE `ai_generations`
  ADD CONSTRAINT `ai_generations_creator_id_foreign` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ai_generations_material_id_foreign` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ai_generations_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ai_question_drafts`
--
ALTER TABLE `ai_question_drafts`
  ADD CONSTRAINT `ai_question_drafts_ai_generation_id_foreign` FOREIGN KEY (`ai_generation_id`) REFERENCES `ai_generations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `attempt_answers`
--
ALTER TABLE `attempt_answers`
  ADD CONSTRAINT `attempt_answers_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attempt_answers_quiz_attempt_id_foreign` FOREIGN KEY (`quiz_attempt_id`) REFERENCES `quiz_attempts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `badge_awards`
--
ALTER TABLE `badge_awards`
  ADD CONSTRAINT `badge_awards_badge_id_foreign` FOREIGN KEY (`badge_id`) REFERENCES `badges` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `badge_awards_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `badge_awards_quiz_attempt_id_foreign` FOREIGN KEY (`quiz_attempt_id`) REFERENCES `quiz_attempts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `badge_awards_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `groups`
--
ALTER TABLE `groups`
  ADD CONSTRAINT `groups_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `group_user`
--
ALTER TABLE `group_user`
  ADD CONSTRAINT `group_user_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `group_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `live_answers`
--
ALTER TABLE `live_answers`
  ADD CONSTRAINT `live_answers_live_participant_id_foreign` FOREIGN KEY (`live_participant_id`) REFERENCES `live_participants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `live_answers_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `live_participants`
--
ALTER TABLE `live_participants`
  ADD CONSTRAINT `live_participants_live_session_id_foreign` FOREIGN KEY (`live_session_id`) REFERENCES `live_sessions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `live_sessions`
--
ALTER TABLE `live_sessions`
  ADD CONSTRAINT `live_sessions_current_question_id_foreign` FOREIGN KEY (`current_question_id`) REFERENCES `questions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `live_sessions_host_id_foreign` FOREIGN KEY (`host_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `live_sessions_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `live_sessions_quiz_id_foreign` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `materials`
--
ALTER TABLE `materials`
  ADD CONSTRAINT `materials_creator_id_foreign` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `materials_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `material_checks`
--
ALTER TABLE `material_checks`
  ADD CONSTRAINT `material_checks_material_id_foreign` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `material_checks_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `material_checks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `material_notes`
--
ALTER TABLE `material_notes`
  ADD CONSTRAINT `material_notes_material_id_foreign` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `material_notes_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `material_notes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `material_progresses`
--
ALTER TABLE `material_progresses`
  ADD CONSTRAINT `material_progresses_material_id_foreign` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `material_progresses_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `material_progresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `organization_user`
--
ALTER TABLE `organization_user`
  ADD CONSTRAINT `organization_user_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `organization_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `questions_creator_id_foreign` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `questions_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `question_tag`
--
ALTER TABLE `question_tag`
  ADD CONSTRAINT `question_tag_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `question_tag_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD CONSTRAINT `quizzes_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `quizzes_creator_id_foreign` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quizzes_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD CONSTRAINT `quiz_attempts_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_attempts_participant_id_foreign` FOREIGN KEY (`participant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_attempts_quiz_id_foreign` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_collaborator`
--
ALTER TABLE `quiz_collaborator`
  ADD CONSTRAINT `quiz_collaborator_quiz_id_foreign` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_collaborator_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_question`
--
ALTER TABLE `quiz_question`
  ADD CONSTRAINT `quiz_question_question_id_foreign` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_question_quiz_id_foreign` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tags`
--
ALTER TABLE `tags`
  ADD CONSTRAINT `tags_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_missions`
--
ALTER TABLE `user_missions`
  ADD CONSTRAINT `user_missions_mission_id_foreign` FOREIGN KEY (`mission_id`) REFERENCES `missions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_missions_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_missions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_progresses`
--
ALTER TABLE `user_progresses`
  ADD CONSTRAINT `user_progresses_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_progresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `xp_events`
--
ALTER TABLE `xp_events`
  ADD CONSTRAINT `xp_events_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `xp_events_quiz_attempt_id_foreign` FOREIGN KEY (`quiz_attempt_id`) REFERENCES `quiz_attempts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `xp_events_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
