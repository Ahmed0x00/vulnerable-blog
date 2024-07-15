<?php
session_start();
header('Content-Type: application/json');

include '../config/dbconnect.php';

if (!isset($_COOKIE['session_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$sessionId = $_COOKIE['session_id'];

$stmt = $conn->prepare("SELECT username FROM sessions WHERE session_id = ?");
$stmt->bind_param("s", $sessionId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $username = $row['username'];
} else {
    echo json_encode(['error' => 'Invalid session']);
    exit();
}

$stmt->close();

$stmt = $conn->prepare("SELECT credits FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $credits = $row['credits'];
    echo json_encode(['credits' => $credits]);
} else {
    echo json_encode(['error' => 'User not found']);
}

$stmt->close();
mysqli_close($conn);
?>
