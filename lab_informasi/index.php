<?php
require_once 'config/database.php';
include 'includes/header.php';  

// Get statistics
$stats = [
    'total_lab' => $pdo->query("SELECT COUNT(*) FROM Laboratorium")->fetchColumn(),
    'total_alat' => $pdo->query("SELECT COUNT(*) FROM Peralatan")->fetchColumn(),
    'peminjaman_aktif' => $pdo->query("SELECT COUNT(*) FROM Peminjaman WHERE Status = 'Dipinjam'")->fetchColumn(),
    'laporan_pending' => $pdo->query("SELECT COUNT(*) FROM Laporan WHERE Status = 'Pending'")->fetchColumn(),
    'denda_belum_bayar' => $pdo->query("SELECT COUNT(*) FROM Denda WHERE StatusPembayaran = 'Belum Dibayar'")->fetchColumn()
];

// Get recent activities
$activities = $pdo->query("
    (SELECT 'peminjaman' as type, p.PeminjamanID as id, p.NamaPeminjam as user, pr.NamaPeralatan as item, p.TanggalPinjam as date
     FROM Peminjaman p 
     JOIN Peralatan pr ON p.PeralatanID = pr.PeralatanID
     ORDER BY p.TanggalPinjam DESC LIMIT 5)
    UNION ALL
    (SELECT 'laporan' as type, l.LaporanID as id, l.NamaPelapor as user, pr.NamaPeralatan as item, l.TanggalLaporan as date
     FROM Laporan l
     JOIN Peralatan pr ON l.PeralatanID = pr.PeralatanID
     ORDER BY l.TanggalLaporan DESC LIMIT 5)
    ORDER BY date DESC LIMIT 10")->fetchAll();
?>

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card" style="display: flex; align-items: center;">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Laboratorium</p>
                                    <h5 class="font-weight-bolder mb-0" style="display: flex;justify-content: flex-start; margin: 10px;"><?= $stats['total_lab'] ?></h5>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                                    <i class="fas fa-flask text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card" style="display: flex; align-items: center;">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Peralatan</p>
                                    <h5 class="font-weight-bolder mb-0" style="display: flex;justify-content: flex-start; margin: 10px;"><?= $stats['total_alat'] ?></h5>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-info shadow text-center border-radius-md">
                                    <i class="fas fa-tools text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card" style="display: flex; align-items: center;">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold">Peminjaman Aktif</p>
                                    <h5 class="font-weight-bolder mb-0 " style="display: flex;justify-content: flex-start; margin: 10px;"><?= $stats['peminjaman_aktif'] ?></h5>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                                    <i class="fas fa-hand-holding text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card" style="display: flex; align-items: center;">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold">Laporan Pending</p>
                                    <h5 class="font-weight-bolder mb-0" style="display: flex;justify-content: flex-start; margin: 10px;"><?= $stats['laporan_pending'] ?></h5>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-warning shadow text-center border-radius-md">
                                    <i class="fas fa-flag text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-lg-7 mb-lg-0 mb-4">
                <div class="card">
                    <div class="card-header pb-0">
                        <h6>Aktivitas Terbaru</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="timeline timeline-one-side">
                            <?php foreach ($activities as $activity): ?>
                                <div class="timeline-block mb-3">
                                    <span class="timeline-step">
                                        <i class="fas fa-<?= $activity['type'] == 'peminjaman' ? 'hand-holding' : 'flag' ?> text-<?= $activity['type'] == 'peminjaman' ? 'success' : 'warning' ?>"></i>
                                    </span>
                                    <div class="timeline-content">
                                        <h6 class="text-dark text-sm font-weight-bold mb-0">
                                            <?= htmlspecialchars($activity['user']) ?>
                                            <?= $activity['type'] == 'peminjaman' ? 'meminjam' : 'melaporkan' ?>
                                            <?= htmlspecialchars($activity['item']) ?>
                                        </h6>
                                        <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">
                                            <?= date('d M Y H:i', strtotime($activity['date'])) ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header pb-0">
                        <h6>Denda Belum Dibayar</h6>
                        <p class="text-sm mb-0">
                            <span class="font-weight-bold"><?= $stats['denda_belum_bayar'] ?></span> denda belum dibayar
                        </p>
                    </div>
                    <div class="card-body p-3">
                        <div class="timeline timeline-one-side">
                            <?php 
                            $denda_list = $pdo->query("
                                SELECT d.*, j.NamaDenda, p.NamaPeminjam
                                FROM Denda d
                                JOIN JenisDenda j ON d.JenisDendaID = j.DendaID
                                LEFT JOIN Peminjaman p ON d.PeminjamanID = p.PeminjamanID  -- Add this join
                                WHERE d.StatusPembayaran = 'Belum Dibayar'
                                ORDER BY d.TanggalDenda DESC
                                LIMIT 5
                            ")->fetchAll();
                            
                            foreach ($denda_list as $denda): 
                            ?>
                                <div class="timeline-block mb-3">
                                    <span class="timeline-step bg-gradient-primary shadow text-center border-radius-md">
                                        <i class="fas fa-money-bill text-danger"></i>
                                    </span>
                                    <div class="timeline-content">
                                        <h6 class="text-dark text-sm font-weight-bold mb-0">
                                            <?= htmlspecialchars($denda['NamaPeminjam']) ?> - <?= htmlspecialchars($denda['NamaDenda']) ?>
                                        </h6>
                                        <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">
                                            Rp <?= number_format($denda['TotalDenda'], 0, ',', '.') ?> - 
                                            <?= date('d M Y', strtotime($denda['TanggalDenda'])) ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


<?php include 'includes/footer.php'; ?>
