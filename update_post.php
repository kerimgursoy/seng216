<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Unauthorized');
}

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$post_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$post_id) {
    http_response_code(400);
    exit('Invalid post ID');
}

// access control
$stmt = $conn->prepare('SELECT content FROM posts WHERE id = ? AND user_id = ?');
$stmt->bind_param('ii', $post_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    http_response_code(403);
    exit('Not allowed');
}
$existing = $result->fetch_assoc()['content'];

// AJAX control
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json');
    $response = [];

    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid CSRF token']);
        exit;
    }

    $content = trim($_POST['content'] ?? '');
    if ($content === '') {
        echo json_encode(['error' => 'Content cannot be empty.']);
    } elseif (mb_strlen($content) > 500) {
        echo json_encode(['error' => 'Content too long (max 500 characters).']);
    } else {
        $up = $conn->prepare('UPDATE posts SET content = ? WHERE id = ? AND user_id = ?');
        $up->bind_param('sii', $content, $post_id, $_SESSION['user_id']);
        if ($up->execute()) {
            unset($_SESSION['csrf_token']);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Update failed.']);
        }
    }
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Edit Post</title>
</head>
<body>
  <form id="editForm">
    <textarea
      name="content"
      rows="5"
      required
      minlength="1"
      maxlength="500"
    ><?= htmlspecialchars($existing, ENT_QUOTES, 'UTF-8') ?></textarea><br>

    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <button type="submit">Save</button>
    <a href="index.php">Cancel</a>
  </form>

  <p id="msg" style="color: red;"></p>

  <script>
    const form = document.getElementById('editForm');
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      const data = new FormData(form);
      fetch(location.href, {
        method: 'POST',
        body: data,
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
      })
      .then(res => res.json())
      .then(json => {
        if (json.success) {
          window.location.href = 'index.php';
        } else {
          document.getElementById('msg').textContent = json.error;
        }
      })
      .catch(() => {
        document.getElementById('msg').textContent = 'An error occurred.';
      });
    });
  </script>
</body>
</html>