<?php
// Installation Script for Studio Foto Cekrek
$step = $_GET['step'] ?? 1;
$message = '';
$messageType = '';

if ($_POST && $step == 2) {
    // Database setup
    $host = $_POST['host'] ?? 'localhost';
    $dbname = $_POST['dbname'] ?? 'studio_foto_cekrek';
    $username = $_POST['username'] ?? 'root';
    $password = $_POST['password'] ?? '';
    
    try {
        // Create database connection
        $pdo = new PDO("mysql:host=$host", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database if not exists
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
        $pdo->exec("USE `$dbname`");
        
        // Read and execute SQL file
        if (!file_exists('database/setup.sql')) {
            throw new Exception('File database/setup.sql tidak ditemukan');
        }

        $sql = file_get_contents('database/setup.sql');
        if ($sql === false) {
            throw new Exception('Gagal membaca file database/setup.sql');
        }

        $sql = str_replace('CREATE DATABASE IF NOT EXISTS studio_foto_cekrek;', '', $sql);
        $sql = str_replace('USE studio_foto_cekrek;', '', $sql);

        // Execute SQL statements
        $statements = explode(';', $sql);
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement) && !preg_match('/^--/', $statement)) {
                try {
                    $pdo->exec($statement);
                } catch (PDOException $e) {
                    // Skip errors for existing tables/data
                    if (strpos($e->getMessage(), 'already exists') === false &&
                        strpos($e->getMessage(), 'Duplicate entry') === false) {
                        throw $e;
                    }
                }
            }
        }
        
        // Update database config file
        $configContent = "<?php
// Database Configuration for Studio Foto Cekrek
class Database {
    private \$host = '$host';
    private \$db_name = '$dbname';
    private \$username = '$username';
    private \$password = '$password';
    private \$conn;

    public function getConnection() {
        \$this->conn = null;
        
        try {
            \$this->conn = new PDO(
                \"mysql:host=\" . \$this->host . \";dbname=\" . \$this->db_name,
                \$this->username,
                \$this->password
            );
            \$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            \$this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException \$exception) {
            echo \"Connection error: \" . \$exception->getMessage();
        }
        
        return \$this->conn;
    }
}

// Database helper functions
function getDB() {
    \$database = new Database();
    return \$database->getConnection();
}

function executeQuery(\$sql, \$params = []) {
    \$db = getDB();
    \$stmt = \$db->prepare(\$sql);
    \$stmt->execute(\$params);
    return \$stmt;
}

function fetchOne(\$sql, \$params = []) {
    \$stmt = executeQuery(\$sql, \$params);
    return \$stmt->fetch();
}

function fetchAll(\$sql, \$params = []) {
    \$stmt = executeQuery(\$sql, \$params);
    return \$stmt->fetchAll();
}

function insertRecord(\$table, \$data) {
    \$db = getDB();
    \$columns = implode(',', array_keys(\$data));
    \$placeholders = ':' . implode(', :', array_keys(\$data));
    
    \$sql = \"INSERT INTO {\$table} ({\$columns}) VALUES ({\$placeholders})\";
    \$stmt = \$db->prepare(\$sql);
    
    foreach (\$data as \$key => \$value) {
        \$stmt->bindValue(\":\$key\", \$value);
    }
    
    \$stmt->execute();
    return \$db->lastInsertId();
}

function updateRecord(\$table, \$data, \$where, \$whereParams = []) {
    \$db = getDB();
    \$setParts = [];
    
    foreach (\$data as \$key => \$value) {
        \$setParts[] = \"{\$key} = :{\$key}\";
    }
    
    \$setClause = implode(', ', \$setParts);
    \$sql = \"UPDATE {\$table} SET {\$setClause} WHERE {\$where}\";
    
    \$stmt = \$db->prepare(\$sql);
    
    foreach (\$data as \$key => \$value) {
        \$stmt->bindValue(\":\$key\", \$value);
    }
    
    foreach (\$whereParams as \$key => \$value) {
        \$stmt->bindValue(\":\$key\", \$value);
    }
    
    return \$stmt->execute();
}

function deleteRecord(\$table, \$where, \$whereParams = []) {
    \$db = getDB();
    \$sql = \"DELETE FROM {\$table} WHERE {\$where}\";
    \$stmt = \$db->prepare(\$sql);
    
    foreach (\$whereParams as \$key => \$value) {
        \$stmt->bindValue(\":\$key\", \$value);
    }
    
    return \$stmt->execute();
}
?>";
        
        file_put_contents('config/database.php', $configContent);
        
        $message = 'Database berhasil disetup! Instalasi selesai.';
        $messageType = 'success';
        $step = 3;
        
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
    <title>Instalasi - Studio Foto Cekrek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-camera"></i> Studio Foto Cekrek - Instalasi
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                        <div class="alert alert-<?php echo $messageType; ?>" role="alert">
                            <?php echo $message; ?>
                        </div>
                        <?php endif; ?>

                        <?php if ($step == 1): ?>
                        <!-- Step 1: Welcome -->
                        <h5>Selamat Datang di Instalasi Studio Foto Cekrek</h5>
                        <p>Sistem ini akan membantu Anda mengsetup database dan konfigurasi awal.</p>
                        
                        <div class="alert alert-info">
                            <h6>Persyaratan Sistem:</h6>
                            <ul class="mb-0">
                                <li>PHP 7.4 atau lebih baru</li>
                                <li>MySQL 5.7 atau lebih baru</li>
                                <li>Apache Web Server</li>
                                <li>PDO MySQL Extension</li>
                            </ul>
                        </div>
                        
                        <div class="d-grid">
                            <a href="?step=2" class="btn btn-primary btn-lg">
                                <i class="fas fa-arrow-right"></i> Lanjutkan Instalasi
                            </a>
                        </div>

                        <?php elseif ($step == 2): ?>
                        <!-- Step 2: Database Setup -->
                        <h5>Konfigurasi Database</h5>
                        <p>Masukkan informasi database MySQL Anda:</p>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="host" class="form-label">Host Database</label>
                                <input type="text" class="form-control" id="host" name="host" value="localhost" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="dbname" class="form-label">Nama Database</label>
                                <input type="text" class="form-control" id="dbname" name="dbname" value="studio_foto_cekrek" required>
                                <small class="form-text text-muted">Database akan dibuat otomatis jika belum ada</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="username" class="form-label">Username Database</label>
                                <input type="text" class="form-control" id="username" name="username" value="root" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password Database</label>
                                <input type="password" class="form-control" id="password" name="password">
                                <small class="form-text text-muted">Kosongkan jika tidak ada password</small>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-database"></i> Setup Database
                                </button>
                            </div>
                        </form>

                        <?php elseif ($step == 3): ?>
                        <!-- Step 3: Complete -->
                        <div class="text-center">
                            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                            <h5>Instalasi Berhasil!</h5>
                            <p>Studio Foto Cekrek telah berhasil diinstal dan siap digunakan.</p>
                            
                            <div class="alert alert-info">
                                <h6>Informasi Login Admin:</h6>
                                <p class="mb-1"><strong>Email:</strong> admin@studiofotocekrek.com</p>
                                <p class="mb-0"><strong>Password:</strong> password</p>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <a href="index.php" class="btn btn-primary btn-lg">
                                    <i class="fas fa-home"></i> Ke Halaman Utama
                                </a>
                                <a href="admin/dashboard.php" class="btn btn-outline-primary">
                                    <i class="fas fa-tachometer-alt"></i> Dashboard Admin
                                </a>
                                <a href="test-connection.php" class="btn btn-outline-info">
                                    <i class="fas fa-vial"></i> Test Koneksi Database
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($step < 3): ?>
                <div class="text-center mt-3">
                    <small class="text-muted">
                        Langkah <?php echo $step; ?> dari 3
                    </small>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
