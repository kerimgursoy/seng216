<?php
session_start();
require_once "db.php";

// Giriş kontrolü
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Feed – Takip Ettiklerin</title>
  <link rel="stylesheet" href="index.css">
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

<h2>📥 Posts of your following</h2>

<?php
$sql = "SELECT posts.id AS post_id, posts.content, posts.created_at, users.usrname,
               (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS like_count
        FROM posts
        JOIN users ON posts.user_id = users.id
        WHERE users.id IN (
            SELECT followed_id FROM follows WHERE follower_id = ?
        )
        ORDER BY posts.created_at DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) > 0) {
    echo "<div class='post-list'>";
    while ($row = mysqli_fetch_assoc($result)) {
        $post_id = $row['post_id'];

        echo "<div class='post'>";
        echo "<strong><a href='profile.php?user=" . urlencode($row['usrname']) . "'>" . htmlspecialchars($row['usrname']) . "</a></strong><br>";
        echo "<p><a href='post.php?id=$post_id'>" . nl2br(htmlspecialchars($row['content'])) . "</a></p>";
        echo "<span class='timestamp'>" . $row['created_at'] . "</span>";

        // like/bookmark durumu
        $liked = false;
        $bookmarked = false;
        if (isset($_SESSION["user_id"])) {
            $uid = $_SESSION["user_id"];
            $liked = mysqli_num_rows($conn->query("SELECT 1 FROM likes WHERE user_id = $uid AND post_id = $post_id")) > 0;
            $bookmarked = mysqli_num_rows($conn->query("SELECT 1 FROM bookmarks WHERE user_id = $uid AND post_id = $post_id")) > 0;
        }

        echo "<div class='post-actions'>";
        echo "<form method='post' action='like_post.php' style='display:inline;'>
                <input type='hidden' name='post_id' value='$post_id'>
                <button type='submit'>" . ($liked ? "💔" : "❤️") . " {$row["like_count"]}</button>
              </form>";
        echo "<form method='post' action='bookmark_post.php' style='display:inline;'>
                <input type='hidden' name='post_id' value='$post_id'>
                <button type='submit'>" . ($bookmarked ? "❌" : "🔖") . "</button>
              </form>";
        echo "</div>";

        echo "</div>"; // post
    }
    echo "</div>";
} else {
    echo "<p>Looks like nobody you follow has posted :(</p>";
}
?>

</body>
</html>
