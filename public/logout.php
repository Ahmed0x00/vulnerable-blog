<?php
session_start();
include '../config/dbconnect.php';

if (!isset($_COOKIE['session_id'])) {
    header("Location: login.php");
    exit();
}

$sessionId = $_COOKIE['session_id'];

$stmt = $conn->prepare("SELECT username FROM sessions WHERE session_id = ?");
$stmt->bind_param("s", $sessionId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $username = $row['username'];

    // Delete discount code associated with the username
    $delete_code_stmt = $conn->prepare("DELETE FROM discount_codes WHERE username = ?");
    $delete_code_stmt->bind_param("s", $username);
    $delete_code_stmt->execute();
    $delete_code_stmt->close();
}

$stmt->close();

// Delete session ID record from sessions table
$delete_stmt = $conn->prepare("DELETE FROM sessions WHERE session_id = ?");
$delete_stmt->bind_param("s", $sessionId);
$delete_result = $delete_stmt->execute();
$delete_stmt->close();

setcookie("session_id", "", time() - 3600, "/");

session_unset();
session_destroy();

mysqli_close($conn);

echo "Logging out...";
header("Location: login.php");
exit();
?>
