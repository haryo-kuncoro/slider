/*
 Navicat Premium Data Transfer

 Source Server         : LOCAL
 Source Server Type    : MySQL
 Source Server Version : 80030 (8.0.30)
 Source Host           : 127.0.0.1:3306
 Source Schema         : db_slider

 Target Server Type    : MySQL
 Target Server Version : 80030 (8.0.30)
 File Encoding         : 65001

 Date: 03/09/2026 17:50:22
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for tbl_prodi_urutan
-- ----------------------------
DROP TABLE IF EXISTS `tbl_prodi_urutan`;
CREATE TABLE `tbl_prodi_urutan`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_prodi` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `urutan` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 13 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of tbl_prodi_urutan
-- ----------------------------
INSERT INTO `tbl_prodi_urutan` VALUES (1, 'Hukum', 1);
INSERT INTO `tbl_prodi_urutan` VALUES (2, 'Manajemen', 2);
INSERT INTO `tbl_prodi_urutan` VALUES (3, 'Akuntansi', 3);

-- ----------------------------
-- Table structure for tbl_slider_background
-- ----------------------------
DROP TABLE IF EXISTS `tbl_slider_background`;
CREATE TABLE `tbl_slider_background`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('active','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of tbl_slider_background
-- ----------------------------
INSERT INTO `tbl_slider_background` VALUES (1, '1763285026_bg-new.png', 'active', '2025-11-16 16:23:46');

-- ----------------------------
-- Table structure for tbl_slider_logo
-- ----------------------------
DROP TABLE IF EXISTS `tbl_slider_logo`;
CREATE TABLE `tbl_slider_logo`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `position` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('active','inactive') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of tbl_slider_logo
-- ----------------------------
INSERT INTO `tbl_slider_logo` VALUES (2, '1763284401_logo-hukum.png', 'center', 'active', '2025-11-16 16:13:21');
INSERT INTO `tbl_slider_logo` VALUES (3, '1763284980_logo-manajemen.png', 'center', 'active', '2025-11-16 16:23:00');
INSERT INTO `tbl_slider_logo` VALUES (4, '1763284986_logo-yayasan.png', 'center', 'active', '2025-11-16 16:23:06');
INSERT INTO `tbl_slider_logo` VALUES (5, '1763284996_diktisaintek.png', 'center', 'active', '2025-11-16 16:23:16');

-- ----------------------------
-- Table structure for tbl_wisudawan
-- ----------------------------
DROP TABLE IF EXISTS `tbl_wisudawan`;
CREATE TABLE `tbl_wisudawan`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `urutan` int NULL DEFAULT NULL,
  `nirm` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `nama` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `ortu_laki` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `ortu_perempuan` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `tmp_tgl_lahir` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,
  `asal_sekolah` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `alamat` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,
  `ipk` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `judul` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,
  `keterangan` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL,
  `prodi` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `gelombang` varchar(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_gelombang_prodi`(`gelombang` ASC, `prodi` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3944 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of tbl_wisudawan
-- ----------------------------
INSERT INTO `tbl_wisudawan` VALUES (3604, 1, '2021020080', 'Wisudawan 01', 'Ayah 01', 'Ibu 01', 'Medan / 25 Februari 1995', NULL, NULL, NULL, 'Sistem Pendukung Keputusan untuk Menentukan Pemberian Reward Pemasok Terbaik Pada PT. Midi Utama Indonesia Tbk Menggunakan Metode Moosra', 'Cumlaude', 'Hukum', '1');
INSERT INTO `tbl_wisudawan` VALUES (3605, 2, '2021020275', 'Wisudawan 02', 'Ayah 02', 'Ibu 02', 'Pesununan / 13 April 2002', NULL, NULL, NULL, 'Penerapan Tanda Tangan Digital Menggunakan Algoritma SHA-1 Untuk Meningkatkan Keamanan Dan Integritas Bukti Pembayaran uang Sekolah Di Perguruan Kristen Methodist Indonesia.', 'Cumlaude', 'Hukum', '1');
INSERT INTO `tbl_wisudawan` VALUES (3606, 1, '2021020128', 'Wisudawan 03', 'Ayah 03', 'Ibu 03', 'Kuta Rimbaru / 19 Desember 2001', '', '', NULL, 'Penerapan Data Mining dalam Segmentasi Kebutuhan Belanja Anggota Koperasi Karyawan Budi Murni Medan Menggunakan Metode K-Nearest Neighbor.', 'Cumlaude', 'Manajemen', '1');
INSERT INTO `tbl_wisudawan` VALUES (3607, 1, '2021020006', 'Wisudawan 04', 'Ayah 04', 'Ibu 04', 'Medan / 02 Januari 2001', '', '', '', 'Analisis Perbandingan Metode Canny, Sobel dan Laplacian of Gaussian (LoG) dalam Mendeteksi Tepi Logo Provinsi di Indonesia', 'Cumlaude', 'Akuntansi', '1');
INSERT INTO `tbl_wisudawan` VALUES (3934, 2, '2021020054', 'Wisudawan 05', 'Ayah 05', 'Ibu 05', 'Medan / 02 Januari 2001', 'Medan', 'Medan', '', 'Dampak Implementasi Enterprise Resource Planning (ERP) Terhadap Efisiensi Proses Bisnis Pada PT. Maju Bersama', 'Sangat Memuaskan', 'Akuntansi', '1');

SET FOREIGN_KEY_CHECKS = 1;
