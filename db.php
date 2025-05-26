<?php
// db.php

$host   = 'localhost';
$user   = 'root';     // or your MySQL user
$pass   = '';         // or your MySQL password
$dbName = 'SENG216';

// 1) Connect without selecting a database
$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2) Create the database if it doesn't exist
if (!$conn->query(
    "CREATE DATABASE IF NOT EXISTS `$dbName`
     CHARACTER SET utf8mb4
     COLLATE utf8mb4_unicode_ci"
)) {
    die("Database creation failed: " . $conn->error);
}

// 3) Select the database
$conn->select_db($dbName);

// 4) Create tables if missing
$tableDDLs = [
    // users
    "CREATE TABLE IF NOT EXISTS `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `usrname` VARCHAR(50) UNIQUE NOT NULL,
        `email` VARCHAR(100) UNIQUE NOT NULL,
        `password` VARCHAR(255) NOT NULL,
        `is_admin` BOOLEAN DEFAULT FALSE,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB",
    // posts
    "CREATE TABLE IF NOT EXISTS `posts` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `content` TEXT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB",
    // comments
    "CREATE TABLE IF NOT EXISTS `comments` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `post_id` INT NOT NULL,
        `user_id` INT NOT NULL,
        `content` TEXT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB",
    // likes
    "CREATE TABLE IF NOT EXISTS `likes` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `post_id` INT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE (`user_id`,`post_id`),
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB",
    // bookmarks
    "CREATE TABLE IF NOT EXISTS `bookmarks` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `post_id` INT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE (`user_id`,`post_id`),
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB",
    // follows
    "CREATE TABLE IF NOT EXISTS `follows` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `follower_id` INT NOT NULL,
        `followed_id` INT NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE (`follower_id`,`followed_id`),
        FOREIGN KEY (`follower_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`followed_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB",
];

foreach ($tableDDLs as $ddl) {
    if (! $conn->query($ddl)) {
        error_log("Table creation failed: " . $conn->error);
    }
}

// 5) Insert sample data (only once per record)
$dataInserts = [
    // Users (note column is `usrname`, not `username`)
    "INSERT IGNORE INTO `users` (usrname, email, password) VALUES
        ('alice', 'alice@example.com', 'password123'),
        ('bob',   'bob@example.com',   'password456')",

    // Posts
    "INSERT IGNORE INTO `posts` (user_id, content) VALUES
        (1, 'Merhaba dünya!'),
        (2, 'İlk gönderim.')",

    // Comments
    "INSERT IGNORE INTO `comments` (post_id, user_id, content) VALUES
        (1, 2, 'Hoş geldin!'),
        (2, 1, 'Teşekkürler!')",

    // Likes
    "INSERT IGNORE INTO `likes` (user_id, post_id) VALUES
        (1, 2),
        (2, 1)",

    // Bookmarks
    "INSERT IGNORE INTO `bookmarks` (user_id, post_id) VALUES
        (1, 2)",

    // Follows
    "INSERT IGNORE INTO `follows` (follower_id, followed_id) VALUES
        (1, 2)"
];

foreach ($dataInserts as $sql) {
    if (! $conn->query($sql)) {
        error_log("Data insert failed: " . $conn->error);
    }
}

// Now $conn is ready for the rest of your app.
