# Vulnerable Blog App
Welcome to the most vulnerable blog app!
I created this blog app so beginners and intermediate Pentesters and Bug Hunters can train on it. Here you can practice basic and important vulnerabilities like XSS, SQLi, CSP bypass, IDOR, Account Takeover, and many more.

## Prerequisites
This app will only run on your local server, so you should have an Apache server on your machine. You must also have SQL and PHP installed.

## Cloning the Repository
Since this will run only on your local server, navigate to `/var/www/html/` and run the following command:
```bash
git clone https://github.com/Ahmed2456/vulnerable-blog.git
```

## phpMyAdmin Setup
You should download and configure phpMyAdmin by following this [video](https://www.youtube.com/watch?v=65BpgWHNJUk). After you have finished, go to `http://127.0.0.1/phpmyadmin` and log in.

Then, go to `http://127.0.0.1/phpmyadmin/index.php?route=/server/databases` and create a new database called `vuln_blog`. After that, go to `Import`, navigate to the directory containing the repository, choose `vuln_blog.sql`, and click on Import.

## Database Connection
Navigate to `vulnerable-blog/config`, where you will find `dbconnect.php`. Update the username and password in the file:
```php
<?php
// Update username and password
$servername = "localhost";
$username = "phpmyadmin";
$password = "123";

$database = "vuln_blog";
$conn = mysqli_connect($servername, $username, $password, $database);
?>
```

Replace `username` and `password` with the username and password you used when logging in to phpMyAdmin.

## Changing Ownership and Permissions
To enable writing files in the API and uploading files, go to the repository directory and run the following commands:
```bash
sudo chmod 644 public/api/comments.json
sudo chmod 644 public/api/posts.json
sudo chmod 644 public/comments/create_comment.php
sudo chmod 644 public/posts/php/create_post.php
sudo chmod -R 755 public/api
sudo chown -R www-data:www-data public/api
sudo chmod 644 public/upload.php
sudo chmod -R 755 public/uploads
sudo chown -R www-data:www-data public/uploads
```

## Starting the Application
Now you are ready. Restart Apache by running:
```bash
sudo systemctl restart apache2
```
Then go to: `http://127.0.0.1/vulnerable-blog`.

**Note:** When you restart your device and want to use the app again, run:
```bash
sudo systemctl start apache2
```
