<?php
session_start();
require_once "db.php";

// Kullanıcı girişi kontrolü
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Kullanıcının bookmarkladığı gönderileri al
$sql = "SELECT posts.id AS post_id, posts.content, posts.created_at, users.usrname
        FROM bookmarks
        JOIN posts ON bookmarks.post_id = posts.id
        JOIN users ON posts.user_id = users.id
        WHERE bookmarks.user_id = ?
        ORDER BY bookmarks.created_at DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kaydedilen Gönderiler</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>
<nav class="navbar">
    <div class="navbar-logo"><a href="index.php">MyApp</a></div>
    <ul class="navbar-links">
        <li><a href="feed.php">📥 Feed</a></li>

        <li><a href="logout.php">Logout</a></li>
    </ul>
</nav>

<h2>🔖 Bookmarks</h2>

<?php
if (mysqli_num_rows($result) > 0) {
    echo "<div class='post-list'>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<div class='post'>";
        echo "<strong>" . htmlspecialchars($row['usrname']) . "</strong><br>";
        echo "<p><a href='post.php?id=" . $row['post_id'] . "'>" . nl2br(htmlspecialchars($row['content'])) . "</a></p>";
        echo "<span class='timestamp'>" . $row['created_at'] . "</span>";
        echo "</div>";
    }

    echo "</div>";
} else {
    echo "<p>You haven't bookmarked anything yet.</p>";
}
?>
</body>
</html>
