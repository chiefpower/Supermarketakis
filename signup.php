<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include database connection
require_once 'db.php';

// Get and sanitize input
$user_name = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid email format."]);
    exit();
}

// Validate password strength (minimum 8 chars, 1 number, 1 special char)
if (strlen($password) < 8 || 
    !preg_match('/[0-9]/', $password) || 
    !preg_match('/[\W]/', $password)) {
    
    http_response_code(400);
    echo json_encode([
        "error" => "Password must be at least 8 characters long and include at least one number and one special character."
    ]);
    exit();
}

// Hash the password securely
$password_hash = password_hash($password, PASSWORD_BCRYPT);

// Prepare SQL statement
$stmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $conn->error]);
    exit();
}

// Bind and execute
$stmt->bind_param("sss", $user_name, $email, $password_hash);

try {
    $stmt->execute();
    echo json_encode(["success" => "New account created successfully!"]);
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() == 1062) { // Duplicate entry
        $errorMessage = $e->getMessage();
        if (strpos($errorMessage, "'username'") !== false) {
            http_response_code(409);
            echo json_encode(["error" => "Username is already taken. Please choose another."]);
        } elseif (strpos($errorMessage, "'email'") !== false) {
            http_response_code(409);
            echo json_encode(["error" => "Email is already registered. Try logging in."]);
        } else {
            http_response_code(409);
            echo json_encode(["error" => "Duplicate entry."]);
        }
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
    }
}

$stmt->close();
$conn->close();
?>
