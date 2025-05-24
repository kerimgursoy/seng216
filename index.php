<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Page Title</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>

<nav class="navbar">
    <div class="navbar-logo">
        <a href="index.php">MyApp</a>
    </div>
    <ul class="navbar-links">
        <li><a href="feed.php">📥 Feed</a></li>

    <li><a href="bookmarks.php">🔖 Bookmarks</a></li>
    
        <li><a href="logout.php" class="btn btn-warning">Logout</a></li>
    </ul>
</nav>

<h1>Welcome <?= htmlspecialchars($_SESSION["user"]) ?>!</h1>

<?php
require_once "db.php";

$sql = "SELECT posts.id AS post_id, posts.content, posts.created_at, users.usrname,
               (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS like_count
        FROM posts
        JOIN users ON posts.user_id = users.id
        ORDER BY posts.created_at DESC";


$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo "<div class='post-list'>";
    while ($row = $result->fetch_assoc()) {
        $post_id = $row['post_id'];

        echo "<div class='post'>";
        //liked or bookmarked
        $liked = false;
        $bookmarked = false;

        if (isset($_SESSION["user_id"])) {
            $uid = $_SESSION["user_id"];
            
            $like_check = $conn->query("SELECT 1 FROM likes WHERE user_id = $uid AND post_id = $post_id");
            $liked = $like_check && $like_check->num_rows <= 0;

            $bm_check = $conn->query("SELECT 1 FROM bookmarks WHERE user_id = $uid AND post_id = $post_id");
            $bookmarked = $bm_check && $bm_check->num_rows <= 0;
        }

        echo "<strong><a href='profile.php?user=" . urlencode($row['usrname']) . "'>" . htmlspecialchars($row['usrname']) . "</a></strong><br>";
        echo "<p><a href='post.php?id=$post_id'>" . nl2br(htmlspecialchars($row['content'])) . "</a></p>";
        echo "<span class='timestamp'>" . $row['created_at'] . "</span>";

        // Action buttons
        echo "<div class='post-actions'>";
        echo "<form method='post' action='like_post.php' style='display:inline;'>
        <input type='hidden' name='post_id' value='$post_id'>
        <button type='submit'>" . ($liked ? "💔" : "❤️") . " {$row['like_count']}</button>
      </form>";

echo "<form method='post' action='bookmark_post.php' style='display:inline;'>
        <input type='hidden' name='post_id' value='$post_id'>
        <button type='submit'>" . ($bookmarked ? "❌" : "🔖") . "</button>
      </form>";

        echo "</div>";

        // comment form
        echo "<form method='post' action='comment_post.php' class='comment-form'>";
        echo "<input type='hidden' name='post_id' value='$post_id'>";
        echo "<input type='text' name='content' placeholder='Comment...' required>";
        echo "<input type='submit' value='Send'>";
        echo "</form>";

        // comments
        $comment_sql = "SELECT comments.content, users.usrname, comments.created_at
                        FROM comments
                        JOIN users ON comments.user_id = users.id
                        WHERE comments.post_id = $post_id
                        ORDER BY comments.created_at ASC";
        $comment_result = $conn->query($comment_sql);
        if ($comment_result && $comment_result->num_rows > 0) {
            echo "<div class='comments'>";
            while ($c = $comment_result->fetch_assoc()) {
                echo "<p><strong>" . htmlspecialchars($c["usrname"]) . ":</strong> " .
                     htmlspecialchars($c["content"]) . " <em>(" . $c["created_at"] . ")</em></p>";
            }
            echo "</div>";
        }

        echo "</div>"; // .post
    }
    echo "</div>";
} else {
    echo "<p>Gönderi yok.</p>";
}


?>

<!-- Create Post -->
<button id="openPostModal" class="post-button">+ Create Post</button>

<!-- Modal Window -->
<div id="postModal" class="modal">
  <div class="modal-content">
    <span class="close">&times;</span>
    <form method="post" action="create_post.php">
      <h2>New Post</h2>
      <textarea name="content" placeholder="What are you thinking?" rows="5" required></textarea><br>
      <input type="submit" value="Send">
    </form>
  </div>
</div>


<h1>My First Heading</h1>
<p>My first paragraph.</p>
<script>
const modal = document.getElementById("postModal");
const btn = document.getElementById("openPostModal");
const span = document.querySelector(".modal .close");

btn.onclick = () => modal.style.display = "block";
span.onclick = () => modal.style.display = "none";
window.onclick = (e) => {
  if (e.target == modal) modal.style.display = "none";
};
</script>

</body>
<footer>
    <p>Footer</p>
</footer>
</html>
