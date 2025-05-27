<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit();
}

// CSRF token
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION["csrf_token"];

// post control
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Gönderi bulunamadı.");
}
$post_id = (int)$_GET["id"];

// post info
$sql = "SELECT posts.content, posts.created_at, users.usrname, users.id AS user_id
        FROM posts
        JOIN users ON posts.user_id = users.id
        WHERE posts.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

if (!($row = $result->fetch_assoc())) {
    die("No such post.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="favicon.png">

</head>
<body>
<nav class="navbar">
    <div class="navbar-logo">
        <a href="index.php">SociaLink</a>
    </div>
    <ul class="navbar-links">
        <li><a href="feed.php">📥 Feed</a></li>
        <li><a href="bookmarks.php">🔖 Bookmarks</a></li>
        <li><a href="logout.php" class="btn btn-warning">Logout</a></li>
    </ul>
</nav>

<!-- post -->
<div class="post">
    <strong><a href="profile.php?user=<?= urlencode($row['usrname']) ?>">
        <?= htmlspecialchars($row['usrname']) ?>
    </a></strong>
    <p><?= nl2br(htmlspecialchars($row['content'])) ?></p>
    <p><em><?= htmlspecialchars($row['created_at']) ?></em></p>
</div>

<!-- comment form-->
<?php if (isset($_SESSION["user_id"])): ?>
    <form id="commentForm">
        <input type="hidden" name="post_id" value="<?= htmlspecialchars($post_id) ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        <textarea name="content" rows="3" required minlength="1" maxlength="500"></textarea><br>
        <input type="submit" value="Comment">
    </form>
<?php endif; ?>

<div id="comment-error" style="color:red;"></div>

<h3>Comments</h3>
<div id="comments">
<?php
$comment_sql = "SELECT comments.id, comments.content, comments.created_at, users.usrname, users.id AS user_id
                FROM comments
                JOIN users ON comments.user_id = users.id
                WHERE comments.post_id = ?
                ORDER BY comments.created_at ASC";
$stmt = $conn->prepare($comment_sql);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$comments = $stmt->get_result();

while ($c = $comments->fetch_assoc()):
?>
    <div class="comment" id="comment-<?= $c['id'] ?>">
        <p><strong><?= htmlspecialchars($c['usrname']) ?>:</strong>
           <?= htmlspecialchars($c['content']) ?>
           <em>(<?= htmlspecialchars($c['created_at']) ?>)</em></p>
        <?php if ($_SESSION["user_id"] == $c["user_id"]): ?>
            <form class="delete-comment-form" data-id="<?= $c['id'] ?>">
                <input type="hidden" name="comment_id" value="<?= $c['id'] ?>">
                <input type="hidden" name="post_id" value="<?= $post_id ?>">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                <button type="submit" style="color:red; background:none; border:none; cursor:pointer;">🗑️ Delete</button>
            </form>
        <?php endif; ?>
    </div>
<?php endwhile; ?>
</div>

<script>
// AJAX comment
document.getElementById('commentForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const form = e.target;
    const data = new FormData(form);
    fetch('comment_post.php', {
        method: 'POST',
        body: data,
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin'
    })
    .then(res => res.json())
    .then(json => {
        if (json.success) {
            const comment = document.createElement('div');
            comment.className = 'comment';
            comment.id = 'comment-' + json.comment_id;
            comment.innerHTML = `<p><strong>${json.username}:</strong> ${json.content} <em>(${json.created_at})</em></p>
                <form class="delete-comment-form" data-id="${json.comment_id}">
                    <input type="hidden" name="comment_id" value="${json.comment_id}">
                    <input type="hidden" name="post_id" value="${json.post_id}">
                    <input type="hidden" name="csrf_token" value="${json.csrf_token}">
                    <button type="submit" style="color:red; background:none; border:none; cursor:pointer;">🗑️ Delete</button>
                </form>`;
            document.getElementById('comments').appendChild(comment);
            form.reset();
            document.getElementById('comment-error').textContent = '';
        } else {
            document.getElementById('comment-error').textContent = json.error;
        }
    }).catch(() => {
        document.getElementById('comment-error').textContent = 'An error occurred.';
    });
});

// AJAX comment delete
document.addEventListener('submit', function(e) {
    if (e.target.classList.contains('delete-comment-form')) {
        e.preventDefault();
        const form = e.target;
        const data = new FormData(form);
        const id = form.dataset.id;
        fetch('delete_comment.php', {
            method: 'POST',
            body: data,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
        .then(res => res.json())
        .then(json => {
            if (json.success) {
                document.getElementById('comment-' + id)?.remove();
            } else {
                alert(json.error || 'Delete failed.');
            }
        })
        .catch(() => alert('An error occurred.'));
    }
});
</script>
</body>
</html>