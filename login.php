<?php
session_start();
header('Content-Type: application/json');

// Check if it's an AJAX request
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Handle redirect URL from POST, fallback to 'signup.html'
$redirect = isset($_POST['redirectAfterLogin']) && $_POST['redirectAfterLogin'] !== 'null'
    ? $_POST['redirectAfterLogin']
    : 'index.php';

// Include database connection
require_once 'db.php';

// Function to check if the user is an employee
function isEmployee($userId, $conn) {
    $stmt = $conn->prepare("SELECT 1 FROM employees WHERE user_id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0; // Return true if employee exists
}

// If already logged in, respond accordingly
if (isset($_SESSION['user_id'])) {
  $response = [
      'success' => 'Login successful!',
      'redirect' => $redirect ?: 'home.php'
  ];
  echo json_encode($response);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $error = '';

    if (!empty($username) && !empty($password)) {
        // Query user from database
        $stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id, $db_username, $db_password);
            $stmt->fetch();

            // Verify password
            if (password_verify($password, $db_password)) {
                // Check if the user is an employee
                if (isEmployee($user_id, $conn)) {
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['username'] = $db_username;
                    $_SESSION['logged_in'] = true;
                    $_SESSION['is_employee'] = true; // Set employee session flag

                    if ($redirect === 'home.php') {
                      $redirect = 'dashboard.php'; // Employee goes to dashboard if redirect is home.php
                    }
                  //  error_log("Redirect after login: " . $redirect);
                    // Redirect to employee dashboard
                    $response = [
                        'success' => 'Login successful!',
                        'redirect' => $redirect // Redirect to dashboard.php for employees
                    ];
                    echo json_encode($response);
                    exit;
                } else {
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['username'] = $db_username;
                    $_SESSION['logged_in'] = true;
                    $_SESSION['is_employee'] = false; // Set normal user session flag

                    // Redirect to home.php for normal users
                    $response = [
                        'success' => 'Login successful!',
                        'redirect' => $redirect ?: 'home.php'// Redirect to home.php for normal users
                    ];
                    echo json_encode($response);
                    exit;
                }
            } else {
                $error = 'Invalid username or password!';
            }
        } else {
            $error = 'Invalid username or password!';
        }

        $stmt->close();
    } else {
        $error = 'Please fill in both fields.';
    }

    // Return error message
    echo json_encode(['error' => $error]);
}
?>