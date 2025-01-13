<?php
require_once 'config/database.php';
include 'includes/header.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM Laboratorium WHERE LabID = ?");
        $stmt->execute([$id]);
        header("Location: laboratorium.php?msg=deleted");
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
            $stmt = $pdo->prepare("UPDATE Laboratorium SET NamaLab = ?, Lokasi = ?, Kapasitas = ?, Deskripsi = ? WHERE LabID = ?");
            $stmt->execute([$_POST['nama'], $_POST['lokasi'], $_POST['kapasitas'], $_POST['deskripsi'], $_POST['id']]);
            header("Location: laboratorium.php?msg=updated");
            exit();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {
        // Insert
        try {
            $stmt = $pdo->prepare("INSERT INTO Laboratorium (NamaLab, Lokasi, Kapasitas, Deskripsi) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_POST['nama'], $_POST['lokasi'], $_POST['kapasitas'], $_POST['deskripsi']]);
            header("Location: laboratorium.php?msg=added");
            exit();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}

// Get all laboratories
$stmt = $pdo->query("SELECT * FROM Laboratorium ORDER BY LabID DESC");
$laboratories = $stmt->fetchAll();
?>

<div class="row mb-3">
    <div class="col-md-6">
        <h2>Data Laboratorium</h2>
    </div>
    <div class="col-md-6 text-end">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLabModal">
            Tambah Laboratorium
        </button>
    </div>
</div>

<?php if (isset($_GET['msg'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?php 
    $msg = $_GET['msg'];
    switch($msg) {
        case 'added':
            echo "Laboratorium berhasil ditambahkan.";
            break;
        case 'updated':
            echo "Laboratorium berhasil diperbarui.";
            break;
        case 'deleted':
            echo "Laboratorium berhasil dihapus.";
            break;
    }
    ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Lab</th>
                <th>Lokasi</th>
                <th>Kapasitas</th>
                <th>Deskripsi</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($laboratories as $lab): ?>
            <tr>
                <td><?= $lab['LabID'] ?></td>
                <td><?= htmlspecialchars($lab['NamaLab']) ?></td>
                <td><?= htmlspecialchars($lab['Lokasi']) ?></td>
                <td><?= $lab['Kapasitas'] ?></td>
                <td><?= htmlspecialchars($lab['Deskripsi']) ?></td>
                <td>
                    <button class="btn btn-sm btn-warning edit-btn" 
                            data-id="<?= $lab['LabID'] ?>"
                            data-nama="<?= htmlspecialchars($lab['NamaLab']) ?>"
                            data-lokasi="<?= htmlspecialchars($lab['Lokasi']) ?>"
                            data-kapasitas="<?= $lab['Kapasitas'] ?>"
                            data-deskripsi="<?= htmlspecialchars($lab['Deskripsi']) ?>"
                            data-bs-toggle="modal" 
                            data-bs-target="#editLabModal">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <a href="?delete=<?= $lab['LabID'] ?>" class="btn btn-sm btn-danger" 
                       onclick="return confirm('Apakah Anda yakin ingin menghapus laboratorium ini?')">
                        <i class="fas fa-trash"></i> Hapus
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addLabModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Laboratorium</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Laboratorium</label>
                        <input type="text" class="form-control" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lokasi</label>
                        <input type="text" class="form-control" name="lokasi" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kapasitas</label>
                        <input type="number" class="form-control" name="kapasitas" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" rows="3"></textarea>
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
<div class="modal fade" id="editLabModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Laboratorium</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Laboratorium</label>
                        <input type="text" class="form-control" name="nama" id="edit-nama" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lokasi</label>
                        <input type="text" class="form-control" name="lokasi" id="edit-lokasi" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kapasitas</label>
                        <input type="number" class="form-control" name="kapasitas" id="edit-kapasitas" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" id="edit-deskripsi" rows="3"></textarea>
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
            document.getElementById('edit-nama').value = this.dataset.nama;
            document.getElementById('edit-lokasi').value = this.dataset.lokasi;
            document.getElementById('edit-kapasitas').value = this.dataset.kapasitas;
            document.getElementById('edit-deskripsi').value = this.dataset.deskripsi;
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
