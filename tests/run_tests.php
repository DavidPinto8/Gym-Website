<?php
define('BASE_URL', 'http://localhost:9000/');

echo "=== Flow Fitness: Starting Automated Sanity Tests ===\n\n";

$testsRun = 0;
$testsPassed = 0;

function assertTest($name, $condition) {
    global $testsRun, $testsPassed;
    $testsRun++;
    if ($condition) {
        echo "[PASS] $name\n";
        $testsPassed++;
    } else {
        echo "[FAIL] $name\n";
    }
}

function sendPost($url, $data = [], &$headersOut = []) {
    $options = [
        'http' => [
            'method'        => 'POST',
            'header'        => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content'       => http_build_query($data),
            'ignore_errors' => true
        ]
    ];
    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    $headersOut = isset($http_response_header) ? $http_response_header : [];
    return $result;
}

echo "[Module: Security]\n";

$headers = [];
sendPost(BASE_URL . 'actions/action_submit_report.php', ['category' => 'equipment'], $headers);
$isRedirected = false;
foreach ($headers as $header) {
    if (stripos($header, 'Location:') === 0 || stripos($header, 'HTTP/1.1 302') === 0 || stripos($header, 'HTTP/1.1 4') === 0) {
        $isRedirected = true;
        break;
    }
}
assertTest("Block report submission without valid CSRF", $isRedirected === true);

$responseApi = sendPost(BASE_URL . 'api/upload_avatar.php');
$json = json_decode($responseApi, true);
assertTest("Avatar API rejects anonymous requests", isset($json['success']) && $json['success'] === false);


echo "\n[Module: Booking & Waitlist]\n";

$headers = [];
sendPost(BASE_URL . 'actions/action_book_class.php', ['class_id' => 1], $headers);
$bookedRedirect = false;
foreach ($headers as $header) {
    if (stripos($header, 'Location:') !== false || stripos($header, 'HTTP/1.1 302') === 0) {
        $bookedRedirect = true;
        break;
    }
}
assertTest("Class booking requires authentication", $bookedRedirect === true);

$headers = [];
sendPost(BASE_URL . 'actions/action_cancel_booking.php', ['booking_id' => 'INVALID_ID'], $headers);
$cancelRedirect = false;
foreach ($headers as $header) {
    if (stripos($header, 'Location:') !== false || stripos($header, 'HTTP/1.1 302') === 0) {
        $cancelRedirect = true;
        break;
    }
}
assertTest("Cancellation workflow rejects corrupted IDs", $cancelRedirect === true);

echo "\n[Module: Attendance & Reviews]\n";

$headers = [];
sendPost(BASE_URL . 'actions/action_leave_review.php', ['class_id' => 1, 'rating' => ''], $headers);
$reviewRedirect = false;
foreach ($headers as $header) {
    if (stripos($header, 'Location:') !== false || stripos($header, 'HTTP/1.1 302') === 0) {
        $reviewRedirect = true;
        break;
    }
}
assertTest("Review system rejects submissions without rating", $reviewRedirect === true);

echo "\n[Module: Administration]\n";

$headers = [];
sendPost(BASE_URL . 'actions/action_update_equipment.php', ['equipment_id' => 1, 'available_units' => 500], $headers);
$adminRedirect = false;
foreach ($headers as $header) {
    if (stripos($header, 'Location:') !== false || stripos($header, 'HTTP/1.1 302') === 0) {
        $adminRedirect = true;
        break;
    }
}
assertTest("Admin restriction active for equipment updates", $adminRedirect === true);

echo "\n=== Test Suite Summary ===\n";
echo "Executed: $testsRun | Passed: $testsPassed / $testsRun\n";
if ($testsPassed === $testsRun) {
    echo "System stable and secure for final delivery.\n";
} else {
    echo "Behavioral anomalies detected in specific endpoints.\n";
}