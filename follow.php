<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"], $_POST["followed_id"])) {
    header("Location: index.php");
    exit();
}

$follower_id = $_SESSION["user_id"];
$followed_id = (int)$_POST["followed_id"];

if ($follower_id === $followed_id) {
    header("Location: index.php"); // Kendisini takip edemez
    exit();
}

// Zaten takip ediyor mu?
$check = mysqli_prepare($conn, "SELECT 1 FROM follows WHERE follower_id = ? AND followed_id = ?");
mysqli_stmt_bind_param($check, "ii", $follower_id, $followed_id);
mysqli_stmt_execute($check);
mysqli_stmt_store_result($check);

if (mysqli_stmt_num_rows($check) > 0) {
    // Takip ediyorsa → sil
    $del = mysqli_prepare($conn, "DELETE FROM follows WHERE follower_id = ? AND followed_id = ?");
    mysqli_stmt_bind_param($del, "ii", $follower_id, $followed_id);
    mysqli_stmt_execute($del);
} else {
    // Takip etmiyorsa → ekle
    $add = mysqli_prepare($conn, "INSERT INTO follows (follower_id, followed_id) VALUES (?, ?)");
    mysqli_stmt_bind_param($add, "ii", $follower_id, $followed_id);
    mysqli_stmt_execute($add);
}

// Profili kimse ziyaret ettiyse oraya geri dön
header("Location: profile.php?user=" . urlencode($_POST["username"]));
exit();
