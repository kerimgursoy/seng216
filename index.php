
<?php
session_start();
// Require login
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit();
}
require_once "db.php";

// Fetch posts with like/bookmark counts
$sql = "
  SELECT
    p.id AS post_id,
    p.user_id AS owner_id,
    p.content,
    p.created_at,
    u.usrname,
    (SELECT COUNT(*) FROM likes WHERE likes.post_id = p.id) AS like_count
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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Home</title>
  <link rel="stylesheet" href="index.css">
  <link rel="stylesheet" href="theme.css">
</head>
<body>
  <nav class="navbar">
    <div class="navbar-logo"><a href="index.php">MyApp</a></div>
    <ul class="navbar-links">
      <li><a href="feed.php">📥 Feed</a></li>
      <li><a href="bookmarks.php">🔖 Bookmarks</a></li>
      <li><a href="logout.php">Logout</a></li>
    </ul>
  </nav>

  <h1>Welcome <?= htmlspecialchars($_SESSION['user']) ?>!</h1>

  <div class="post-list">
    <?php while ($row = $result->fetch_assoc()): ?>
      <?php
        $post_id   = $row['post_id'];
        $owner_id  = $row['owner_id'];
        // Determine current user’s like/bookmark status
        $liked     = false;
        $bookmarked= false;
        if (isset($_SESSION['user_id'])) {
            $uid = $_SESSION['user_id'];
            $like_check = $conn->query("SELECT 1 FROM likes WHERE user_id=$uid AND post_id=$post_id");
            $liked = ($like_check && $like_check->num_rows > 0);
            $bm_check = $conn->query("SELECT 1 FROM bookmarks WHERE user_id=$uid AND post_id=$post_id");
            $bookmarked = ($bm_check && $bm_check->num_rows > 0);
        }
      ?>
      <div class="post" id="post-<?= htmlspecialchars($post_id) ?>">
        <strong>
          <a href="profile.php?user=<?= urlencode($row['usrname']) ?>">
            <?= htmlspecialchars($row['usrname']) ?>
          </a>
        </strong>
        <p><a href="post.php?id=<?= htmlspecialchars($post_id) ?>">
          <?= nl2br(htmlspecialchars($row['content'])) ?>
        </a></p>
        <span class="timestamp"><?= htmlspecialchars($row['created_at']) ?></span>

        <?php if ($_SESSION['user_id'] === (int)$owner_id): ?>
          <p>
            <a href="update_post.php?id=<?= $post_id ?>">Edit</a> |
            <a href="delete_post.php?id=<?= $post_id ?>"
               onclick="return confirm('Delete this post?');">
               Delete
            </a>
          </p>
        <?php endif; ?>

        <div class="post-actions">
          <form action="like_post.php" method="post" style="display:inline;">
            <input type="hidden" name="post_id" value="<?= $post_id ?>">
            <button type="submit"><?= $liked ? '❤️' : '💔' ?> <?= htmlspecialchars($row['like_count']) ?></button>
          </form>
          <form action="bookmark_post.php" method="post" style="display:inline;">
            <input type="hidden" name="post_id" value="<?= $post_id ?>">
            <button type="submit"><?= $bookmarked ? '🔖' : '❌' ?></button>
          </form>
        </div>

        <form action="comment_post.php" method="post" class="comment-form">
          <input type="hidden" name="post_id" value="<?= $post_id ?>">
          <input type="text" name="content" placeholder="Comment..." required maxlength="300">
          <button type="submit">Send</button>
        </form>
      </div>
    <?php endwhile; ?>
  </div>

  <!-- Modal Trigger -->
  <button id="openPostModal" class="post-button">+ Create Post</button>

  <!-- Modal Window -->
  <div id="postModal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>
      <form action="create_post.php" method="post">
        <h2>New Post</h2>
        <textarea
          name="content"
          placeholder="What are you thinking?"
          rows="5"
          required
          minlength="1"
          maxlength="500"
        ></textarea><br>
        <button type="submit">Send</button>
      </form>
    </div>
  </div>

  <script>
    // Modal logic
    const modal = document.getElementById('postModal'),
          btn   = document.getElementById('openPostModal'),
          span  = document.querySelector('.modal .close');
    btn.onclick = () => modal.style.display = 'block';
    span.onclick = () => modal.style.display = 'none';
    window.onclick = e => { if (e.target === modal) modal.style.display = 'none'; };
  </script>

  <script>
    // AJAX for Like buttons
    document.querySelectorAll('.post-actions form[action="like_post.php"]').forEach(form => {
      form.addEventListener('submit', e => {
        e.preventDefault();
        const data = new FormData(form);
        fetch(form.action, { method: 'POST', body: data, credentials: 'same-origin' })
          .then(() => {
            let btn = form.querySelector('button'),
                [icon, count] = btn.textContent.trim().split(' ');
            count = +count;
            if (icon === '❤️') { icon = '💔'; count--; }
            else            { icon = '❤️'; count++; }
            btn.textContent = `${icon} ${count}`;
          });
      });
    });

    // AJAX for Bookmark buttons
    document.querySelectorAll('.post-actions form[action="bookmark_post.php"]').forEach(form => {
      form.addEventListener('submit', e => {
        e.preventDefault();
        const data = new FormData(form);
        fetch(form.action, { method: 'POST', body: data, credentials: 'same-origin' })
          .then(() => {
            let btn = form.querySelector('button');
            btn.textContent = btn.textContent.trim() === '🔖' ? '❌' : '🔖';
          });
      });
    });
  </script>
</body>
</html>
