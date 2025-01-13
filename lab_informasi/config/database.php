<?php
$host = 'localhost';
$port = '8081';
$dbname = 'sistem_informasi_laboratorium';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create Peminjaman table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS Peminjaman (
        PeminjamanID INT AUTO_INCREMENT PRIMARY KEY,
        PeralatanID INT,
        NamaPeminjam VARCHAR(100) NOT NULL,
        JabatanPeminjam ENUM('Mahasiswa', 'Dosen', 'Staff Lab', 'Satpam') NOT NULL,
        TanggalPinjam DATE NOT NULL,
        TanggalKembali DATE,
        Status ENUM('Dipinjam', 'Dikembalikan', 'Terlambat') DEFAULT 'Dipinjam',
        Catatan TEXT,
        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (PeralatanID) REFERENCES Peralatan(PeralatanID) ON DELETE SET NULL
    )";
    $pdo->exec($sql);
    
    // Create Jadwal table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS Jadwal (
        JadwalID INT AUTO_INCREMENT PRIMARY KEY,
        LabID INT,
        TanggalMulai DATE NOT NULL,
        TanggalSelesai DATE NOT NULL,
        JamMulai TIME NOT NULL,
        JamSelesai TIME NOT NULL,
        Keperluan TEXT NOT NULL,
        NamaPengguna VARCHAR(100) NOT NULL,
        RolePengguna ENUM('Mahasiswa', 'Dosen', 'Staff Lab', 'Satpam') NOT NULL,
        Status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (LabID) REFERENCES Laboratorium(LabID) ON DELETE SET NULL
    )";
    $pdo->exec($sql);
    
    // Create Laporan table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS Laporan (
        LaporanID INT AUTO_INCREMENT PRIMARY KEY,
        PeralatanID INT,
        NamaPelapor VARCHAR(100) NOT NULL,
        JabatanPelapor ENUM('Mahasiswa', 'Dosen', 'Staff Lab', 'Satpam') NOT NULL,
        Masalah TEXT NOT NULL,
        TanggalLaporan DATE NOT NULL,
        Status ENUM('Pending', 'Diproses', 'Selesai') DEFAULT 'Pending',
        Solusi TEXT,
        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (PeralatanID) REFERENCES Peralatan(PeralatanID) ON DELETE SET NULL
    )";
    $pdo->exec($sql);
    
    // Create JenisDenda table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS JenisDenda (
        DendaID INT PRIMARY KEY AUTO_INCREMENT,
        NamaDenda VARCHAR(100) NOT NULL,
        BiayaDenda DECIMAL(10,2) NOT NULL,
        Keterangan TEXT,
        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Create Denda table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS Denda (
        DendaID INT PRIMARY KEY AUTO_INCREMENT,
        PeminjamanID INT,
        JenisDendaID INT,
        NamaPeminjam VARCHAR(100) NOT NULL,
        TotalDenda DECIMAL(10,2) NOT NULL,
        TanggalDenda DATE NOT NULL,
        StatusPembayaran ENUM('Belum Dibayar', 'Sudah Dibayar') DEFAULT 'Belum Dibayar',
        TanggalPembayaran DATE,
        Keterangan TEXT,
        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (PeminjamanID) REFERENCES Peminjaman(PeminjamanID),
        FOREIGN KEY (JenisDendaID) REFERENCES JenisDenda(DendaID)
    )");

    // Add default jenis denda if not exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM JenisDenda");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO JenisDenda (NamaDenda, BiayaDenda, Keterangan) VALUES
            ('Keterlambatan', 5000.00, 'Denda per hari untuk keterlambatan pengembalian'),
            ('Kerusakan Ringan', 50000.00, 'Denda untuk kerusakan ringan pada peralatan'),
            ('Kerusakan Berat', 200000.00, 'Denda untuk kerusakan berat pada peralatan')");
    }

} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
