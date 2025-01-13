<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Laboratorium</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src=".\assets\images\1.png" alt="Logo" height="80" width="80" class="me-2">
                <div>
                    <div class="brand-title">Sistem Laboratorium</div>
                    <div class="brand-subtitle">Kelola Lab Anda dengan Efisien</div>
                </div>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item mx-2">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                            <i class="fas fa-home me-1"></i>
                            Beranda
                        </a>
                    </li>
                    <li class="nav-item mx-2">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'laboratorium.php' ? 'active' : ''; ?>" href="laboratorium.php">
                            <i class="fas fa-flask me-1"></i>
                            Laboratorium
                        </a>
                    </li>
                    <li class="nav-item mx-2">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'peralatan.php' ? 'active' : ''; ?>" href="peralatan.php">
                            <i class="fas fa-tools me-1"></i>
                            Peralatan
                        </a>
                    </li>
                    <li class="nav-item mx-2">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'peminjaman.php' ? 'active' : ''; ?>" href="peminjaman.php">
                            <i class="fas fa-hand-holding me-1"></i>
                            Peminjaman
                        </a>
                    </li>
                    <li class="nav-item mx-2">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'laporan.php' ? 'active' : ''; ?>" href="laporan.php">
                            <i class="fas fa-flag me-1"></i>
                            Laporan
                        </a>
                    </li>
                    <li class="nav-item mx-2">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'denda.php' ? 'active' : ''; ?>" href="denda.php">
                            <i class="fas fa-money-bill me-1"></i>
                            Denda
                        </a>
                    </li>
                </ul>

                <?php if (isset($_SESSION['user_id'])): ?>
                <div class="ms-auto">
                    <a href="logout.php" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-sign-out-alt me-1"></i>
                        Keluar
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main class="content body">
        <div class="container py-4 mt-5">

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
