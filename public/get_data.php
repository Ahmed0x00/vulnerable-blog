<?php
session_start();
include '../config/dbconnect.php';

if (!isset($_COOKIE['session_id'])) {
    header("Location: login.php");
    exit();
}

$sessionId = $_COOKIE['session_id'];

$userId = isset($_GET['id']) ? intval($_GET['id']) : null;
if ($userId === null) {
    die("Invalid user ID");
}

// Fetch user data based on user ID
$user_stmt = $conn->prepare("SELECT username, email, membership, credits FROM users WHERE id = ?");
$user_stmt->bind_param("i", $userId);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();
$user_stmt->close();

if (!$user_data) {
    echo "User not found.";
    exit();
}

$data = [
    'username' => $user_data['username'],
    'email' => $user_data['email'],
    'membership' => $user_data['membership'],
    'credits' => $user_data['credits']
];

// Fetch discount code based on username
$code_stmt = $conn->prepare("SELECT code, used FROM discount_codes WHERE username = ?");
$code_stmt->bind_param("s", $data['username']);
$code_stmt->execute();
$code_result = $code_stmt->get_result();
$code_data = $code_result->fetch_assoc();
$code_stmt->close();

$data['discount_code'] = $code_data['code'] ?? 'No code available';
$data['discount_used'] = ($code_data['used'] == 0) ? "no" : "yes";

// Count posts and comments in JSON files
$files = ['posts', 'comments'];
foreach ($files as $file) {
    $file_path = "../public/api/{$file}.json";
    $count = 0;
    if (file_exists($file_path)) {
        $json = file_get_contents($file_path);
        $items = json_decode($json, true);
        if ($items !== null) {
            foreach ($items as $item) {
                if (isset($item['username']) && $item['username'] === $data['username']) {
                    $count++;
                }
            }
        }
    }
    $data["number_of_{$file}"] = $count;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Data</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <header class="text-center mb-4">
            <h1 class="text-primary">User Data</h1>
        </header>

        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title">User Information</h2>
                <p><strong>Username:</strong> <?php echo $data['username']; ?></p>
                <p><strong>Email:</strong> <?php echo $data['email']; ?></p>
                <p><strong>Membership:</strong> <?php echo $data['membership']; ?></p>
                <p><strong>Credits:</strong> <?php echo $data['credits']; ?></p>
                <p><strong>Discount Code:</strong> <?php echo $data['discount_code']; ?></p>
                <p><strong>Discount Used:</strong> <?php echo $data['discount_used']; ?></p>
                <p><strong>Number of Posts:</strong> <?php echo $data['number_of_posts']; ?></p>
                <p><strong>Number of Comments:</strong> <?php echo $data['number_of_comments']; ?></p>
            </div>
        </div>

        <a href="welcome.php" class="btn btn-primary me-2" style="padding: 10px 20px;">Back to Welcome Page</a>
        <form action="logout.php" method="post" style="display:inline;">
            <button type="submit" class="btn btn-danger" style="padding: 10px 20px;">Logout</button>
        </form>
    </div>
</body>
</html>
