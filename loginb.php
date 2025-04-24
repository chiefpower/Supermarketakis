<?php
session_start();

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// If the user is already logged in
if (isset($_SESSION['user_id'])) {
    if ($isAjax) {
        if (isset($_SESSION['redirectAfterLogin'])) {
            // Get the redirect URL from session
            $redirectUrl = $_SESSION['redirectAfterLogin'];
            // Clear the redirect session variable
            unset($_SESSION['redirectAfterLogin']);
            // Redirect the user to the stored URL
            echo json_encode(['success' => 'Login successful!', 'redirect' => $redirectUrl]);
            exit;
        } else {
            // If no redirect URL is stored, send them to the home page
            echo json_encode(['success' => 'Login successful!', 'redirect' => 'home.php']);
            exit;
        }

      //  echo json_encode(['success' => 'Login successful!', 'redirect' => 'home.php']);
      //  exit;
    } else {
        if (isset($_SESSION['redirectAfterLogin'])) {
            // Get the redirect URL from session
            $redirectUrl = $_SESSION['redirectAfterLogin'];
            // Clear the redirect session variable
            unset($_SESSION['redirectAfterLogin']);
            // Redirect the user to the stored URL
            echo json_encode(['success' => 'Login successful!', 'redirect' => $redirectUrl]);
            exit;
        } else {
            // If no redirect URL is stored, send them to the home page
            echo json_encode(['success' => 'Login successful!', 'redirect' => 'home.php']);
            exit;
        }
      //  echo json_encode(['success' => 'Login successful!', 'redirect' => 'home.php']);
      //  exit;
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'db.php';

    $username = $_POST["username"] ?? '';
    $password = $_POST["password"] ?? '';

    if (!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id, $db_username, $db_password);
            $stmt->fetch();

            if (password_verify($password, $db_password)) {
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $db_username;
                $_SESSION['logged_in'] = true;

                if ($isAjax) {
                    if (isset($_SESSION['redirectAfterLogin'])) {
                        // Get the redirect URL from session
                        $redirectUrl = $_SESSION['redirectAfterLogin'];
                        // Clear the redirect session variable
                        unset($_SESSION['redirectAfterLogin']);
                        // Redirect the user to the stored URL
                        echo json_encode(['success' => 'Login successful!', 'redirect' => $redirectUrl]);
                        exit;
                    } else {
                        // If no redirect URL is stored, send them to the home page
                        echo json_encode(['success' => 'Login successful!', 'redirect' => 'home.php']);
                        exit;
                    }
                  //  echo json_encode(['success' => 'Login successful!', 'redirect' => 'home.php']);
                  //  echo json_encode(['success' => 'Login successful!']);
                } else {
                    if (isset($_SESSION['redirectAfterLogin'])) {
                        // Get the redirect URL from session
                        $redirectUrl = $_SESSION['redirectAfterLogin'];
                        // Clear the redirect session variable
                        unset($_SESSION['redirectAfterLogin']);
                        // Redirect the user to the stored URL
                        echo json_encode(['success' => 'Login successful!', 'redirect' => $redirectUrl]);
                        exit;
                    } else {
                        // If no redirect URL is stored, send them to the home page
                        echo json_encode(['success' => 'Login successful!', 'redirect' => 'home.php']);
                        exit;
                    }
                   //echo json_encode(['success' => 'Login successful!', 'redirect' => 'index.php']);
                  //  exit;
                }
                exit;
            } else {
                $error = 'Invalid username or password!';
            }
        } else {
            $error = "Invalid username or password!";
        }

        $stmt->close();
    } else {
        $error = 'Please fill in both fields.';
    }

    // Return error message
    if ($isAjax) {
        echo json_encode(['error' => $error]);
    } else {
     //   echo $error;
    }
}
?>
