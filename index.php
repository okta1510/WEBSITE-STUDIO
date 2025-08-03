<?php
session_start();
require_once 'config/database.php';

// Initialize variables
$featuredImages = [];
$packages = [];

try {
    // Get featured gallery images
    $featuredImages = fetchAll("SELECT * FROM gallery WHERE is_featured = 1 ORDER BY created_at DESC LIMIT 6");
    if (!$featuredImages) {
        $featuredImages = [];
    }

    // Get specific packages only (Solo, Duo, Trio, Grup, Photobox)
    $packages = fetchAll("SELECT * FROM packages WHERE is_active = 1 AND deleted_at IS NULL ORDER BY price ASC");
    if (!$packages) {
        $packages = [];
    }
} catch (Exception $e) {
    // If database error, continue with empty arrays
    error_log("Database error in index.php: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Studio Foto Cekrek - Jasa Fotografi Profesional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="assets/css/style.css?v=<?php echo time(); ?>" rel="stylesheet">
    <style>
        .contact-info-box, .contact-info-box *, .contact-info-box div, .contact-info-box span, .contact-info-box strong, .contact-info-box a {
            background: #2c3e50 !important;
        }
        .contact-info-box i {
            background: transparent !important;
        }
    </style>
</head>
<body>
    <!-- Loading Screen -->
    <div id="loading-screen">
        <div class="loading-content">
            <div class="loading-logo">
                <i class="fas fa-camera"></i>
                <h3>Studio Foto Cekrek</h3>
            </div>
            <div class="loading-spinner">
                <div class="spinner-ring"></div>
                <div class="spinner-ring"></div>
                <div class="spinner-ring"></div>
            </div>
        </div>
    </div>
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
                        <a class="nav-link active" href="index.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#packages">Paket</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="schedule-studio.php">Jadwal Studio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#gallery">Portofolio</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User'); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="profile.php">
                                    <i class="fas fa-user"></i> Profil
                                </a></li>
                                <li><a class="dropdown-item" href="my-reservations.php">
                                    <i class="fas fa-list"></i> Reservasi Saya
                                </a></li>

                                <?php if ($_SESSION['role'] == 'admin'): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="admin/dashboard.php">
                                        <i class="fas fa-cog"></i> Dashboard Admin
                                    </a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a></li>
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

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-overlay">
            <div class="container">
                <div class="row align-items-center min-vh-100">
                    <div class="col-lg-6">
                        <h1 class="display-4 text-white mb-4">Abadikan Momen Terbaik Anda</h1>
                        <p class="lead text-white mb-4">Studio Foto Cekrek menghadirkan pengalaman self photo studio yang menyenangkan dan praktis! Datang, bergaya, dan ambil fotomu sendiri sepuasnya dengan hasil berkualitas tinggi.</p>
                        <div class="hero-buttons">
                            <?php if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'): ?>
                            <a href="booking.php" class="btn btn-primary btn-lg me-3">
                                <i class="fas fa-calendar-plus"></i> Booking Sekarang
                            </a>
                            <?php else: ?>
                            <a href="admin/dashboard.php" class="btn btn-warning btn-lg me-3">
                                <i class="fas fa-tachometer-alt"></i> Dashboard Admin
                            </a>
                            <?php endif; ?>
                            <a href="#gallery" class="btn btn-outline-light btn-lg">
                                <i class="fas fa-images"></i> Lihat Portfolio
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row text-center">
                <div class="col-lg-4 mb-4">
                    <div class="feature-box">
                        <i class="fas fa-camera fa-3x text-primary mb-3"></i>
                        <h4>Peralatan Profesional</h4>
                        <p>Menggunakan kamera dan peralatan fotografi terkini untuk hasil terbaik</p>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="feature-box">
                        <i class="fas fa-users fa-3x text-primary mb-3"></i>
                        <h4>Self Photo Experience</h4>
                        <p>Studio mandiri yang memungkinkan Anda mengambil foto sendiri dengan hasil profesional</p>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="feature-box">
                        <i class="fas fa-heart fa-3x text-primary mb-3"></i>
                        <h4>Pelayanan Terbaik</h4>
                        <p>Komitmen memberikan pelayanan terbaik dan hasil yang memuaskan untuk setiap klien</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Packages Section -->
    <section id="packages" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5">Paket Fotografi</h2>
                    <p class="lead">Pilih paket yang sesuai dengan kebutuhan Anda</p>
                </div>
            </div>
            <div class="row">
                <?php foreach ($packages as $package): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card package-card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($package['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($package['description']); ?></p>
                            <div class="package-details">
                                <p><i class="fas fa-clock"></i>
                                    <?php
                                    // Display duration from database
                                    if (isset($package['duration_minutes']) && $package['duration_minutes'] > 0) {
                                        echo '<strong>' . $package['duration_minutes'] . ' menit</strong>';
                                    } else {
                                        // Fallback: convert hours to minutes
                                        $minutes = round($package['duration_hours'] * 60);
                                        echo '<strong>' . $minutes . ' menit</strong>';
                                    }
                                    ?>
                                </p>
                                <p><i class="fas fa-users"></i>
                                    <?php
                                    // Extract people count from description
                                    if (preg_match('/(\d+(?:-\d+)?)\s*orang/', $package['description'], $matches)) {
                                        echo $matches[1] . ' orang';
                                    } elseif (strpos($package['description'], 'Max 6 orang') !== false) {
                                        echo 'Max 6 orang';
                                    } else {
                                        echo 'Fleksibel';
                                    }
                                    ?>
                                </p>
                                <p><i class="fas fa-gift"></i> Free all soft file (GDRIVE)</p>
                            </div>
                            <div class="package-price">
                                <h4 class="text-primary">
                                    <?php
                                    // Check if this is a per-person package
                                    $isPerPerson = ($package['name'] === 'Grup' || $package['name'] === 'Photobox') ||
                                                  (isset($package['includes']) && strpos(strtolower($package['includes']), 'per orang') !== false) ||
                                                  (isset($package['description']) && strpos(strtolower($package['description']), 'per orang') !== false);
                                    ?>
                                    Rp <?php echo number_format($package['price'], 0, ',', '.'); ?>
                                    <?php if ($isPerPerson): ?>
                                        <small>/orang</small>
                                    <?php endif; ?>
                                </h4>
                            </div>
                        </div>
                        <div class="card-footer">
                            <?php if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'): ?>
                            <a href="booking.php?package=<?php echo $package['id']; ?>" class="btn btn-primary w-100">
                                <i class="fas fa-calendar-plus"></i> Pilih Paket
                            </a>
                            <?php else: ?>
                            <div class="text-center text-muted">
                                <i class="fas fa-eye me-1"></i> Mode Admin - Hanya Melihat
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Portfolio Section -->
    <section id="gallery" class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="display-5">Portofolio Kami</h2>
                    <p class="lead">Lihat hasil karya terbaik kami</p>
                </div>
            </div>
            <div class="row">
                <!-- Duo Portfolio -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="portfolio-category">
                        <h4 class="portfolio-title">Duo</h4>
                        <div class="portfolio-grid">
                            <div class="portfolio-item" data-bs-toggle="modal" data-bs-target="#portfolioModal"
                                 data-image="assets/images/duo1.jpg" data-title="Duo Session 1" data-category="Duo">
                                <img src="assets/images/duo1.jpg" alt="Duo 1" class="img-fluid">
                                <div class="portfolio-overlay">
                                    <i class="fas fa-search-plus"></i>
                                </div>
                            </div>
                            <div class="portfolio-item" data-bs-toggle="modal" data-bs-target="#portfolioModal"
                                 data-image="assets/images/duo2.jpg" data-title="Duo Session 2" data-category="Duo">
                                <img src="assets/images/duo2.jpg" alt="Duo 2" class="img-fluid">
                                <div class="portfolio-overlay">
                                    <i class="fas fa-search-plus"></i>
                                </div>
                            </div>
                            <div class="portfolio-item" data-bs-toggle="modal" data-bs-target="#portfolioModal"
                                 data-image="assets/images/duo3.jpg" data-title="Duo Session 3" data-category="Duo">
                                <img src="assets/images/duo3.jpg" alt="Duo 3" class="img-fluid">
                                <div class="portfolio-overlay">
                                    <i class="fas fa-search-plus"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Trio Portfolio -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="portfolio-category">
                        <h4 class="portfolio-title">Trio</h4>
                        <div class="portfolio-grid">
                            <div class="portfolio-item" data-bs-toggle="modal" data-bs-target="#portfolioModal"
                                 data-image="assets/images/trio1.jpg" data-title="Trio Session 1" data-category="Trio">
                                <img src="assets/images/trio1.jpg" alt="Trio 1" class="img-fluid">
                                <div class="portfolio-overlay">
                                    <i class="fas fa-search-plus"></i>
                                </div>
                            </div>
                            <div class="portfolio-item" data-bs-toggle="modal" data-bs-target="#portfolioModal"
                                 data-image="assets/images/trio2.jpg" data-title="Trio Session 2" data-category="Trio">
                                <img src="assets/images/trio2.jpg" alt="Trio 2" class="img-fluid">
                                <div class="portfolio-overlay">
                                    <i class="fas fa-search-plus"></i>
                                </div>
                            </div>
                            <div class="portfolio-item" data-bs-toggle="modal" data-bs-target="#portfolioModal"
                                 data-image="assets/images/trio3.jpg" data-title="Trio Session 3" data-category="Trio">
                                <img src="assets/images/trio3.jpg" alt="Trio 3" class="img-fluid">
                                <div class="portfolio-overlay">
                                    <i class="fas fa-search-plus"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grup Portfolio -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="portfolio-category">
                        <h4 class="portfolio-title">Grup</h4>
                        <div class="portfolio-grid">
                            <div class="portfolio-item" data-bs-toggle="modal" data-bs-target="#portfolioModal"
                                 data-image="assets/images/grup1.jpg" data-title="Grup Session 1" data-category="Grup">
                                <img src="assets/images/grup1.jpg" alt="Grup 1" class="img-fluid">
                                <div class="portfolio-overlay">
                                    <i class="fas fa-search-plus"></i>
                                </div>
                            </div>
                            <div class="portfolio-item" data-bs-toggle="modal" data-bs-target="#portfolioModal"
                                 data-image="assets/images/grup2.jpg" data-title="Grup Session 2" data-category="Grup">
                                <img src="assets/images/grup2.jpg" alt="Grup 2" class="img-fluid">
                                <div class="portfolio-overlay">
                                    <i class="fas fa-search-plus"></i>
                                </div>
                            </div>
                            <div class="portfolio-item" data-bs-toggle="modal" data-bs-target="#portfolioModal"
                                 data-image="assets/images/grup3.jpg" data-title="Grup Session 3" data-category="Grup">
                                <img src="assets/images/grup3.jpg" alt="Grup 3" class="img-fluid">
                                <div class="portfolio-overlay">
                                    <i class="fas fa-search-plus"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Portfolio Modal -->
    <div class="modal fade" id="portfolioModal" tabindex="-1" aria-labelledby="portfolioModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="portfolioModalLabel">Portfolio Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="" class="img-fluid rounded">
                    <div class="mt-3">
                        <h6 id="modalTitle"></h6>
                        <p id="modalCategory" class="text-muted"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Footer -->
    <footer class="py-5" style="background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); color: #ecf0f1;">
        <div class="container">
            <div class="row">
                <!-- Studio Info -->
                <div class="col-lg-6 col-md-6 mb-4">
                    <div class="contact-info-box p-4 rounded" style="background: #2c3e50 !important; border: 1px solid #34495e;">
                        <h5 class="mb-4 text-uppercase fw-bold" style="color: #f39c12; background: transparent !important;">Studio Foto Cekrek</h5>
                        <div class="contact-info" style="background: #2c3e50 !important;">
                            <div class="d-flex align-items-start mb-3" style="background: #2c3e50 !important;">
                                <i class="fas fa-map-marker-alt me-3 mt-1" style="color: #e74c3c; background: transparent !important;"></i>
                                <div style="background: #2c3e50 !important; padding: 0; margin: 0;">
                                    <strong style="color: #f39c12; background: transparent !important;">Alamat:</strong><br>
                                    <span style="color: #ecf0f1; background: transparent !important;">Jalan Tandipau, Kecamatan Wara Utara</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-3" style="background: #2c3e50 !important;">
                                <i class="fas fa-phone me-3" style="color: #27ae60; background: transparent !important;"></i>
                                <div style="background: #2c3e50 !important; padding: 0; margin: 0;">
                                    <strong style="color: #f39c12; background: transparent !important;">HP/WA:</strong>
                                    <a href="https://wa.me/6285709982869" class="text-decoration-none" style="color: #3498db; background: transparent !important;">
                                        0857 0998 2869
                                    </a>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-3" style="background: #2c3e50 !important;">
                                <i class="fas fa-envelope me-3" style="color: #9b59b6; background: transparent !important;"></i>
                                <div style="background: #2c3e50 !important; padding: 0; margin: 0;">
                                    <strong style="color: #f39c12; background: transparent !important;">Email:</strong>
                                    <a href="mailto:studiofotocekrek@gmail.com" class="text-decoration-none" style="color: #3498db; background: transparent !important;">
                                        studiofotocekrek@gmail.com
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Social Media -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <h6 class="mb-4 text-uppercase fw-bold" style="color: #f39c12;">Ikuti Kami</h6>
                    <div class="social-links">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fab fa-facebook fa-lg me-3" style="color: #3b5998;"></i>
                            <a href="#" class="text-decoration-none" style="color: #ecf0f1;">Facebook</a>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <i class="fab fa-instagram fa-lg me-3" style="color: #e4405f;"></i>
                            <a href="#" class="text-decoration-none" style="color: #ecf0f1;">Instagram</a>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <i class="fab fa-whatsapp fa-lg me-3" style="color: #25d366;"></i>
                            <a href="https://wa.me/6285709982869" class="text-decoration-none" style="color: #ecf0f1;">WhatsApp</a>
                        </div>
                    </div>
                </div>

                <!-- Quick Info -->
                <div class="col-lg-3 col-md-12 mb-4">
                    <h6 class="mb-4 text-uppercase fw-bold" style="color: #f39c12;">Jam Operasional</h6>
                    <div class="operating-hours">
                        <div class="mb-2">
                            <i class="fas fa-clock me-2" style="color: #e67e22;"></i>
                            <strong style="color: #ecf0f1;">Senin - Minggu</strong>
                        </div>
                        <div class="mb-3 ms-4" style="color: #bdc3c7;">
                            08:00 - 21:00 WIB
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-camera me-2" style="color: #e67e22;"></i>
                            <strong style="color: #ecf0f1;">Self Photo Studio</strong>
                        </div>
                        <div class="ms-4 small" style="color: #bdc3c7;">
                            Ambil foto sendiri dengan hasil berkualitas tinggi
                        </div>
                    </div>
                </div>
            </div>

            <!-- Copyright -->
            <div class="row">
                <div class="col-12">
                    <hr class="my-4" style="border-color: #7f8c8d;">
                    <div class="text-center">
                        <p class="mb-0" style="color: #95a5a6;">&copy; 2025 Studio Foto Cekrek. All rights reserved.</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Floating Action Button -->
    <?php if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'): ?>
    <div class="floating-btn" id="floatingBtn">
        <a href="booking.php" class="fab-link">
            <i class="fas fa-calendar-plus"></i>
            <span class="fab-text">Book Now</span>
        </a>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>

    <script>
        // Loading Screen
        window.addEventListener('load', function() {
            const loadingScreen = document.getElementById('loading-screen');
            setTimeout(() => {
                loadingScreen.classList.add('fade-out');
                setTimeout(() => {
                    loadingScreen.style.display = 'none';
                }, 500);
            }, 1500);
        });

        // Add stagger animation to elements
        document.addEventListener('DOMContentLoaded', function() {
            // Animate navbar on scroll
            let lastScrollTop = 0;
            const navbar = document.querySelector('.navbar');

            window.addEventListener('scroll', function() {
                let scrollTop = window.pageYOffset || document.documentElement.scrollTop;

                if (scrollTop > lastScrollTop && scrollTop > 100) {
                    navbar.style.transform = 'translateY(-100%)';
                } else {
                    navbar.style.transform = 'translateY(0)';
                }
                lastScrollTop = scrollTop;
            });

            // Add entrance animations to cards (simplified)
            const cards = document.querySelectorAll('.package-card, .feature-box, .portfolio-category');
            cards.forEach((card, index) => {
                card.style.transition = 'all 0.6s ease';
                card.style.transform = 'translateY(20px)';

                // Animate cards after a short delay
                setTimeout(() => {
                    card.style.transform = 'translateY(0)';
                }, 200 + (index * 50));
            });
        });

        // Parallax Effect for Hero Section
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const heroSection = document.querySelector('.hero-section');
            const rate = scrolled * -0.5;

            if (heroSection) {
                heroSection.style.transform = `translateY(${rate}px)`;
            }
        });

        // Scroll Animation for Elements
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        // Add scroll-fade class to sections
        document.addEventListener('DOMContentLoaded', function() {
            const sections = document.querySelectorAll('#packages, #gallery, #contact');
            sections.forEach(section => {
                section.classList.add('scroll-fade');
                observer.observe(section);
            });

            // Add scroll-fade to feature boxes
            const featureBoxes = document.querySelectorAll('.feature-box');
            featureBoxes.forEach((box, index) => {
                box.classList.add('scroll-fade');
                box.style.animationDelay = `${index * 0.2}s`;
                observer.observe(box);
            });

            // Add scroll-fade to package cards
            const packageCards = document.querySelectorAll('.package-card');
            packageCards.forEach((card, index) => {
                card.classList.add('scroll-fade');
                card.style.animationDelay = `${index * 0.1}s`;
                observer.observe(card);
            });

        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Portfolio Modal Functionality
        const portfolioModal = document.getElementById('portfolioModal');
        if (portfolioModal) {
            portfolioModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const imageSrc = button.getAttribute('data-image');
                const imageTitle = button.getAttribute('data-title');
                const imageCategory = button.getAttribute('data-category');

                const modalImage = document.getElementById('modalImage');
                const modalTitle = document.getElementById('modalTitle');
                const modalCategory = document.getElementById('modalCategory');

                modalImage.src = imageSrc;
                modalImage.alt = imageTitle;
                modalTitle.textContent = imageTitle;
                modalCategory.textContent = imageCategory;
            });
        }

        // Add hover effects to portfolio items
        document.querySelectorAll('.portfolio-item').forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.05)';
            });

            item.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
            });
        });

        // Floating Action Button
        const floatingBtn = document.getElementById('floatingBtn');
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                floatingBtn.classList.add('show');
            } else {
                floatingBtn.classList.remove('show');
            }
        });

        // Add pulse animation to floating button
        setInterval(() => {
            if (floatingBtn.classList.contains('show')) {
                floatingBtn.style.animation = 'pulse 0.6s ease-in-out';
                setTimeout(() => {
                    floatingBtn.style.animation = '';
                }, 600);
            }
        }, 5000);


    </script>
</body>
</html>
