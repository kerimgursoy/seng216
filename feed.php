<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"], $_SESSION["user"])) {
    header("Location: login.php");
    exit();
}

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION["csrf_token"];
$user_id = $_SESSION["user_id"];
$username = $_SESSION["user"];

// get posts of follwed users
$sql = "SELECT p.id AS post_id, p.user_id AS owner_id, p.content, p.created_at, u.usrname,
        (SELECT COUNT(*) FROM likes WHERE likes.post_id = p.id) AS like_count
        FROM posts p
        JOIN users u ON p.user_id = u.id
        JOIN follows f ON f.followed_id = p.user_id
        WHERE f.follower_id = ?
        ORDER BY p.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Feed</title>
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

<h2>📥 Feed</h2>

<div class="post-list">
<?php while ($row = $result->fetch_assoc()):
  $post_id = $row["post_id"];
  $liked = $conn->query("SELECT 1 FROM likes WHERE user_id = $user_id AND post_id = $post_id")->num_rows > 0;
  $bookmarked = $conn->query("SELECT 1 FROM bookmarks WHERE user_id = $user_id AND post_id = $post_id")->num_rows > 0;
?>
  <div class="post" id="post-<?= $post_id ?>">
    <strong>
      <a href="profile.php?user=<?= urlencode($row['usrname']) ?>">
        <?= htmlspecialchars($row['usrname']) ?>
      </a>
    </strong>
    
    <p><a href="post.php?id=<?= $post_id ?>"><?= nl2br(htmlspecialchars($row['content'])) ?></a></p>
    <span class="timestamp"><?= htmlspecialchars($row["created_at"]) ?></span>

    <div class="post-actions">
      <form action="like_post.php" method="post" class="like-form" style="display:inline;">
        <input type="hidden" name="post_id" value="<?= $post_id ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        <button type="submit"><?= $liked ? '❤️' : '💔' ?> <?= $row["like_count"] ?></button>
      </form>
      <form action="bookmark_post.php" method="post" class="bookmark-form" style="display:inline;">
        <input type="hidden" name="post_id" value="<?= $post_id ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        <button type="submit"><?= $bookmarked ? '🔖' : '❌' ?></button>
      </form>
    </div>

    <form action="comment_post.php" method="post" class="comment-form">
      <input type="hidden" name="post_id" value="<?= $post_id ?>">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
      <input type="text" name="content" placeholder="Comment..." required maxlength="300">
      <button type="submit">Send</button>
    </form>

    <?php
    $stmt_c = $conn->prepare("SELECT c.content, c.created_at, u.usrname
                              FROM comments c
                              JOIN users u ON c.user_id = u.id
                              WHERE c.post_id = ?
                              ORDER BY c.created_at ASC");
    $stmt_c->bind_param("i", $post_id);
    $stmt_c->execute();
    $comments = $stmt_c->get_result();
    if ($comments->num_rows > 0):
    ?>
    <div class="comments">
      <?php while ($c = $comments->fetch_assoc()): ?>
        <p><strong><?= htmlspecialchars($c["usrname"]) ?>:</strong>
           <?= htmlspecialchars($c["content"]) ?>
           <em>(<?= $c["created_at"] ?>)</em></p>
      <?php endwhile; ?>
    </div>
    <?php endif; ?>
  </div>
<?php endwhile; ?>
</div>

<!-- Modal Trigger -->
<button id="openPostModal" class="post-button">+ Create Post</button>

<!-- Modal Window -->
<div id="postModal" class="modal">
  <div class="modal-content">
    <span class="close">&times;</span>
    <form id="newPostFormModal">
      <h2>New Post</h2>
      <textarea name="content" placeholder="What are you thinking?" rows="5" required minlength="1" maxlength="500"></textarea><br>
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
      <button type="submit">Send</button>
      <p id="post-error" style="color:red;"></p>
    </form>
  </div>
</div>

<script>
// Modal open/close
const modal = document.getElementById('postModal');
const btn = document.getElementById('openPostModal');
const span = document.querySelector('.modal .close');
btn.onclick = () => modal.style.display = 'block';
span.onclick = () => modal.style.display = 'none';
window.onclick = e => { if (e.target === modal) modal.style.display = 'none'; };

// AJAX - send post
document.getElementById("newPostFormModal").addEventListener("submit", function(e) {
  e.preventDefault();
  const form = e.target;
  const data = new FormData(form);

  fetch("create_post.php", {
    method: "POST",
    body: data,
    headers: { "X-Requested-With": "XMLHttpRequest" },
    credentials: "same-origin"
  })
  .then(res => res.json())
  .then(json => {
    if (json.success) location.reload();
    else document.getElementById("post-error").textContent = json.error;
  })
  .catch(() => {
    document.getElementById("post-error").textContent = "Unexpected error.";
  });
});

// AJAX Like
document.querySelectorAll('.like-form').forEach(form => {
  form.addEventListener("submit", e => {
    e.preventDefault();
    const data = new FormData(form);
    fetch("like_post.php", {
      method: "POST",
      body: data,
      headers: { "X-Requested-With": "XMLHttpRequest" },
      credentials: "same-origin"
    })
    .then(res => res.json())
    .then(json => {
      if (json.success) {
        const btn = form.querySelector("button");
        btn.textContent = (json.liked ? "❤️" : "💔") + " " + json.like_count;
      } else {
        alert(json.error || "Like failed.");
      }
    });
  });
});

// AJAX Bookmark
document.querySelectorAll('.bookmark-form').forEach(form => {
  form.addEventListener("submit", e => {
    e.preventDefault();
    const data = new FormData(form);
    fetch("bookmark_post.php", {
      method: "POST",
      body: data,
      headers: { "X-Requested-With": "XMLHttpRequest" },
      credentials: "same-origin"
    })
    .then(res => res.json())
    .then(json => {
      const btn = form.querySelector("button");
      btn.textContent = json.bookmarked ? "🔖" : "❌";
    });
  });
});

document.querySelectorAll('.comment-form').forEach(form => {
  form.addEventListener("submit", e => {
    e.preventDefault();
    const data = new FormData(form);
    fetch("comment_post.php", {
      method: "POST",
      body: data,
      headers: { "X-Requested-With": "XMLHttpRequest" },
      credentials: "same-origin"
    })
    .then(res => res.json())
    .then(json => {
      if (json.success) {
        // reload if problem occurs
        location.reload();
      } else {
        alert(json.error || "Comment failed.");
      }
    })
    .catch(() => alert("Comment request failed."));
  });
});
</script>
</body>
</html>
