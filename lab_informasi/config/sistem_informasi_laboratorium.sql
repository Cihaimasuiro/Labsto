-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for sistem_informasi_laboratorium
CREATE DATABASE IF NOT EXISTS `sistem_informasi_laboratorium` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `sistem_informasi_laboratorium`;

-- Dumping structure for table sistem_informasi_laboratorium.denda
CREATE TABLE IF NOT EXISTS `denda` (
  `DendaID` int NOT NULL AUTO_INCREMENT,
  `PeminjamanID` int DEFAULT NULL,
  `JenisDendaID` int DEFAULT NULL,
  `TotalDenda` decimal(10,2) NOT NULL DEFAULT '0.00',
  `TanggalDenda` date NOT NULL,
  `StatusPembayaran` enum('Belum Dibayar','Lunas') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Belum Dibayar',
  `TanggalPembayaran` date DEFAULT NULL,
  `Keterangan` text COLLATE utf8mb4_general_ci,
  `CreatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`DendaID`),
  KEY `JenisDendaID` (`JenisDendaID`),
  KEY `denda_ibfk_1` (`PeminjamanID`),
  CONSTRAINT `denda_ibfk_1` FOREIGN KEY (`PeminjamanID`) REFERENCES `peminjaman` (`PeminjamanID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `denda_ibfk_2` FOREIGN KEY (`JenisDendaID`) REFERENCES `jenisdenda` (`DendaID`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table sistem_informasi_laboratorium.denda: ~1 rows (approximately)
DELETE FROM `denda`;
INSERT INTO `denda` (`DendaID`, `PeminjamanID`, `JenisDendaID`, `TotalDenda`, `TanggalDenda`, `StatusPembayaran`, `TanggalPembayaran`, `Keterangan`, `CreatedAt`) VALUES
	(4, 9, 2, 0.00, '2025-01-22', 'Belum Dibayar', NULL, 'lupa tempat penyimpanan', '2025-01-12 07:56:06');

-- Dumping structure for table sistem_informasi_laboratorium.jadwal
CREATE TABLE IF NOT EXISTS `jadwal` (
  `JadwalID` int NOT NULL AUTO_INCREMENT,
  `LabID` int DEFAULT NULL,
  `TanggalMulai` date NOT NULL,
  `TanggalSelesai` date NOT NULL,
  `JamMulai` time NOT NULL,
  `JamSelesai` time NOT NULL,
  `Keperluan` text COLLATE utf8mb4_general_ci NOT NULL,
  `NamaPengguna` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `RolePengguna` enum('Mahasiswa','Dosen','Staff Lab','Satpam') COLLATE utf8mb4_general_ci NOT NULL,
  `Status` enum('Pending','Approved','Rejected') COLLATE utf8mb4_general_ci DEFAULT 'Pending',
  `CreatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`JadwalID`),
  KEY `LabID` (`LabID`),
  CONSTRAINT `jadwal_ibfk_1` FOREIGN KEY (`LabID`) REFERENCES `laboratorium` (`LabID`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table sistem_informasi_laboratorium.jadwal: ~1 rows (approximately)
DELETE FROM `jadwal`;
INSERT INTO `jadwal` (`JadwalID`, `LabID`, `TanggalMulai`, `TanggalSelesai`, `JamMulai`, `JamSelesai`, `Keperluan`, `NamaPengguna`, `RolePengguna`, `Status`, `CreatedAt`) VALUES
	(1, 1, '2025-01-15', '2025-01-15', '10:00:00', '12:00:00', 'Praktikum Kimia', 'Budi', 'Mahasiswa', 'Approved', '2025-01-12 08:08:11');

-- Dumping structure for table sistem_informasi_laboratorium.jenisdenda
CREATE TABLE IF NOT EXISTS `jenisdenda` (
  `DendaID` int NOT NULL AUTO_INCREMENT,
  `NamaDenda` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `BiayaDenda` decimal(10,2) NOT NULL,
  `Keterangan` text COLLATE utf8mb4_general_ci,
  `CreatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`DendaID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table sistem_informasi_laboratorium.jenisdenda: ~3 rows (approximately)
DELETE FROM `jenisdenda`;
INSERT INTO `jenisdenda` (`DendaID`, `NamaDenda`, `BiayaDenda`, `Keterangan`, `CreatedAt`) VALUES
	(1, 'Keterlambatan Pengembalian', 5000.00, 'Denda per hari untuk keterlambatan pengembalian', '2025-01-12 03:51:41'),
	(2, 'Kehilangan Barang', 0.00, 'Denda untuk kerusakan ringan pada peralatan', '2025-01-12 03:51:41'),
	(3, 'Kerusakan Berat', 200000.00, 'Denda untuk kerusakan berat pada peralatan', '2025-01-12 03:51:41');

-- Dumping structure for table sistem_informasi_laboratorium.laboratorium
CREATE TABLE IF NOT EXISTS `laboratorium` (
  `LabID` int NOT NULL AUTO_INCREMENT,
  `NamaLab` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `Lokasi` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Kapasitas` int DEFAULT NULL,
  `Deskripsi` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`LabID`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table sistem_informasi_laboratorium.laboratorium: ~3 rows (approximately)
DELETE FROM `laboratorium`;
INSERT INTO `laboratorium` (`LabID`, `NamaLab`, `Lokasi`, `Kapasitas`, `Deskripsi`) VALUES
	(1, 'Lab mawar', 'Gedung A1', 20, 'Farmasi lab'),
	(3, 'lab melati', 'Gedung B1', 25, 'untuk praktikum'),
	(4, 'Lab Anggrek', 'Gedung A2', 15, 'Praktikum sedang');

-- Dumping structure for table sistem_informasi_laboratorium.laporan
CREATE TABLE IF NOT EXISTS `laporan` (
  `LaporanID` int NOT NULL AUTO_INCREMENT,
  `PeralatanID` int DEFAULT NULL,
  `NamaPelapor` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `JabatanPelapor` enum('Mahasiswa','Dosen','Staff Lab','Satpam') COLLATE utf8mb4_general_ci NOT NULL,
  `Masalah` text COLLATE utf8mb4_general_ci NOT NULL,
  `TanggalLaporan` date NOT NULL,
  `Status` enum('Pending','Diproses','Selesai') COLLATE utf8mb4_general_ci DEFAULT 'Pending',
  `Solusi` text COLLATE utf8mb4_general_ci,
  `CreatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`LaporanID`),
  KEY `PeralatanID` (`PeralatanID`),
  CONSTRAINT `laporan_ibfk_1` FOREIGN KEY (`PeralatanID`) REFERENCES `peralatan` (`PeralatanID`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table sistem_informasi_laboratorium.laporan: ~1 rows (approximately)
DELETE FROM `laporan`;
INSERT INTO `laporan` (`LaporanID`, `PeralatanID`, `NamaPelapor`, `JabatanPelapor`, `Masalah`, `TanggalLaporan`, `Status`, `Solusi`, `CreatedAt`) VALUES
	(2, 2, 'melati', 'Dosen', 'patah 1', '2025-01-13', 'Pending', NULL, '2025-01-12 07:55:05');

-- Dumping structure for table sistem_informasi_laboratorium.laporankerusakan
CREATE TABLE IF NOT EXISTS `laporankerusakan` (
  `LaporanID` int NOT NULL AUTO_INCREMENT,
  `PeralatanID` int DEFAULT NULL,
  `PenggunaID` int DEFAULT NULL,
  `DeskripsiKerusakan` text COLLATE utf8mb4_general_ci NOT NULL,
  `TanggalLapor` datetime DEFAULT CURRENT_TIMESTAMP,
  `StatusPerbaikan` enum('Pending','Sedang Diperbaiki','Selesai') COLLATE utf8mb4_general_ci DEFAULT 'Pending',
  PRIMARY KEY (`LaporanID`),
  KEY `PeralatanID` (`PeralatanID`),
  KEY `PenggunaID` (`PenggunaID`),
  CONSTRAINT `laporankerusakan_ibfk_1` FOREIGN KEY (`PeralatanID`) REFERENCES `peralatan` (`PeralatanID`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `laporankerusakan_ibfk_2` FOREIGN KEY (`PenggunaID`) REFERENCES `pengguna` (`PenggunaID`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table sistem_informasi_laboratorium.laporankerusakan: ~0 rows (approximately)
DELETE FROM `laporankerusakan`;

-- Dumping structure for table sistem_informasi_laboratorium.logaktivitas
CREATE TABLE IF NOT EXISTS `logaktivitas` (
  `LogID` int NOT NULL AUTO_INCREMENT,
  `PenggunaID` int DEFAULT NULL,
  `Aktivitas` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `TanggalAktivitas` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`LogID`),
  KEY `PenggunaID` (`PenggunaID`),
  CONSTRAINT `logaktivitas_ibfk_1` FOREIGN KEY (`PenggunaID`) REFERENCES `pengguna` (`PenggunaID`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table sistem_informasi_laboratorium.logaktivitas: ~0 rows (approximately)
DELETE FROM `logaktivitas`;

-- Dumping structure for table sistem_informasi_laboratorium.peminjaman
CREATE TABLE IF NOT EXISTS `peminjaman` (
  `PeminjamanID` int NOT NULL AUTO_INCREMENT,
  `PeralatanID` int DEFAULT NULL,
  `NamaPeminjam` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `JabatanPeminjam` enum('Mahasiswa','Dosen','Staff Lab','Satpam') COLLATE utf8mb4_general_ci NOT NULL,
  `TanggalPinjam` date NOT NULL,
  `TanggalKembali` date DEFAULT NULL,
  `Status` enum('Dipinjam','Dikembalikan','Terlambat') COLLATE utf8mb4_general_ci DEFAULT 'Dipinjam',
  `Catatan` text COLLATE utf8mb4_general_ci,
  `CreatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`PeminjamanID`),
  KEY `PeralatanID` (`PeralatanID`),
  CONSTRAINT `peminjaman_ibfk_1` FOREIGN KEY (`PeralatanID`) REFERENCES `peralatan` (`PeralatanID`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table sistem_informasi_laboratorium.peminjaman: ~3 rows (approximately)
DELETE FROM `peminjaman`;
INSERT INTO `peminjaman` (`PeminjamanID`, `PeralatanID`, `NamaPeminjam`, `JabatanPeminjam`, `TanggalPinjam`, `TanggalKembali`, `Status`, `Catatan`, `CreatedAt`) VALUES
	(7, 2, 'natan', 'Mahasiswa', '2025-01-12', '2025-01-13', 'Dipinjam', 'Praktikum', '2025-01-12 07:47:12'),
	(8, 4, 'wira', 'Staff Lab', '2025-01-12', '2025-01-14', 'Dipinjam', 'tes praktikum', '2025-01-12 07:54:22'),
	(9, 5, 'helin', 'Mahasiswa', '2025-01-22', '2025-01-13', 'Dipinjam', 'kosong', '2025-01-12 07:56:06');

-- Dumping structure for table sistem_informasi_laboratorium.pengguna
CREATE TABLE IF NOT EXISTS `pengguna` (
  `PenggunaID` int NOT NULL AUTO_INCREMENT,
  `Nama` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `Email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `Password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `Role` enum('Admin','User') COLLATE utf8mb4_general_ci DEFAULT 'User',
  `TanggalBergabung` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`PenggunaID`),
  UNIQUE KEY `Email` (`Email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table sistem_informasi_laboratorium.pengguna: ~1 rows (approximately)
DELETE FROM `pengguna`;
INSERT INTO `pengguna` (`PenggunaID`, `Nama`, `Email`, `Password`, `Role`, `TanggalBergabung`) VALUES
	(1, 'admin', 'admin@gmail.com', 'admin', 'Admin', '2025-01-12 00:00:00');

-- Dumping structure for table sistem_informasi_laboratorium.peralatan
CREATE TABLE IF NOT EXISTS `peralatan` (
  `PeralatanID` int NOT NULL AUTO_INCREMENT,
  `NamaPeralatan` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `Gambar` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `LabID` int DEFAULT NULL,
  `Kondisi` enum('Baik','Rusak','Perbaikan') COLLATE utf8mb4_general_ci DEFAULT 'Baik',
  `TanggalPembelian` date DEFAULT NULL,
  `HargaBarang` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`PeralatanID`),
  KEY `LabID` (`LabID`),
  CONSTRAINT `peralatan_ibfk_1` FOREIGN KEY (`LabID`) REFERENCES `laboratorium` (`LabID`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table sistem_informasi_laboratorium.peralatan: ~4 rows (approximately)
DELETE FROM `peralatan`;
INSERT INTO `peralatan` (`PeralatanID`, `NamaPeralatan`, `Gambar`, `LabID`, `Kondisi`, `TanggalPembelian`, `HargaBarang`) VALUES
	(2, 'suntikan', '67839021e0a5b.jpg', 1, 'Baik', '2025-01-08', 0.00),
	(4, 'Tabung reaksi', '6783d6a92c4eb.jpeg', 3, 'Baik', '2025-01-13', 0.00),
	(5, 'cawan petri', '6783d7079ce60.jpeg', 3, 'Baik', '2025-01-13', 0.00),
	(6, 'kaca arloji', '6783d7408a400.jpeg', 3, 'Baik', '2025-01-13', 0.00);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
