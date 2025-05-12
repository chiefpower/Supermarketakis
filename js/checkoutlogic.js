// checkoutlogic.js 
import { emptyCart } from './testcart.js';

document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("userorderform");
    const messageBox = document.getElementById("message");
   // messageBox.innerHTML = "";

    form.addEventListener("submit", function (e) {
        e.preventDefault(); // Prevent default form submission

        const cartItems = JSON.parse(localStorage.getItem('shoppingCart')) || [];
        const userData = new FormData(form); // Get form data (user details)
        
         // Add cart items to the form data
        userData.append("cartItems", JSON.stringify(cartItems)); // Convert cart items to JSON string
       
        // Use AJAX to submit the form data
        fetch('process_checkout.php', {
            method: 'POST',
            body: userData
        })
            .then(response => response.json()) // Parse the JSON response
            .then(data => {
                console.log(data);
                if (data.success) {
                    emptyCart();
                    // Success: Display the order ID in a success message
                    messageBox.innerHTML = `<div class="alert alert-success">Order created successfully! Your order ID is: ${data.order_id}</div>`;
                    // Redirect the user to an order confirmation page
                    setTimeout(() => {
                        window.location.href = `order_confirmation.php?order_id=${data.order_id}`;
                      }, 2000);
                } else {
                    // Error: Display the error message
                    console.log("Parsed result:", data.error);
                    messageBox.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                }
            })
            .catch(error => {
                // Unexpected error: Display a generic error message
                console.log("Parsed result:", error);
                messageBox.innerHTML = `<div class="alert alert-danger">An unexpected error occurred. Please try again.</div>`;
            });
    });
});
