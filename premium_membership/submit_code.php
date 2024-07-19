<?php
session_start();
include '../config/dbconnect.php';

if (!isset($_COOKIE['session_id'])) {
    header("Location: http://127.0.0.1/vulnerable-blog/public/login.php");
    exit();
}

$sessionId = $_COOKIE['session_id'];

$result = $conn->query("SELECT username FROM sessions WHERE session_id = '$sessionId'");

if ($row = $result->fetch_assoc()) {
    $username = $row['username'];
} else {
    echo 'Invalid session';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = $_POST['discount_code'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM discount_codes WHERE code = ? AND used = 0");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $discount = 10;

        $updateCredits = $conn->prepare("UPDATE users SET credits = credits + ? WHERE username = ?");
        $updateCredits->bind_param("is", $discount, $username);
        $updateCredits->execute();
        $updateCredits->close();

        $updateDiscountCode = $conn->query("UPDATE discount_codes SET used = 1 WHERE code = '$code'"); 
        echo "Discount applied successfully!<br>";
    } else {
        die("Invalid or already used discount code.");
    }
}

?>
