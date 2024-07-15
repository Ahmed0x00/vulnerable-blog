<?php
session_start();
include '../config/dbconnect.php';

if (!isset($_COOKIE['session_id'])) {
    header("Location: http://127.0.0.1/vulnerable-blog/public/login.php");
    exit();
}

$sessionId = $_COOKIE['session_id'];

$stmt = $conn->prepare("SELECT users.username, users.credits FROM users JOIN sessions ON users.username = sessions.username WHERE sessions.session_id = ?");
$stmt->bind_param("s", $sessionId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $username = $row['username'];
    $credits = $row['credits'];
} else {
    echo 'Invalid session';
    exit();
}

$stmt->close();

if (!empty($_POST['discount_code'])) {
    $discountCode = $_POST['discount_code'];

    include 'submit_code.php'; 
}


$totalCost = 100 - $credits;

if ($credits >= $totalCost) {
    $updateStmt = $conn->prepare("UPDATE users SET membership = 'premium' WHERE username = ?");
    $updateStmt->bind_param("s", $username);
    if ($updateStmt->execute()) {
        echo 'You have successfully upgraded to premium membership!';
    } else {
        echo 'Failed to upgrade to premium membership. Please try again later.';
    }
    $updateStmt->close();
} else {
    echo 'Insufficient credits. You need at least ' . $totalCost . ' credits to upgrade to premium membership.';
}

mysqli_close($conn);
?>
