<?php
session_start();
header('Content-Type: application/json');

include '../config/dbconnect.php'; 

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

$isAdmin = $loggedInUsername === 'admin';

$accountToDelete = $_POST['username'] ?? '';

if (empty($accountToDelete)) {
    echo json_encode(['error' => 'Account username is required']);
    exit();
}

$stmt = $conn->prepare("SELECT username FROM users WHERE username = ?");
$stmt->bind_param("s", $accountToDelete);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Account not found']);
    exit();
}
$stmt->close();

if (!$isAdmin && $accountToDelete !== $loggedInUsername) {
    echo json_encode(['error' => 'Unauthorized to delete this account']);
    exit();
}

if ($accountToDelete == 'admin') {
    echo json_encode(['error' => 'Admin account cannot be deleted']);
    exit();
}

function deleteUserRelatedData($username, $filePath) {
    $data = json_decode(file_get_contents($filePath), true);
    $filteredData = array_filter($data, function ($item) use ($username) {
        return $item['username'] !== $username;
    });

    return file_put_contents($filePath, json_encode(array_values($filteredData), JSON_PRETTY_PRINT));
}

$stmt = $conn->prepare("DELETE FROM users WHERE username = ?");
$stmt->bind_param("s", $accountToDelete);
if ($stmt->execute()) {
    // Delete the user's session
    $stmt = $conn->prepare("DELETE FROM sessions WHERE username = ?");
    $stmt->bind_param("s", $accountToDelete);
    $stmt->execute();
    $stmt->close();

    // Delete the user's discount codes
    $stmt = $conn->prepare("DELETE FROM discount_codes WHERE username = ?");
    $stmt->bind_param("s", $accountToDelete);
    $stmt->execute();
    $stmt->close();

    // Clear session data and cookies
    if ($accountToDelete === $loggedInUsername) {
        session_unset();
        session_destroy();
        setcookie('session_id', '', time() - 3600, '/');
    }

    // Delete associated posts and comments
    $postsFile = '../public/data/posts.json';
    $commentsFile = '../public/data/comments.json';

    $postDeleteSuccess = deleteUserRelatedData($accountToDelete, $postsFile);
    $commentDeleteSuccess = deleteUserRelatedData($accountToDelete, $commentsFile);

    if ($postDeleteSuccess && $commentDeleteSuccess) {
        echo json_encode(['success' => 'Account deleted successfully']);
    } else {
        echo json_encode(['error' => 'Failed to delete account posts or comments']);
    }
} else {
    echo json_encode(['error' => 'Failed to delete account']);
}

$stmt->close();
mysqli_close($conn);
?>
