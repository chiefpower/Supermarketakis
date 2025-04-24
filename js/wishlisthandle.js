document.querySelectorAll('.btn-wishlist').forEach(button => {
  button.addEventListener('click', function (e) {
    e.preventDefault();

    // Check if the user is logged in
    //const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    const isLoggedIn = document.body.dataset.loggedin === 'true';
    if (!isLoggedIn) {
      // Save the redirect path to session
      localStorage.setItem('redirectAfterLogin', 'favourites.php');
      window.location.href = 'login.html';  // redirect to the login page
    } else {
      window.location.href = 'favourites.php'; // go to favourites if logged in
    }
  });
});
