<?php
session_start();
require 'db.php';

// 1) Only logged-in users
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2) Trim & validate length
    $content = trim($_POST['content'] ?? '');
    if ($content === '') {
        $error = 'Post content cannot be empty.';
    } elseif (mb_strlen($content) > 500) {
        $error = 'Post too long (max 500 characters).';
    } else {
        // 3) Prepared statement to prevent SQL injection
        $stmt = $conn->prepare(
          'INSERT INTO posts (user_id, content, created_at) VALUES (?, ?, NOW())'
        );
        $stmt->bind_param('is', $_SESSION['user_id'], $content);
        if ($stmt->execute()) {
            header('Location: index.php');
            exit;
        } else {
            $error = 'Database error, please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>New Post</title></head>
<body>
  <?php if ($error): ?>
    <p style="color:red"><?= htmlspecialchars($error) ?></p>
  <?php endif ?>
  <!-- (Optionally re-display the form here) -->
</body>
</html>
