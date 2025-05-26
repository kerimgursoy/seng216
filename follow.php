<?php
session_start();
require_once "db.php";

// login control
if (!isset($_SESSION["user_id"])) {
    http_response_code(403);
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit;
}

$follower_id = $_SESSION["user_id"];
$followed_id = filter_input(INPUT_POST, 'followed_id', FILTER_VALIDATE_INT);
$csrf = $_POST["csrf_token"] ?? '';

if (!$followed_id || !hash_equals($_SESSION["csrf_token"], $csrf)) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Invalid input or CSRF token"]);
    exit;
}

// follow control
$stmt = $conn->prepare("SELECT 1 FROM follows WHERE follower_id = ? AND followed_id = ?");
$stmt->bind_param("ii", $follower_id, $followed_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // unfollow
    $stmt = $conn->prepare("DELETE FROM follows WHERE follower_id = ? AND followed_id = ?");
    $stmt->bind_param("ii", $follower_id, $followed_id);
    $stmt->execute();
    $status = "unfollowed";
} else {
    // follow
    $stmt = $conn->prepare("INSERT INTO follows (follower_id, followed_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $follower_id, $followed_id);
    $stmt->execute();
    $status = "followed";
}

// follower count
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM follows WHERE followed_id = ?");
$stmt->bind_param("i", $followed_id);
$stmt->execute();
$result = $stmt->get_result();
$follower_count = $result->fetch_assoc()["total"] ?? 0;

// return
header("Content-Type: application/json");
echo json_encode([
    "success" => true,
    "status" => $status,
    "follower_count" => $follower_count
]);
