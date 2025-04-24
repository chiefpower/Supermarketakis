const cartKey = 'shoppingCart';

  function getCart() {
    return JSON.parse(localStorage.getItem(cartKey)) || [];
  }

  function saveCart(cart) {
    localStorage.setItem(cartKey, JSON.stringify(cart));
  }

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

  function removeFromCart(itemId) {
    const cart = getCart().filter(item => item.id !== itemId);
    saveCart(cart);
    renderCart();
  }

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

  function calculateTotal(cart) {
    return cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
  }

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

      li.innerHTML = `
        <div>
          <h6 class="my-0">${item.name}</h6>
          <small class="text-body-secondary">x<span class="item-qty">${item.quantity}</span></small>
          <div class="mt-1">
            <button class="btn btn-sm btn-outline-secondary me-1 minus">–</button>
            <button class="btn btn-sm btn-outline-secondary plus">+</button>
          </div>
        </div>
        <span class="text-body-secondary">$${item.price * item.quantity}</span>
      `;

      cartList.appendChild(li);
    });

    // Add total
    const total = calculateTotal(cart);
    const totalLi = document.createElement('li');
    totalLi.className = 'list-group-item d-flex justify-content-between';
    totalLi.innerHTML = `<span>Total (USD)</span><strong>$${total.toFixed(2)}</strong>`;
    cartList.appendChild(totalLi);

    // Update header total
    if (headerTotal) headerTotal.textContent = `$${total.toFixed(2)}`;

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

  // Restore cart on page load
  document.addEventListener('DOMContentLoaded', () => {
    //addToCart({ id: "item-1", name: "Growers cider", price: 12, quantity: 1 });
   // addToCart({ id: "item-2", name: "Heinz Ketcup", price: 5, quantity: 2 });
    renderCart();

  });

  // Example: Hook to add to cart button
  // addToCart({ id: "item-1", name: "Growers cider", price: 12, quantity: 1 });
