<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=profile.php');
    exit();
}

// Get user data
$user = null;
try {
    $user = fetchOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
    if (!$user) {
        // User not found, logout
        session_destroy();
        header('Location: login.php');
        exit();
    }
} catch (Exception $e) {
    error_log("Database error in profile.php: " . $e->getMessage());
    header('Location: login.php');
    exit();
}

$message = '';
$messageType = '';

// Handle profile update
if ($_POST) {
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    // Validate required fields
    if (empty($fullName)) {
        $errors[] = 'Nama lengkap harus diisi';
    }
    
    // Validate password change if provided
    if (!empty($newPassword)) {
        if (empty($currentPassword)) {
            $errors[] = 'Password lama harus diisi untuk mengubah password';
        } elseif (!password_verify($currentPassword, $user['password'])) {
            $errors[] = 'Password lama tidak benar';
        } elseif (strlen($newPassword) < 6) {
            $errors[] = 'Password baru minimal 6 karakter';
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = 'Konfirmasi password baru tidak cocok';
        }
    }
    
    if (empty($errors)) {
        try {
            // Update profile data
            $updateData = [
                'full_name' => $fullName,
                'phone' => $phone,
                'address' => $address
            ];
            
            // Add password to update if provided
            if (!empty($newPassword)) {
                $updateData['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
            }
            
            updateRecord('users', $updateData, 'id = :id', ['id' => $_SESSION['user_id']]);
            
            // Update session data
            $_SESSION['full_name'] = $fullName;
            
            $message = 'Profil berhasil diperbarui';
            $messageType = 'success';
            
            // Refresh user data
            $user = fetchOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
            
        } catch (Exception $e) {
            $errors[] = 'Terjadi kesalahan saat memperbarui profil';
        }
    }
    
    if (!empty($errors)) {
        $message = implode('<br>', $errors);
        $messageType = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Studio Foto Cekrek</title>
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
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($user['full_name'] ?? $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User'); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item active" href="profile.php">
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
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="fas fa-user-edit"></i> Profil Saya
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" 
                                           value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                                    <small class="form-text text-muted">Username tidak dapat diubah</small>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" 
                                           value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                                    <small class="form-text text-muted">Email tidak dapat diubah</small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="full_name" class="form-label">Nama Lengkap *</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" 
                                       value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Nomor Telepon</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($user['phone']); ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="role" class="form-label">Role</label>
                                    <input type="text" class="form-control" id="role" 
                                           value="<?php echo ucfirst($user['role']); ?>" readonly>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="address" class="form-label">Alamat</label>
                                <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
                            </div>

                            <hr class="my-4">

                            <h5 class="mb-3">Ubah Password</h5>
                            <p class="text-muted small">Kosongkan jika tidak ingin mengubah password</p>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="current_password" class="form-label">Password Lama</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="new_password" class="form-label">Password Baru</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password">
                                    <small class="form-text text-muted">Minimal 6 karakter</small>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Account Information -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Informasi Akun</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Tanggal Bergabung:</strong><br>
                                <?php echo date('d F Y', strtotime($user['created_at'])); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Terakhir Diperbarui:</strong><br>
                                <?php echo date('d F Y H:i', strtotime($user['updated_at'])); ?></p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Status Akun:</strong><br>
                                <span class="badge <?php echo $user['is_active'] ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo $user['is_active'] ? 'Aktif' : 'Tidak Aktif'; ?>
                                </span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>ID Pengguna:</strong><br>
                                #<?php echo str_pad($user['id'], 6, '0', STR_PAD_LEFT); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && newPassword !== confirmPassword) {
                this.setCustomValidity('Password tidak cocok');
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
            }
        });
    </script>
</body>
</html>
