<?php
session_start();
require_once 'config/database.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['date']) || !isset($input['duration'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit();
}

$date = $input['date'];
$durationMinutes = intval($input['duration']); // Duration in minutes

try {
    // Get booked times for the selected date
    $bookedTimes = fetchAll("
        SELECT start_time, end_time 
        FROM reservations 
        WHERE reservation_date = ? 
        AND status NOT IN ('cancelled') 
        ORDER BY start_time
    ", [$date]);
    
    if (!$bookedTimes) {
        $bookedTimes = [];
    }
    
    // Check if the date is blocked
    $blockedDate = fetchOne("
        SELECT * FROM schedules 
        WHERE date = ? AND is_available = 0
    ", [$date]);
    
    if ($blockedDate) {
        echo json_encode([
            'availableTimes' => [],
            'message' => 'Studio tutup pada tanggal ini'
        ]);
        exit();
    }
    
    // Define studio operating hours (8 AM to 9 PM)
    $studioOpenMinutes = 8 * 60; // 8:00 AM in minutes
    $studioCloseMinutes = 21 * 60; // 9:00 PM in minutes

    // Generate all possible time slots (every 30 minutes)
    $allTimeSlots = [];
    for ($minutes = $studioOpenMinutes; $minutes <= $studioCloseMinutes - $durationMinutes; $minutes += 30) {
        $hour = intval($minutes / 60);
        $minute = $minutes % 60;
        $timeSlot = sprintf('%02d:%02d', $hour, $minute);
        $allTimeSlots[] = $timeSlot;
    }
    
    // Filter out unavailable time slots
    $availableTimes = [];
    
    foreach ($allTimeSlots as $timeSlot) {
        $startTime = $timeSlot;

        // Calculate end time in minutes
        list($startHour, $startMinute) = explode(':', $startTime);
        $startTotalMinutes = ($startHour * 60) + $startMinute;
        $endTotalMinutes = $startTotalMinutes + $durationMinutes;

        $endHour = intval($endTotalMinutes / 60);
        $endMinute = $endTotalMinutes % 60;
        $endTime = sprintf('%02d:%02d', $endHour, $endMinute);

        // Check if this time slot conflicts with any booked time
        $isAvailable = true;

        foreach ($bookedTimes as $booked) {
            $bookedStart = $booked['start_time'];
            $bookedEnd = $booked['end_time'];

            // Convert to minutes for precise comparison
            list($bookedStartHour, $bookedStartMinute) = explode(':', $bookedStart);
            list($bookedEndHour, $bookedEndMinute) = explode(':', $bookedEnd);

            $bookedStartMinutes = ($bookedStartHour * 60) + $bookedStartMinute;
            $bookedEndMinutes = ($bookedEndHour * 60) + $bookedEndMinute;

            // Check if there's any overlap
            if (($startTotalMinutes >= $bookedStartMinutes && $startTotalMinutes < $bookedEndMinutes) ||
                ($endTotalMinutes > $bookedStartMinutes && $endTotalMinutes <= $bookedEndMinutes) ||
                ($startTotalMinutes <= $bookedStartMinutes && $endTotalMinutes >= $bookedEndMinutes)) {
                $isAvailable = false;
                break;
            }
        }

        if ($isAvailable) {
            $availableTimes[] = $startTime;
        }
    }
    
    // Check if selected date is in the past
    $today = date('Y-m-d');
    if ($date < $today) {
        echo json_encode([
            'availableTimes' => [],
            'message' => 'Tidak dapat membuat reservasi untuk tanggal yang sudah lewat'
        ]);
        exit();
    }
    
    // If date is today, filter out past hours
    if ($date === $today) {
        $currentHour = intval(date('H'));
        $availableTimes = array_filter($availableTimes, function($time) use ($currentHour) {
            $timeHour = intval(substr($time, 0, 2));
            return $timeHour > $currentHour;
        });
        $availableTimes = array_values($availableTimes); // Re-index array
    }
    
    echo json_encode([
        'availableTimes' => $availableTimes,
        'bookedTimes' => $bookedTimes,
        'message' => count($availableTimes) > 0 ? 'Waktu tersedia' : 'Tidak ada waktu tersedia'
    ]);
    
} catch (Exception $e) {
    error_log('Error in check-availability.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
?>
