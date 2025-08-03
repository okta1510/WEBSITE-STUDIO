<?php
// Studio Foto Cekrek - Start Page
// This page helps users get started with the system

// Check if system is already installed
$isInstalled = file_exists('config/database.php');
$dbWorking = false;

if ($isInstalled) {
    try {
        require_once 'config/database.php';
        $db = getDB();
        if ($db) {
            // Test a simple query
            $result = $db->query("SELECT COUNT(*) FROM users");
            if ($result) {
                $dbWorking = true;
            }
        }
    } catch (Exception $e) {
        $dbWorking = false;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Studio Foto Cekrek - Getting Started</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .start-page {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem 0;
        }
        .start-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        .start-header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 2rem;
            text-align: center;
            border-radius: 15px 15px 0 0;
        }
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
            margin: 0.5rem;
        }
        .status-success { background: #d4edda; color: #155724; }
        .status-warning { background: #fff3cd; color: #856404; }
        .status-danger { background: #f8d7da; color: #721c24; }
        .quick-action {
            padding: 1.5rem;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            text-align: center;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
        }
        .quick-action:hover {
            border-color: #007bff;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .quick-action i {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #007bff;
        }
    </style>
</head>
<body class="start-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Header -->
                <div class="start-card">
                    <div class="start-header">
                        <h1><i class="fas fa-camera"></i> Studio Foto Cekrek</h1>
                        <p class="mb-0">Sistem Informasi Manajemen Reservasi Studio Foto</p>
                    </div>
                    <div class="card-body p-4">
                        <!-- System Status -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5>Status Sistem</h5>
                                <div class="d-flex flex-wrap">
                                    <?php if ($isInstalled): ?>
                                        <span class="status-badge status-success">
                                            <i class="fas fa-check"></i> Terinstal
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge status-warning">
                                            <i class="fas fa-exclamation-triangle"></i> Belum Terinstal
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($dbWorking): ?>
                                        <span class="status-badge status-success">
                                            <i class="fas fa-database"></i> Database OK
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge status-danger">
                                            <i class="fas fa-database"></i> Database Error
                                        </span>
                                    <?php endif; ?>
                                    
                                    <span class="status-badge status-success">
                                        <i class="fas fa-server"></i> PHP <?php echo PHP_VERSION; ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="row">
                            <div class="col-12">
                                <h5>Quick Actions</h5>
                            </div>
                            
                            <?php if (!$isInstalled || !$dbWorking): ?>
                            <!-- Setup Actions -->
                            <div class="col-md-6 col-lg-4">
                                <a href="setup.php" class="quick-action text-decoration-none text-dark d-block">
                                    <i class="fas fa-cog"></i>
                                    <h6>Setup System</h6>
                                    <p class="small text-muted mb-0">Install dan konfigurasi sistem</p>
                                </a>
                            </div>
                            
                            <div class="col-md-6 col-lg-4">
                                <a href="install.php" class="quick-action text-decoration-none text-dark d-block">
                                    <i class="fas fa-download"></i>
                                    <h6>Alternative Install</h6>
                                    <p class="small text-muted mb-0">Installer alternatif</p>
                                </a>
                            </div>
                            
                            <div class="col-md-6 col-lg-4">
                                <a href="fix-database.php" class="quick-action text-decoration-none text-dark d-block">
                                    <i class="fas fa-wrench"></i>
                                    <h6>Fix Database</h6>
                                    <p class="small text-muted mb-0">Perbaiki masalah database</p>
                                </a>
                            </div>
                            <?php else: ?>
                            <!-- Normal Actions -->
                            <div class="col-md-6 col-lg-4">
                                <a href="index.php" class="quick-action text-decoration-none text-dark d-block">
                                    <i class="fas fa-home"></i>
                                    <h6>Website</h6>
                                    <p class="small text-muted mb-0">Buka halaman utama</p>
                                </a>
                            </div>
                            
                            <div class="col-md-6 col-lg-4">
                                <a href="login.php" class="quick-action text-decoration-none text-dark d-block">
                                    <i class="fas fa-sign-in-alt"></i>
                                    <h6>Login</h6>
                                    <p class="small text-muted mb-0">Masuk ke sistem</p>
                                </a>
                            </div>
                            
                            <div class="col-md-6 col-lg-4">
                                <a href="admin/" class="quick-action text-decoration-none text-dark d-block">
                                    <i class="fas fa-tachometer-alt"></i>
                                    <h6>Admin Dashboard</h6>
                                    <p class="small text-muted mb-0">Panel administrasi</p>
                                </a>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Always Available -->
                            <div class="col-md-6 col-lg-4">
                                <a href="test-connection.php" class="quick-action text-decoration-none text-dark d-block">
                                    <i class="fas fa-vial"></i>
                                    <h6>Test Database</h6>
                                    <p class="small text-muted mb-0">Tes koneksi database</p>
                                </a>
                            </div>
                            
                            <div class="col-md-6 col-lg-4">
                                <a href="admin/debug.php" class="quick-action text-decoration-none text-dark d-block">
                                    <i class="fas fa-bug"></i>
                                    <h6>Debug Tools</h6>
                                    <p class="small text-muted mb-0">Tools debugging sistem</p>
                                </a>
                            </div>
                            
                            <div class="col-md-6 col-lg-4">
                                <a href="features-complete.php" class="quick-action text-decoration-none text-dark d-block">
                                    <i class="fas fa-check-circle text-success"></i>
                                    <h6>Semua Fitur Lengkap!</h6>
                                    <p class="small text-muted mb-0">Lihat semua fitur yang sudah selesai</p>
                                </a>
                            </div>
                        </div>

                        <!-- Diagnostic Tools -->
                        <div class="row mt-3">
                            <div class="col-md-6 col-lg-4">
                                <a href="check-files.php" class="quick-action text-decoration-none text-dark d-block">
                                    <i class="fas fa-search text-info"></i>
                                    <h6>Check Files</h6>
                                    <p class="small text-muted mb-0">Cek file yang ada/missing</p>
                                </a>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <a href="fix-all-issues.php" class="quick-action text-decoration-none text-dark d-block">
                                    <i class="fas fa-wrench text-warning"></i>
                                    <h6>Fix All Issues</h6>
                                    <p class="small text-muted mb-0">Perbaiki semua masalah</p>
                                </a>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <a href="update-database.php" class="quick-action text-decoration-none text-dark d-block">
                                    <i class="fas fa-sync text-primary"></i>
                                    <h6>Update Database</h6>
                                    <p class="small text-muted mb-0">Update struktur database</p>
                                </a>
                            </div>
                        </div>

                        <!-- Information -->
                        <?php if ($isInstalled && $dbWorking): ?>
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="alert alert-success">
                                    <h6><i class="fas fa-check-circle"></i> Sistem Siap Digunakan!</h6>
                                    <p class="mb-2">Login dengan akun admin:</p>
                                    <ul class="mb-0">
                                        <li><strong>Email:</strong> admin@studiofotocekrek.com</li>
                                        <li><strong>Password:</strong> password</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-exclamation-triangle"></i> Setup Diperlukan</h6>
                                    <p class="mb-0">Sistem belum terinstal dengan benar. Silakan jalankan setup terlebih dahulu.</p>
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
</body>
</html>
