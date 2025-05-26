<?php
session_start();
require 'db.php';

// Only logged-in users can comment
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Validate post_id as integer
    $post_id = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
    // 2) Trim & validate comment length
    $content = trim($_POST['content'] ?? '');
    if ($post_id && $content !== '' && mb_strlen($content) <= 300) {
        // 3) Use prepared statement
        $stmt = $conn->prepare(
          'INSERT INTO comments (post_id, user_id, content, created_at)
           VALUES (?, ?, ?, NOW())'
        );
        $stmt->bind_param('iis', $post_id, $_SESSION['user_id'], $content);
        $stmt->execute();
    }
}
// Redirect back (or return JSON if using AJAX)
header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;
