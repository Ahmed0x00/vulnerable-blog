<?php
session_start();
include '../config/dbconnect.php'; 

if (!isset($_COOKIE['session_id'])) {
    // If not logged in, redirect to login page
    header("Location: login.php");
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
    header("Location: login.php");
    exit();
}

$stmt->close();

$isAdmin = $loggedInUsername === 'admin';

function canEditUser($loggedInUsername, $targetUsername, $isAdmin) {
    return $isAdmin || $loggedInUsername === $targetUsername;
}

$userToUpdate = $loggedInUsername;
$formActionUsername = "../update_data/update_username.php";
$formActionEmail = "../update_data/update_email.php";
$formActionPassword = "../update_data/change_password.php";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
    <div class="container mt-5">
        <h1>Update Your Settings</h1>

        <?php if ($isAdmin): ?>
            <form id="usernameFormAdmin" action="<?php echo $formActionUsername; ?>?username=<?php echo $_GET['username']; ?>" method="post" class="mb-4">
                <div class="mb-3">
                    <label for="target_username" class="form-label">Target Username (Admin Only):</label>
                    <input type="text" id="target_username" name="username" class="form-control" required>
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                </div>
                <button type="submit" name="submit" class="btn btn-primary">Update Username</button>
            </form>
            
            <form id="emailFormAdmin" action="<?php echo $formActionEmail; ?>?username=<?php echo $_GET['username']; ?>" method="post" class="mb-4">
                <div class="mb-3">
                    <label for="email" class="form-label">Target Email (Admin Only):</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                </div>
                <button type="submit" name="submit" class="btn btn-primary">Update Email</button>
            </form>

            <form action="../update_data/change_password_admin.php" method="GET" class="mb-4">
                <input type="hidden" name="username" value="<?php echo isset($_GET['username']) ? $_GET['username'] : ''; ?>">
                <button type="submit" name="submit" class="btn btn-secondary">Change Password</button>
            </form>
        
        <?php else: ?>
            <form id="usernameFormUser" action="<?php echo $formActionUsername; ?>?username=<?php echo $loggedInUsername; ?>" method="post" class="mb-4">
                <div class="mb-3">
                    <label for="username" class="form-label">New Username:</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                </div>
                <button type="submit" class="btn btn-primary">Update Username</button>
            </form>
            
            <form id="emailForm" action="<?php echo $formActionEmail; ?>" method="post" class="mb-4">
                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                </div>
                <button type="submit" class="btn btn-primary">Update Email</button>
            </form>

            <form action="../update_data/change_password.html" method="GET" class="mb-4">
                <button type="submit" name="submit" class="btn btn-secondary">Change Password</button>
            </form>
        <?php endif; ?>

        <form id="deleteAccountForm" action="../update_data/delete_account.php" method="post" class="mb-4">
            <input type="hidden" id="usernameToDelete" name="username" value="<?php echo $userToUpdate; ?>">
            <button type="submit" class="btn btn-danger">Delete Account</button>
        </form>

        <a href="welcome.php?username=<?php echo $loggedInUsername; ?>" class="btn btn-link">Back to Welcome Page</a>
    </div>
</body>
</html>
