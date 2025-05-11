const cartKey = 'shoppingCart';

// Retrieve the cart from localStorage
function getCart() {
  return JSON.parse(localStorage.getItem(cartKey)) || [];
}

// Save the cart to localStorage
function saveCart(cart) {
  localStorage.setItem(cartKey, JSON.stringify(cart));
}

// Add an item to the cart
function addToCart(item) {
  const cart = getCart();
  const existing = cart.find(i => i.id === item.id);

  if (existing) {
    existing.quantity += item.quantity;
  } else {
    cart.push(item);
  }

  saveCart(cart);
  renderCart();
}

// Remove an item from the cart
function removeFromCart(itemId) {
  const cart = getCart().filter(item => item.id !== itemId);
  saveCart(cart);
  renderCart();
}

// Empty the cart (remove all items)
function emptyCart() {
  // Clear the cart from localStorage
  localStorage.setItem(cartKey, JSON.stringify([]));
  renderCart();
}

// Update the quantity of an item in the cart
function updateQuantity(itemId, delta) {
  const cart = getCart();
  const item = cart.find(i => i.id === itemId);

  if (item) {
    item.quantity += delta;
    if (item.quantity < 1) {
      removeFromCart(itemId);
      return;
    }
  }

  saveCart(cart);
  renderCart();
}

// Calculate the total price of the items in the cart
function calculateTotal(cart) {
  return cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
}

// Render the cart on the page
function renderCart() {
  const cart = getCart();
  const cartList = document.querySelector('.list-group');
  const totalElement = document.querySelector('.list-group-item strong');
  const headerTotal = document.getElementById('header-cart-total');

  // Clear existing list except total
  cartList.innerHTML = '';

  cart.forEach(item => {
    const li = document.createElement('li');
    li.className = 'list-group-item d-flex justify-content-between align-items-center lh-sm cart-item';
    li.dataset.id = item.id;
    li.dataset.price = item.price;

    //let roundedPrice = Math.round((item.price * item.quantity) * 100) / 100;
    let roundedPrice = item.price * item.quantity;
    li.innerHTML = `
      <div>
        <h6 class="my-0">${item.name}</h6>
        <small class="text-body-secondary">x<span class="item-qty">${item.quantity}</span></small>
        <div class="mt-1">
          <button class="btn btn-sm btn-outline-secondary me-1 minus">–</button>
          <button class="btn btn-sm btn-outline-secondary plus">+</button>
        </div>
      </div>
      <span class="text-body-secondary">€${roundedPrice.toFixed(2)}</span>
    `;

    cartList.appendChild(li);
  });

  // Add total
  const total = calculateTotal(cart);
  const totalLi = document.createElement('li');
  totalLi.className = 'list-group-item d-flex justify-content-between';
  totalLi.innerHTML = `<span>Total (EUR)</span><strong>€${total.toFixed(2)}</strong>`;
  cartList.appendChild(totalLi);

  // Update header total
  if (headerTotal) headerTotal.textContent = `€${total.toFixed(2)}`;

  // Add event listeners to +/– buttons
  document.querySelectorAll('.plus').forEach(btn => {
    btn.addEventListener('click', e => {
      const id = btn.closest('.cart-item').dataset.id;
      updateQuantity(id, 1);
    });
  });

  document.querySelectorAll('.minus').forEach(btn => {
    btn.addEventListener('click', e => {
      const id = btn.closest('.cart-item').dataset.id;
      updateQuantity(id, -1);
    });
  });

  // Update badge with total quantity
  const cartItemCount = document.getElementById('cart-item-count');
  if (cartItemCount) {
    const totalQuantity = cart.reduce((sum, item) => sum + item.quantity, 0);
    cartItemCount.textContent = totalQuantity;
  }
}

// Add event listeners to +/– buttons 
document.addEventListener('DOMContentLoaded', function () {
  // Minus button
  document.querySelectorAll('.quantity-left-minus').forEach(button => {
    button.addEventListener('click', function (e) {
      const input = this.closest('.product-qty').querySelector('.input-number');
      let currentValue = parseInt(input.value) || 1;
      if (currentValue > 1) {
        input.value = currentValue - 1;
      }
    });
  });

  // Plus button
  document.querySelectorAll('.quantity-right-plus').forEach(button => {
    button.addEventListener('click', function (e) {
      const input = this.closest('.product-qty').querySelector('.input-number');
    //  console.log("id is: ",input);
      let currentValue = parseInt(input.value) || 1;
      input.value = currentValue + 1;
    });
  });
});

// Event listener for the "Add to Cart" buttons
document.querySelectorAll('.add-to-cart').forEach(button => {
  button.addEventListener('click', function(e) {
    e.preventDefault();

    // Get product information from the button's data attributes
    const productId = this.getAttribute('data-id');
    const productName = this.getAttribute('data-name');
    const productPrice = parseFloat(this.getAttribute('data-price'));
   // const productQuantity = parseInt(this.getAttribute('data-quantity'));

     // Get the quantity input near the current product card
     const productItem = this.closest('.product-item');
     const quantityInput = productItem.querySelector('.input-number');
     const productQuantity = parseInt(quantityInput.value) || 1;

    // Create the product item object
    const item = {
      id: productId,
      name: productName,
      price: productPrice,
      quantity: productQuantity
    };
   // addToCart({ id: "item-1", name: "Growers cider", price: 12, quantity: 1 });
    // Add item to the cart
    addToCart(item);
  });
});

// Restore cart on page load
document.addEventListener('DOMContentLoaded', () => {
  renderCart();
});
