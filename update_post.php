<?php
session_start();
require 'db.php';

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 1) Validate post ID
$post_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$post_id) {
    header('Location: index.php');
    exit;
}

// 2) Fetch & authorize
$stmt = $conn->prepare('SELECT content FROM posts WHERE id = ? AND user_id = ?');
$stmt->bind_param('ii', $post_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header('Location: index.php');
    exit;
}
$existing = $result->fetch_assoc()['content'];

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['content'] ?? '');
    if ($content === '') {
        $error = 'Content cannot be empty.';
    } elseif (mb_strlen($content) > 500) {
        $error = 'Content too long (max 500 characters).';
    } else {
        $up = $conn->prepare('UPDATE posts SET content = ? WHERE id = ?');
        $up->bind_param('si', $content, $post_id);
        if ($up->execute()) {
            header('Location: index.php');
            exit;
        }
        $error = 'Update failed.';
    }
}
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Edit Post</title></head>
<body>
  <?php if ($error): ?>
    <p style="color:red"><?= htmlspecialchars($error) ?></p>
  <?php endif ?>
  <form method="post" action="update_post.php?id=<?= $post_id ?>">
    <textarea
      name="content"
      rows="5"
      required
      minlength="1"
      maxlength="500"
    ><?= htmlspecialchars($existing) ?></textarea><br>
    <button type="submit">Save</button>
    <a href="index.php">Cancel</a>
  </form>
</body>
</html>
