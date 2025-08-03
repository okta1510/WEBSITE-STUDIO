<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo 'Access denied';
    exit();
}

$paymentId = $_GET['payment_id'] ?? null;
if (!$paymentId) {
    http_response_code(400);
    echo 'Invalid payment ID';
    exit();
}

try {
    // Get payment details
    $payment = fetchOne("
        SELECT p.*, r.customer_id, r.id as reservation_id
        FROM payments p
        JOIN reservations r ON p.reservation_id = r.id
        WHERE p.id = ?
    ", [$paymentId]);
    
    if (!$payment) {
        http_response_code(404);
        echo 'Payment not found';
        exit();
    }
    
    // Check access rights
    $isAdmin = $_SESSION['role'] === 'admin';
    $isOwner = $payment['customer_id'] == $_SESSION['user_id'];
    
    if (!$isAdmin && !$isOwner) {
        http_response_code(403);
        echo 'Access denied';
        exit();
    }
    
    if (!$payment['payment_proof']) {
        http_response_code(404);
        echo 'No payment proof available';
        exit();
    }
    
    $filePath = 'uploads/payment_proofs/' . $payment['payment_proof'];
    
    if (!file_exists($filePath)) {
        http_response_code(404);
        echo 'File not found';
        exit();
    }
    
    // Get file info
    $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $fileName = 'bukti_pembayaran_' . $payment['reservation_id'] . '_' . $paymentId . '.' . $fileExtension;
    
    // Set appropriate headers
    if ($fileExtension === 'pdf') {
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $fileName . '"');
    } else {
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png'
        ];
        
        $mimeType = $mimeTypes[$fileExtension] ?? 'application/octet-stream';
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: inline; filename="' . $fileName . '"');
    }
    
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: private, max-age=3600');
    
    // Output file
    readfile($filePath);
    
} catch (Exception $e) {
    http_response_code(500);
    echo 'Server error';
    error_log('Error in view-payment-proof.php: ' . $e->getMessage());
}
?>
