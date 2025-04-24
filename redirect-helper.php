<?php
$target = $_GET['redirectTo'] ?? 'index.php';
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Redirecting...</title>
  <script>
    localStorage.setItem('redirectAfterLogin', '<?php echo htmlspecialchars($target); ?>');
    window.location.href = 'login.html';
  </script>
</head>
<body>
  <p>Redirecting to login...</p>
</body>
</html>