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

$stmt = $conn->prepare("SELECT username FROM sessions WHERE session_id = ?");
$stmt->bind_param("s", $sessionId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $loggedInUsername = $row['username'];
} else {
    echo json_encode(['error' => 'Invalid session']);
    exit();
}

$stmt->close();



if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $postId = $_POST['id'] ?? '';
    $newTitle = $_POST['title'] ?? '';
    $newContent = $_POST['content'] ?? '';

    if (empty($postId) || empty($newTitle) || empty($newContent)) {
        echo json_encode(['error' => 'Post ID, title, and content are required']);
        exit();
    }

    $postUrl = "http://127.0.0.1/vulnerable-blog/public/posts/php/get_posts.php?id=$postId";
    $postResponse = file_get_contents($postUrl);
    $postData = json_decode($postResponse, true);

    if (!$postData['success'] || empty($postData['posts'])) {
        echo json_encode(['error' => 'Post not found']);
        exit();
    }

    $post = $postData['posts'][0];
    $postUsername = $post['username'];

    if ($postUsername !== $loggedInUsername && $loggedInUsername !== 'admin') {
        echo json_encode(['error' => 'Unauthorized to edit this post']);
        exit();
    }

    $postFile = '../../data/posts.json';
    $postsData = json_decode(file_get_contents($postFile), true);

    $found = false;
    foreach ($postsData as &$post) {
        if ($post['id'] == $postId) {
            $post['title'] = htmlspecialchars($newTitle);
            $post['content'] = htmlspecialchars($newContent);
            $post['updated_at'] = date('Y-m-d H:i:s');
            $found = true;
            break;
        }
    }

    if (!$found) {
        echo json_encode(['error' => 'Post not found in JSON file']);
        exit();
    }

    if (file_put_contents($postFile, json_encode($postsData, JSON_PRETTY_PRINT))) {
        echo json_encode(['success' => 'Post updated successfully']);
        header("Location: ../html/view_all_posts.html");
    } else {
        echo json_encode(['error' => 'Failed to update post']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method']);
}

mysqli_close($conn);
?>
