<?php
session_start();
include '../config/dbconnect.php'; // Assuming dbconnect.php includes any necessary initialization
include '../public/functions/csrf.php';

// Check if the user is logged in by verifying the session ID cookie
if (!isset($_COOKIE['session_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $new_username = $_POST['username'];
    $target_username = $_GET['username']; // Get the target username from GET parameter

    if ($target_username == "admin") {
        die("admin username can't be updated!");
    }

    if ($new_username === "admin") {
        die("You can't be an admin!");
    } 

    // Retrieve the target user from the users table
    $fetch_stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $fetch_stmt->bind_param("s", $target_username);
    $fetch_stmt->execute();
    $fetch_result = $fetch_stmt->get_result();

    // Initialize a flag to check if the user exists
    $user_exists = false;

    // Check if the target user exists
    if ($fetch_row = $fetch_result->fetch_assoc()) {
        $user_exists = true;
    } else {
        die("Target user not found!");
    }

    // Update username in users table
    $update_users_stmt = $conn->prepare("UPDATE users SET username = ? WHERE username = ?");
    $update_users_stmt->bind_param("ss", $new_username, $target_username);
    $update_users_result = $update_users_stmt->execute();

    if ($update_users_result) {
        echo "<div class='alert alert-success'>Username updated successfully!</div>";

        // If the user exists, update the username in sessions table
        if ($user_exists) {
            $update_sessions_stmt = $conn->prepare("UPDATE sessions SET username = ? WHERE username = ?");
            $update_sessions_stmt->bind_param("ss", $new_username, $target_username);
            $update_sessions_stmt->execute();
            $update_sessions_stmt->close();
        }
    } else {
        echo "<div class='alert alert-danger'>Error updating username in users table: " . $update_users_stmt->error . "</div>";
    }

    $update_users_stmt->close();
    $fetch_stmt->close();
}

mysqli_close($conn);
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
