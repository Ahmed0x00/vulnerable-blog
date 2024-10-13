<?php
session_start();

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

$postId = $_POST['id'];

$postFile = '../../data/posts.json';
$postsData = json_decode(file_get_contents($postFile), true);

$canDelete = false;
foreach ($postsData as $key => $post) {
    if ($post['id'] == $postId) {
        if ($post['username'] === $loggedInUsername || $loggedInUsername === 'admin') {
            $canDelete = true;
            unset($postsData[$key]);
            break;
        }
    }
}

if (!$canDelete) {
    echo json_encode(['error' => 'Unauthorized to delete this post']);
    exit();
}

if ($canDelete) {
    if (file_put_contents($postFile, json_encode(array_values($postsData), JSON_PRETTY_PRINT))) {
        // Delete comments associated with the post
        $commentsFile = '../../data/comments.json';
        $commentsData = json_decode(file_get_contents($commentsFile), true);

        foreach ($commentsData as $key => $comment) {
            if ($comment['postId'] == $postId) {
                unset($commentsData[$key]);
            }
        }

        if (file_put_contents($commentsFile, json_encode(array_values($commentsData), JSON_PRETTY_PRINT))) {
            // Rearrange post IDs
            $postsData = array_values($postsData); 
            foreach ($postsData as $index => &$post) {
                $post['id'] = $index + 1;
            }
            file_put_contents($postFile, json_encode($postsData, JSON_PRETTY_PRINT));

            echo json_encode(['success' => 'Post and associated comments deleted successfully, IDs rearranged']);
        } else {
            echo json_encode(['error' => 'Failed to delete comments']);
        }
    } else {
        echo json_encode(['error' => 'Failed to delete post']);
    }
}

mysqli_close($conn);
?>
