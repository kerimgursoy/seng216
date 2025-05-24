<?php
session_start();
require_once("db.php");

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo "E-posta ve şifre zorunludur.";
    exit;
}

$stmt = $conn->prepare("SELECT id, username, password, is_admin FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = $user['is_admin'];
        echo "success";
    } else {
        echo "Şifre yanlış.";
    }
} else {
    echo "Kullanıcı bulunamadı.";
}
