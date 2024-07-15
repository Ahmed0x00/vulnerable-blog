<?php
session_start();
header('Content-Type: application/json');

include '../../config/dbconnect.php';

if (!isset($_COOKIE['session_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); 
    exit('Method not allowed');
}

if (!isset($_POST['commentId'])) {
    http_response_code(400);
    exit('Comment ID is required');
}

$sessionId = $_COOKIE['session_id'];
$result = $conn->query("SELECT username FROM sessions WHERE session_id = '$sessionId'");

if ($row = $result->fetch_assoc()) {
    $username = $row['username'];
} else {
    echo json_encode(['error' => 'Invalid session']);
    exit();
}

$commentId = $_POST['commentId'];
$commentData = json_decode(file_get_contents('../api/comments.json'), true);

$commentFound = false;
foreach ($commentData as $key => $comment) {
    if ($comment['id'] == $commentId) {
        if ($comment['username'] === $username || $username === 'admin') {
            unset($commentData[$key]);
            $commentFound = true;
            break;
        } else {
            echo json_encode(['error' => 'You do not have permission to delete this comment']);
            exit();
        }
    }
}

if ($commentFound) {
    // Re-index the comments array and reset IDs
    $commentData = array_values($commentData);
    foreach ($commentData as $index => &$comment) {
        $comment['id'] = $index + 1;
    }

    if (file_put_contents('../api/comments.json', json_encode($commentData, JSON_PRETTY_PRINT))) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500); 
        echo json_encode(['error' => 'Failed to save comments to file']);
    }
} else {
    echo json_encode(['error' => 'Comment not found']);
}
?>
