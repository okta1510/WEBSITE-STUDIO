<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get payment ID
$paymentId = $_GET['payment_id'] ?? null;
if (!$paymentId) {
    header('Location: my-reservations.php');
    exit();
}

// Get payment details
$payment = null;
try {
    $payment = fetchOne("
        SELECT p.*, r.*, pkg.name as package_name, u.full_name as customer_name
        FROM payments p
        JOIN reservations r ON p.reservation_id = r.id
        JOIN packages pkg ON r.package_id = pkg.id
        JOIN users u ON r.customer_id = u.id
        WHERE p.id = ? AND r.customer_id = ?
    ", [$paymentId, $_SESSION['user_id']]);
    
    if (!$payment) {
        header('Location: my-reservations.php');
        exit();
    }
} catch (Exception $e) {
    header('Location: my-reservations.php');
    exit();
}

$message = '';
$messageType = '';

// Handle file upload
if ($_POST && isset($_POST['upload_proof'])) {
    try {
        if (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Silakan pilih file bukti pembayaran");
        }
        
        $uploadDir = 'uploads/payment_proofs/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileExtension = strtolower(pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception("Format file tidak didukung. Gunakan JPG, PNG, atau PDF.");
        }
        
        if ($_FILES['payment_proof']['size'] > 5 * 1024 * 1024) { // 5MB
            throw new Exception("Ukuran file terlalu besar. Maksimal 5MB.");
        }
        
        $fileName = 'payment_' . $payment['reservation_id'] . '_' . $paymentId . '_' . time() . '.' . $fileExtension;
        $filePath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $filePath)) {
            // Update payment record with proof
            updateRecord('payments', ['payment_proof' => $fileName], 'id = :id', ['id' => $paymentId]);
            
            $message = 'Bukti pembayaran berhasil diupload! Menunggu konfirmasi admin.';
            $messageType = 'success';
            
            // Refresh payment data
            $payment = fetchOne("
                SELECT p.*, r.*, pkg.name as package_name, u.full_name as customer_name
                FROM payments p
                JOIN reservations r ON p.reservation_id = r.id
                JOIN packages pkg ON r.package_id = pkg.id
                JOIN users u ON r.customer_id = u.id
                WHERE p.id = ? AND r.customer_id = ?
            ", [$paymentId, $_SESSION['user_id']]);
        } else {
            throw new Exception("Gagal mengupload file. Silakan coba lagi.");
        }
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Bukti Pembayaran - Studio Foto Cekrek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-camera"></i> Studio Foto Cekrek
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#packages">Paket</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="schedule-studio.php">Jadwal Studio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#gallery">Portofolio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#contact">Kontak</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo $_SESSION['full_name']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php">
                                <i class="fas fa-user"></i> Profil
                            </a></li>
                            <li><a class="dropdown-item" href="booking.php">
                                <i class="fas fa-calendar-plus"></i> Booking
                            </a></li>
                            <li><a class="dropdown-item" href="my-reservations.php">
                                <i class="fas fa-list"></i> Reservasi Saya
                            </a></li>

                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5 pt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-upload"></i> Upload Bukti Pembayaran
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>

                        <!-- Payment Details -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Detail Pembayaran</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>ID Transaksi:</strong> <?php echo htmlspecialchars($payment['transaction_id']); ?></p>
                                        <p><strong>Reservasi:</strong> #<?php echo str_pad($payment['reservation_id'], 6, '0', STR_PAD_LEFT); ?></p>
                                        <p><strong>Paket:</strong> <?php echo htmlspecialchars($payment['package_name']); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Jumlah:</strong> Rp <?php echo number_format($payment['amount'], 0, ',', '.'); ?></p>
                                        <p><strong>Metode:</strong> <?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></p>
                                        <p><strong>Tanggal:</strong> <?php echo date('d/m/Y H:i', strtotime($payment['payment_date'])); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if ($payment['payment_method'] === 'cash'): ?>
                        <!-- Cash Payment - No Upload Required -->
                        <div class="alert alert-success">
                            <h6><i class="fas fa-check-circle"></i> Pembayaran Tunai</h6>
                            <p class="mb-2">Untuk pembayaran tunai, tidak perlu upload bukti pembayaran. Silakan datang ke studio sesuai jadwal yang telah ditentukan.</p>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="my-reservations.php" class="btn btn-primary">
                                    <i class="fas fa-arrow-left"></i> Kembali ke Reservasi
                                </a>
                            </div>
                        </div>
                        <?php elseif ($payment['payment_proof']): ?>
                        <!-- Existing Proof -->
                        <div class="alert alert-success">
                            <h6><i class="fas fa-check-circle"></i> Bukti Pembayaran Sudah Diupload</h6>
                            <p class="mb-2">Anda sudah mengupload bukti pembayaran untuk transaksi ini.</p>
                            <a href="view-payment-proof.php?payment_id=<?php echo $paymentId; ?>" target="_blank" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-eye"></i> Lihat Bukti
                            </a>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Upload Bukti Baru (Opsional)</h6>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">Jika Anda ingin mengganti bukti pembayaran yang sudah diupload, Anda dapat mengupload file baru di bawah ini.</p>

                                <form method="POST" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label class="form-label">Pilih File Bukti Pembayaran *</label>
                                        <input type="file" class="form-control" name="payment_proof"
                                               accept=".jpg,.jpeg,.png,.pdf" required>
                                        <small class="form-text text-muted">
                                            Format: JPG, PNG, PDF. Maksimal 5MB.
                                        </small>
                                    </div>

                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <a href="my-reservations.php" class="btn btn-secondary me-md-2">
                                            <i class="fas fa-arrow-left"></i> Kembali
                                        </a>
                                        <button type="submit" name="upload_proof" class="btn btn-primary">
                                            <i class="fas fa-upload"></i> Upload Bukti
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php else: ?>
                        <!-- Upload Form -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Upload Bukti Pembayaran</h6>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle"></i> Petunjuk Upload</h6>
                                    <ul class="mb-0">
                                        <li>Upload screenshot atau foto bukti transfer</li>
                                        <li>Format yang didukung: JPG, PNG, PDF</li>
                                        <li>Ukuran maksimal: 5MB</li>
                                        <li>Pastikan bukti pembayaran jelas dan terbaca</li>
                                    </ul>
                                </div>

                                <form method="POST" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label class="form-label">Pilih File Bukti Pembayaran *</label>
                                        <input type="file" class="form-control" name="payment_proof"
                                               accept=".jpg,.jpeg,.png,.pdf" required>
                                        <small class="form-text text-muted">
                                            Format: JPG, PNG, PDF. Maksimal 5MB.
                                        </small>
                                    </div>

                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <a href="my-reservations.php" class="btn btn-secondary me-md-2">
                                            <i class="fas fa-arrow-left"></i> Kembali
                                        </a>
                                        <button type="submit" name="upload_proof" class="btn btn-primary">
                                            <i class="fas fa-upload"></i> Upload Bukti
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Payment Instructions -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h6 class="mb-0">Informasi Pembayaran</h6>
                            </div>
                            <div class="card-body">
                                <?php if ($payment['payment_method'] === 'bank_transfer'): ?>
                                <h6>Rekening Studio Foto Cekrek:</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <p><strong>Bank BCA</strong><br>
                                        1234567890<br>
                                        a.n. Studio Foto Cekrek</p>
                                    </div>
                                    <div class="col-md-4">
                                        <p><strong>Bank Mandiri</strong><br>
                                        0987654321<br>
                                        a.n. Studio Foto Cekrek</p>
                                    </div>
                                    <div class="col-md-4">
                                        <p><strong>Bank BNI</strong><br>
                                        1122334455<br>
                                        a.n. Studio Foto Cekrek</p>
                                    </div>
                                </div>
                                <?php elseif ($payment['payment_method'] === 'e_wallet'): ?>
                                <div class="alert alert-success">
                                    <h6><i class="fas fa-mobile-alt"></i> Pembayaran E-Wallet dengan QRIS</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="text-center">
                                                <img src="assets/images/qris-code.jpg" alt="QRIS Code" class="img-fluid" style="max-width: 200px; border: 2px solid #ddd; border-radius: 10px;">
                                                <p class="mt-2"><strong>Scan QRIS Code</strong></p>
                                                <p class="small text-muted">Gunakan aplikasi e-wallet favorit Anda untuk scan QRIS di atas</p>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <h6><i class="fas fa-mobile-alt"></i> Atau transfer manual ke:</h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3 p-2 border rounded" style="background: #f8f9fa;">
                                                        <div class="d-flex align-items-center mb-1">
                                                            <img src="https://cdn.jsdelivr.net/gh/simple-icons/simple-icons/icons/ovo.svg" alt="OVO" style="width: 20px; height: 20px; margin-right: 8px;">
                                                            <strong style="color: #4c63d2;">OVO</strong>
                                                        </div>
                                                        <p class="mb-0 small">0812-3456-7890</p>
                                                        <p class="mb-0 small text-muted">a.n. Studio Foto Cekrek</p>
                                                    </div>

                                                    <div class="mb-3 p-2 border rounded" style="background: #f8f9fa;">
                                                        <div class="d-flex align-items-center mb-1">
                                                            <div style="width: 20px; height: 20px; background: #00aa5b; border-radius: 50%; margin-right: 8px; display: flex; align-items: center; justify-content: center;">
                                                                <span style="color: white; font-size: 10px; font-weight: bold;">G</span>
                                                            </div>
                                                            <strong style="color: #00aa5b;">GoPay</strong>
                                                        </div>
                                                        <p class="mb-0 small">0812-3456-7890</p>
                                                        <p class="mb-0 small text-muted">a.n. Studio Foto Cekrek</p>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="mb-3 p-2 border rounded" style="background: #f8f9fa;">
                                                        <div class="d-flex align-items-center mb-1">
                                                            <div style="width: 20px; height: 20px; background: #118eea; border-radius: 4px; margin-right: 8px; display: flex; align-items: center; justify-content: center;">
                                                                <span style="color: white; font-size: 10px; font-weight: bold;">D</span>
                                                            </div>
                                                            <strong style="color: #118eea;">DANA</strong>
                                                        </div>
                                                        <p class="mb-0 small">0812-3456-7890</p>
                                                        <p class="mb-0 small text-muted">a.n. Studio Foto Cekrek</p>
                                                    </div>

                                                    <div class="mb-3 p-2 border rounded" style="background: #f8f9fa;">
                                                        <div class="d-flex align-items-center mb-1">
                                                            <div style="width: 20px; height: 20px; background: #ff6b35; border-radius: 4px; margin-right: 8px; display: flex; align-items: center; justify-content: center;">
                                                                <span style="color: white; font-size: 10px; font-weight: bold;">S</span>
                                                            </div>
                                                            <strong style="color: #ff6b35;">ShopeePay</strong>
                                                        </div>
                                                        <p class="mb-0 small">0812-3456-7890</p>
                                                        <p class="mb-0 small text-muted">a.n. Studio Foto Cekrek</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="alert alert-info mt-3">
                                                <small><i class="fas fa-info-circle"></i> <strong>Tips:</strong> Setelah transfer, jangan lupa upload bukti pembayaran di bawah ini untuk konfirmasi.</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php elseif ($payment['payment_method'] === 'cash'): ?>
                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-money-bill-wave"></i> Pembayaran Tunai</h6>
                                    <p class="mb-2">Silakan datang ke studio untuk melakukan pembayaran tunai.</p>
                                    <div class="alert alert-light border">
                                        <h6><i class="fas fa-map-marker-alt"></i> Alamat Studio:</h6>
                                        <p class="mb-1"><strong>Studio Foto Cekrek</strong></p>
                                        <p class="mb-1">Jl. Contoh No. 123, Jakarta</p>
                                        <p class="mb-1"><i class="fas fa-phone"></i> 0812-3456-7890</p>
                                        <p class="mb-0"><i class="fas fa-clock"></i> Buka: Senin-Minggu 09:00-17:00</p>
                                    </div>
                                    <p class="mb-0 text-success"><strong>Tidak perlu upload bukti pembayaran untuk metode tunai.</strong></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
