# Vulnerable Blog App  
Welcome to the **Vulnerable Blog App**—a training platform for beginner and intermediate Pentesters and Bug Hunters! This app allows you to practice key vulnerabilities such as:  
- **XSS (Cross-Site Scripting)**  
- **SQL Injection (SQLi)**  
- **CSP Bypass**  
- **IDOR (Insecure Direct Object References)**  
- **Account Takeover**  
- And many more!

## Prerequisites  
Before getting started, make sure your system meets the following requirements:  
1. **Apache Server** installed on your machine.  
2. **SQL** and **PHP** configured.

## Cloning the Repository  
To set up the app, navigate to your local server's directory (e.g., `/var/www/html/`) and run the following command:  
```bash
git clone https://github.com/Ahmed2456/vulnerable-blog.git
```

## phpMyAdmin Setup  
You will need phpMyAdmin to manage the database. Follow the [video tutorial](https://www.youtube.com/watch?v=65BpgWHNJUk) to download and configure phpMyAdmin. Once set up, navigate to `http://127.0.0.1/phpmyadmin` and log in.

Next, create a new database:
1. Go to `http://127.0.0.1/phpmyadmin/index.php?route=/server/databases`.
2. Create a new database named `vuln_blog`.
3. Go to the **Import** tab, select the `vuln_blog.sql` file from the repository directory, and click **Import**.

## Database Connection  
Navigate to `vulnerable-blog/config` and open the `dbconnect.php` file. Update the username and password to match your phpMyAdmin login credentials:  
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

## Changing Ownership and Permissions  
To allow file writing and uploading, set the correct permissions by running the following commands from the repository directory:  
```bash
sudo chmod 644 public/data/comments.json
sudo chmod 644 public/data/posts.json
sudo chmod 644 public/comments/create_comment.php
sudo chmod 644 public/posts/php/create_post.php
sudo chmod -R 755 public/data
sudo chown -R www-data:www-data public/data
sudo chmod 644 public/upload.php
sudo chmod -R 755 public/uploads
sudo chown -R www-data:www-data public/uploads
```

## Starting the Application  
You're now ready to start the app. First, restart Apache with the following command:  
```bash
sudo systemctl restart apache2
```
Afterward, navigate to: `http://127.0.0.1/vulnerable-blog`.

## After a Restart  
If you restarted your device, you need to run the following to start Apache again:  
```bash
sudo systemctl start apache2
```
