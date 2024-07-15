<?php
session_start();
include '../config/dbconnect.php';
include '../public/functions/csrf.php';

if (!isset($_COOKIE['session_id'])) {
    header("Location: ../login.php");
    exit();
}

$sessionId = $_COOKIE['session_id'];

$stmt = $conn->prepare("SELECT username FROM sessions WHERE session_id = ?");
$stmt->bind_param("s", $sessionId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $loggedInUsername = $row['username'];
} else {
    header("Location: ../login.php");
    exit();
}

$stmt->close();

$isAdmin = ($loggedInUsername === 'admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $target_username = isset($_GET['username']) ? $_GET['username'] : null;

    if ($new_password !== $confirm_password) {
        echo '<div class="alert alert-danger">New password and confirm password do not match.</div>';
        exit();
    }

    if ($target_username === 'admin' && !$isAdmin) {
        die('<div class="alert alert-danger">Unauthorized: Only admin can change his password</div>');
    }

    if ($target_username === null) {
        $target_username = $loggedInUsername;
    }

    $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
    $update_stmt->bind_param("ss", $new_password, $target_username);
    $update_result = $update_stmt->execute();

    if ($update_result) {
        echo '<div class="alert alert-success">Password updated successfully!</div>';
    } else {
        echo '<div class="alert alert-danger">Error updating password: ' . htmlspecialchars($update_stmt->error) . '</div>';
    }

    $update_stmt->close();
    mysqli_close($conn);
}
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

