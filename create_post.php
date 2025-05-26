<?php
session_start();
require_once "db.php";

header('Content-Type: application/json');

// only logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// get data
$content = trim($_POST['content'] ?? '');
$csrf = $_POST['csrf_token'] ?? '';

if (!hash_equals($_SESSION['csrf_token'], $csrf)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

if ($content === '' || mb_strlen($content) > 500) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid content']);
    exit;
}

// add to db
$stmt = $conn->prepare("INSERT INTO posts (user_id, content, created_at) VALUES (?, ?, NOW())");
$stmt->bind_param("is", $_SESSION['user_id'], $content);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>
