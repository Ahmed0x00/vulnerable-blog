<?php
header('Content-Type: application/json');

$posts_file = '../../data/posts.json';

if (file_exists($posts_file)) {
    $posts = json_decode(file_get_contents($posts_file), true);
    
    if ($posts === null && json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['error' => 'Failed to decode JSON: ' . json_last_error_msg()]);
        exit();
    }

    $searchTerm = $_GET['search'] ?? null;
    $postId = $_GET['id'] ?? null;

    // Fetch post by ID
    if ($postId !== null) {
        $filteredPosts = array_filter($posts, function($post) use ($postId) {
            return (string)$post['id'] === (string)$postId; // Ensure both are strings for comparison
        });

        if (empty($filteredPosts)) {
            echo json_encode(['error' => 'Post not found']);
        } else {
            echo json_encode(['success' => 'Post fetched successfully', 'posts' => array_values($filteredPosts)]);
        }
    } 
    // Fetch posts by search term
    elseif ($searchTerm !== null) {
        $filteredPosts = array_filter($posts, function($post) use ($searchTerm) {
            return stripos($post['title'], $searchTerm) !== false; // Case-insensitive search
        });

        if (empty($filteredPosts)) {
            echo json_encode(['error' => "No posts found matching the search term $searchTerm"]);
        } else {
            echo json_encode(['success' => 'Posts fetched successfully', 'posts' => array_values($filteredPosts)]);
        }
    } 
    // Fetch all posts if no filters are provided
    else {
        if (!empty($posts)) {
            echo json_encode(['success' => 'Posts fetched successfully', 'posts' => $posts]);
        } else {
            echo json_encode(['error' => 'No posts found']);
        }
    }
} else {
    echo json_encode(['error' => 'Posts file not found']);
}
?>
