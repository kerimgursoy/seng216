
<?php
session_start();
// Require login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}
require 'db.php';

// Fetch all posts with author username
$sql = "
  SELECT p.id, p.content, p.created_at, p.user_id, u.usrname AS username
  FROM posts p
  JOIN users u ON p.user_id = u.id
  ORDER BY p.created_at DESC
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Feed</title>
  <link rel="stylesheet" href="post.css">
  <link rel="stylesheet" href="theme.css">
</head>
<body>
  <h1>Feed</h1>

  <!-- Create Post Form -->
  <form action="create_post.php" method="post">
    <textarea
      name="content"
      rows="3"
      cols="50"
      placeholder="What's on your mind?"
      required
      minlength="1"
      maxlength="500"
    ></textarea><br>
    <button type="submit">Post</button>
  </form>

  <hr>

  <?php if ($result && $result->num_rows > 0): ?>
    <?php while ($post = $result->fetch_assoc()): ?>
      <div class="post" id="post-<?= htmlspecialchars($post['id']) ?>">
        <p>
          <strong><?= htmlspecialchars($post['username']) ?></strong>
          <small><?= htmlspecialchars($post['created_at']) ?></small>
        </p>
        <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>

        <!-- Edit/Delete for owner -->
        <?php if ($_SESSION['user_id'] === (int)$post['user_id']): ?>
          <p>
            <a href="update_post.php?id=<?= htmlspecialchars($post['id']) ?>">Edit</a>
            |
            <a href="delete_post.php?id=<?= htmlspecialchars($post['id']) ?>"
               onclick="return confirm('Delete this post?');">
              Delete
            </a>
          </p>
        <?php endif; ?>

        <!-- View single post -->
        <p><a href="post.php?id=<?= htmlspecialchars($post['id']) ?>">View Post</a></p>
        <hr>

        <!-- Comment Form -->
        <form method="post" action="comment_post.php" class="comment-form">
          <input type="hidden" name="post_id" value="<?= htmlspecialchars($post['id']) ?>">
          <input
            type="text"
            name="content"
            placeholder="Comment..."
            required
            maxlength="300"
          >
          <button type="submit">Send</button>
        </form>

        <!-- Display Comments -->
        <?php
          $csql = "
            SELECT c.content, u.usrname, c.created_at
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.post_id = " . (int)$post['id'] . "
            ORDER BY c.created_at ASC
          ";
          $cres = $conn->query($csql);
          if ($cres && $cres->num_rows > 0):
        ?>
          <div class="comments">
            <?php while ($c = $cres->fetch_assoc()): ?>
              <p>
                <strong><?= htmlspecialchars($c['usrname']) ?>:</strong>
                <?= htmlspecialchars($c['content']) ?>
                <em>(<?= htmlspecialchars($c['created_at']) ?>)</em>
              </p>
            <?php endwhile; ?>
          </div>
        <?php endif; ?>

      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p>No posts yet.</p>
  <?php endif; ?>
</body>
</html>
