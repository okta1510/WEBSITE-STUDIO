<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get current month and year
$currentMonth = $_GET['month'] ?? date('m');
$currentYear = $_GET['year'] ?? date('Y');

// Validate month and year
$currentMonth = max(1, min(12, intval($currentMonth)));
$currentYear = max(2020, min(2030, intval($currentYear)));

// Get first day of month and number of days
$firstDay = mktime(0, 0, 0, $currentMonth, 1, $currentYear);
$daysInMonth = date('t', $firstDay);
$dayOfWeek = date('w', $firstDay);

// Get reservations for this month
$reservations = [];
try {
    $startDate = "$currentYear-" . str_pad($currentMonth, 2, '0', STR_PAD_LEFT) . "-01";
    $endDate = "$currentYear-" . str_pad($currentMonth, 2, '0', STR_PAD_LEFT) . "-$daysInMonth";
    
    $reservations = fetchAll("
        SELECT r.*, p.name as package_name, u.full_name as customer_name
        FROM reservations r
        JOIN packages p ON r.package_id = p.id
        JOIN users u ON r.customer_id = u.id
        WHERE r.reservation_date BETWEEN ? AND ?
        AND r.status NOT IN ('cancelled')
        ORDER BY r.reservation_date, r.start_time
    ", [$startDate, $endDate]);
    
    if (!$reservations) {
        $reservations = [];
    }
} catch (Exception $e) {
    $reservations = [];
}

// Get blocked dates
$blockedDates = [];
try {
    $blockedDates = fetchAll("
        SELECT * FROM schedules 
        WHERE date BETWEEN ? AND ? 
        AND is_available = 0
    ", [$startDate, $endDate]);
    
    if (!$blockedDates) {
        $blockedDates = [];
    }
} catch (Exception $e) {
    $blockedDates = [];
}

// Organize data by date
$scheduleData = [];
foreach ($reservations as $reservation) {
    $date = $reservation['reservation_date'];
    if (!isset($scheduleData[$date])) {
        $scheduleData[$date] = ['reservations' => [], 'blocked' => false];
    }
    $scheduleData[$date]['reservations'][] = $reservation;
}

foreach ($blockedDates as $blocked) {
    $date = $blocked['date'];
    if (!isset($scheduleData[$date])) {
        $scheduleData[$date] = ['reservations' => [], 'blocked' => false];
    }
    $scheduleData[$date]['blocked'] = true;
}

// Month names in Indonesian
$monthNames = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

// Day names in Indonesian
$dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Studio Foto - Studio Foto Cekrek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .calendar-day {
            height: 120px;
            border: 1px solid #dee2e6;
            padding: 5px;
            position: relative;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .calendar-day:hover {
            background-color: #f8f9fa;
        }
        .calendar-day.other-month {
            background-color: #f8f9fa;
            color: #6c757d;
        }
        .calendar-day.blocked {
            background-color: #f8d7da;
            color: #721c24;
        }
        .calendar-day.has-reservations {
            background-color: #d1ecf1;
        }
        .calendar-day.today {
            background-color: #fff3cd;
            border-color: #ffc107;
            font-weight: bold;
        }
        .day-number {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .reservation-item {
            background: #007bff;
            color: white;
            font-size: 0.7rem;
            padding: 2px 4px;
            border-radius: 3px;
            margin-bottom: 2px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .blocked-indicator {
            background: #dc3545;
            color: white;
            font-size: 0.7rem;
            padding: 2px 4px;
            border-radius: 3px;
            text-align: center;
        }
        .legend {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 3px;
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
                        <a class="nav-link" href="index.php#packages">Paket</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="schedule-studio.php">Jadwal Studio</a>
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
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-calendar-alt"></i> Jadwal Studio Foto</h2>
                    <div>
                        <a href="booking.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Buat Reservasi
                        </a>
                    </div>
                </div>

                <!-- Month Navigation -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <a href="?month=<?php echo $currentMonth == 1 ? 12 : $currentMonth - 1; ?>&year=<?php echo $currentMonth == 1 ? $currentYear - 1 : $currentYear; ?>" 
                                   class="btn btn-outline-primary">
                                    <i class="fas fa-chevron-left"></i> Bulan Sebelumnya
                                </a>
                            </div>
                            <div class="col-md-4 text-center">
                                <h4 class="mb-0"><?php echo $monthNames[$currentMonth] . ' ' . $currentYear; ?></h4>
                            </div>
                            <div class="col-md-4 text-end">
                                <a href="?month=<?php echo $currentMonth == 12 ? 1 : $currentMonth + 1; ?>&year=<?php echo $currentMonth == 12 ? $currentYear + 1 : $currentYear; ?>" 
                                   class="btn btn-outline-primary">
                                    Bulan Berikutnya <i class="fas fa-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Legend -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h6>Keterangan:</h6>
                        <div class="legend">
                            <div class="legend-item">
                                <div class="legend-color" style="background-color: #fff3cd; border: 2px solid #ffc107;"></div>
                                <span>Hari Ini</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color" style="background-color: #d1ecf1;"></div>
                                <span>Ada Reservasi</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color" style="background-color: #f8d7da;"></div>
                                <span>Studio Tutup</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color" style="background-color: white; border: 1px solid #dee2e6;"></div>
                                <span>Tersedia</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calendar -->
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <?php foreach ($dayNames as $dayName): ?>
                                        <th class="text-center" style="width: 14.28%;"><?php echo $dayName; ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $currentDate = 1;
                                    $today = date('Y-m-d');
                                    
                                    for ($week = 0; $week < 6; $week++):
                                        if ($currentDate > $daysInMonth) break;
                                    ?>
                                    <tr>
                                        <?php for ($day = 0; $day < 7; $day++): ?>
                                        <td class="p-0">
                                            <?php
                                            if ($week == 0 && $day < $dayOfWeek) {
                                                // Empty cells before first day
                                                echo '<div class="calendar-day other-month"></div>';
                                            } elseif ($currentDate <= $daysInMonth) {
                                                $dateStr = "$currentYear-" . str_pad($currentMonth, 2, '0', STR_PAD_LEFT) . "-" . str_pad($currentDate, 2, '0', STR_PAD_LEFT);
                                                $dayData = $scheduleData[$dateStr] ?? ['reservations' => [], 'blocked' => false];
                                                
                                                $classes = ['calendar-day'];
                                                if ($dateStr == $today) $classes[] = 'today';
                                                if ($dayData['blocked']) $classes[] = 'blocked';
                                                elseif (!empty($dayData['reservations'])) $classes[] = 'has-reservations';
                                                
                                                echo '<div class="' . implode(' ', $classes) . '" onclick="showDayDetails(\'' . $dateStr . '\')">';
                                                echo '<div class="day-number">' . $currentDate . '</div>';
                                                
                                                if ($dayData['blocked']) {
                                                    echo '<div class="blocked-indicator">TUTUP</div>';
                                                } else {
                                                    foreach (array_slice($dayData['reservations'], 0, 3) as $reservation) {
                                                        $time = date('H:i', strtotime($reservation['start_time']));
                                                        echo '<div class="reservation-item" title="' . htmlspecialchars($reservation['package_name']) . '">';
                                                        echo $time . ' - ' . substr($reservation['package_name'], 0, 10) . '...';
                                                        echo '</div>';
                                                    }
                                                    if (count($dayData['reservations']) > 3) {
                                                        echo '<div class="reservation-item">+' . (count($dayData['reservations']) - 3) . ' lainnya</div>';
                                                    }
                                                }
                                                
                                                echo '</div>';
                                                $currentDate++;
                                            } else {
                                                echo '<div class="calendar-day other-month"></div>';
                                            }
                                            ?>
                                        </td>
                                        <?php endfor; ?>
                                    </tr>
                                    <?php endfor; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h4 class="text-primary"><?php echo count($reservations); ?></h4>
                                <p class="mb-0">Total Reservasi</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h4 class="text-success"><?php echo count(array_filter($reservations, function($r) { return $r['status'] === 'confirmed'; })); ?></h4>
                                <p class="mb-0">Dikonfirmasi</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h4 class="text-warning"><?php echo count(array_filter($reservations, function($r) { return $r['status'] === 'pending'; })); ?></h4>
                                <p class="mb-0">Menunggu</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h4 class="text-danger"><?php echo count($blockedDates); ?></h4>
                                <p class="mb-0">Hari Tutup</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Day Details Modal -->
    <div class="modal fade" id="dayDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Jadwal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="dayDetailsContent">
                    <!-- Content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <a href="booking.php" class="btn btn-primary">Buat Reservasi</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const scheduleData = <?php echo json_encode($scheduleData); ?>;
        
        function showDayDetails(date) {
            const dayData = scheduleData[date] || {reservations: [], blocked: false};
            const dateObj = new Date(date);
            const formattedDate = dateObj.toLocaleDateString('id-ID', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            let content = `<h6>${formattedDate}</h6>`;
            
            if (dayData.blocked) {
                content += `
                    <div class="alert alert-danger">
                        <i class="fas fa-times-circle"></i> Studio tutup pada hari ini
                    </div>
                `;
            } else if (dayData.reservations.length === 0) {
                content += `
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Studio tersedia sepanjang hari
                    </div>
                    <p>Anda dapat membuat reservasi untuk tanggal ini.</p>
                `;
            } else {
                content += `<h6>Jadwal Reservasi:</h6>`;
                content += `<div class="table-responsive">`;
                content += `<table class="table table-sm">`;
                content += `<thead><tr><th>Waktu</th><th>Paket</th><th>Customer</th><th>Status</th></tr></thead>`;
                content += `<tbody>`;
                
                dayData.reservations.forEach(reservation => {
                    const startTime = new Date('2000-01-01 ' + reservation.start_time).toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'});
                    const endTime = new Date('2000-01-01 ' + reservation.end_time).toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'});
                    
                    let statusBadge = '';
                    switch(reservation.status) {
                        case 'confirmed': statusBadge = '<span class="badge bg-success">Dikonfirmasi</span>'; break;
                        case 'pending': statusBadge = '<span class="badge bg-warning">Menunggu</span>'; break;
                        case 'completed': statusBadge = '<span class="badge bg-info">Selesai</span>'; break;
                        case 'in_progress': statusBadge = '<span class="badge bg-primary">Berlangsung</span>'; break;
                        default: statusBadge = '<span class="badge bg-secondary">' + reservation.status + '</span>';
                    }
                    
                    content += `
                        <tr>
                            <td>${startTime} - ${endTime}</td>
                            <td>${reservation.package_name}</td>
                            <td>${reservation.customer_name}</td>
                            <td>${statusBadge}</td>
                        </tr>
                    `;
                });
                
                content += `</tbody></table></div>`;
                
                // Show available time slots
                content += `<div class="alert alert-info mt-3">`;
                content += `<i class="fas fa-info-circle"></i> Untuk melihat slot waktu yang tersedia, silakan gunakan form booking.`;
                content += `</div>`;
            }
            
            document.getElementById('dayDetailsContent').innerHTML = content;
            new bootstrap.Modal(document.getElementById('dayDetailsModal')).show();
        }
    </script>
</body>
</html>
