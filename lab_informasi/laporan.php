<?php
require_once 'config/database.php';
include 'includes/header.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['id'])) {
        // Update existing report
        try {
            $stmt = $pdo->prepare("UPDATE Laporan SET 
                PeralatanID = ?,
                NamaPelapor = ?,
                JabatanPelapor = ?,
                Masalah = ?,
                TanggalLaporan = ?,
                Status = ?,
                Solusi = ?
                WHERE LaporanID = ?");
            $stmt->execute([
                $_POST['peralatan_id'],
                $_POST['nama_pelapor'],
                $_POST['jabatan_pelapor'],
                $_POST['masalah'],
                $_POST['tanggal_laporan'],
                $_POST['status'],
                $_POST['id']
            ]);
            header("Location: laporan.php?msg=updated");
            exit();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {
        // Add new report
        try {
            $stmt = $pdo->prepare("INSERT INTO Laporan 
                (PeralatanID, NamaPelapor, JabatanPelapor, Masalah, TanggalLaporan, Status) 
                VALUES (?, ?, ?, ?, ?, 'Pending')");
            $stmt->execute([
                $_POST['peralatan_id'],
                $_POST['nama_pelapor'],
                $_POST['jabatan_pelapor'],
                $_POST['masalah'],
                $_POST['tanggal_laporan']
            ]);
            header("Location: laporan.php?msg=added");
            exit();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM Laporan WHERE LaporanID = ?");
        $stmt->execute([$_GET['delete']]);
        header("Location: laporan.php?msg=deleted");
        exit();
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Get all reports
$stmt = $pdo->query("SELECT l.*, p.NamaPeralatan, lab.NamaLab 
                     FROM Laporan l 
                     LEFT JOIN Peralatan p ON l.PeralatanID = p.PeralatanID 
                     LEFT JOIN Laboratorium lab ON p.LabID = lab.LabID 
                     ORDER BY l.CreatedAt DESC");
$reports = $stmt->fetchAll();

// Get all equipment for dropdown
$stmt = $pdo->query("SELECT p.*, l.NamaLab 
                     FROM Peralatan p 
                     LEFT JOIN Laboratorium l ON p.LabID = l.LabID 
                     ORDER BY l.NamaLab, p.NamaPeralatan");
$equipment = $stmt->fetchAll();
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="row">
                        <div class="col-md-6">
                            <h2 class="mb-0">Data Laporan Kerusakan</h2>
                        </div>
                        <div class="col-md-6 text-end">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addReportModal">
                                <i class="fas fa-plus me-2"></i>Tambah Laporan
                            </button>
                        </div>
                    </div>
                </div>

                <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success alert-dismissible fade show mx-4" role="alert">
                    <?php 
                    $msg = $_GET['msg'];
                    switch($msg) {
                        case 'added':
                            echo "Laporan berhasil ditambahkan.";
                            break;
                        case 'updated':
                            echo "Laporan berhasil diperbarui.";
                            break;
                        case 'deleted':
                            echo "Laporan berhasil dihapus.";
                            break;
                    }
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">ID</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Peralatan</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Laboratorium</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Nama Pelapor</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Jabatan</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Tanggal</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Masalah</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Solusi</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reports as $report): ?>
                                <tr>
                                    <td class="align-middle text-center">
                                        <p class="text-xs font-weight-bold mb-0"><?= $report['LaporanID'] ?></p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0"><?= htmlspecialchars($report['NamaPeralatan']) ?></p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0"><?= htmlspecialchars($report['NamaLab']) ?></p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0"><?= htmlspecialchars($report['NamaPelapor']) ?></p>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm bg-<?= 
                                            $report['JabatanPelapor'] == 'Dosen' ? 'primary' : 
                                            ($report['JabatanPelapor'] == 'Staff Lab' ? 'success' : 
                                            ($report['JabatanPelapor'] == 'Satpam' ? 'warning' : 'info')) 
                                        ?>">
                                            <?= $report['JabatanPelapor'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0"><?= date('d/m/Y', strtotime($report['TanggalLaporan'])) ?></p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0"><?= htmlspecialchars($report['Masalah']) ?></p>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm bg-<?= 
                                            $report['Status'] == 'Selesai' ? 'success' : 
                                            ($report['Status'] == 'Diproses' ? 'warning' : 'danger') 
                                        ?>">
                                            <?= $report['Status'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0"><?= htmlspecialchars($report['Solusi'] ?? '-') ?></p>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-link text-warning text-gradient px-3 mb-0 edit-btn" 
                                                    data-id="<?= $report['LaporanID'] ?>"
                                                    data-peralatan="<?= $report['PeralatanID'] ?>"
                                                    data-nama="<?= htmlspecialchars($report['NamaPelapor']) ?>"
                                                    data-jabatan="<?= $report['JabatanPelapor'] ?>"
                                                    data-tanggal="<?= $report['TanggalLaporan'] ?>"
                                                    data-masalah="<?= htmlspecialchars($report['Masalah']) ?>"
                                                    data-status="<?= $report['Status'] ?>"
                                                    data-solusi="<?= htmlspecialchars($report['Solusi'] ?? '') ?>"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editReportModal">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="?delete=<?= $report['LaporanID'] ?>" 
                                               class="btn btn-link text-danger text-gradient px-3 mb-0"
                                               onclick="return confirm('Apakah Anda yakin ingin menghapus laporan ini?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addReportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Laporan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Peralatan</label>
                                <select class="form-select" name="peralatan_id" required>
                                    <option value="">Pilih Peralatan</option>
                                    <?php foreach ($equipment as $item): ?>
                                    <option value="<?= $item['PeralatanID'] ?>">
                                        <?= htmlspecialchars($item['NamaPeralatan']) ?> 
                                        (<?= htmlspecialchars($item['NamaLab']) ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nama Pelapor</label>
                                <input type="text" class="form-control" name="nama_pelapor" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Jabatan Pelapor</label>
                                <select class="form-select" name="jabatan_pelapor" required>
                                    <option value="">Pilih Jabatan</option>
                                    <option value="Mahasiswa">Mahasiswa</option>
                                    <option value="Dosen">Dosen</option>
                                    <option value="Staff Lab">Staff Lab</option>
                                    <option value="Satpam">Satpam</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tanggal Laporan</label>
                                <input type="date" class="form-control" name="tanggal_laporan" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Masalah</label>
                                <textarea class="form-control" name="masalah" rows="4" required></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editReportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Laporan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Peralatan</label>
                                <select class="form-select" name="peralatan_id" id="edit-peralatan" required>
                                    <option value="">Pilih Peralatan</option>
                                    <?php foreach ($equipment as $item): ?>
                                    <option value="<?= $item['PeralatanID'] ?>">
                                        <?= htmlspecialchars($item['NamaPeralatan']) ?> 
                                        (<?= htmlspecialchars($item['NamaLab']) ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nama Pelapor</label>
                                <input type="text" class="form-control" name="nama_pelapor" id="edit-nama" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Jabatan Pelapor</label>
                                <select class="form-select" name="jabatan_pelapor" id="edit-jabatan" required>
                                    <option value="">Pilih Jabatan</option>
                                    <option value="Mahasiswa">Mahasiswa</option>
                                    <option value="Dosen">Dosen</option>
                                    <option value="Staff Lab">Staff Lab</option>
                                    <option value="Satpam">Satpam</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tanggal Laporan</label>
                                <input type="date" class="form-control" name="tanggal_laporan" id="edit-tanggal" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Masalah</label>
                                <textarea class="form-control" name="masalah" id="edit-masalah" rows="4" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status" id="edit-status" required>
                                    <option value="Pending">Pending</option>
                                    <option value="Diproses">Diproses</option>
                                    <option value="Selesai">Selesai</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Solusi</label>
                                <textarea class="form-control" name="solusi" id="edit-solusi" rows="4"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle edit button clicks
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('edit-id').value = this.dataset.id;
            document.getElementById('edit-peralatan').value = this.dataset.peralatan;
            document.getElementById('edit-nama').value = this.dataset.nama;
            document.getElementById('edit-jabatan').value = this.dataset.jabatan;
            document.getElementById('edit-tanggal').value = this.dataset.tanggal;
            document.getElementById('edit-masalah').value = this.dataset.masalah;
            document.getElementById('edit-status').value = this.dataset.status;
            document.getElementById('edit-solusi').value = this.dataset.solusi;
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
