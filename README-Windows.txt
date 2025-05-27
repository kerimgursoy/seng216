
==================================
Social Media Web Application - Installation Guide (Windows)
==================================

This guide explains how to install and run the PHP-based social media web app on Windows using XAMPP.

-------------------------------
1. Required Software
-------------------------------
You need to install the following:

- XAMPP (https://www.apachefriends.org/index.html)
  (Includes Apache, MySQL, and PHP)

-------------------------------
2. Install and Start XAMPP
-------------------------------
1. Download and install XAMPP.
2. Launch XAMPP Control Panel.
3. Start Apache and MySQL modules.

Make sure both services are running (green status).

-------------------------------
3. Setup Project Directory
-------------------------------
1. Go to the XAMPP installation folder:
   C:\xampp\htdocs\

2. Create a new folder, for example:
   C:\xampp\htdocs\socialapp

3. Copy all your project files into this folder.

-------------------------------
4. Setup Database using phpMyAdmin
-------------------------------
1. Open your browser and go to:
   http://localhost/phpmyadmin

2. Create a new database (e.g. social_db).

3. Select the database and run the following SQL to create tables:

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
5. Configure db.php
-------------------------------
Edit the db.php file inside your project and set database credentials:

$host = 'localhost';
$db   = 'social_db';
$user = 'root';
$pass = ''; // XAMPP default has no password
$conn = new mysqli($host, $user, $pass, $db);

-------------------------------
6. Run the Application
-------------------------------
Open your browser and go to:

http://localhost/socialapp/

You should see the homepage or login/register page of your social media site.

-------------------------------
7. Common Issues & Tips
-------------------------------
- If you get a blank page, enable PHP error reporting in php.ini or add:
  ini_set('display_errors', 1);
  error_reporting(E_ALL);

- If MySQL doesn't start, make sure port 3306 is not being used by another program.

- Don't forget to restart Apache/MySQL after configuration changes.

==================================
