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

$result = $conn->query("SELECT username, email FROM users");
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Accounts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">View Accounts</h1>
        <table class="table table-striped mt-4">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['username']; ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <form action="../update_data/delete_account.php" method="post" style="display: inline;">
                                <input type="hidden" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this account?');">Delete</button>
                            </form>
                            <form action="../public/settings.php" method="get" style="display: inline;">
                                <input type="hidden" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">
                                <button type="submit" class="btn btn-warning">Update Settings</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <br>
        <a href="../public/welcome.php?username=<?php echo htmlspecialchars($loggedInUsername); ?>" class="btn btn-secondary">Back to Welcome Page</a>
    </div>
</body>
</html>
