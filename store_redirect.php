<?php
session_start();

// Check if the redirect URL is provided
if (isset($_POST['redirectUrl'])) {
    $_SESSION['redirectAfterLogin'] = $_POST['redirectUrl'];
    echo 'Redirect URL stored successfully.';
} else {
    echo 'No redirect URL provided.';
}
?>
