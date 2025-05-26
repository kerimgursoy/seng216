<?php
session_start();
require_once "db.php";

// json response setting
header("Content-Type: application/json");

// session and CSRF
if (
    !isset($_SESSION["user_id"], $_POST["post_id"], $_POST["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
) {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized or invalid CSRF token."]);
    exit();
}

// post ID check
$user_id = $_SESSION["user_id"];
$post_id = filter_input(INPUT_POST, "post_id", FILTER_VALIDATE_INT);
if (!$post_id) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid post ID."]);
    exit();
}

// Bookmark check
$check = $conn->prepare("SELECT 1 FROM bookmarks WHERE user_id = ? AND post_id = ?");
$check->bind_param("ii", $user_id, $post_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    // delete if bookmarked
    $del = $conn->prepare("DELETE FROM bookmarks WHERE user_id = ? AND post_id = ?");
    $del->bind_param("ii", $user_id, $post_id);
    $success = $del->execute();
    $bookmarked = false;
} else {
    // add if bookmarked
    $insert = $conn->prepare("INSERT INTO bookmarks (user_id, post_id) VALUES (?, ?)");
    $insert->bind_param("ii", $user_id, $post_id);
    $success = $insert->execute();
    $bookmarked = true;
}

if ($success) {
    echo json_encode(["success" => true, "bookmarked" => $bookmarked]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Database error occurred."]);
}