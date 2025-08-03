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

// Get reservation details with payments
$reservation = null;
$payments = [];

try {
    $reservation = fetchOne("
        SELECT r.*, p.name as package_name, p.description as package_description,
               u.full_name as customer_name, u.email as customer_email, u.phone as customer_phone,
               u.address as customer_address
        FROM reservations r
        JOIN packages p ON r.package_id = p.id
        JOIN users u ON r.customer_id = u.id
        WHERE r.id = ? AND r.customer_id = ?
    ", [$reservationId, $_SESSION['user_id']]);
    
    if (!$reservation) {
        header('Location: my-reservations.php');
        exit();
    }
    
    $payments = fetchAll("SELECT * FROM payments WHERE reservation_id = ? AND status = 'completed' ORDER BY payment_date ASC", [$reservationId]);
    
} catch (Exception $e) {
    header('Location: my-reservations.php');
    exit();
}

// Calculate totals
$totalPaid = array_sum(array_column($payments, 'amount'));
$remainingAmount = $reservation['total_amount'] - $totalPaid;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - Studio Foto Cekrek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            .container { max-width: none !important; }
        }
        .invoice-header {
            border-bottom: 2px solid #007bff;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }
        .invoice-footer {
            border-top: 1px solid #dee2e6;
            padding-top: 1rem;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Print Controls -->
                <div class="no-print mb-3">
                    <div class="d-flex justify-content-between">
                        <a href="my-reservations.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <button onclick="window.print()" class="btn btn-primary">
                            <i class="fas fa-print"></i> Cetak Invoice
                        </button>
                    </div>
                </div>

                <!-- Invoice -->
                <div class="card">
                    <div class="card-body">
                        <!-- Header -->
                        <div class="invoice-header">
                            <div class="row">
                                <div class="col-md-6">
                                    <h2 class="text-primary">
                                        <i class="fas fa-camera"></i> Studio Foto Cekrek
                                    </h2>
                                    <p class="mb-1">Jl. Contoh No. 123, Jakarta</p>
                                    <p class="mb-1">Telp: 021-12345678</p>
                                    <p class="mb-0">Email: info@studiofotocekrek.com</p>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <h3>INVOICE</h3>
                                    <p class="mb-1"><strong>No. Invoice:</strong> INV-<?php echo str_pad($reservation['id'], 6, '0', STR_PAD_LEFT); ?></p>
                                    <p class="mb-1"><strong>Tanggal:</strong> <?php echo date('d/m/Y'); ?></p>
                                    <p class="mb-0"><strong>Jatuh Tempo:</strong> <?php echo date('d/m/Y', strtotime($reservation['reservation_date'])); ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Info -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>Tagihan Kepada:</h5>
                                <p class="mb-1"><strong><?php echo htmlspecialchars($reservation['customer_name']); ?></strong></p>
                                <p class="mb-1"><?php echo htmlspecialchars($reservation['customer_email']); ?></p>
                                <?php if ($reservation['customer_phone']): ?>
                                <p class="mb-1"><?php echo htmlspecialchars($reservation['customer_phone']); ?></p>
                                <?php endif; ?>
                                <?php if ($reservation['customer_address']): ?>
                                <p class="mb-0"><?php echo htmlspecialchars($reservation['customer_address']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h5>Detail Reservasi:</h5>
                                <p class="mb-1"><strong>ID Reservasi:</strong> #<?php echo str_pad($reservation['id'], 6, '0', STR_PAD_LEFT); ?></p>
                                <p class="mb-1"><strong>Tanggal Sesi:</strong> <?php echo date('d/m/Y', strtotime($reservation['reservation_date'])); ?></p>
                                <p class="mb-1"><strong>Waktu:</strong> <?php echo date('H:i', strtotime($reservation['start_time'])); ?> - <?php echo date('H:i', strtotime($reservation['end_time'])); ?></p>
                                <?php if ($reservation['location']): ?>
                                <p class="mb-0"><strong>Lokasi:</strong> <?php echo htmlspecialchars($reservation['location']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Service Details -->
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Deskripsi</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Harga</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($reservation['package_name']); ?></strong>
                                            <?php if ($reservation['package_description']): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($reservation['package_description']); ?></small>
                                            <?php endif; ?>
                                            <?php if ($reservation['special_requests']): ?>
                                            <br><small class="text-muted">Permintaan khusus: <?php echo htmlspecialchars($reservation['special_requests']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">1</td>
                                        <td class="text-end">Rp <?php echo number_format($reservation['total_amount'], 0, ',', '.'); ?></td>
                                        <td class="text-end">Rp <?php echo number_format($reservation['total_amount'], 0, ',', '.'); ?></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-end">Subtotal:</th>
                                        <th class="text-end">Rp <?php echo number_format($reservation['total_amount'], 0, ',', '.'); ?></th>
                                    </tr>
                                    <tr>
                                        <th colspan="3" class="text-end">Total:</th>
                                        <th class="text-end text-primary">Rp <?php echo number_format($reservation['total_amount'], 0, ',', '.'); ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Payment History -->
                        <?php if (!empty($payments)): ?>
                        <h5>Riwayat Pembayaran</h5>
                        <div class="table-responsive mb-4">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>ID Transaksi</th>
                                        <th>Metode</th>
                                        <th class="text-end">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y H:i', strtotime($payment['payment_date'])); ?></td>
                                        <td><?php echo htmlspecialchars($payment['transaction_id']); ?></td>
                                        <td><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></td>
                                        <td class="text-end">Rp <?php echo number_format($payment['amount'], 0, ',', '.'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="table-success">
                                        <th colspan="3" class="text-end">Total Dibayar:</th>
                                        <th class="text-end">Rp <?php echo number_format($totalPaid, 0, ',', '.'); ?></th>
                                    </tr>
                                    <?php if ($remainingAmount > 0): ?>
                                    <tr class="table-warning">
                                        <th colspan="3" class="text-end">Sisa Tagihan:</th>
                                        <th class="text-end">Rp <?php echo number_format($remainingAmount, 0, ',', '.'); ?></th>
                                    </tr>
                                    <?php endif; ?>
                                </tfoot>
                            </table>
                        </div>
                        <?php endif; ?>

                        <!-- Payment Status -->
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Status Pembayaran:</h6>
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
                                <span class="badge bg-<?php echo $color; ?> fs-6"><?php echo $label; ?></span>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <?php if ($remainingAmount <= 0): ?>
                                <p class="text-success mb-0"><strong>✓ LUNAS</strong></p>
                                <?php else: ?>
                                <p class="text-danger mb-0"><strong>Sisa: Rp <?php echo number_format($remainingAmount, 0, ',', '.'); ?></strong></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="invoice-footer">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Catatan:</h6>
                                    <p class="small text-muted">
                                        - Pembayaran dapat dilakukan melalui transfer bank atau tunai di studio<br>
                                        - Sesi foto akan dilaksanakan setelah pembayaran dikonfirmasi<br>
                                        - Untuk pertanyaan hubungi customer service kami
                                    </p>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <p class="small text-muted">
                                        Terima kasih atas kepercayaan Anda<br>
                                        <strong>Studio Foto Cekrek</strong>
                                    </p>
                                </div>
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
