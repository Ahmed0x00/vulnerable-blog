<?php
session_start();
include '../../config/dbconnect.php'; 
header('Content-Type: application/json');

if (!isset($_COOKIE['session_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); 
    exit('Method not allowed');
}

if (!isset($_POST['commentId']) || empty($_POST['newComment'])) {
    http_response_code(400);
    exit('Comment ID and new comment are required');
}

$sessionId = $_COOKIE['session_id'];

$result = $conn->query("SELECT username FROM sessions WHERE session_id = '$sessionId'");

if ($row = $result->fetch_assoc()) {
    $username = $row['username'];
} else {
    echo json_encode(['error' => 'Invalid session']);
    exit();
}

// Read comments from comments.json
$commentData = json_decode(file_get_contents('../api/comments.json'), true);

$commentId = $_POST['commentId'];
$newComment = $_POST['newComment']; 

// Verify if the user has the right to edit this comment
$commentFound = false;
foreach ($commentData as &$comment) {
    if ($comment['id'] == $commentId) {
        if ($comment['username'] === $username || $username === 'admin') {
            $comment['comment'] = $newComment;
            $comment['edited_at'] = date('Y-m-d H:i:s');
            $commentFound = true;
            break;
        } else {
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }
    }
}

if (!$commentFound) {
    echo json_encode(['error' => 'Comment not found']);
    exit();
}

if (file_put_contents('../api/comments.json', json_encode($commentData, JSON_PRETTY_PRINT))) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save comment to file']);
}
?>
