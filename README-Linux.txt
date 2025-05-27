
==================================
Social Media Web Application - Installation Guide (Linux)
==================================

This guide will help you install and run the PHP-based social media application on a Linux environment using Apache, MySQL, and PHPMyAdmin.

-------------------------------
1. Required Packages
-------------------------------
Make sure the following are installed:

- Apache2 Web Server
- MySQL Server
- PHP and necessary extensions (php-mysqli, php-curl, php-mbstring)
- PHPMyAdmin (for easy DB management)

Install them using:

sudo apt update
sudo apt install apache2 mysql-server php php-mysql php-mbstring php-curl php-xml php-zip phpmyadmin

During PHPMyAdmin installation, make sure to configure it with the MySQL server.

-------------------------------
2. Setup Web Directory
-------------------------------
1. Create a new directory under Apache root:

sudo mkdir /var/www/html/socialapp

2. Copy all project files into this directory:

sudo cp -r * /var/www/html/socialapp/

3. Ensure permissions:

sudo chown -R www-data:www-data /var/www/html/socialapp

-------------------------------
3. Configure Database
-------------------------------
1. Open PHPMyAdmin at:

http://localhost/phpmyadmin

2. Create a new database, for example: social_db

3. Select the database and run the following SQL queries:

-- Users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usrname VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Posts
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Comments
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Likes
CREATE TABLE likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    post_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, post_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
);

-- Bookmarks
CREATE TABLE bookmarks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    post_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, post_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
);

-- Follows
CREATE TABLE follows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    follower_id INT NOT NULL,
    followed_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(follower_id, followed_id),
    FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (followed_id) REFERENCES users(id) ON DELETE CASCADE
);

-------------------------------
4. Configure Database Connection
-------------------------------
In your project directory, open db.php and set your database credentials:

$host = 'localhost';
$db   = 'social_db';
$user = 'root';
$pass = ''; // or your MySQL password
$conn = new mysqli($host, $user, $pass, $db);

-------------------------------
5. Run the Application
-------------------------------
Now go to your browser and open:

http://localhost/socialapp/

You should see the homepage or login/register page.

-------------------------------
6. Notes
-------------------------------
- Make sure Apache and MySQL are running:

sudo systemctl start apache2
sudo systemctl start mysql

- Use sudo systemctl enable apache2 to start Apache automatically on boot.

Enjoy your application!

==================================
