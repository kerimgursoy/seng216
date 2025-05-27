<?php
session_start();
require_once "db.php";

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// user control
if (!isset($_GET["user"])) die("User not found.");
$username = trim($_GET["user"]);

// get profile
$sql_user = "SELECT id, usrname FROM users WHERE usrname = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("s", $username);
$stmt_user->execute();
$user_result = $stmt_user->get_result();
if (!$user_result || $user_result->num_rows === 0) die("User not found.");
$user_data = $user_result->fetch_assoc();
$user_id = $user_data["id"];
$is_owner = isset($_SESSION["user_id"]) && $_SESSION["user_id"] == $user_id;
$viewer_id = $_SESSION["user_id"] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($user_data["usrname"]) ?>'s Profile</title>
  <link rel="stylesheet" href="style.css">
  <link rel="icon" type="image/png" href="favicon.png">

  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
<div id="top-profile">
<h2><?= htmlspecialchars($user_data["usrname"]) ?>'s Posts</h2>

<?php
// follow data
$stmt1 = $conn->prepare("SELECT COUNT(*) AS total FROM follows WHERE followed_id = ?");
$stmt1->bind_param("i", $user_id);
$stmt1->execute();
$followers = $stmt1->get_result()->fetch_assoc()["total"];

$stmt2 = $conn->prepare("SELECT COUNT(*) AS total FROM follows WHERE follower_id = ?");
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$following = $stmt2->get_result()->fetch_assoc()["total"];

echo "<p id='follower-count'>$followers followers • $following following</p>";

// follow button
if ($viewer_id && !$is_owner) {
    $fcheck = $conn->prepare("SELECT 1 FROM follows WHERE follower_id = ? AND followed_id = ?");
    $fcheck->bind_param("ii", $viewer_id, $user_id);
    $fcheck->execute();
    $fcheck->store_result();
    $isFollowing = $fcheck->num_rows > 0;
    ?>
    <form method="post" action="follow.php" class="follow-form">
      <input type="hidden" name="followed_id" value="<?= $user_id ?>">
      <input type="hidden" name="username" value="<?= htmlspecialchars($username) ?>">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
      <button type="submit"><?= $isFollowing ? "Unfollow" : "Follow" ?></button>
    </form>
<?php } ?>
</div>
<div class="post-list">
<?php
$sql = "SELECT id AS post_id, content, created_at,
        (SELECT COUNT(*) FROM likes WHERE post_id = posts.id) AS like_count
        FROM posts WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$posts = $stmt->get_result();

while ($row = $posts->fetch_assoc()):
    $post_id = $row["post_id"];
    $liked = $conn->query("SELECT 1 FROM likes WHERE user_id = $viewer_id AND post_id = $post_id")->num_rows > 0;
    $bookmarked = $conn->query("SELECT 1 FROM bookmarks WHERE user_id = $viewer_id AND post_id = $post_id")->num_rows > 0;
?>
  <div class="post" id="post-<?= $post_id ?>">
    <p><a href="post.php?id=<?= $post_id ?>"><?= nl2br(htmlspecialchars($row["content"])) ?></a></p>
    <span class="timestamp"><?= htmlspecialchars($row["created_at"]) ?></span>

    <?php if ($is_owner): ?>
      <p>
        <a href="update_post.php?id=<?= $post_id ?>">Edit</a> |
        <a href="delete_post.php?id=<?= $post_id ?>" onclick="return confirm('Delete?');">Delete</a>
      </p>
    <?php endif; ?>

    <div class="post-actions">
      <form action="like_post.php" method="post" class="like-form">
        <input type="hidden" name="post_id" value="<?= $post_id ?>">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <button type="submit"><?= $liked ? "❤️" : "💔" ?> <?= $row["like_count"] ?></button>
      </form>
      <form action="bookmark_post.php" method="post" class="bookmark-form">
        <input type="hidden" name="post_id" value="<?= $post_id ?>">
        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
        <button type="submit"><?= $bookmarked ? "🔖" : "❌" ?></button>
      </form>
    </div>

    <form action="comment_post.php" method="post" class="comment-form">
      <input type="hidden" name="post_id" value="<?= $post_id ?>">
      <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
      <input type="text" name="content" placeholder="Comment..." required maxlength="300">
      <button type="submit">Send</button>
    </form>
  </div>
<?php endwhile; ?>
</div>

<script>
// follow AJAX
document.querySelectorAll('.follow-form').forEach(form => {
  form.addEventListener('submit', e => {
    e.preventDefault();
    const data = new FormData(form);
    data.append("ajax", "1");

    fetch('follow.php', {
      method: 'POST',
      body: data,
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    })
    .then(res => res.json())
    .then(json => {
      if (json.success) {
        form.querySelector('button').textContent = json.status === 'followed' ? 'Unfollow' : 'Follow';

        const countElem = document.getElementById('follower-count');
        if (countElem && json.follower_count !== undefined) {
          const following = countElem.textContent.split("•")[1].trim(); // örn: "13 following"
          countElem.textContent = `${json.follower_count} followers • ${following}`;
        }
      } else {
        alert(json.error || "Action failed.");
      }
    })
    .catch(() => alert("Follow/unfollow failed."));
  });
});

</script>
</body>
</html>
