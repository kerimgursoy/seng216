<?php
session_start();
require_once "db.php";

// check if ajax
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

//session
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user'])) {
    if ($is_ajax) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    } else {
        header("Location: login.php");
    }
    exit;
}

$post_id = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
$content = trim($_POST['content'] ?? '');
$csrf = $_POST['csrf_token'] ?? '';

// CSRF 
if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
    if ($is_ajax) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    } else {
        header("Location: " . $_SERVER['HTTP_REFERER']);
    }
    exit;
}

// check data
if (!$post_id || $content === '' || mb_strlen($content) > 300) {
    if ($is_ajax) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid input']);
    } else {
        header("Location: " . $_SERVER['HTTP_REFERER']);
    }
    exit;
}

// post to db
$stmt = $conn->prepare(
    "INSERT INTO comments (post_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())"
);
$stmt->bind_param("iis", $post_id, $_SESSION['user_id'], $content);
$success = $stmt->execute();
$comment_id = $conn->insert_id;

if ($is_ajax) {
    if ($success) {
        echo json_encode([
            'success' => true,
            'comment_id' => $comment_id,
            'post_id' => $post_id,
            'username' => $_SESSION['user'],
            'content' => htmlspecialchars($content),
            'created_at' => date("Y-m-d H:i:s"),
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
} else {
    // main page
    header("Location: " . $_SERVER['HTTP_REFERER']);
}
?>
