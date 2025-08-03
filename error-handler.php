<?php
// Error handler untuk mengatasi file yang tidak ditemukan
function handleMissingFile($requestedFile) {
    $baseUrl = dirname($_SERVER['PHP_SELF']);
    if ($baseUrl === '/') $baseUrl = '';
    
    // Daftar file yang ada dan alternatifnya
    $fileMap = [
        // Admin files
        'admin/photographers.php' => 'admin/customers.php',
        'admin/availability.php' => 'admin/schedules.php',
        'admin/blocked-dates.php' => 'admin/schedules.php',
        'admin/payments-pending.php' => 'admin/payments.php',
        'admin/invoices.php' => 'admin/payments.php',
        'admin/reports-reservations.php' => 'admin/reports.php',
        'admin/reports-revenue.php' => 'admin/reports.php',
        'admin/reports-customers.php' => 'admin/reports.php',
        'admin/reports-packages.php' => 'admin/reports.php',
        'admin/settings.php' => 'admin/dashboard.php',
        
        // Customer files
        'reservations-pending.php' => 'my-reservations.php',
        'reservations-today.php' => 'my-reservations.php',
        
        // Other files
        '404.php' => 'index.php',
        '500.php' => 'index.php',
        'INSTALLATION.md' => 'README.md'
    ];
    
    // Cek apakah ada alternatif
    if (isset($fileMap[$requestedFile])) {
        $alternative = $fileMap[$requestedFile];
        if (file_exists($alternative)) {
            header("Location: $baseUrl/$alternative");
            exit();
        }
    }
    
    // Jika tidak ada alternatif, tampilkan halaman error yang friendly
    showFriendlyError($requestedFile);
}

function showFriendlyError($requestedFile) {
    http_response_code(404);
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Halaman Tidak Ditemukan - Studio Foto Cekrek</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
        <style>
            .error-page {
                min-height: 100vh;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .error-card {
                background: white;
                border-radius: 15px;
                box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
                padding: 3rem;
                text-align: center;
                max-width: 600px;
            }
            .error-icon {
                font-size: 5rem;
                color: #6c757d;
                margin-bottom: 2rem;
            }
        </style>
    </head>
    <body class="error-page">
        <div class="error-card">
            <i class="fas fa-exclamation-triangle error-icon"></i>
            <h1 class="mb-4">Halaman Tidak Ditemukan</h1>
            <p class="text-muted mb-4">
                Maaf, halaman yang Anda cari tidak ditemukan atau sedang dalam pengembangan.
            </p>
            <p class="small text-muted mb-4">
                File yang diminta: <code><?php echo htmlspecialchars($requestedFile); ?></code>
            </p>
            
            <div class="row mb-4">
                <div class="col-md-6 mb-2">
                    <a href="index.php" class="btn btn-primary w-100">
                        <i class="fas fa-home"></i> Kembali ke Beranda
                    </a>
                </div>
                <div class="col-md-6 mb-2">
                    <a href="admin/test-admin.php" class="btn btn-outline-primary w-100">
                        <i class="fas fa-cog"></i> Test Admin Panel
                    </a>
                </div>
            </div>
            
            <div class="alert alert-info">
                <h6><i class="fas fa-info-circle"></i> Fitur Tersedia</h6>
                <p class="mb-2">Semua fitur utama sudah tersedia:</p>
                <div class="row">
                    <div class="col-md-6">
                        <ul class="list-unstyled text-start">
                            <li>✓ Booking & Pembayaran</li>
                            <li>✓ Kelola Reservasi</li>
                            <li>✓ Admin Dashboard</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-unstyled text-start">
                            <li>✓ Galeri Portfolio</li>
                            <li>✓ Laporan & Statistik</li>
                            <li>✓ Profile Management</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <p class="small text-muted">
                Jika Anda yakin halaman ini seharusnya ada, silakan hubungi administrator.
            </p>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// Jika file ini dipanggil langsung
if (basename($_SERVER['PHP_SELF']) === 'error-handler.php') {
    $requestedFile = $_GET['file'] ?? 'unknown';
    handleMissingFile($requestedFile);
}
?>
