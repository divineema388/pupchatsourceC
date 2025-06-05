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
$username = isset($_POST['username']) ? sanitize_input($_POST['username']) : '';
$email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
$password = isset($_POST['password']) ? sanitize_input($_POST['password']) : '';

// Validate input
if (empty($username) || empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Validate password length
if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
    exit;
}

// Check if email already exists
$check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
if (!$check_email) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit;
}

$check_email->bind_param("s", $email);
$check_email->execute();
$result = $check_email->get_result();

if ($result->num_rows > 0) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'Email already exists']);
    $check_email->close();
    exit;
}
$check_email->close();

// Check if username already exists
$check_username = $conn->prepare("SELECT id FROM users WHERE username = ?");
if (!$check_username) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit;
}

$check_username->bind_param("s", $username);
$check_username->execute();
$result = $check_username->get_result();

if ($result->num_rows > 0) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'Username already exists']);
    $check_username->close();
    exit;
}
$check_username->close();

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert new user
$stmt = $conn->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit;
}

$stmt->bind_param("sss", $username, $email, $hashed_password);

if ($stmt->execute()) {
    http_response_code(201);
    echo json_encode([
        'success' => true, 
        'message' => 'User registered successfully',
        'user_id' => $conn->insert_id
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>