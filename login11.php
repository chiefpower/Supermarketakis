<?php
session_start();
header('Content-Type: application/json');
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
//const redirectUrl = localStorage.getItem('redirectAfterLogin');
//$redirect = isset($_POST['redirectAfterLogin']) && !empty($_POST['redirectAfterLogin'])  ? $_POST['redirectAfterLogin'] 
//: 'signup.html';
$redirect = isset($_POST['redirectAfterLogin']) && $_POST['redirectAfterLogin'] !== 'null'
    ? $_POST['redirectAfterLogin']
    : 'signup.html';
// If the user is already logged in
if (isset($_SESSION['user_id'])) {
    if ($isAjax) {
   //   $redirectUrl = isset($_SESSION['redirectAfterLogin']) ? $_SESSION['redirectAfterLogin'] : null;
    //  console.log("Parsed sdfafdfa:", $redirectUrl); 
    
     
      if ($redirect) {
        echo json_encode([
          'success' => 'Login successful!',
          'redirect' => $redirect
        ]);
      //  localStorage.removeItem('redirectAfterLogin');
     //   window.location.href = redirectUrl;
      } else {
        echo json_encode(['success' => 'Login successful!', 'redirect' => 'home.php']);
      //    window.location.href = 'home.php'; // or some default page
      }
      
      //  echo json_encode(['success' => 'Login successful!', 'redirect' => 'home.php']);
      //  exit;
    } else {
        if ($redirect) {
          echo json_encode([
            'success' => 'Login successful!',
            'redirect' => $redirect
          ]);
            //localStorage.removeItem('redirectAfterLogin');
            //window.location.href = redirectUrl;
          } else {
            echo json_encode(['success' => 'Login successful!', 'redirect' => 'home.php']);
           // window.location.href = 'home.php'; // or some default page
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
                    if ($redirect) {
                      echo json_encode([
                        'success' => 'Login successful!',
                        'redirect' => $redirect
                      ]);
                        //localStorage.removeItem('redirectAfterLogin');
                        //window.location.href = redirectUrl;
                      } else {
                        echo json_encode(['success' => 'Login successful!', 'redirect' => 'home.php']);
                        //window.location.href = 'home.php'; // or some default page
                      }
                  //  echo json_encode(['success' => 'Login successful!', 'redirect' => 'home.php']);
                  //  echo json_encode(['success' => 'Login successful!']);
                } else {
                    if ($redirect) {
                      echo json_encode([
                        'success' => 'Login successful!',
                        'redirect' => $redirect
                      ]);
                        //localStorage.removeItem('redirectAfterLogin');
                        //window.location.href = redirectUrl;
                        exit;
                      } else {
                        echo json_encode(['success' => 'Login successful!', 'redirect' => 'home.php']);
                        //window.location.href = 'home.php'; // or some default page
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