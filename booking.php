<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=booking.php');
    exit();
}

// Prevent admin from making reservations
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $adminMessage = "Admin tidak dapat melakukan reservasi. Anda dapat melihat website sebagai pengunjung.";
}

// Initialize variables
$packages = [];
$selectedPackage = null;

try {
    // Get specific packages only (Solo, Duo, Trio, Grup, Photobox)
    $packages = fetchAll("SELECT * FROM packages WHERE is_active = 1 AND deleted_at IS NULL ORDER BY price ASC");
    if (!$packages) {
        $packages = [];
    }

    // Get selected package if specified
    if (isset($_GET['package'])) {
        $selectedPackage = fetchOne("SELECT * FROM packages WHERE id = ? AND is_active = 1 AND deleted_at IS NULL", [$_GET['package']]);
    }
} catch (Exception $e) {
    error_log("Database error in booking.php: " . $e->getMessage());
    $packages = [];
}

// Function to get booked times for a specific date
function getBookedTimes($date) {
    try {
        $bookedTimes = fetchAll("
            SELECT start_time, end_time
            FROM reservations
            WHERE reservation_date = ?
            AND status NOT IN ('cancelled')
            ORDER BY start_time
        ", [$date]);
        return $bookedTimes ?: [];
    } catch (Exception $e) {
        error_log("Error in getBookedTimes: " . $e->getMessage());
        return [];
    }
}

// Function to check if a time slot is available
function isTimeSlotAvailable($date, $startTime, $endTime) {
    $bookedTimes = getBookedTimes($date);

    foreach ($bookedTimes as $booked) {
        $bookedStart = $booked['start_time'];
        $bookedEnd = $booked['end_time'];

        // Check if there's any overlap
        if (($startTime >= $bookedStart && $startTime < $bookedEnd) ||
            ($endTime > $bookedStart && $endTime <= $bookedEnd) ||
            ($startTime <= $bookedStart && $endTime >= $bookedEnd)) {
            return false;
        }
    }
    return true;
}

// Handle form submission
$message = '';
$messageType = '';

if ($_POST) {
    try {
        $customerId = $_SESSION['user_id'];
        $packageId = $_POST['package_id'];
        $reservationDate = $_POST['reservation_date'];
        $startTime = $_POST['start_time'];
        $specialRequests = $_POST['special_requests'] ?? '';
        
        // Get package details
        $package = fetchOne("SELECT * FROM packages WHERE id = ?", [$packageId]);
        if (!$package) {
            throw new Exception("Paket tidak ditemukan");
        }
        
        // Calculate end time using minutes
        $durationMinutes = $package['duration_minutes'] ?? ($package['duration_hours'] * 60);
        $endTime = date('H:i:s', strtotime($startTime . ' + ' . $durationMinutes . ' minutes'));

        // Calculate total amount based on per-person or fixed price
        $numberOfPeople = isset($_POST['people_count']) ? (int)$_POST['people_count'] : 1;

        // Check if this is a per-person package
        $isPerPerson = ($package['name'] === 'Grup' || $package['name'] === 'Photobox') ||
                      (isset($package['includes']) && strpos(strtolower($package['includes']), 'per orang') !== false) ||
                      (isset($package['description']) && strpos(strtolower($package['description']), 'per orang') !== false);

        $totalAmount = $isPerPerson ? ($package['price'] * $numberOfPeople) : $package['price'];

        // Check availability (simplified - you might want more complex logic)
        $existingReservation = fetchOne(
            "SELECT id FROM reservations WHERE reservation_date = ? AND
             ((start_time <= ? AND end_time > ?) OR (start_time < ? AND end_time >= ?))
             AND status NOT IN ('cancelled')",
            [$reservationDate, $startTime, $startTime, $endTime, $endTime]
        );

        if ($existingReservation) {
            throw new Exception("Waktu yang dipilih sudah terboking. Silakan pilih waktu lain.");
        }

        // Insert reservation
        $reservationData = [
            'customer_id' => $customerId,
            'package_id' => $packageId,
            'reservation_date' => $reservationDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'special_requests' => $specialRequests,
            'number_of_people' => $numberOfPeople,
            'total_amount' => $totalAmount,
            'status' => 'pending'
        ];
        
        $reservationId = insertRecord('reservations', $reservationData);

        // Redirect to payment page
        header('Location: payment.php?reservation_id=' . $reservationId);
        exit();
        
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking - Studio Foto Cekrek</title>
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
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User'); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php">
                                <i class="fas fa-user"></i> Profil
                            </a></li>
                            <li><a class="dropdown-item active" href="booking.php">
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
            <div class="col-12">
                <h2 class="mb-4">
                    <i class="fas fa-calendar-plus"></i> Booking Sesi Foto
                </h2>
                
                <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Form Reservasi</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($adminMessage)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Mode Admin:</strong> <?php echo $adminMessage; ?>
                            <br><small>Untuk melakukan reservasi, silakan logout dan daftar sebagai customer.</small>
                        </div>
                        <div class="text-center py-4">
                            <i class="fas fa-user-shield fa-3x text-muted mb-3"></i>
                            <h5>Anda login sebagai Administrator</h5>
                            <p class="text-muted">Admin dapat melihat website tapi tidak dapat melakukan reservasi.</p>
                            <a href="admin/dashboard.php" class="btn btn-primary me-2">
                                <i class="fas fa-tachometer-alt me-1"></i>Dashboard Admin
                            </a>
                            <a href="logout.php" class="btn btn-outline-secondary">
                                <i class="fas fa-sign-out-alt me-1"></i>Logout
                            </a>
                        </div>
                        <?php else: ?>
                        <form method="POST" id="bookingForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="package_id" class="form-label">Pilih Paket *</label>
                                    <select class="form-select" id="package_id" name="package_id" required>
                                        <option value="">-- Pilih Paket --</option>
                                        <?php foreach ($packages as $package): ?>
                                        <option value="<?php echo $package['id']; ?>"
                                                data-price="<?php echo $package['price']; ?>"
                                                data-duration="<?php echo $package['duration_minutes'] ?? ($package['duration_hours'] * 60); ?>"
                                                data-description="<?php echo htmlspecialchars($package['description']); ?>"
                                                data-name="<?php echo htmlspecialchars($package['name']); ?>"
                                                data-max-people="<?php echo $package['max_people'] ?? 10; ?>"
                                                data-per-person="<?php
                                                    $isPerPerson = ($package['name'] === 'Grup' || $package['name'] === 'Photobox') ||
                                                                  (isset($package['includes']) && strpos(strtolower($package['includes']), 'per orang') !== false) ||
                                                                  (isset($package['description']) && strpos(strtolower($package['description']), 'per orang') !== false);
                                                    echo $isPerPerson ? 'true' : 'false';
                                                ?>"
                                                <?php echo ($selectedPackage && $selectedPackage['id'] == $package['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($package['name']); ?> -
                                            <?php
                                            $isPerPersonOption = ($package['name'] === 'Grup' || $package['name'] === 'Photobox') ||
                                                               (isset($package['includes']) && strpos(strtolower($package['includes']), 'per orang') !== false) ||
                                                               (isset($package['description']) && strpos(strtolower($package['description']), 'per orang') !== false);
                                            ?>
                                            Rp <?php echo number_format($package['price'], 0, ',', '.'); ?>
                                            <?php if ($isPerPersonOption): ?>/orang<?php endif; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="reservation_date" class="form-label">Tanggal Reservasi *</label>
                                    <input type="date" class="form-control" id="reservation_date" name="reservation_date" 
                                           min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="start_time" class="form-label">Waktu Mulai *</label>
                                    <select class="form-select" id="start_time" name="start_time" required>
                                        <option value="">-- Pilih Waktu --</option>
                                        <option value="09:00:00">09:00</option>
                                        <option value="10:00:00">10:00</option>
                                        <option value="11:00:00">11:00</option>
                                        <option value="12:00:00">12:00</option>
                                        <option value="13:00:00">13:00</option>
                                        <option value="14:00:00">14:00</option>
                                        <option value="15:00:00">15:00</option>
                                        <option value="16:00:00">16:00</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3" id="people_count_field" style="display: none;">
                                    <label for="people_count" class="form-label">Jumlah Orang *</label>
                                    <select class="form-select" id="people_count" name="people_count">
                                        <option value="">-- Pilih Jumlah --</option>
                                        <option value="1">1 orang</option>
                                        <option value="2">2 orang</option>
                                        <option value="3">3 orang</option>
                                        <option value="4">4 orang</option>
                                        <option value="5">5 orang</option>
                                        <option value="6">6 orang</option>
                                        <option value="7">7 orang</option>
                                        <option value="8">8 orang</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="end_time_display" class="form-label">Estimasi Selesai</label>
                                    <input type="text" class="form-control" id="end_time_display" readonly 
                                           placeholder="Pilih paket dan waktu mulai">
                                </div>
                            </div>



                            <div class="mb-3">
                                <label for="special_requests" class="form-label">Permintaan Khusus</label>
                                <textarea class="form-control" id="special_requests" name="special_requests" rows="4"
                                          placeholder="Ceritakan konsep foto yang Anda inginkan, tema, atau permintaan khusus lainnya..."></textarea>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-calendar-check"></i> Buat Reservasi
                                </button>
                            </div>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Ringkasan Booking</h5>
                    </div>
                    <div class="card-body">
                        <div id="booking-summary">
                            <p class="text-muted">Pilih paket untuk melihat ringkasan</p>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Informasi Penting</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li><i class="fas fa-info-circle text-info"></i> Reservasi minimal 1 hari sebelumnya</li>
                            <li><i class="fas fa-clock text-warning"></i> Konfirmasi dalam 24 jam</li>
                            <li><i class="fas fa-credit-card text-success"></i> Pembayaran DP 50% untuk konfirmasi</li>
                            <li><i class="fas fa-phone text-primary"></i> Hubungi kami untuk perubahan jadwal</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const packageSelect = document.getElementById('package_id');
            const startTimeSelect = document.getElementById('start_time');
            const endTimeDisplay = document.getElementById('end_time_display');
            const bookingSummary = document.getElementById('booking-summary');
            const dateInput = document.getElementById('reservation_date');
            const peopleCountField = document.getElementById('people_count_field');
            const peopleCountSelect = document.getElementById('people_count');

            function updateEndTime() {
                const selectedPackage = packageSelect.options[packageSelect.selectedIndex];
                const startTime = startTimeSelect.value;

                if (selectedPackage.value && startTime) {
                    const packageName = selectedPackage.dataset.name;

                    // Get duration from data attribute (in minutes)
                    let durationMinutes = parseInt(selectedPackage.dataset.duration) || 60;

                    // Calculate end time
                    const [startHour, startMinute] = startTime.split(':').map(Number);
                    const startTotalMinutes = startHour * 60 + startMinute;
                    const endTotalMinutes = startTotalMinutes + durationMinutes;

                    const endHour = Math.floor(endTotalMinutes / 60);
                    const endMinute = endTotalMinutes % 60;

                    const endTime = endHour.toString().padStart(2, '0') + ':' + endMinute.toString().padStart(2, '0');
                    endTimeDisplay.value = endTime;
                }
            }

            function checkAvailability() {
                const date = dateInput.value;
                const packageId = packageSelect.value;

                if (date && packageId) {
                    // Get package duration in minutes
                    const selectedPackage = packageSelect.options[packageSelect.selectedIndex];
                    const duration = parseInt(selectedPackage.dataset.duration) || 60;

                    // Fetch booked times for this date
                    fetch('check-availability.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            date: date,
                            duration: duration
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        updateTimeOptions(data.availableTimes);
                    })
                    .catch(error => {
                        console.error('Error checking availability:', error);
                    });
                }
            }

            function updateTimeOptions(availableTimes) {
                // Clear current options except the first one
                startTimeSelect.innerHTML = '<option value="">-- Pilih Waktu --</option>';

                // Add available time options
                availableTimes.forEach(time => {
                    const option = document.createElement('option');
                    option.value = time;
                    option.textContent = time;
                    startTimeSelect.appendChild(option);
                });

                // Show message if no times available
                if (availableTimes.length === 0) {
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = 'Tidak ada waktu tersedia';
                    option.disabled = true;
                    startTimeSelect.appendChild(option);
                }
            }

            function showHidePeopleCount() {
                const selectedPackage = packageSelect.options[packageSelect.selectedIndex];

                if (selectedPackage && selectedPackage.value) {
                    const packageName = selectedPackage.dataset.name;
                    const isPerPerson = selectedPackage.dataset.perPerson === 'true';

                    // Show field for all per-person packages
                    if (isPerPerson) {
                        peopleCountField.style.display = 'block';
                        peopleCountSelect.required = true;

                        // Set options based on package
                        peopleCountSelect.innerHTML = '<option value="">-- Pilih Jumlah --</option>';

                        if (packageName === 'Grup') {
                            // Grup: 4-8 orang
                            for (let i = 4; i <= 8; i++) {
                                const option = document.createElement('option');
                                option.value = i;
                                option.textContent = i + ' orang';
                                peopleCountSelect.appendChild(option);
                            }
                        } else if (packageName === 'Photobox') {
                            // Photobox: 1-6 orang
                            for (let i = 1; i <= 6; i++) {
                                const option = document.createElement('option');
                                option.value = i;
                                option.textContent = i + ' orang';
                                peopleCountSelect.appendChild(option);
                            }
                        } else {
                            // For custom per-person packages, use max_people from database
                            const maxPeople = parseInt(selectedPackage.dataset.maxPeople) || 10;
                            const minPeople = 1;

                            for (let i = minPeople; i <= maxPeople; i++) {
                                const option = document.createElement('option');
                                option.value = i;
                                option.textContent = i + ' orang';
                                peopleCountSelect.appendChild(option);
                            }
                        }
                    } else {
                        // Hide for non per-person packages
                        peopleCountField.style.display = 'none';
                        peopleCountSelect.required = false;
                        peopleCountSelect.value = '';
                    }
                } else {
                    peopleCountField.style.display = 'none';
                    peopleCountSelect.required = false;
                }
            }

            function updateSummary() {
                const selectedPackage = packageSelect.options[packageSelect.selectedIndex];

                if (selectedPackage.value) {
                    const packageName = selectedPackage.dataset.name;

                    // Get duration from data attribute
                    const durationMinutes = parseInt(selectedPackage.dataset.duration) || 60;
                    const displayDuration = durationMinutes + ' menit';

                    // Check if this is a per-person package and set correct price
                    const isPerPerson = selectedPackage.dataset.perPerson === 'true';
                    let price = parseInt(selectedPackage.dataset.price);

                    let priceDisplay = `Rp ${price.toLocaleString('id-ID')}`;
                    let totalDisplay = `Rp ${price.toLocaleString('id-ID')}`;

                    if (isPerPerson) {
                        priceDisplay = `Rp ${price.toLocaleString('id-ID')}/orang`;
                        const peopleCount = parseInt(peopleCountSelect.value) || 0;

                        if (peopleCount > 0) {
                            const totalPrice = price * peopleCount;
                            totalDisplay = `Rp ${totalPrice.toLocaleString('id-ID')} (${peopleCount} orang × Rp ${price.toLocaleString('id-ID')})`;
                        } else {
                            totalDisplay = `Rp ${price.toLocaleString('id-ID')}/orang`;
                        }
                    }

                    bookingSummary.innerHTML = `
                        <h6>Paket: ${packageName}</h6>
                        <p class="mb-2">Durasi: <strong>${displayDuration}</strong></p>
                        <p class="mb-2">Harga: ${priceDisplay}</p>
                        ${isPerPerson && peopleCountSelect.value ? `<p class="mb-2">Jumlah: <strong>${peopleCountSelect.value} orang</strong></p>` : ''}
                        <p class="mb-2"><i class="fas fa-gift text-success"></i> Free all soft file (GDRIVE)</p>
                        <hr>
                        <h6 class="text-primary">Total: <strong>${totalDisplay}</strong></h6>
                        ${isPerPerson ? '<small class="text-muted">*Pilih jumlah orang untuk melihat total harga</small>' : '<small class="text-muted">*Harga sudah termasuk semua file</small>'}
                    `;
                } else {
                    bookingSummary.innerHTML = '<p class="text-muted">Pilih paket untuk melihat ringkasan</p>';
                }
            }

            packageSelect.addEventListener('change', function() {
                // Use setTimeout to ensure DOM is ready
                setTimeout(function() {
                    showHidePeopleCount();
                    updateEndTime();
                    updateSummary();
                    checkAvailability();
                }, 10);
            });

            peopleCountSelect.addEventListener('change', function() {
                updateSummary();
            });

            dateInput.addEventListener('change', function() {
                checkAvailability();
            });

            startTimeSelect.addEventListener('change', updateEndTime);

            // Initialize on page load
            showHidePeopleCount();

            // Initialize if package is pre-selected
            if (packageSelect.value) {
                showHidePeopleCount();
                updateSummary();
                checkAvailability();
            }
        });
    </script>
</body>
</html>
