function redirectToLoginPage() {
    // Store the current page (or the desired page) in the session
    const currentPage = window.location.pathname;
    console.log("in the wishlisdfsfsfsdfsdfst");
    // Use AJAX to send the current page URL to the server
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "store_redirect.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
      if (xhr.readyState == 4 && xhr.status == 200) {
        // After storing the redirect URL, redirect the user to the login page
        window.location.href = "login.html";  // Redirect to login page
      }
    };
    
    // Send the current page as a parameter
    xhr.send("redirectUrl=" + encodeURIComponent(currentPage));
  }
  