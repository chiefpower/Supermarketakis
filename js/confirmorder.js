document.addEventListener("DOMContentLoaded", function () {

    $(document).on('click', '#confirm-order', function () {
        const messageBox = document.getElementById('message');
        const messageBox1 = document.getElementById('message1');
        const orderId = this.getAttribute('data-order-id');

        console.log("Order confirmed for ID:", orderId); 
        fetch('confirm_order.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({ order_id: orderId })
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              messageBox1.innerHTML = `<div class="alert alert-success">'Order confirmed successfully!'</div>`;
              messageBox.innerHTML = `<div class="alert alert-success">'Thank you for your order! A confirmation email will be sent shortly.'</div>`;
            } else {
               // console.error('Error:', ${data.success});
              messageBox.innerHTML = `<div class="alert alert-danger">${data.error} 'Error confirming order.'</div>`;
            }
          })
          .catch(error => {
            console.error('Error:', error);
            messageBox.innerHTML = `<div class="alert alert-danger">'Server error. Please try again.'</div>`;
            //document.getElementById('message').innerText = 'Server error.';
          });
      //}
      });

});