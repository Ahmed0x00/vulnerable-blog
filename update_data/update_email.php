<?php
session_start();
include '../config/dbconnect.php';

if (!isset($_COOKIE['session_id'])) {
    header("Location: ../public/login.php");
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
    header("Location: ../public/login.php");
    exit();
}
$stmt->close();

$isAdmin = ($loggedInUsername === 'admin');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_email = $_POST['email'];
    $target_username = isset($_GET['username']) ? $_GET['username'] : null;

    if ($target_username !== null && !$isAdmin) {
        echo "<div class='alert alert-danger'>Unauthorized: Only admin can update another user's email.</div>";
    } else {
        if ($target_username === null) {
            $target_username = $loggedInUsername;
        }

        $update_stmt = $conn->prepare("UPDATE users SET email = ? WHERE username = ?");
        $update_stmt->bind_param("ss", $new_email, $target_username);
        $update_result = $update_stmt->execute();

        if ($update_result) {
            echo "<div class='alert alert-success'>Email updated successfully!</div>";
        } else {
            echo "<div class='alert alert-danger'>Error updating email: " . $update_stmt->error . "</div>";
        }

        $update_stmt->close();
    }
}

mysqli_close($conn);
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
