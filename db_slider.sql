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

 Date: 13/10/2025 12:32:36
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

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
) ENGINE = InnoDB AUTO_INCREMENT = 3933 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = COMPACT;

-- ----------------------------
-- Records of tbl_wisudawan
-- ----------------------------
INSERT INTO `tbl_wisudawan` VALUES (3604, 1, '2021020080', 'Wisudawan 01', 'Ayah 01', 'Ibu 01', 'Medan / 25 Februari 1995', NULL, NULL, '3,69', 'Sistem Pendukung Keputusan untuk Menentukan Pemberian Reward Pemasok Terbaik Pada PT. Midi Utama Indonesia Tbk Menggunakan Metode Moosra', 'Cumlaude', 'Hukum', '1');
INSERT INTO `tbl_wisudawan` VALUES (3605, 2, '2021020275', 'Wisudawan 02', 'Ayah 02', 'Ibu 02', 'Pesununan / 13 April 2002', NULL, NULL, '3,73', 'Penerapan Tanda Tangan Digital Menggunakan Algoritma SHA-1 Untuk Meningkatkan Keamanan Dan Integritas Bukti Pembayaran uang Sekolah Di Perguruan Kristen Methodist Indonesia.', 'Cumlaude', 'Hukum', '1');
INSERT INTO `tbl_wisudawan` VALUES (3606, 3, '2021020128', 'Wisudawan 03', 'Ayah 03', 'Ibu 03', 'Kuta Rimbaru / 19 Desember 2001', NULL, NULL, '3,59', 'Penerapan Data Mining dalam Segmentasi Kebutuhan Belanja Anggota Koperasi Karyawan Budi Murni Medan Menggunakan Metode K-Nearest Neighbor.', 'Cumlaude', 'Manajemen', '1');
INSERT INTO `tbl_wisudawan` VALUES (3607, 4, '2021020006', 'Wisudawan 04', 'Ayah 04', 'Ibu 04', 'Medan / 02 Januari 2001', NULL, NULL, '3,74', 'Analisis Perbandingan Metode Canny, Sobel dan Laplacian of Gaussian (LoG) dalam Mendeteksi Tepi Logo Provinsi di Indonesia', 'Cumlaude', 'Akuntansi', '1');

SET FOREIGN_KEY_CHECKS = 1;
