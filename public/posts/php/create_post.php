<?php
session_start();
header('Content-Type: application/json');

include '../../../config/dbconnect.php';
include '../../functions/csrf.php';

if (!isset($_COOKIE['session_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}


$sessionId = $_COOKIE['session_id'];

$result = $conn->query("SELECT username FROM sessions WHERE session_id = '$sessionId'");

if ($row = $result->fetch_assoc()) {
    $username = $row['username'];
} else {
    echo json_encode(['error' => 'Invalid session']);
    exit();
}

$userResult = $conn->query("SELECT membership FROM users WHERE username = '$username'");
$userRow = $userResult->fetch_assoc();
$membershipStatus = $userRow['membership'] ?? 'free';

$posts_file = '../../data/posts.json';
if (file_exists($posts_file)) {
    $posts = json_decode(file_get_contents($posts_file), true);
    if ($posts === null && json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['error' => 'Failed to decode JSON: ' . json_last_error_msg()]);
        exit();
    }
} else {
    $posts = [];
}

$userPostsCount = count(array_filter($posts, function ($post) use ($username) {
    return $post['username'] === $username;
}));

// Limit free membership users to 5 posts
if ($membershipStatus === 'free' && $userPostsCount >= 5) {
    echo json_encode(['error' => 'Post limit reached. Upgrade your membership to create more posts.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';

    if (empty($title) || empty($content)) {
        echo json_encode(['error' => 'Title and content are required']);
        exit();
    }

    $new_post = [
        'id' => count($posts) + 1,
        'username' => $username,
        'title' => htmlspecialchars($title),
        'content' => htmlspecialchars($content),
        'created_at' => date('Y-m-d H:i:s')
    ];
    $posts[] = $new_post;

    $json_data = json_encode($posts, JSON_PRETTY_PRINT);
    if ($json_data === false) {
        echo json_encode(['error' => 'Failed to encode JSON: ' . json_last_error_msg()]);
        exit();
    }

    if (file_put_contents($posts_file, $json_data) === false) {
        echo json_encode(['error' => 'Failed to save post']);
        exit();
    }

    echo json_encode(['success' => 'Post created successfully']);
    header("Location: ../html/view_all_posts.html");

} else {
    echo json_encode(['error' => 'Invalid request method']);
}

mysqli_close($conn);
?>
