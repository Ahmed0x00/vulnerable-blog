<?php
session_start();
include '../config/dbconnect.php';

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

if ($loggedInUsername !== 'admin') {
    die("Unauthorized access: Only admin can change other passwords.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $target_username = isset($_GET['username']) ? $_GET['username'] : null;

    if ($new_password !== $confirm_password) {
        echo '<div class="alert alert-danger">New password and confirm password do not match.</div>';
        exit();
    }

    $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
    $update_stmt->bind_param("ss", $new_password, $target_username);
    $update_result = $update_stmt->execute();

    if ($update_result) {
        echo '<div class="alert alert-success">Password updated successfully for user: ' . htmlspecialchars($target_username) . '</div>';
    } else {
        echo '<div class="alert alert-danger">Error updating password: ' . $update_stmt->error . '</div>';
    }

    $update_stmt->close();
    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change User Password (Admin Only)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h1 class="text-center">Change User Password (Admin Only)</h1>
        <form id="changePasswordAdminForm" action="change_password_admin.php?username=<?php echo urlencode($_GET['username']); ?>" method="post" class="mt-4">
            <div class="mb-3">
                <label for="new_password" class="form-label">New Password:</label>
                <input type="password" id="new_password" name="new_password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm New Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
            </div>
            <input type="hidden" id="csrf_token" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <button type="submit" name="submit" class="btn btn-primary">Change Password</button>
        </form>
    </div>
</body>
</html>
