<?php
include '../config/dbconnect.php';

session_start();

// Check if the user is already logged in
if (isset($_COOKIE['session_id'])) {
    echo '<div class="container mt-5">
        <h2>You are already logged in.</h2>
        <a href="welcome.php" class="btn btn-primary me-2" style="padding: 10px 20px;">Go to Welcome Page</a>
        <form action="logout.php" method="post" style="display:inline;">
            <button type="submit" class="btn btn-danger" style="padding: 10px 20px;">Logout</button>
        </form>
      </div>';
} else {
    echo '<div class="container mt-5">';
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $identifier = $_POST['identifier']; // This can be either username or email
        $password = $_POST['password'];

        $result = $conn->query("SELECT * FROM users WHERE (username = '$identifier' OR email = '$identifier') AND password = '$password'");
        if (mysqli_num_rows($result) > 0) {
            $row = $result->fetch_assoc();
            $username = $row['username'];

            // Generate a session ID
            $sessionId = bin2hex(random_bytes(16));
            setcookie("session_id", $sessionId, time() + (86400 * 30), "/");

            $stmt = $conn->prepare("INSERT INTO sessions (session_id, username) VALUES (?, ?)");
            $stmt->bind_param("ss", $sessionId, $username);
            $stmt->execute();

            // Request a discount code creation
            $createCodeUrl = 'http://127.0.0.1/vulnerable-blog/premium_membership/create_code.php';
            $contextOptions = [
                "http" => [
                    "method" => "GET",
                    "header" => "Cookie: session_id=$sessionId\r\n"
                ]
            ];
            $context = stream_context_create($contextOptions);
            file_get_contents($createCodeUrl, false, $context);

            // Redirect to welcome.php
            header("Location: welcome.php?username=" . urlencode($username));
            exit();
        } else {
            echo '<div class="alert alert-danger">Invalid username or password</div>';
            // Fetch and display the result of the SQL query
            while ($row = $result->fetch_assoc()) {
                echo '<pre>' . print_r($row, true) . '</pre>';
            }
        }
        
    }

    echo '<h2>Login</h2>
    <form action="' . $_SERVER['PHP_SELF'] . '" method="post" class="mt-4">
        <div class="mb-3">
            <label for="identifier" class="form-label">Username or Email:</label>
            <input type="text" id="identifier" name="identifier" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password:</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
    </form>';
}

mysqli_close($conn);
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
