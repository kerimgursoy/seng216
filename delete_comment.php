<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"], $_POST["comment_id"], $_POST["post_id"])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$comment_id = (int)$_POST["comment_id"];
$post_id = (int)$_POST["post_id"];

// Check if the comment belongs to this user
$sql = "SELECT * FROM comments WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $comment_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    // The user owns this comment — delete it
    $delete_sql = "DELETE FROM comments WHERE id = ?";
    $del_stmt = mysqli_prepare($conn, $delete_sql);
    mysqli_stmt_bind_param($del_stmt, "i", $comment_id);
    mysqli_stmt_execute($del_stmt);
}

header("Location: post.php?id=$post_id");
exit();
