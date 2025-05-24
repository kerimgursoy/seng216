 <!DOCTYPE html>
<html>
<head>
<title>Page Title</title>
<link rel="stylesheet" href="index.css">
</head>
<body>

<?php
session_start();
require_once "db.php";

if (!isset($_GET["user"])) {
    die("User not found.");
}

$username = $_GET["user"];

// user info
$sql_user = "SELECT id, usrname FROM users WHERE usrname = ?";
$stmt_user = mysqli_prepare($conn, $sql_user);
mysqli_stmt_bind_param($stmt_user, "s", $username);
mysqli_stmt_execute($stmt_user);
$user_result = mysqli_stmt_get_result($stmt_user);

if (!$user_result || mysqli_num_rows($user_result) === 0) {
    die("User not found.");
}
$user_data = mysqli_fetch_assoc($user_result);
$user_id = $user_data["id"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($user_data["usrname"]) ?>'s Profile</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>
<nav class="navbar">
    <div class="navbar-logo"><a href="index.php">MyApp</a></div>
    <ul class="navbar-links">
        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>

<h2><?= htmlspecialchars($user_data["usrname"]) ?>'s Posts</h2>





<?php
// follower count
$res1 = $conn->query("SELECT COUNT(*) AS total FROM follows WHERE followed_id = $user_id");
$followers = $res1->fetch_assoc()["total"];

// following count
$res2 = $conn->query("SELECT COUNT(*) AS total FROM follows WHERE follower_id = $user_id");
$following = $res2->fetch_assoc()["total"];

echo "<p>$followers followers • $following following</p>";




if (isset($_SESSION["user_id"]) && $_SESSION["user_id"] !== $user_id) {
    $fcheck = mysqli_prepare($conn, "SELECT 1 FROM follows WHERE follower_id = ? AND followed_id = ?");
    mysqli_stmt_bind_param($fcheck, "ii", $_SESSION["user_id"], $user_id);
    mysqli_stmt_execute($fcheck);
    mysqli_stmt_store_result($fcheck);
    $isFollowing = mysqli_stmt_num_rows($fcheck) > 0;

    echo "<form method='post' action='follow.php' style='margin: 10px 0;'>";
    echo "<input type='hidden' name='followed_id' value='$user_id'>";
    echo "<input type='hidden' name='username' value='" . htmlspecialchars($username) . "'>";
    echo "<button type='submit'>" . ($isFollowing ? "Unfollow" : "Follow") . "</button>";
    echo "</form>";
}

// get posts
$sql_posts = "SELECT posts.id AS post_id, posts.content, posts.created_at,
             (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS like_count
             FROM posts
             WHERE posts.user_id = ?
             ORDER BY posts.created_at DESC";
$stmt_posts = mysqli_prepare($conn, $sql_posts);
mysqli_stmt_bind_param($stmt_posts, "i", $user_id);
mysqli_stmt_execute($stmt_posts);
$posts_result = mysqli_stmt_get_result($stmt_posts);

if (mysqli_num_rows($posts_result) > 0) {
    echo "<div class='post-list'>";
    while ($row = mysqli_fetch_assoc($posts_result)) {
        $post_id = $row["post_id"];

        echo "<div class='post'>";
        echo "<p><a href='post.php?id=$post_id'>" . nl2br(htmlspecialchars($row["content"])) . "</a></p>";
        echo "<span class='timestamp'>" . $row["created_at"] . "</span>";

        
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
    echo "<p>This user hasn't posted anything yet.</p>";
}
?>
</body>
</html>


</body>
</html> 


