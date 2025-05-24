<?php
session_start();
require_once "db.php";
if (isset($_POST["post_id"], $_POST["content"], $_SESSION["user_id"])) {
    $stmt = mysqli_prepare($conn, "INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iis", $_POST["post_id"], $_SESSION["user_id"], $_POST["content"]);
    mysqli_stmt_execute($stmt);
}
header("Location: index.php");
exit();
