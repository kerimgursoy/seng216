<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$username = $_SESSION["user"] ?? "";

// CSRF token
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION["csrf_token"];

// get bookmarked posts
$sql = "SELECT posts.id AS post_id, posts.content, posts.created_at, users.usrname
        FROM bookmarks
        JOIN posts ON bookmarks.post_id = posts.id
        JOIN users ON posts.user_id = users.id
        WHERE bookmarks.user_id = ?
        ORDER BY bookmarks.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>🔖 Bookmarked Posts</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="favicon.png">

</head>
<body>
<nav class="navbar">
    <div class="navbar-logo"><a href="index.php">SociaLink</a></div>
    <ul class="navbar-links">
      <li><a href="feed.php">📥 Feed</a></li>
      <li><a href="bookmarks.php">🔖 Bookmarks</a></li>
      <li><a href="logout.php">Logout</a></li>
    </ul>
  </nav>

<h2>🔖 Bookmarks</h2>

<?php if ($result->num_rows > 0): ?>
<div class="post-list">
<?php while ($row = $result->fetch_assoc()):
    $post_id = $row['post_id'];
?>
  <div class="post" id="post-<?= $post_id ?>">
    <strong><a href="profile.php?user=<?= urlencode($row["usrname"]) ?>"><?= htmlspecialchars($row["usrname"]) ?></a></strong><br>
    <p><a href="post.php?id=<?= $post_id ?>"><?= nl2br(htmlspecialchars($row["content"])) ?></a></p>
    <span class="timestamp"><?= htmlspecialchars($row["created_at"]) ?></span>

    <form class="bookmark-form" method="post" action="bookmark_post.php" style="display:inline;">
        <input type="hidden" name="post_id" value="<?= $post_id ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        <button type="submit">❌</button>
    </form>
  </div>
<?php endwhile; ?>
</div>
<?php else: ?>
  <p>You haven't bookmarked anything yet.</p>
<?php endif; ?>

<script>
// AJAX bookmark kaldırma
document.querySelectorAll('.bookmark-form').forEach(form => {
  form.addEventListener('submit', e => {
    e.preventDefault();
    const data = new FormData(form);
    fetch(form.action, {
      method: 'POST',
      body: data,
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    })
    .then(res => res.json())
    .then(json => {
      if (json.success && !json.bookmarked) {
        const post = form.closest('.post');
        post?.remove();
      } else {
        alert(json.error || "Bookmark failed.");
      }
    })
    .catch(() => alert("Request failed."));
  });
});
</script>
</body>
</html>