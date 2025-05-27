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

```bash
sudo apt update
sudo apt install apache2 mysql-server php php-mysql php-mbstring php-curl php-xml php-zip phpmyadmin
===========================================
Create a directory under Apache root:
sudo mkdir /var/www/html/socialapp

copy all project files into this directory.

Ensure permissions:
sudo chown -R www-data:www-data /var/www/html/socialapp

configure database

Open PHPHMyadmin at:
http://localhost/phpmyadmin

connect your database to the project using db.php

================================================
query for phpmyadmin console:

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usrname VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Post Entity
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Comment Entity
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Like Entity
CREATE TABLE likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    post_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, post_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
);

-- Bookmark Entity
CREATE TABLE bookmarks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    post_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, post_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
);

-- Follow Entity
CREATE TABLE follows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    follower_id INT NOT NULL,
    followed_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(follower_id, followed_id),
    FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (followed_id) REFERENCES users(id) ON DELETE CASCADE
);

========================================================

Now, go to your browser and open:
http://localhost/socialapp/

Make sure Apache and MySQL are running.
