<?php
require_once 'config/database.php';
include 'includes/header.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM Peminjaman WHERE PeminjamanID = ?");
        $stmt->execute([$id]);
        header("Location: peminjaman.php?msg=deleted");
        exit();
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Handle Return
if (isset($_GET['return'])) {
    $id = $_GET['return'];
    try {
        $stmt = $pdo->prepare("UPDATE Peminjaman SET Status = 'Dikembalikan', TanggalKembali = CURRENT_DATE WHERE PeminjamanID = ?");
        $stmt->execute([$id]);
        header("Location: peminjaman.php?msg=returned");
        exit();
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['id'])) {
        // Update
        try {
            $stmt = $pdo->prepare("UPDATE Peminjaman SET 
                PeralatanID = ?, 
                NamaPeminjam = ?,
                JabatanPeminjam = ?,
                TanggalPinjam = ?, 
                TanggalKembali = ?,
                Catatan = ?
                WHERE PeminjamanID = ?");
            $stmt->execute([
                $_POST['peralatan_id'],
                $_POST['nama_peminjam'],
                $_POST['jabatan_peminjam'],
                $_POST['tanggal_pinjam'],
                $_POST['tanggal_kembali'],
                $_POST['catatan'],
                $_POST['id']
            ]);
            header("Location: peminjaman.php?msg=updated");
            exit();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {
        // Insert
        try {
            $stmt = $pdo->prepare("INSERT INTO Peminjaman 
                (PeralatanID, NamaPeminjam, JabatanPeminjam, TanggalPinjam, TanggalKembali, Catatan, Status) 
                VALUES (?, ?, ?, ?, ?, ?, 'Dipinjam')");
            $stmt->execute([
                $_POST['peralatan_id'],
                $_POST['nama_peminjam'],
                $_POST['jabatan_peminjam'],
                $_POST['tanggal_pinjam'],
                $_POST['tanggal_kembali'],
                $_POST['catatan']
            ]);
            header("Location: peminjaman.php?msg=added");
            exit();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}

// Get all loans with equipment details
$stmt = $pdo->query("SELECT p.*, pr.NamaPeralatan, l.NamaLab 
                     FROM Peminjaman p 
                     LEFT JOIN Peralatan pr ON p.PeralatanID = pr.PeralatanID 
                     LEFT JOIN Laboratorium l ON pr.LabID = l.LabID 
                     ORDER BY p.CreatedAt DESC");
$loans = $stmt->fetchAll();

// Get all equipment for dropdown
$stmt = $pdo->query("SELECT p.*, l.NamaLab 
                     FROM Peralatan p 
                     LEFT JOIN Laboratorium l ON p.LabID = l.LabID 
                     WHERE p.PeralatanID NOT IN (
                         SELECT PeralatanID FROM Peminjaman WHERE Status = 'Dipinjam'
                     )
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
                            <h2 class="mb-0">Data Peminjaman Peralatan</h2>
                        </div>
                        <div class="col-md-6 text-end">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLoanModal">
                                <i class="fas fa-plus me-2"></i>Tambah Peminjaman
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
                            echo "Peminjaman berhasil ditambahkan.";
                            break;
                        case 'updated':
                            echo "Peminjaman berhasil diperbarui.";
                            break;
                        case 'deleted':
                            echo "Peminjaman berhasil dihapus.";
                            break;
                        case 'returned':
                            echo "Peralatan berhasil dikembalikan.";
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
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Nama Peminjam</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Jabatan</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Tanggal Pinjam</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Tanggal Kembali</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Catatan</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($loans as $loan): ?>
                                <tr>
                                    <td class="align-middle text-center">
                                        <p class="text-xs font-weight-bold mb-0"><?= $loan['PeminjamanID'] ?></p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0"><?= htmlspecialchars($loan['NamaPeralatan']) ?></p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0"><?= htmlspecialchars($loan['NamaLab']) ?></p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0"><?= htmlspecialchars($loan['NamaPeminjam']) ?></p>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm bg-<?= 
                                            $loan['JabatanPeminjam'] == 'Dosen' ? 'primary' : 
                                            ($loan['JabatanPeminjam'] == 'Staff Lab' ? 'success' : 
                                            ($loan['JabatanPeminjam'] == 'Satpam' ? 'warning' : 'info')) 
                                        ?>">
                                            <?= $loan['JabatanPeminjam'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0"><?= date('d/m/Y', strtotime($loan['TanggalPinjam'])) ?></p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">
                                            <?= $loan['TanggalKembali'] ? date('d/m/Y', strtotime($loan['TanggalKembali'])) : '-' ?>
                                        </p>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm bg-<?= 
                                            $loan['Status'] == 'Dikembalikan' ? 'success' : 
                                            ($loan['Status'] == 'Terlambat' ? 'danger' : 'warning') 
                                        ?>">
                                            <?= $loan['Status'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0"><?= htmlspecialchars($loan['Catatan']) ?></p>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <?php if ($loan['Status'] == 'Dipinjam'): ?>
                                            <button class="btn btn-link text-warning text-gradient px-3 mb-0 edit-btn" 
                                                    data-id="<?= $loan['PeminjamanID'] ?>"
                                                    data-peralatan="<?= $loan['PeralatanID'] ?>"
                                                    data-nama="<?= htmlspecialchars($loan['NamaPeminjam']) ?>"
                                                    data-jabatan="<?= $loan['JabatanPeminjam'] ?>"
                                                    data-tanggal="<?= $loan['TanggalPinjam'] ?>"
                                                    data-tanggal-kembali="<?= $loan['TanggalKembali'] ?>"
                                                    data-catatan="<?= htmlspecialchars($loan['Catatan']) ?>"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editLoanModal">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="?return=<?= $loan['PeminjamanID'] ?>" 
                                               class="btn btn-link text-success text-gradient px-3 mb-0"
                                               onclick="return confirm('Apakah Anda yakin ingin mengembalikan peralatan ini?')">
                                                <i class="fas fa-check"></i>
                                            </a>
                                            <?php endif; ?>
                                            <a href="?delete=<?= $loan['PeminjamanID'] ?>" 
                                               class="btn btn-link text-danger text-gradient px-3 mb-0"
                                               onclick="return confirm('Apakah Anda yakin ingin menghapus data peminjaman ini?')">
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
<div class="modal fade" id="addLoanModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Peminjaman</h5>
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
                                <label class="form-label">Nama Peminjam</label>
                                <input type="text" class="form-control" name="nama_peminjam" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Jabatan Peminjam</label>
                                <select class="form-select" name="jabatan_peminjam" required>
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
                                <label class="form-label">Tanggal Pinjam</label>
                                <input type="date" class="form-control" name="tanggal_pinjam" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tanggal Kembali</label>
                                <input type="date" class="form-control" name="tanggal_kembali" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Catatan</label>
                                <textarea class="form-control" name="catatan" rows="4"></textarea>
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
<div class="modal fade" id="editLoanModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Peminjaman</h5>
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
                                <label class="form-label">Nama Peminjam</label>
                                <input type="text" class="form-control" name="nama_peminjam" id="edit-nama" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Jabatan Peminjam</label>
                                <select class="form-select" name="jabatan_peminjam" id="edit-jabatan" required>
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
                                <label class="form-label">Tanggal Pinjam</label>
                                <input type="date" class="form-control" name="tanggal_pinjam" id="edit-tanggal" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tanggal Kembali</label>
                                <input type="date" class="form-control" name="tanggal_kembali" id="edit-tanggal-kembali" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Catatan</label>
                                <textarea class="form-control" name="catatan" id="edit-catatan" rows="4"></textarea>
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
            document.getElementById('edit-tanggal-kembali').value = this.dataset.tanggalKembali;
            document.getElementById('edit-catatan').value = this.dataset.catatan;
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
