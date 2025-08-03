<?php
session_start();
require_once 'config/database.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$message = '';
$messageType = '';

if ($_POST) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($username)) {
        $errors[] = 'Username harus diisi';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Username minimal 3 karakter';
    }
    
    if (empty($email)) {
        $errors[] = 'Email harus diisi';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid';
    }
    
    if (empty($password)) {
        $errors[] = 'Password harus diisi';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter';
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = 'Konfirmasi password tidak cocok';
    }
    
    if (empty($fullName)) {
        $errors[] = 'Nama lengkap harus diisi';
    }
    
    // Check if username or email already exists
    if (empty($errors)) {
        try {
            $existingUser = fetchOne("SELECT id FROM users WHERE username = ? OR email = ?", [$username, $email]);
            if ($existingUser) {
                $errors[] = 'Username atau email sudah terdaftar';
            }
        } catch (Exception $e) {
            $errors[] = 'Terjadi kesalahan sistem. Silakan coba lagi.';
            error_log("Database error in register.php: " . $e->getMessage());
        }
    }
    
    if (empty($errors)) {
        try {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $userData = [
                'username' => $username,
                'email' => $email,
                'password' => $hashedPassword,
                'full_name' => $fullName,
                'phone' => $phone,
                'address' => $address,
                'role' => 'customer'
            ];
            
            $userId = insertRecord('users', $userData);
            
            // Auto login after registration
            $_SESSION['user_id'] = $userId;
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            $_SESSION['full_name'] = $fullName;
            $_SESSION['role'] = 'customer';
            
            header('Location: index.php?registered=1');
            exit();
            
        } catch (Exception $e) {
            $errors[] = 'Terjadi kesalahan saat mendaftar. Silakan coba lagi.';
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
    <title>Daftar - Studio Foto Cekrek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .register-container {
            min-height: 100vh;
            padding: 2rem 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .register-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .register-header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .register-body {
            padding: 2rem;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="register-card">
                        <div class="register-header">
                            <h3 class="mb-0">
                                <i class="fas fa-camera"></i> Studio Foto Cekrek
                            </h3>
                            <p class="mb-0 mt-2">Buat akun baru</p>
                        </div>
                        <div class="register-body">
                            <?php if ($message): ?>
                            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                                <?php echo $message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php endif; ?>

                            <form method="POST" id="registerForm">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="username" class="form-label">Username *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-user"></i>
                                            </span>
                                            <input type="text" class="form-control" id="username" name="username" 
                                                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-envelope"></i>
                                            </span>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Nama Lengkap *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-id-card"></i>
                                        </span>
                                        <input type="text" class="form-control" id="full_name" name="full_name" 
                                               value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="password" class="form-label">Password *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                            <input type="password" class="form-control" id="password" name="password" required>
                                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                        <small class="form-text text-muted">Minimal 6 karakter</small>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="confirm_password" class="form-label">Konfirmasi Password *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label">Nomor Telepon</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-phone"></i>
                                        </span>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="address" class="form-label">Alamat</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </span>
                                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                                    </div>
                                </div>

                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="terms" required>
                                    <label class="form-check-label" for="terms">
                                        Saya setuju dengan <a href="#" class="text-decoration-none">syarat dan ketentuan</a>
                                    </label>
                                </div>

                                <div class="d-grid mb-3">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-user-plus"></i> Daftar
                                    </button>
                                </div>
                            </form>

                            <div class="text-center">
                                <p class="mb-0">
                                    Sudah punya akun? 
                                    <a href="login.php" class="text-decoration-none fw-bold">
                                        Masuk sekarang
                                    </a>
                                </p>
                            </div>

                            <hr class="my-4">

                            <div class="text-center">
                                <a href="index.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-home"></i> Kembali ke Beranda
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Password confirmation validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Konfirmasi password tidak cocok!');
                return false;
            }
        });
    </script>
</body>
</html>
