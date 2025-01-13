<?php
require_once 'config/database.php';
include 'includes/header.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM Jadwal WHERE JadwalID = ?");
        $stmt->execute([$id]);
        header("Location: jadwal.php?msg=deleted");
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
            $stmt = $pdo->prepare("UPDATE Jadwal SET 
                LabID = ?, 
                TanggalMulai = ?, 
                TanggalSelesai = ?, 
                JamMulai = ?,
                JamSelesai = ?,
                Keperluan = ?,
                NamaPengguna = ?,
                RolePengguna = ?,
                Status = ?
                WHERE JadwalID = ?");
            $stmt->execute([
                $_POST['lab_id'],
                $_POST['tanggal_mulai'],
                $_POST['tanggal_selesai'],
                $_POST['jam_mulai'],
                $_POST['jam_selesai'],
                $_POST['keperluan'],
                $_POST['nama_pengguna'],
                $_POST['role_pengguna'],
                $_POST['status'],
                $_POST['id']
            ]);
            header("Location: jadwal.php?msg=updated");
            exit();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {
        // Insert
        try {
            $stmt = $pdo->prepare("INSERT INTO Jadwal 
                (LabID, TanggalMulai, TanggalSelesai, JamMulai, JamSelesai, Keperluan, NamaPengguna, RolePengguna, Status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_POST['lab_id'],
                $_POST['tanggal_mulai'],
                $_POST['tanggal_selesai'],
                $_POST['jam_mulai'],
                $_POST['jam_selesai'],
                $_POST['keperluan'],
                $_POST['nama_pengguna'],
                $_POST['role_pengguna'],
                'Pending'
            ]);
            header("Location: jadwal.php?msg=added");
            exit();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}

// Get all schedules with lab names
$stmt = $pdo->query("SELECT j.*, l.NamaLab FROM Jadwal j 
                     LEFT JOIN Laboratorium l ON j.LabID = l.LabID 
                     ORDER BY j.TanggalMulai DESC, j.JamMulai DESC");
$schedules = $stmt->fetchAll();

// Get all labs for dropdown
$stmt = $pdo->query("SELECT * FROM Laboratorium ORDER BY NamaLab");
$labs = $stmt->fetchAll();
?>

<div class="row mb-3">
    <div class="col-md-6">
        <h2>Jadwal Penggunaan Laboratorium</h2>
    </div>
    <div class="col-md-6 text-end">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
            <i class="fas fa-plus"></i> Tambah Jadwal
        </button>
    </div>
</div>

<?php if (isset($_GET['msg'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?php 
    $msg = $_GET['msg'];
    switch($msg) {
        case 'added':
            echo "Jadwal berhasil ditambahkan.";
            break;
        case 'updated':
            echo "Jadwal berhasil diperbarui.";
            break;
        case 'deleted':
            echo "Jadwal berhasil dihapus.";
            break;
    }
    ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Laboratorium</th>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Keperluan</th>
                        <th>Pengguna</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($schedules as $schedule): ?>
                    <tr>
                        <td><?= $schedule['JadwalID'] ?></td>
                        <td><?= htmlspecialchars($schedule['NamaLab']) ?></td>
                        <td>
                            <?= date('d/m/Y', strtotime($schedule['TanggalMulai'])) ?> - 
                            <?= date('d/m/Y', strtotime($schedule['TanggalSelesai'])) ?>
                        </td>
                        <td>
                            <?= $schedule['JamMulai'] ?> - 
                            <?= $schedule['JamSelesai'] ?>
                        </td>
                        <td><?= htmlspecialchars($schedule['Keperluan']) ?></td>
                        <td><?= htmlspecialchars($schedule['NamaPengguna']) ?></td>
                        <td>
                            <span class="badge bg-<?= 
                                $schedule['RolePengguna'] == 'Dosen' ? 'primary' : 
                                ($schedule['RolePengguna'] == 'Staff Lab' ? 'success' : 
                                ($schedule['RolePengguna'] == 'Satpam' ? 'warning' : 'info')) 
                            ?>">
                                <?= $schedule['RolePengguna'] ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?= 
                                $schedule['Status'] == 'Approved' ? 'success' : 
                                ($schedule['Status'] == 'Rejected' ? 'danger' : 'warning') 
                            ?>">
                                <?= $schedule['Status'] ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-warning edit-btn" 
                                    data-id="<?= $schedule['JadwalID'] ?>"
                                    data-lab="<?= $schedule['LabID'] ?>"
                                    data-tanggal-mulai="<?= $schedule['TanggalMulai'] ?>"
                                    data-tanggal-selesai="<?= $schedule['TanggalSelesai'] ?>"
                                    data-jam-mulai="<?= $schedule['JamMulai'] ?>"
                                    data-jam-selesai="<?= $schedule['JamSelesai'] ?>"
                                    data-keperluan="<?= htmlspecialchars($schedule['Keperluan']) ?>"
                                    data-nama="<?= htmlspecialchars($schedule['NamaPengguna']) ?>"
                                    data-role="<?= $schedule['RolePengguna'] ?>"
                                    data-status="<?= $schedule['Status'] ?>"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editScheduleModal">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="?delete=<?= $schedule['JadwalID'] ?>" 
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addScheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Jadwal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Laboratorium</label>
                        <select class="form-select" name="lab_id" required>
                            <option value="">Pilih Laboratorium</option>
                            <?php foreach ($labs as $lab): ?>
                            <option value="<?= $lab['LabID'] ?>"><?= htmlspecialchars($lab['NamaLab']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" name="tanggal_mulai" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" class="form-control" name="tanggal_selesai" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jam Mulai</label>
                        <input type="time" class="form-control" name="jam_mulai" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jam Selesai</label>
                        <input type="time" class="form-control" name="jam_selesai" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keperluan</label>
                        <textarea class="form-control" name="keperluan" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Pengguna</label>
                        <input type="text" class="form-control" name="nama_pengguna" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role Pengguna</label>
                        <select class="form-select" name="role_pengguna" required>
                            <option value="">Pilih Role</option>
                            <option value="Mahasiswa">Mahasiswa</option>
                            <option value="Dosen">Dosen</option>
                            <option value="Staff Lab">Staff Lab</option>
                            <option value="Satpam">Satpam</option>
                        </select>
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
<div class="modal fade" id="editScheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Jadwal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Laboratorium</label>
                        <select class="form-select" name="lab_id" id="edit-lab" required>
                            <option value="">Pilih Laboratorium</option>
                            <?php foreach ($labs as $lab): ?>
                            <option value="<?= $lab['LabID'] ?>"><?= htmlspecialchars($lab['NamaLab']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" name="tanggal_mulai" id="edit-tanggal-mulai" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" class="form-control" name="tanggal_selesai" id="edit-tanggal-selesai" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jam Mulai</label>
                        <input type="time" class="form-control" name="jam_mulai" id="edit-jam-mulai" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jam Selesai</label>
                        <input type="time" class="form-control" name="jam_selesai" id="edit-jam-selesai" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keperluan</label>
                        <textarea class="form-control" name="keperluan" id="edit-keperluan" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Pengguna</label>
                        <input type="text" class="form-control" name="nama_pengguna" id="edit-nama" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role Pengguna</label>
                        <select class="form-select" name="role_pengguna" id="edit-role" required>
                            <option value="">Pilih Role</option>
                            <option value="Mahasiswa">Mahasiswa</option>
                            <option value="Dosen">Dosen</option>
                            <option value="Staff Lab">Staff Lab</option>
                            <option value="Satpam">Satpam</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" id="edit-status" required>
                            <option value="Pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Rejected</option>
                        </select>
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
            document.getElementById('edit-lab').value = this.dataset.lab;
            document.getElementById('edit-tanggal-mulai').value = this.dataset.tanggalMulai;
            document.getElementById('edit-tanggal-selesai').value = this.dataset.tanggalSelesai;
            document.getElementById('edit-jam-mulai').value = this.dataset.jamMulai;
            document.getElementById('edit-jam-selesai').value = this.dataset.jamSelesai;
            document.getElementById('edit-keperluan').value = this.dataset.keperluan;
            document.getElementById('edit-nama').value = this.dataset.nama;
            document.getElementById('edit-role').value = this.dataset.role;
            document.getElementById('edit-status').value = this.dataset.status;
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
