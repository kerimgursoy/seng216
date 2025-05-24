<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"], $_POST["post_id"])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$post_id = (int)$_POST["post_id"];

// Zaten kaydetmiş mi?
$check = mysqli_prepare($conn, "SELECT 1 FROM bookmarks WHERE user_id = ? AND post_id = ?");
mysqli_stmt_bind_param($check, "ii", $user_id, $post_id);
mysqli_stmt_execute($check);
mysqli_stmt_store_result($check);

if (mysqli_stmt_num_rows($check) > 0) {
    // Varsa → Sil
    $del = mysqli_prepare($conn, "DELETE FROM bookmarks WHERE user_id = ? AND post_id = ?");
    mysqli_stmt_bind_param($del, "ii", $user_id, $post_id);
    mysqli_stmt_execute($del);
} else {
    // Yoksa → Ekle
    $insert = mysqli_prepare($conn, "INSERT INTO bookmarks (user_id, post_id) VALUES (?, ?)");
    mysqli_stmt_bind_param($insert, "ii", $user_id, $post_id);
    mysqli_stmt_execute($insert);
}

header("Location: index.php");
exit();
