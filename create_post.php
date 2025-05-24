<?php
// display error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// session
if (!isset($_SESSION["user_id"])) {
    die("Giriş yapılmamış veya oturum süresi dolmuş.");
}

require_once "db.php";

// POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $content = trim($_POST["content"] ?? '');
    $user_id = $_SESSION["user_id"];

    if (empty($content)) {
        die("Boş içerik gönderilemez.");
    }

    // SQL
    $sql = "INSERT INTO posts (user_id, content) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        die("Sorgu hazırlanamadı: " . mysqli_error($conn));
    }

    // parameter binding
    mysqli_stmt_bind_param($stmt, "is", $user_id, $content);
    mysqli_stmt_execute($stmt);

    header("Location: index.php");
    exit();
} else {
    echo "Geçersiz istek.";
}
?>
