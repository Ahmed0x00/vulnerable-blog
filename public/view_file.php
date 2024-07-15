<?php
if (isset($_GET['path'])) {
    $requested_path = $_GET['path'];

    if (file_exists($requested_path)) {
        $extension = strtolower(pathinfo($requested_path, PATHINFO_EXTENSION));
        switch ($extension) {
            case 'png':
                header('Content-Type: image/png');
                break;
            case 'jpeg':
            case 'jpg':
                header('Content-Type: image/jpeg');
                break;
            case 'gif':
                header('Content-Type: image/gif');
                break;
        }

        readfile($requested_path);
    } 
    else {
        echo 'Invalid file path';
    }
} else {
    echo "Path not specified";
}
?>
