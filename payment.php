<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get reservation ID
$reservationId = $_GET['reservation_id'] ?? null;
if (!$reservationId) {
    header('Location: my-reservations.php');
    exit();
}

// Get reservation details
$reservation = null;
try {
    $reservation = fetchOne("
        SELECT r.*, p.name as package_name, p.description as package_description,
               u.full_name as customer_name, u.email as customer_email
        FROM reservations r
        JOIN packages p ON r.package_id = p.id
        JOIN users u ON r.customer_id = u.id
        WHERE r.id = ? AND r.customer_id = ?
    ", [$reservationId, $_SESSION['user_id']]);
    
    if (!$reservation) {
        header('Location: my-reservations.php');
        exit();
    }
} catch (Exception $e) {
    header('Location: my-reservations.php');
    exit();
}

// Handle payment submission
$message = '';
$messageType = '';

if ($_POST && isset($_POST['submit_payment'])) {
    try {
        $paymentMethod = $_POST['payment_method'];
        $amount = $_POST['amount'];
        $bankName = $_POST['bank_name'] ?? '';
        $accountNumber = $_POST['account_number'] ?? '';
        $accountName = $_POST['account_name'] ?? '';
        $notes = $_POST['notes'] ?? '';

        // Validate amount
        if ($amount < 1000) {
            throw new Exception("Minimal pembayaran Rp 1.000");
        }

        if ($amount > $reservation['total_amount']) {
            throw new Exception("Jumlah pembayaran tidak boleh melebihi total tagihan");
        }

        // Note: Bukti pembayaran akan diupload setelah pembayaran dibuat

        // Generate transaction ID
        $transactionId = 'TRX' . date('YmdHis') . rand(100, 999);
        
        // Insert payment record
        $paymentData = [
            'reservation_id' => (int)$reservationId,
            'amount' => (float)$amount,
            'payment_method' => $paymentMethod,
            'transaction_id' => $transactionId,
            'notes' => $notes,
            'status' => 'pending' // Admin needs to confirm
        ];

        // Add bank details if bank transfer
        if ($paymentMethod === 'bank_transfer') {
            $paymentData['notes'] = "Bank: $bankName, Rekening: $accountNumber, Nama: $accountName. $notes";
        }

        // Debug: Log payment data
        error_log("Payment data: " . print_r($paymentData, true));

        $paymentId = insertRecord('payments', $paymentData);

        if (!$paymentId) {
            throw new Exception("Failed to create payment record");
        }

        // Update reservation payment status
        $totalPaid = fetchOne("SELECT SUM(amount) as total FROM payments WHERE reservation_id = ? AND status IN ('completed', 'pending')", [$reservationId])['total'] ?? 0;

        $paymentStatus = 'unpaid';
        if ($totalPaid >= $reservation['total_amount']) {
            $paymentStatus = 'paid';
        } elseif ($totalPaid > 0) {
            $paymentStatus = 'partial';
        }

        updateRecord('reservations', ['payment_status' => $paymentStatus], 'id = :id', ['id' => $reservationId]);

        // Redirect to upload proof page
        // Redirect based on payment method
        if ($paymentMethod === 'cash') {
            header('Location: my-reservations.php?payment_success=1');
        } else {
            header('Location: upload-proof.php?payment_id=' . $paymentId);
        }
        exit();
        
        // Refresh reservation data
        $reservation = fetchOne("
            SELECT r.*, p.name as package_name, p.description as package_description,
                   u.full_name as customer_name, u.email as customer_email
            FROM reservations r
            JOIN packages p ON r.package_id = p.id
            JOIN users u ON r.customer_id = u.id
            WHERE r.id = ? AND r.customer_id = ?
        ", [$reservationId, $_SESSION['user_id']]);
        
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

// Get payment history
$payments = [];
try {
    $payments = fetchAll("SELECT * FROM payments WHERE reservation_id = ? ORDER BY payment_date DESC", [$reservationId]);
} catch (Exception $e) {
    $payments = [];
}

// Calculate remaining amount
$totalPaid = 0;
foreach ($payments as $payment) {
    if ($payment['status'] === 'completed' || $payment['status'] === 'pending') {
        $totalPaid += $payment['amount'];
    }
}
$remainingAmount = $reservation['total_amount'] - $totalPaid;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - Studio Foto Cekrek</title>
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
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User'); ?>
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
                            <i class="fas fa-credit-card"></i> Pembayaran Reservasi
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>

                        <!-- Reservation Details -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>Detail Reservasi</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>ID Reservasi:</strong></td>
                                        <td>#<?php echo str_pad($reservation['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Paket:</strong></td>
                                        <td><?php echo htmlspecialchars($reservation['package_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tanggal:</strong></td>
                                        <td><?php echo date('d/m/Y', strtotime($reservation['reservation_date'])); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Waktu:</strong></td>
                                        <td><?php echo date('H:i', strtotime($reservation['start_time'])); ?> - <?php echo date('H:i', strtotime($reservation['end_time'])); ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h5>Informasi Pembayaran</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Total Tagihan:</strong></td>
                                        <td class="text-primary"><strong>Rp <?php echo number_format($reservation['total_amount'], 0, ',', '.'); ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Sudah Dibayar:</strong></td>
                                        <td class="text-success">Rp <?php echo number_format($totalPaid, 0, ',', '.'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Sisa Tagihan:</strong></td>
                                        <td class="text-danger"><strong>Rp <?php echo number_format($remainingAmount, 0, ',', '.'); ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td>
                                            <?php
                                            $statusColors = [
                                                'unpaid' => 'danger',
                                                'partial' => 'warning',
                                                'paid' => 'success'
                                            ];
                                            $statusLabels = [
                                                'unpaid' => 'Belum Bayar',
                                                'partial' => 'Sebagian',
                                                'paid' => 'Lunas'
                                            ];
                                            $color = $statusColors[$reservation['payment_status']] ?? 'secondary';
                                            $label = $statusLabels[$reservation['payment_status']] ?? 'Unknown';
                                            ?>
                                            <span class="badge bg-<?php echo $color; ?>"><?php echo $label; ?></span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <?php if ($remainingAmount > 0): ?>
                        <!-- Payment Form -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Form Pembayaran</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Jumlah Pembayaran *</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="number" class="form-control" name="amount" 
                                                       min="1000" max="<?php echo $remainingAmount; ?>" 
                                                       value="<?php echo $remainingAmount; ?>" required>
                                            </div>
                                            <small class="form-text text-muted">Minimal Rp 1.000</small>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Metode Pembayaran *</label>
                                            <select class="form-select" name="payment_method" id="paymentMethod" required>
                                                <option value="">Pilih Metode</option>
                                                <option value="bank_transfer">Transfer Bank</option>
                                                <option value="cash">Tunai (Bayar di Studio)</option>
                                                <option value="e_wallet">E-Wallet</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Bank Transfer Details -->
                                    <div id="bankDetails" style="display: none;">
                                        <div class="alert alert-info">
                                            <h6>Informasi Rekening Studio:</h6>
                                            <p class="mb-1"><strong>Bank BCA:</strong> 1234567890 a.n. Studio Foto Cekrek</p>
                                            <p class="mb-1"><strong>Bank Mandiri:</strong> 0987654321 a.n. Studio Foto Cekrek</p>
                                            <p class="mb-0"><strong>Bank BNI:</strong> 1122334455 a.n. Studio Foto Cekrek</p>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Nama Bank</label>
                                                <input type="text" class="form-control" name="bank_name" placeholder="Contoh: BCA">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Nomor Rekening</label>
                                                <input type="text" class="form-control" name="account_number" placeholder="Nomor rekening Anda">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Nama Pemilik</label>
                                                <input type="text" class="form-control" name="account_name" placeholder="Nama di rekening">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- E-Wallet Details -->
                                    <div id="ewalletDetails" style="display: none;">
                                        <div class="alert alert-success">
                                            <h6><i class="fas fa-mobile-alt"></i> Informasi E-Wallet Studio:</h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-2">
                                                        <strong style="color: #4c63d2;">OVO:</strong> 0812-3456-7890<br>
                                                        <small class="text-muted">a.n. Studio Foto Cekrek</small>
                                                    </div>
                                                    <div class="mb-2">
                                                        <strong style="color: #00aa5b;">GoPay:</strong> 0812-3456-7890<br>
                                                        <small class="text-muted">a.n. Studio Foto Cekrek</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-2">
                                                        <strong style="color: #118eea;">DANA:</strong> 0812-3456-7890<br>
                                                        <small class="text-muted">a.n. Studio Foto Cekrek</small>
                                                    </div>
                                                    <div class="mb-2">
                                                        <strong style="color: #ff6b35;">ShopeePay:</strong> 0812-3456-7890<br>
                                                        <small class="text-muted">a.n. Studio Foto Cekrek</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-2">
                                                <small><i class="fas fa-qrcode"></i> <strong>QRIS tersedia</strong> - Scan QRIS code setelah pembayaran dibuat</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Cash Payment Details -->
                                    <div id="cashDetails" style="display: none;">
                                        <div class="alert alert-warning">
                                            <h6><i class="fas fa-money-bill-wave"></i> Pembayaran Tunai di Studio</h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p><strong>Alamat Studio:</strong><br>
                                                    Studio Foto Cekrek<br>
                                                    Jl. Contoh No. 123, Jakarta</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p><strong>Kontak:</strong><br>
                                                    <i class="fas fa-phone"></i> 0812-3456-7890<br>
                                                    <i class="fas fa-clock"></i> Senin-Minggu 09:00-17:00</p>
                                                </div>
                                            </div>
                                            <div class="mt-2">
                                                <small><i class="fas fa-info-circle"></i> <strong>Tidak perlu upload bukti</strong> untuk pembayaran tunai</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Catatan</label>
                                        <textarea class="form-control" name="notes" rows="3"
                                                  placeholder="Catatan tambahan (opsional)"></textarea>
                                    </div>

                                    <div class="alert alert-info">
                                        <h6><i class="fas fa-info-circle"></i> Langkah Selanjutnya</h6>
                                        <p class="mb-0">Setelah mengklik "Buat Pembayaran", Anda akan diarahkan ke halaman upload bukti pembayaran.</p>
                                    </div>

                                    <div class="d-grid">
                                        <button type="submit" name="submit_payment" class="btn btn-primary btn-lg">
                                            <i class="fas fa-credit-card"></i> Buat Pembayaran
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-success text-center">
                            <h5><i class="fas fa-check-circle"></i> Pembayaran Lunas</h5>
                            <p class="mb-0">Semua pembayaran untuk reservasi ini sudah lunas.</p>
                        </div>
                        <?php endif; ?>

                        <!-- Payment History -->
                        <?php if (!empty($payments)): ?>
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Riwayat Pembayaran</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>ID Transaksi</th>
                                                <th>Metode</th>
                                                <th>Jumlah</th>
                                                <th>Bukti</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($payments as $payment): ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y H:i', strtotime($payment['payment_date'])); ?></td>
                                                <td><?php echo htmlspecialchars($payment['transaction_id']); ?></td>
                                                <td><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></td>
                                                <td>Rp <?php echo number_format($payment['amount'], 0, ',', '.'); ?></td>
                                                <td>
                                                    <?php if ($payment['payment_proof']): ?>
                                                    <a href="view-payment-proof.php?payment_id=<?php echo $payment['id']; ?>"
                                                       target="_blank" class="btn btn-sm btn-outline-info">
                                                        <i class="fas fa-eye"></i> Lihat
                                                    </a>
                                                    <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $statusColors = [
                                                        'pending' => 'warning',
                                                        'completed' => 'success',
                                                        'failed' => 'danger'
                                                    ];
                                                    $statusLabels = [
                                                        'pending' => 'Menunggu Konfirmasi',
                                                        'completed' => 'Berhasil',
                                                        'failed' => 'Gagal'
                                                    ];
                                                    $color = $statusColors[$payment['status']] ?? 'secondary';
                                                    $label = $statusLabels[$payment['status']] ?? 'Unknown';
                                                    ?>
                                                    <span class="badge bg-<?php echo $color; ?>"><?php echo $label; ?></span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('paymentMethod').addEventListener('change', function() {
            const bankDetails = document.getElementById('bankDetails');
            const ewalletDetails = document.getElementById('ewalletDetails');
            const cashDetails = document.getElementById('cashDetails');

            // Hide all details first
            bankDetails.style.display = 'none';
            ewalletDetails.style.display = 'none';
            cashDetails.style.display = 'none';

            // Show relevant details based on selection
            if (this.value === 'bank_transfer') {
                bankDetails.style.display = 'block';
            } else if (this.value === 'e_wallet') {
                ewalletDetails.style.display = 'block';
            } else if (this.value === 'cash') {
                cashDetails.style.display = 'block';
            }
        });
    </script>
</body>
</html>
