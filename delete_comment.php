<?php
session_start();
require_once "db.php";

// session control
if (!isset($_SESSION["user_id"])) {
    http_response_code(403);
    echo json_encode(["success" => false, "error" => "Not logged in."]);
    exit();
}

// CSRF control
if (!isset($_POST["csrf_token"]) || $_POST["csrf_token"] !== $_SESSION["csrf_token"]) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Invalid CSRF token."]);
    exit();
}

// parameter control
$comment_id = filter_input(INPUT_POST, "comment_id", FILTER_VALIDATE_INT);
$post_id = filter_input(INPUT_POST, "post_id", FILTER_VALIDATE_INT);
if (!$comment_id || !$post_id) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Invalid input."]);
    exit();
}

// check if it belongs to the user
$stmt = $conn->prepare("SELECT user_id FROM comments WHERE id = ?");
$stmt->bind_param("i", $comment_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row || $row["user_id"] != $_SESSION["user_id"]) {
    http_response_code(403);
    echo json_encode(["success" => false, "error" => "You are not authorized to delete this comment."]);
    exit();
}

// delete
$stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
$stmt->bind_param("i", $comment_id);
$stmt->execute();

// json if success
header("Content-Type: application/json");
echo json_encode(["success" => true, "comment_id" => $comment_id]);
exit();
