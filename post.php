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
    <link rel="stylesheet" href="post.css">
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

    <?php
    require_once "db.php";

    if (!isset($_GET["id"])) {
        die("Gönderi bulunamadı.");
    }

    $post_id = (int)$_GET["id"];

    // Gönderiyi getir
    $sql = "SELECT posts.content, posts.created_at, users.usrname
            FROM posts
            JOIN users ON posts.user_id = users.id
            WHERE posts.id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $post_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        echo "<strong><a href='profile.php?user=" . urlencode($row['usrname']) . "'>"
             . htmlspecialchars($row['usrname']) . "</a></strong><br>";
        echo "<p>" . nl2br(htmlspecialchars($row['content'])) . "</p>";
        echo "<p><em>" . $row['created_at'] . "</em></p>";
    } else {
        die("No such post.");
    }

    // Yorum formu
    if (isset($_SESSION["user_id"])) {
        echo "<form method='post' action='comment_post.php'>
                <input type='hidden' name='post_id' value='$post_id'>
                <textarea name='content' rows='3' required></textarea><br>
                <input type='submit' value='Comment'>
              </form>";
    }

    // Yorumları getir (include comments.id so delete works)
    $comment_sql = "SELECT comments.id, comments.content, comments.created_at, users.usrname
                    FROM comments
                    JOIN users ON comments.user_id = users.id
                    WHERE comments.post_id = ?
                    ORDER BY comments.created_at ASC";
    $stmt = mysqli_prepare($conn, $comment_sql);
    mysqli_stmt_bind_param($stmt, "i", $post_id);
    mysqli_stmt_execute($stmt);
    $comments = mysqli_stmt_get_result($stmt);

    echo "<h3>Comments</h3>";
    while ($c = mysqli_fetch_assoc($comments)) {
        echo "<div class='comment'>";
        echo "<p><strong>" . htmlspecialchars($c['usrname']) . ":</strong> "
             . htmlspecialchars($c['content']) . " <em>(" . $c['created_at'] . ")</em></p>";

        // Show delete button if the comment belongs to the logged-in user
        if (isset($_SESSION["user_id"]) && $c["usrname"] === $_SESSION["user"]) {
            echo "<form method='post' action='delete_comment.php' style='display:inline;'>";
            echo "<input type='hidden' name='comment_id' value='" . $c["id"] . "'>";
            echo "<input type='hidden' name='post_id' value='$post_id'>";
            echo "<button type='submit' style='background:none; border:none; color:red; cursor:pointer;'>🗑️ Delete</button>";
            echo "</form>";
        }

        echo "</div>";
    }
    ?>
</body>
</html>
