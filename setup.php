<?php
// Complete Setup Script for Studio Foto Cekrek
error_reporting(E_ALL);
ini_set('display_errors', 1);

$step = $_GET['step'] ?? 1;
$message = '';
$messageType = '';

// Function to check requirements
function checkRequirements() {
    $requirements = [
        'PHP Version >= 7.4' => version_compare(PHP_VERSION, '7.4.0', '>='),
        'PDO Extension' => extension_loaded('pdo'),
        'PDO MySQL Extension' => extension_loaded('pdo_mysql'),
        'Database folder writable' => is_writable('database'),
        'Config folder writable' => is_writable('config'),
    ];
    
    return $requirements;
}

// Function to test database connection
function testDatabaseConnection($host, $username, $password) {
    try {
        $pdo = new PDO("mysql:host=$host", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// Handle form submission
if ($_POST && $step == 3) {
    $host = $_POST['host'] ?? 'localhost';
    $dbname = $_POST['dbname'] ?? 'studio_foto_cekrek';
    $username = $_POST['username'] ?? 'root';
    $password = $_POST['password'] ?? '';
    
    try {
        // Test connection first
        if (!testDatabaseConnection($host, $username, $password)) {
            throw new Exception('Tidak dapat terhubung ke database. Periksa kredensial Anda.');
        }
        
        // Create database connection
        $pdo = new PDO("mysql:host=$host", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create database if not exists
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$dbname`");
        
        // Read SQL file
        $sqlFile = 'database/setup.sql';
        if (!file_exists($sqlFile)) {
            throw new Exception('File database/setup.sql tidak ditemukan');
        }
        
        $sql = file_get_contents($sqlFile);
        if ($sql === false) {
            throw new Exception('Gagal membaca file database/setup.sql');
        }
        
        // Clean SQL
        $sql = str_replace(['CREATE DATABASE IF NOT EXISTS studio_foto_cekrek;', 'USE studio_foto_cekrek;'], '', $sql);
        
        // Execute SQL statements
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        foreach ($statements as $statement) {
            if (!empty($statement) && !preg_match('/^--/', $statement)) {
                try {
                    $pdo->exec($statement);
                } catch (PDOException $e) {
                    // Skip duplicate entry errors
                    if (strpos($e->getMessage(), 'Duplicate entry') === false && 
                        strpos($e->getMessage(), 'already exists') === false) {
                        throw new Exception('SQL Error: ' . $e->getMessage() . ' in statement: ' . substr($statement, 0, 100));
                    }
                }
            }
        }
        
        // Create/update config file
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
                \"mysql:host=\" . \$this->host . \";dbname=\" . \$this->db_name . \";charset=utf8mb4\",
                \$this->username,
                \$this->password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                 PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
            );
        } catch(PDOException \$exception) {
            error_log(\"Database connection error: \" . \$exception->getMessage());
            return null;
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
    if (!\$db) {
        throw new Exception(\"Database connection failed\");
    }
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
    if (!\$db) {
        throw new Exception(\"Database connection failed\");
    }
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
    if (!\$db) {
        throw new Exception(\"Database connection failed\");
    }
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
    if (!\$db) {
        throw new Exception(\"Database connection failed\");
    }
    \$sql = \"DELETE FROM {\$table} WHERE {\$where}\";
    \$stmt = \$db->prepare(\$sql);
    
    foreach (\$whereParams as \$key => \$value) {
        \$stmt->bindValue(\":\$key\", \$value);
    }
    
    return \$stmt->execute();
}
?>";
        
        if (file_put_contents('config/database.php', $configContent) === false) {
            throw new Exception('Gagal menulis file konfigurasi database');
        }
        
        $message = 'Database berhasil disetup! Instalasi selesai.';
        $messageType = 'success';
        $step = 4;
        
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
    <title>Setup - Studio Foto Cekrek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .setup-container { min-height: 100vh; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .setup-card { background: white; border-radius: 15px; box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1); }
        .setup-header { background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 2rem; text-align: center; border-radius: 15px 15px 0 0; }
        .requirement-check { padding: 0.5rem 0; }
        .requirement-pass { color: #28a745; }
        .requirement-fail { color: #dc3545; }
    </style>
</head>
<body class="setup-container d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="setup-card">
                    <div class="setup-header">
                        <h3 class="mb-0"><i class="fas fa-camera"></i> Studio Foto Cekrek</h3>
                        <p class="mb-0 mt-2">Setup & Instalasi Sistem</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($message): ?>
                        <div class="alert alert-<?php echo $messageType; ?>" role="alert">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                        <?php endif; ?>

                        <?php if ($step == 1): ?>
                        <!-- Step 1: Welcome -->
                        <h5>Selamat Datang</h5>
                        <p>Setup ini akan membantu Anda menginstal Studio Foto Cekrek dengan mudah.</p>
                        <div class="alert alert-info">
                            <h6>Yang akan disetup:</h6>
                            <ul class="mb-0">
                                <li>Database MySQL</li>
                                <li>Tabel dan data awal</li>
                                <li>Konfigurasi sistem</li>
                                <li>User admin default</li>
                            </ul>
                        </div>
                        <div class="d-grid">
                            <a href="?step=2" class="btn btn-primary btn-lg">
                                <i class="fas fa-arrow-right"></i> Mulai Setup
                            </a>
                        </div>

                        <?php elseif ($step == 2): ?>
                        <!-- Step 2: Requirements Check -->
                        <h5>Pemeriksaan Sistem</h5>
                        <p>Memeriksa persyaratan sistem...</p>
                        
                        <?php 
                        $requirements = checkRequirements();
                        $allPassed = true;
                        ?>
                        
                        <?php foreach ($requirements as $req => $passed): ?>
                        <div class="requirement-check">
                            <i class="fas <?php echo $passed ? 'fa-check requirement-pass' : 'fa-times requirement-fail'; ?>"></i>
                            <?php echo $req; ?>
                        </div>
                        <?php if (!$passed) $allPassed = false; ?>
                        <?php endforeach; ?>
                        
                        <div class="mt-4">
                            <?php if ($allPassed): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> Semua persyaratan terpenuhi!
                            </div>
                            <div class="d-grid">
                                <a href="?step=3" class="btn btn-primary btn-lg">
                                    <i class="fas fa-arrow-right"></i> Lanjut ke Database Setup
                                </a>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> Beberapa persyaratan belum terpenuhi. Silakan perbaiki terlebih dahulu.
                            </div>
                            <div class="d-grid">
                                <a href="?step=2" class="btn btn-warning">
                                    <i class="fas fa-refresh"></i> Periksa Ulang
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php elseif ($step == 3): ?>
                        <!-- Step 3: Database Setup -->
                        <h5>Konfigurasi Database</h5>
                        <p>Masukkan informasi database MySQL:</p>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="host" class="form-label">Host Database</label>
                                <input type="text" class="form-control" id="host" name="host" value="localhost" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="dbname" class="form-label">Nama Database</label>
                                <input type="text" class="form-control" id="dbname" name="dbname" value="studio_foto_cekrek" required>
                                <small class="form-text text-muted">Database akan dibuat otomatis</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="username" class="form-label">Username Database</label>
                                <input type="text" class="form-control" id="username" name="username" value="root" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password Database</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Kosongkan jika tidak ada password">
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-database"></i> Setup Database
                                </button>
                            </div>
                        </form>

                        <?php elseif ($step == 4): ?>
                        <!-- Step 4: Complete -->
                        <div class="text-center">
                            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                            <h5>Setup Berhasil!</h5>
                            <p>Studio Foto Cekrek telah berhasil diinstal.</p>
                            
                            <div class="alert alert-success">
                                <h6>Login Admin:</h6>
                                <p class="mb-1"><strong>Email:</strong> admin@studiofotocekrek.com</p>
                                <p class="mb-0"><strong>Password:</strong> password</p>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <a href="index.php" class="btn btn-primary btn-lg">
                                    <i class="fas fa-home"></i> Buka Website
                                </a>
                                <a href="login.php" class="btn btn-success">
                                    <i class="fas fa-sign-in-alt"></i> Login Admin
                                </a>
                                <a href="test-connection.php" class="btn btn-info">
                                    <i class="fas fa-vial"></i> Test Database
                                </a>
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
