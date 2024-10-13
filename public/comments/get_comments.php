<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit('Method not allowed');
}

if (!isset($_GET['postId'])) {
    http_response_code(400);
    exit('Post ID is required');
}

$postId = $_GET['postId'];

$comments = json_decode(file_get_contents('../data/comments.json'), true);

// Filter comments based on postId
$filteredComments = array_filter($comments, function($comment) use ($postId) {
    return $comment['postId'] == $postId;
});

echo json_encode(['success' => true, 'comments' => array_values($filteredComments)], JSON_UNESCAPED_SLASHES);?>
?>
