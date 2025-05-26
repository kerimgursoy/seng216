<?php
session_start();
require 'db.php';

// Only logged-in users
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Validate ID
$post_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($post_id) {
    $stmt = $conn->prepare(
      'DELETE FROM posts WHERE id = ? AND user_id = ?'
    );
    $stmt->bind_param('ii', $post_id, $_SESSION['user_id']);
    $stmt->execute();
}

// Back home
header('Location: index.php');
exit;