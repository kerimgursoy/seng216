<?php
session_start();
require_once "db.php";

// JSON setting
header('Content-Type: application/json');

// login, CSRF, postid
if (!isset($_SESSION["user_id"], $_POST["post_id"], $_POST["csrf_token"])) {
    http_response_code(400);
    echo json_encode(["error" => "Unauthorized or missing data."]);
    exit();
}

// CSRF control
if (!hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])) {
    http_response_code(403);
    echo json_encode(["error" => "Invalid CSRF token."]);
    exit();
}

$user_id = $_SESSION["user_id"];
$post_id = filter_input(INPUT_POST, "post_id", FILTER_VALIDATE_INT);

if (!$post_id) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid post ID."]);
    exit();
}

// check like
$check = $conn->prepare("SELECT 1 FROM likes WHERE user_id = ? AND post_id = ?");
$check->bind_param("ii", $user_id, $post_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    // remove
    $del = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?");
    $del->bind_param("ii", $user_id, $post_id);
    $success = $del->execute();
    $liked = false;
} else {
    // add
    $insert = $conn->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)");
    $insert->bind_param("ii", $user_id, $post_id);
    $success = $insert->execute();
    $liked = true;
}

// like count
$count_stmt = $conn->prepare("SELECT COUNT(*) AS like_count FROM likes WHERE post_id = ?");
$count_stmt->bind_param("i", $post_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result()->fetch_assoc();
$like_count = $count_result["like_count"] ?? 0;

// response
if ($success) {
    echo json_encode([
        "success" => true,
        "liked" => $liked,
        "like_count" => $like_count
    ]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Like action failed."]);
}