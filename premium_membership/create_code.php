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

$isAdmin = $username === 'admin';

if (!$isAdmin) {
    $codeCheck = $conn->query("SELECT * FROM discount_codes WHERE username = '$username'");
    if ($codeCheck->num_rows > 0) {
        echo 'You can have only one code when you log in';
        exit();
    }
}

function generateDiscountCode() {
    $salt = "DISCOUNT";
    $hashedString = md5(rand(100, 999) . $salt);
    return $hashedString; 
}

$discountCode = generateDiscountCode();

$stmt = $conn->prepare("INSERT INTO discount_codes (username, code) VALUES (?, ?)");
$stmt->bind_param("ss", $username, $discountCode);
$stmt->execute();
$stmt->close();

echo "Your discount code is: $discountCode";

mysqli_close($conn);
?>
