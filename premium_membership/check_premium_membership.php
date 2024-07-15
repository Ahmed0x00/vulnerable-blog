<?php
session_start();
include '../config/dbconnect.php';

if (!isset($_COOKIE['session_id'])) {
    header("Location: http://127.0.0.1/vulnerable-blog/public/login.php");
    exit();
}

$sessionId = $_COOKIE['session_id'];

$stmt = $conn->prepare("SELECT users.username, users.membership FROM users JOIN sessions ON users.username = sessions.username WHERE sessions.session_id = ?");
$stmt->bind_param("s", $sessionId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $membership = $row['membership'];
} else {
    echo 'Invalid session';
    exit();
}

$stmt->close();

if ($membership === 'premium') {
    echo 'You already have a premium membership.';
    echo '<br><a href="welcome.php">Back to Welcome Page</a>';
} else {
    header("Location: premium_membership.html");
    exit();
}

mysqli_close($conn);
?>
