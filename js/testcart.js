// cart.js (as an ES6 module)

export const cartKey = 'shoppingCart';

export function getCart() {
  return JSON.parse(localStorage.getItem(cartKey)) || [];
}

export function saveCart(cart) {
  localStorage.setItem(cartKey, JSON.stringify(cart));
}

export function emptyCart() {
  localStorage.setItem(cartKey, JSON.stringify([]));
  renderCart();
}

export function renderCart() {
  const cart = getCart();
  const cartList = document.querySelector('.list-group');
  const totalElement = document.querySelector('.list-group-item strong');
  const headerTotal = document.getElementById('header-cart-total');

  cartList.innerHTML = '';

  cart.forEach(item => {
    const li = document.createElement('li');
    li.className = 'list-group-item d-flex justify-content-between align-items-center lh-sm cart-item';
    li.dataset.id = item.id;
    li.dataset.price = item.price;

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

  const total = calculateTotal(cart);
  const totalLi = document.createElement('li');
  totalLi.className = 'list-group-item d-flex justify-content-between';
  totalLi.innerHTML = `<span>Total (EUR)</span><strong>€${total.toFixed(2)}</strong>`;
  cartList.appendChild(totalLi);

  if (headerTotal) headerTotal.textContent = `€${total.toFixed(2)}`;

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

  const cartItemCount = document.getElementById('cart-item-count');
  if (cartItemCount) {
    const totalQuantity = cart.reduce((sum, item) => sum + item.quantity, 0);
    cartItemCount.textContent = totalQuantity;
  }
}

export function calculateTotal(cart) {
  return cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
}

export function updateQuantity(itemId, delta) {
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

export function removeFromCart(itemId) {
  const cart = getCart().filter(item => item.id !== itemId);
  saveCart(cart);
  renderCart();
}
