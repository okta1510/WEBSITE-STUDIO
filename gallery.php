<?php
session_start();
require_once 'config/database.php';

// Initialize variables
$category = $_GET['category'] ?? '';
$whereClause = '';
$params = [];
$galleryImages = [];
$categories = [];

try {
    if ($category) {
        $whereClause = 'WHERE category = ?';
        $params[] = $category;
    }

    // Get gallery images
    $galleryImages = fetchAll("SELECT * FROM gallery $whereClause ORDER BY created_at DESC", $params);
    if (!$galleryImages) {
        $galleryImages = [];
    }

    // Get categories for filter
    $categories = fetchAll("SELECT DISTINCT category FROM gallery ORDER BY category");
    if (!$categories) {
        $categories = [];
    }
} catch (Exception $e) {
    error_log("Database error in gallery.php: " . $e->getMessage());
    $galleryImages = [];
    $categories = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeri - Studio Foto Cekrek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .gallery-filter {
            margin-bottom: 2rem;
        }
        .gallery-item {
            margin-bottom: 2rem;
        }
        .gallery-item img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 8px;
            transition: transform 0.3s ease;
        }
        .gallery-item:hover img {
            transform: scale(1.05);
        }
    </style>
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
                        <a class="nav-link" href="booking.php">Booking</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="gallery.php">Galeri</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User'); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="profile.php">Profil</a></li>
                                <li><a class="dropdown-item" href="my-reservations.php">Reservasi Saya</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Daftar</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5 pt-4">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="display-5">Galeri Portfolio</h2>
                <p class="lead">Lihat hasil karya terbaik kami</p>
            </div>
        </div>

        <!-- Filter -->
        <div class="gallery-filter">
            <div class="d-flex justify-content-center flex-wrap">
                <a href="gallery.php" class="btn <?php echo empty($category) ? 'btn-primary' : 'btn-outline-primary'; ?> me-2 mb-2">
                    Semua
                </a>
                <?php foreach ($categories as $cat): ?>
                <a href="gallery.php?category=<?php echo urlencode($cat['category']); ?>" 
                   class="btn <?php echo $category === $cat['category'] ? 'btn-primary' : 'btn-outline-primary'; ?> me-2 mb-2">
                    <?php echo ucfirst($cat['category']); ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Gallery Grid -->
        <?php if (empty($galleryImages)): ?>
        <div class="row">
            <div class="col-12 text-center py-5">
                <i class="fas fa-images fa-3x text-muted mb-3"></i>
                <h5>Galeri Kosong</h5>
                <p class="text-muted">Portfolio akan segera ditampilkan</p>
            </div>
        </div>
        <?php else: ?>
        <div class="row">
            <?php foreach ($galleryImages as $image): ?>
            <div class="col-lg-4 col-md-6 gallery-item">
                <a href="<?php echo htmlspecialchars($image['image_path']); ?>" 
                   data-lightbox="gallery" 
                   data-title="<?php echo htmlspecialchars($image['title']); ?>">
                    <img src="<?php echo htmlspecialchars($image['image_path']); ?>" 
                         alt="<?php echo htmlspecialchars($image['title']); ?>" 
                         class="img-fluid">
                </a>
                <div class="mt-2">
                    <h6><?php echo htmlspecialchars($image['title']); ?></h6>
                    <small class="text-muted">
                        <i class="fas fa-tag"></i> <?php echo ucfirst($image['category']); ?>
                        <?php if ($image['photographer_id']): ?>
                        | <i class="fas fa-user"></i> Photographer
                        <?php endif; ?>
                    </small>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Call to Action -->
        <div class="row mt-5">
            <div class="col-12 text-center">
                <div class="card bg-primary text-white">
                    <div class="card-body py-5">
                        <h3>Tertarik dengan Hasil Karya Kami?</h3>
                        <p class="lead">Wujudkan foto impian Anda bersama Studio Foto Cekrek</p>
                        <a href="booking.php" class="btn btn-light btn-lg">
                            <i class="fas fa-calendar-plus"></i> Booking Sekarang
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; 2024 Studio Foto Cekrek. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-end">
                    <a href="#" class="text-white me-3"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
