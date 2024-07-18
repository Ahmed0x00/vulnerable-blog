<?php
$target_dir = "uploads/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;

$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
if (!$imageFileType) {
    die("No image specified");
}

// Check if image is real or fake
$check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($check !== false) {
        echo '<div class="alert alert-success" role="alert">';
        echo "File is an image - " . htmlspecialchars($check["mime"]) . ".<br>";
        echo '</div>';
        $uploadOk = 1;
    } else {
        echo '<div class="alert alert-danger" role="alert">';
        echo "File is not an image.<br>";
        echo '</div>';
        $uploadOk = 0;
    }
}

// Blacklist of executable files
$blacklist = [
    'php3', 'php4', 'php5', 'phtml', 'exe', 'sh', 'bat', 'js', 'html', 'htm',
    'pl', 'py', 'cgi', 'rb', 'asp', 'aspx', 'jsp', 'css', 'xml', 'xhtml', 'jhtml',
    'jar', 'dll', 'com', 'cmd', 'vbs', 'vbe', 'wsf', 'wsh', 'ps1', 'psm1', 'psd1',
    'bash', 'zsh', 'ksh', 'csh', 'tcsh', 'fish', 'perl', 'pyc', 'pyo', 'pyd', 'htacess'
];

if (in_array($imageFileType, $blacklist)) {
    echo '<div class="alert alert-danger" role="alert">';
    echo "Sorry, files of this type are not allowed.<br>";
    echo '</div>';
    $uploadOk = 0;
}

if (file_exists($target_file)) {
    echo '<div class="alert alert-warning" role="alert">';
    echo "Sorry, file already exists.<br>";
    echo '</div>';
    $uploadOk = 0;
}

if ($_FILES["fileToUpload"]["size"] > 500000) {
    echo '<div class="alert alert-danger" role="alert">';
    echo "Sorry, your file is too large.<br>";
    echo '</div>';
    $uploadOk = 0;
}

if ($uploadOk == 0) {
    echo '<div class="alert alert-danger" role="alert">';
    echo "Sorry, your file was not uploaded.<br>";
    echo '</div>';
} else {
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        echo '<div class="alert alert-success" role="alert">';
        echo "File uploaded successfully: <a href='view_file.php?path=" . urlencode($target_file) . "'>View File</a><br>";
        echo '</div>';
    } else {
        echo '<div class="alert alert-danger" role="alert">';
        echo "Sorry, there was an error uploading your file.<br>";
        echo '</div>';
    }
}
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
