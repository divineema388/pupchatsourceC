<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Use require_once to prevent multiple inclusions
require_once 'config.php';

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only POST method allowed']);
    exit;
}

// Get POST data
$email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
$password = isset($_POST['password']) ? sanitize_input($_POST['password']) : '';

// Validate input
if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email and password are required']);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Check user credentials
$stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    $stmt->close();
    exit;
}

$user = $result->fetch_assoc();

// Verify password
if (password_verify($password, $user['password'])) {
    // Update last login time
    $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    if ($update_stmt) {
        $update_stmt->bind_param("i", $user['id']);
        $update_stmt->execute();
        $update_stmt->close();
    }
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user_id' => $user['id'],
        'username' => $user['username']
    ]);
} else {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
}

$stmt->close();
$conn->close();
?>