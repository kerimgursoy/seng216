<?php
session_start();
require_once "db.php";

// AJAX control and login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

header("Content-Type: application/json");

$post_id = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
$content = trim($_POST['content'] ?? '');
$csrf = $_POST['csrf_token'] ?? '';

// CSRF verification
if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

// data reliability
if (!$post_id || $content === '' || mb_strlen($content) > 300) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

// add comment to database
$stmt = $conn->prepare(
    "INSERT INTO comments (post_id, user_id, content, created_at) VALUES (?, ?, ?, NOW())"
);
$stmt->bind_param("iis", $post_id, $_SESSION['user_id'], $content);
$success = $stmt->execute();
$comment_id = $conn->insert_id;

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
?>
