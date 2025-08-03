<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=my-reservations.php');
    exit();
}

// Initialize variables
$reservations = [];

try {
    // Get user's reservations
    $reservations = fetchAll("
        SELECT r.*, p.name as package_name, p.category, p.duration_hours, p.duration_minutes,
               p.price, p.includes, p.description,
               u.full_name as photographer_name
        FROM reservations r
        JOIN packages p ON r.package_id = p.id
        LEFT JOIN users u ON r.photographer_id = u.id
        WHERE r.customer_id = ?
        ORDER BY r.reservation_date DESC, r.start_time DESC
    ", [$_SESSION['user_id']]);

    if (!$reservations) {
        $reservations = [];
    }

    // Get payments without proof for each reservation
    foreach ($reservations as &$reservation) {
        $reservation['pending_payments'] = fetchAll("
            SELECT * FROM payments
            WHERE reservation_id = ? AND status = 'pending' AND (payment_proof IS NULL OR payment_proof = '')
            ORDER BY payment_date DESC
        ", [$reservation['id']]);
    }
} catch (Exception $e) {
    error_log("Database error in my-reservations.php: " . $e->getMessage());
    $reservations = [];
}

// Handle cancellation
if (isset($_POST['cancel_reservation'])) {
    $reservationId = $_POST['reservation_id'];
    
    // Check if reservation belongs to user and can be cancelled
    $reservation = fetchOne("
        SELECT * FROM reservations 
        WHERE id = ? AND customer_id = ? AND status IN ('pending', 'confirmed')
        AND reservation_date > CURDATE()
    ", [$reservationId, $_SESSION['user_id']]);
    
    if ($reservation) {
        updateRecord('reservations', 
            ['status' => 'cancelled'], 
            'id = :id', 
            ['id' => $reservationId]
        );
        
        header('Location: my-reservations.php?cancelled=1');
        exit();
    }
}

function getStatusBadge($status) {
    $badges = [
        'pending' => 'bg-warning',
        'confirmed' => 'bg-info',
        'in_progress' => 'bg-primary',
        'completed' => 'bg-success',
        'cancelled' => 'bg-danger'
    ];
    
    $labels = [
        'pending' => 'Menunggu',
        'confirmed' => 'Dikonfirmasi',
        'in_progress' => 'Berlangsung',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan'
    ];
    
    return '<span class="badge ' . $badges[$status] . '">' . $labels[$status] . '</span>';
}

function getPaymentStatusBadge($status) {
    $badges = [
        'unpaid' => 'bg-danger',
        'partial' => 'bg-warning',
        'paid' => 'bg-success',
        'refunded' => 'bg-secondary'
    ];
    
    $labels = [
        'unpaid' => 'Belum Bayar',
        'partial' => 'Sebagian',
        'paid' => 'Lunas',
        'refunded' => 'Dikembalikan'
    ];
    
    return '<span class="badge ' . $badges[$status] . '">' . $labels[$status] . '</span>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservasi Saya - Studio Foto Cekrek</title>
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
                            <li><a class="dropdown-item active" href="my-reservations.php">
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
        <div class="row">
            <div class="col-12">
                <?php if (isset($_GET['payment_success'])): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-clock"></i> <strong>Pembayaran Berhasil Dicatat!</strong>
                    Pembayaran tunai Anda telah dicatat dalam sistem. Menunggu konfirmasi dari admin untuk mengaktifkan reservasi.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>
                        <i class="fas fa-calendar-alt"></i> Reservasi Saya
                    </h2>
                    <a href="booking.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Booking Baru
                    </a>
                </div>

                <?php if (isset($_GET['cancelled'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Reservasi berhasil dibatalkan.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <?php if (empty($reservations)): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <h5>Belum Ada Reservasi</h5>
                        <p class="text-muted">Anda belum memiliki reservasi. Mulai booking sesi foto sekarang!</p>
                        <a href="booking.php" class="btn btn-primary">
                            <i class="fas fa-calendar-plus"></i> Booking Sekarang
                        </a>
                    </div>
                </div>
                <?php else: ?>
                <div class="row">
                    <?php foreach ($reservations as $reservation): ?>
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    #<?php echo str_pad($reservation['id'], 6, '0', STR_PAD_LEFT); ?>
                                </h6>
                                <div>
                                    <?php echo getStatusBadge($reservation['status']); ?>
                                    <?php echo getPaymentStatusBadge($reservation['payment_status']); ?>
                                </div>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($reservation['package_name']); ?></h5>
                                
                                <div class="reservation-details">
                                    <p class="mb-2">
                                        <i class="fas fa-calendar text-primary"></i>
                                        <?php echo date('d F Y', strtotime($reservation['reservation_date'])); ?>
                                    </p>
                                    <p class="mb-2">
                                        <i class="fas fa-clock text-primary"></i>
                                        <?php echo date('H:i', strtotime($reservation['start_time'])); ?> -
                                        <?php echo date('H:i', strtotime($reservation['end_time'])); ?>
                                        (<?php
                                        $minutes = $reservation['duration_minutes'] ?? round($reservation['duration_hours'] * 60);
                                        echo $minutes . ' menit';
                                        ?>)
                                    </p>
                                    <?php if ($reservation['location']): ?>
                                    <p class="mb-2">
                                        <i class="fas fa-map-marker-alt text-primary"></i>
                                        <?php echo htmlspecialchars($reservation['location']); ?>
                                    </p>
                                    <?php endif; ?>
                                    <?php if ($reservation['photographer_name']): ?>
                                    <p class="mb-2">
                                        <i class="fas fa-user-camera text-primary"></i>
                                        <?php echo htmlspecialchars($reservation['photographer_name']); ?>
                                    </p>
                                    <?php endif; ?>
                                    <p class="mb-2">
                                        <i class="fas fa-money-bill text-primary"></i>
                                        Rp <?php echo number_format($reservation['total_amount'], 0, ',', '.'); ?>
                                        <?php
                                        // Check if this is a per-person package
                                        $isPerPerson = ($reservation['package_name'] === 'Grup' || $reservation['package_name'] === 'Photobox') ||
                                                      (isset($reservation['includes']) && strpos(strtolower($reservation['includes']), 'per orang') !== false) ||
                                                      (isset($reservation['description']) && strpos(strtolower($reservation['description']), 'per orang') !== false);

                                        if ($isPerPerson && isset($reservation['number_of_people']) && $reservation['number_of_people'] > 1): ?>
                                            <small class="text-muted d-block">
                                                (<?php echo $reservation['number_of_people']; ?> orang × Rp <?php echo number_format($reservation['total_amount'] / $reservation['number_of_people'], 0, ',', '.'); ?>)
                                            </small>
                                        <?php endif; ?>
                                    </p>
                                </div>

                                <?php if ($reservation['special_requests']): ?>
                                <div class="mt-3">
                                    <small class="text-muted">Permintaan Khusus:</small>
                                    <p class="small"><?php echo htmlspecialchars($reservation['special_requests']); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="card-footer">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        Dibuat: <?php echo date('d/m/Y H:i', strtotime($reservation['created_at'])); ?>
                                    </small>
                                    <div>
                                        <?php if ($reservation['payment_status'] !== 'paid'): ?>
                                        <a href="payment.php?reservation_id=<?php echo $reservation['id']; ?>" class="btn btn-sm btn-success me-2">
                                            <i class="fas fa-credit-card"></i>
                                            <?php echo $reservation['payment_status'] === 'unpaid' ? 'Bayar' : 'Bayar Sisa'; ?>
                                        </a>
                                        <?php endif; ?>

                                        <?php if (!empty($reservation['pending_payments'])): ?>
                                        <?php foreach ($reservation['pending_payments'] as $payment): ?>
                                        <a href="upload-proof.php?payment_id=<?php echo $payment['id']; ?>" class="btn btn-sm btn-warning me-2">
                                            <i class="fas fa-upload"></i> Upload Bukti
                                        </a>
                                        <?php endforeach; ?>
                                        <?php endif; ?>

                                        <a href="invoice.php?reservation_id=<?php echo $reservation['id']; ?>" class="btn btn-sm btn-info me-2">
                                            <i class="fas fa-file-invoice"></i> Invoice
                                        </a>

                                        <?php if (in_array($reservation['status'], ['pending', 'confirmed']) &&
                                                  strtotime($reservation['reservation_date']) > time()): ?>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin membatalkan reservasi ini?')">
                                            <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                            <button type="submit" name="cancel_reservation" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-times"></i> Batal
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
