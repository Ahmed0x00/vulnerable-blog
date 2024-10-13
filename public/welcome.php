<?php
session_start();
include '../config/dbconnect.php';
include 'functions/csrf.php';

$token = isset($_GET['token']) ? $_GET['token'] : '';
header("Content-Security-Policy: script-src 'self'; report-uri /csp-report?token=" . $token);

if (!isset($_COOKIE['session_id'])) {
    header("Location: login.php");
    exit();
}

function containsXSS($input) {
    $patterns = [
        '/<script\b[^>]*>(.*?)<\/script>/is',
        '/<img\b[^>]*src=[^>]+onerror\s*=[^>]+>/is',
        '/<[^>]+on\w+\s*=[^>]+>/is',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $input)) {
            return true;
        }
    }
    return false;
}

if (isset($_GET['username'])) {
    $username = $_GET['username'];

    if (containsXSS($username)) {
        echo '<p class="alert alert-danger">HAHAHAHA XSS is not possible here, you loser!</p>';
    }

} else {
    $sessionId = $_COOKIE['session_id'];
    $result = $conn->query("SELECT username FROM sessions WHERE session_id = '$sessionId'");

    if ($row = $result->fetch_assoc()) {
        $username = $row['username'];
    } else {
        header("Location: login.php");
        exit();
    }
}

$isAdmin = ($username === 'admin');

$codeResult = $conn->query("SELECT code FROM discount_codes WHERE username = '$username'");
$discountCode = $codeResult->fetch_assoc()['code'] ?? 'No code available';

$userResult = $conn->query("SELECT id FROM users WHERE username = '$username'");
$userId = $userResult->fetch_assoc()['id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <header class="text-center mb-4">
            <h1 class="text-primary">Welcome, <?php echo $username; ?>!</h1>
            <p class="alert alert-success">You are logged in successfully.</p>
        </header>

        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title">Premium Membership (buy it to create more than 5 posts)</h2>
                <p>Your Credits: <span id="credits" class="font-weight-bold">Loading...</span></p>
                <p>Your discount code (use it to gain 10 more credits): <strong><?php echo htmlspecialchars($discountCode); ?></strong></p>
                <a class="btn btn-outline-primary" href="../premium_membership/check_premium_membership.php">Upgrade to Premium Membership</a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title">Upload an Image</h2>
                <form action="upload.php" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="fileToUpload" class="form-label">Select image to upload:</label>
                        <input type="file" name="fileToUpload" id="fileToUpload" class="form-control">
                    </div>
                    <button type="submit" name="submit" class="btn btn-primary">Upload Image</button>
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title">See your data</h2>
                <a class="btn btn-info" href="get_data.php?id=<?php echo $userId; ?>"> View My Data</a>
            </div>
        </div>

        <nav class="mb-4">
            <h3>Quick Links</h3>
            <ul class="list-group">
                <li class="list-group-item"><a href="settings.php">Update your settings</a></li>
                <li class="list-group-item"><a href="posts/html/view_all_posts.html">See all posts</a></li>
                <li class="list-group-item"><a href="posts/html/create_post.html">Create a post</a></li>
            </ul>
        </nav>

        <?php if ($isAdmin): ?>
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="card-title">Admin Panel</h2>
                    <a class="btn btn-warning" href="../admin/admin_panel.php">Go to Admin Panel</a>
                </div>
            </div>
        <?php endif; ?>

        <form action="logout.php" method="post" class="text-center">
            <button type="submit" class="btn btn-danger">Logout</button>
        </form>
    </div>

    <script src="../JS/get_credits.js"></script>
</body>
</html>
