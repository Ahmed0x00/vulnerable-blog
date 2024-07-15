<?php
session_start();
include '../config/dbconnect.php';

if (!isset($_COOKIE['session_id'])) {
    header("Location: ../public/login.php");
    exit();
}

$sessionId = $_COOKIE['session_id'];

$result = $conn->query("SELECT username FROM sessions WHERE session_id = '$sessionId'");

if ($row = $result->fetch_assoc()) {
    $loggedInUsername = $row['username'];
    if ($loggedInUsername == 'admin') {
        $isAdmin = true;
    } else {
        $isAdmin = false;
    }
} else {
    echo 'Invalid session';
    $isAdmin = false;
}

if (!$isAdmin) {
    header('HTTP/1.1 403 Forbidden');
    echo 'Unauthorized access';
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Admin Panel</h1>
        <ul class="list-group mt-4">
            <li class="list-group-item">
                <a href="view_accounts.php" class="btn btn-primary">View User Accounts</a>
            </li>
        </ul>
    </div>
</body>
</html>
