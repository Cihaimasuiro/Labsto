<?php
require_once 'config/database.php';
include 'includes/header.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        // Get image filename before deleting record
        $stmt = $pdo->prepare("SELECT Gambar FROM Peralatan WHERE PeralatanID = ?");
        $stmt->execute([$id]);
        $gambar = $stmt->fetchColumn();
        
        // Delete image file if exists
        if ($gambar && file_exists("assets/uploads/" . $gambar)) {
            unlink("assets/uploads/" . $gambar);
        }
        
        $stmt = $pdo->prepare("DELETE FROM Peralatan WHERE PeralatanID = ?");
        $stmt->execute([$id]);
        header("Location: peralatan.php?msg=deleted");
        exit();
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $gambar = null;
    
    // Handle image upload
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['gambar']['name'];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($file_ext, $allowed)) {
            $new_filename = uniqid() . '.' . $file_ext;
            $upload_path = "assets/uploads/" . $new_filename;
            
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_path)) {
                $gambar = $new_filename;
            }
        }
    }
    
    if (isset($_POST['id'])) {
        // Update
        try {
            // If new image is uploaded, delete old image
            if ($gambar) {
                $stmt = $pdo->prepare("SELECT Gambar FROM Peralatan WHERE PeralatanID = ?");
                $stmt->execute([$_POST['id']]);
                $old_gambar = $stmt->fetchColumn();
                
                if ($old_gambar && file_exists("assets/uploads/" . $old_gambar)) {
                    unlink("assets/uploads/" . $old_gambar);
                }
                
                $stmt = $pdo->prepare("UPDATE Peralatan SET NamaPeralatan = ?, Gambar = ?, LabID = ?, Kondisi = ?, TanggalPembelian = ? WHERE PeralatanID = ?");
                $stmt->execute([$_POST['nama'], $gambar, $_POST['lab_id'], $_POST['kondisi'], $_POST['tanggal_pembelian'], $_POST['id']]);
            } else {
                $stmt = $pdo->prepare("UPDATE Peralatan SET NamaPeralatan = ?, LabID = ?, Kondisi = ?, TanggalPembelian = ? WHERE PeralatanID = ?");
                $stmt->execute([$_POST['nama'], $_POST['lab_id'], $_POST['kondisi'], $_POST['tanggal_pembelian'], $_POST['id']]);
            }
            header("Location: peralatan.php?msg=updated");
            exit();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {
        // Insert
        try {
            $stmt = $pdo->prepare("INSERT INTO Peralatan (NamaPeralatan, Gambar, LabID, Kondisi, TanggalPembelian) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$_POST['nama'], $gambar, $_POST['lab_id'], $_POST['kondisi'], $_POST['tanggal_pembelian']]);
            header("Location: peralatan.php?msg=added");
            exit();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}

// Get all equipment with lab names
$stmt = $pdo->query("SELECT p.*, l.NamaLab FROM Peralatan p 
                     LEFT JOIN Laboratorium l ON p.LabID = l.LabID 
                     ORDER BY p.PeralatanID DESC");
$equipment = $stmt->fetchAll();

// Get all labs for dropdown
$stmt = $pdo->query("SELECT * FROM Laboratorium ORDER BY NamaLab");
$labs = $stmt->fetchAll();
?>
<div class="card" >
    <div class="row mb-3">
        <div class="col-md-6">
            <h2>Data Peralatan</h2>
        </div>
        <div class="col-md-6 text-end">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEquipModal">
                <i class="fas fa-plus"></i> Tambah Peralatan
            </button>
        </div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php 
        $msg = $_GET['msg'];
        switch($msg) {
            case 'added':
                echo "Peralatan berhasil ditambahkan.";
                break;
            case 'updated':
                echo "Peralatan berhasil diperbarui.";
                break;
            case 'deleted':
                echo "Peralatan berhasil dihapus.";
                break;
        }
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <?php foreach ($equipment as $equip): ?>
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-img-top text-center p-3">
                <?php if ($equip['Gambar']): ?>
                    <img src="assets/uploads/<?= htmlspecialchars($equip['Gambar']) ?>" 
                         alt="<?= htmlspecialchars($equip['NamaPeralatan']) ?>"
                         class="img-fluid" style="max-height: 200px; object-fit: contain;">
                <?php else: ?>
                    <img src="assets/images/no-image.png" 
                         alt="No Image Available"
                         class="img-fluid" style="max-height: 200px; object-fit: contain;">
                <?php endif; ?>
            </div>
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($equip['NamaPeralatan']) ?></h5>
                <p class="card-text">
                    <strong>Laboratorium:</strong> <?= htmlspecialchars($equip['NamaLab'] ?? 'Tidak Ada') ?><br>
                    <strong>Kondisi:</strong> 
                    <span class="badge bg-<?= 
                        $equip['Kondisi'] == 'Baik' ? 'success' : 
                        ($equip['Kondisi'] == 'Rusak' ? 'danger' : 'warning') 
                    ?>"><?= $equip['Kondisi'] ?></span><br>
                    <strong>Tanggal Pembelian:</strong> <?= $equip['TanggalPembelian'] ?>
                </p>
                <div class="btn-group">
                    <button class="btn btn-sm btn-warning edit-btn" 
                            data-id="<?= $equip['PeralatanID'] ?>"
                            data-nama="<?= htmlspecialchars($equip['NamaPeralatan']) ?>"
                            data-lab="<?= $equip['LabID'] ?>"
                            data-kondisi="<?= $equip['Kondisi'] ?>"
                            data-tanggal="<?= $equip['TanggalPembelian'] ?>"
                            data-bs-toggle="modal" 
                            data-bs-target="#editEquipModal">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <a href="?delete=<?= $equip['PeralatanID'] ?>" class="btn btn-sm btn-danger" 
                       onclick="return confirm('Apakah Anda yakin ingin menghapus peralatan ini?')">
                        <i class="fas fa-trash"></i> Hapus
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addEquipModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Peralatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Peralatan</label>
                        <input type="text" class="form-control" name="nama" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gambar Peralatan</label>
                        <input type="file" class="form-control" name="gambar" accept="image/*">
                        <small class="text-muted">Format yang diizinkan: JPG, JPEG, PNG, GIF</small>
                    </div>
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
                        <label class="form-label">Kondisi</label>
                        <select class="form-select" name="kondisi" required>
                            <option value="Baik">Baik</option>
                            <option value="Rusak">Rusak</option>
                            <option value="Perbaikan">Perbaikan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Pembelian</label>
                        <input type="date" class="form-control" name="tanggal_pembelian" required>
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
<div class="modal fade" id="editEquipModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Peralatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Peralatan</label>
                        <input type="text" class="form-control" name="nama" id="edit-nama" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gambar Peralatan</label>
                        <input type="file" class="form-control" name="gambar" accept="image/*">
                        <small class="text-muted">Biarkan kosong jika tidak ingin mengubah gambar</small>
                    </div>
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
                        <label class="form-label">Kondisi</label>
                        <select class="form-select" name="kondisi" id="edit-kondisi" required>
                            <option value="Baik">Baik</option>
                            <option value="Rusak">Rusak</option>
                            <option value="Perbaikan">Perbaikan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Pembelian</label>
                        <input type="date" class="form-control" name="tanggal_pembelian" id="edit-tanggal" required>
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
            document.getElementById('edit-lab').value = this.dataset.lab;
            document.getElementById('edit-kondisi').value = this.dataset.kondisi;
            document.getElementById('edit-tanggal').value = this.dataset.tanggal;
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
