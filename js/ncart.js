document.addEventListener("DOMContentLoaded", function () {
  // Quantity minus button
  document.addEventListener('click', function (e) {
    if (e.target.closest('.quantity-left-minus1')) {
      const input = e.target.closest('.product-qty').querySelector('.input-number');
      let currentValue = parseInt(input.value) || 1;
      if (currentValue > 1) {
        input.value = currentValue - 1;
      }
    }
  });

  // Quantity plus button
  document.addEventListener('click', function (e) {
    if (e.target.closest('.quantity-right-plus1')) {
      const input = e.target.closest('.product-qty').querySelector('.input-number');
      let currentValue = parseInt(input.value) || 1;
      input.value = currentValue + 1;
    }
  });

  // Add to cart button
  document.addEventListener('click', function (e) {
    const button = e.target.closest('.add-to-cart1');
    if (button) {
      e.preventDefault();
      const productId = button.getAttribute('data-id');
      const productName = button.getAttribute('data-name');
      const productPrice = parseFloat(button.getAttribute('data-price'));

      const productItem = button.closest('.product-item');
      const quantityInput = productItem.querySelector('.input-number');
      const productQuantity = parseInt(quantityInput.value) || 1;

      const item = {
        id: productId,
        name: productName,
        price: productPrice,
        quantity: productQuantity
      };

      addToCart(item); // Make sure addToCart is globally defined or imported properly
    }
  });
});