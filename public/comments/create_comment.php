<?php
session_start();
header('Content-Type: application/json');

include '../../config/dbconnect.php';

if (!isset($_COOKIE['session_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    exit('Method not allowed');
}

if (!isset($_POST['postId']) || empty($_POST['comment'])) {
    http_response_code(400); // Bad Request
    exit('Post ID and comment are required');
}

$sessionId = $_COOKIE['session_id'];
$result = $conn->query("SELECT username FROM sessions WHERE session_id = '$sessionId'");

if ($row = $result->fetch_assoc()) {
    $username = $row['username'];
} else {
    echo json_encode(['error' => 'Invalid session']);
    exit();
}

$postId = $_POST['postId'];
$comment = $_POST['comment'];

$comments = json_decode(file_get_contents('../api/comments.json'), true);

$newComment = array(
    'id' => count($comments) + 1,
    'postId' => $postId,
    'username' => $username,
    'comment' => htmlspecialchars($comment),
);

$comments[] = $newComment;

if (file_put_contents('../api/comments.json', json_encode($comments, JSON_PRETTY_PRINT))) {
    echo json_encode(array('success' => true));
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(array('error' => 'Failed to save comment to file'));
}
?>
