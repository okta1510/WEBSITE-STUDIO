<?php
// Auto redirect untuk file yang tidak ada
$requestUri = $_SERVER['REQUEST_URI'];
$requestedFile = basename(parse_url($requestUri, PHP_URL_PATH));

// Mapping file yang tidak ada ke file yang ada
$redirectMap = [
    // Admin redirects
    'photographers.php' => 'customers.php',
    'availability.php' => 'schedules.php',
    'blocked-dates.php' => 'schedules.php',
    'payments-pending.php' => 'payments.php',
    'invoices.php' => 'payments.php',
    'reports-reservations.php' => 'reports.php',
    'reports-revenue.php' => 'reports.php',
    'reports-customers.php' => 'reports.php',
    'reports-packages.php' => 'reports.php',
    'settings.php' => 'dashboard.php',
    
    // Customer redirects
    'reservations-pending.php' => 'my-reservations.php',
    'reservations-today.php' => 'my-reservations.php',
    
    // Other redirects
    '404.php' => 'index.php',
    '500.php' => 'index.php'
];

// Cek apakah file yang diminta ada dalam mapping
if (isset($redirectMap[$requestedFile])) {
    $targetFile = $redirectMap[$requestedFile];
    
    // Cek apakah kita di folder admin
    if (strpos($requestUri, '/admin/') !== false) {
        $redirectUrl = '/admin/' . $targetFile;
    } else {
        $redirectUrl = '/' . $targetFile;
    }
    
    // Redirect dengan pesan
    header("Location: $redirectUrl?redirected=1&from=" . urlencode($requestedFile));
    exit();
}

// Jika tidak ada mapping, redirect ke homepage
header("Location: /index.php?error=404&file=" . urlencode($requestedFile));
exit();
?>
