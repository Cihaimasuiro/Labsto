<?php
require_once 'config/database.php';
require_once 'includes/fine_calculator.php';
include 'includes/header.php';

// Handle payment status update
if (isset($_POST['update_payment'])) {
    $denda_id = $_POST['denda_id'];
    $status = $_POST['status'];
    
    $stmt = $pdo->prepare("UPDATE Denda SET StatusPembayaran = ? WHERE DendaID = ?");
    $stmt->execute([$status, $denda_id]);
    
    header("Location: denda.php");
    exit();
}

// Handle form submission untuk menambah denda
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_denda'])) {
        $nama_peminjam = $_POST['nama_peminjam'];
        $peralatan_id = $_POST['peralatan_id'];
        $jenis_denda_id = $_POST['jenis_denda_id'];
        $tanggal_denda = $_POST['tanggal_denda'];
        $keterangan = $_POST['keterangan'];
        
        // Get peralatan details for lost item calculation
        $stmt = $pdo->prepare("SELECT HargaBarang FROM Peralatan WHERE PeralatanID = ?");
        $stmt->execute([$peralatan_id]);
        $peralatan = $stmt->fetch();
        
        // Calculate fine based on type
        if ($jenis_denda_id == 1) { // Keterlambatan
            $tanggal_kembali = $_POST['tanggal_kembali'];
            $total_denda = FineCalculator::calculateLateFine($tanggal_kembali, $tanggal_denda);
        } else if ($jenis_denda_id == 2) { // Kehilangan
            $total_denda = FineCalculator::calculateLostItemFine($peralatan['HargaBarang']);
        }
        
        // Create new peminjaman record first
        $stmt = $pdo->prepare("INSERT INTO Peminjaman (NamaPeminjam, PeralatanID, TanggalPinjam) VALUES (?, ?, ?)");
        $stmt->execute([$nama_peminjam, $peralatan_id, $tanggal_denda]);
        $peminjaman_id = $pdo->lastInsertId();
        
        // Then create denda record
        $stmt = $pdo->prepare("INSERT INTO Denda (PeminjamanID, JenisDendaID, TotalDenda, TanggalDenda, StatusPembayaran, Keterangan) VALUES (?, ?, ?, ?, 'Belum Dibayar', ?)");
        $stmt->execute([$peminjaman_id, $jenis_denda_id, $total_denda, $tanggal_denda, $keterangan]);
        
        header("Location: denda.php");
        exit();
    }
}

// Get denda list with related data
$stmt = $pdo->query("
   SELECT d.*, j.NamaDenda, j.BiayaDenda, p.NamaPeminjam, pr.NamaPeralatan, pr.HargaBarang
FROM Denda d
LEFT JOIN JenisDenda j ON d.JenisDendaID = j.DendaID
LEFT JOIN Peminjaman p ON d.PeminjamanID = p.PeminjamanID
LEFT JOIN Peralatan pr ON p.PeralatanID = pr.PeralatanID
ORDER BY d.TanggalDenda DESC
");
$denda_list = $stmt->fetchAll();

// Get jenis denda list
$stmt = $pdo->query("SELECT * FROM JenisDenda ORDER BY NamaDenda");
$jenis_denda = $stmt->fetchAll();

// Get peralatan list
$stmt = $pdo->query("SELECT * FROM Peralatan ORDER BY NamaPeralatan");
$peralatan_list = $stmt->fetchAll();
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Tambah Denda Baru</h6>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <form action="" method="POST" class="p-3">
                        <input type="hidden" name="add_denda" value="1">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nama_peminjam" class="form-label">Nama Peminjam</label>
                                    <input type="text" class="form-control" id="nama_peminjam" name="nama_peminjam" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="peralatan_id" class="form-label">Peralatan</label>
                                    <select class="form-select" id="peralatan_id" name="peralatan_id" required>
                                        <option value="">Pilih Peralatan</option>
                                        <?php foreach ($peralatan_list as $peralatan): ?>
                                        <option value="<?= $peralatan['PeralatanID'] ?>" data-harga="<?= $peralatan['HargaBarang'] ?>">
                                            <?= htmlspecialchars($peralatan['NamaPeralatan']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jenis_denda_id" class="form-label">Jenis Denda</label>
                                    <select class="form-select" id="jenis_denda_id" name="jenis_denda_id" required onchange="toggleTanggalKembali()">
                                        <option value="">Pilih Jenis Denda</option>
                                        <?php foreach ($jenis_denda as $jenis): ?>
                                        <option value="<?= $jenis['DendaID'] ?>"><?= htmlspecialchars($jenis['NamaDenda']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tanggal_denda" class="form-label">Tanggal Denda</label>
                                    <input type="date" class="form-control" id="tanggal_denda" name="tanggal_denda" required>
                                </div>
                            </div>
                        </div>
                        <div class="row" id="tanggal_kembali_container" style="display: none;">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tanggal_kembali" class="form-label">Tanggal Seharusnya Kembali</label>
                                    <input type="date" class="form-control" id="tanggal_kembali" name="tanggal_kembali">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="keterangan" class="form-label">Keterangan</label>
                                    <textarea class="form-control" id="keterangan" name="keterangan" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Simpan Denda</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header pb-0">
                    <h6>Daftar Denda</h6>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th>Nama Peminjam</th>
                                    <th>Peralatan</th>
                                    <th>Jenis Denda</th>
                                    <th>Total Denda</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($denda_list as $denda): ?>
                                <tr>
                                    <td><?= htmlspecialchars($denda['NamaPeminjam']) ?></td>
                                    <td><?= htmlspecialchars($denda['NamaPeralatan']) ?></td>
                                    <td><?= htmlspecialchars($denda['NamaDenda']) ?></td>
                                    <td>Rp <?= number_format($denda['TotalDenda'], 0, ',', '.') ?></td>
                                    <td><?= date('d/m/Y', strtotime($denda['TanggalDenda'])) ?></td>
                                    <td>
                                        <span class="badge <?= $denda['StatusPembayaran'] == 'Lunas' ? 'bg-success' : 'bg-warning' ?>">
                                            <?= $denda['StatusPembayaran'] ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($denda['Keterangan']) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm <?= $denda['StatusPembayaran'] == 'Lunas' ? 'btn-secondary' : 'btn-success' ?>" 
                                                onclick="updatePaymentStatus(<?= $denda['DendaID'] ?>, '<?= $denda['StatusPembayaran'] ?>')"
                                                <?= $denda['StatusPembayaran'] == 'Lunas' ? 'disabled' : '' ?>>
                                            <?= $denda['StatusPembayaran'] == 'Lunas' ? 'Sudah Lunas' : 'Bayar' ?>
                                        </button>
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

<!-- Modal Update Status Pembayaran -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Status Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="update_payment" value="1">
                    <input type="hidden" name="denda_id" id="modal_denda_id">
                    <div class="mb-3">
                        <label class="form-label">Status Pembayaran</label>
                        <select name="status" class="form-select" required>
                            <option value="Belum Dibayar">Belum Dibayar</option>
                            <option value="Lunas">Lunas</option>
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

<script>
function toggleTanggalKembali() {
    var jenisSelect = document.getElementById('jenis_denda_id');
    var tanggalContainer = document.getElementById('tanggal_kembali_container');
    var tanggalInput = document.getElementById('tanggal_kembali');
    
    // Show tanggal kembali field only for late return (assume DendaID 1 is for late return)
    if (jenisSelect.value == '1') {
        tanggalContainer.style.display = 'block';
        tanggalInput.required = true;
    } else {
        tanggalContainer.style.display = 'none';
        tanggalInput.required = false;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleTanggalKembali();
});

function updatePaymentStatus(dendaId, currentStatus) {
    const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
    document.getElementById('modal_denda_id').value = dendaId;
    
    // Set current status in select
    const statusSelect = document.querySelector('#paymentModal select[name="status"]');
    statusSelect.value = currentStatus;
    
    modal.show();
}
</script>

<?php include 'includes/footer.php'; ?>
