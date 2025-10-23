// Cart management
let cart = []

// Add to cart
function addToCart(productId, name, price, stock) {
  if (stock <= 0) {
    alert("Product is out of stock")
    return
  }

  const existingItem = cart.find((item) => item.product_id === productId)

  if (existingItem) {
    if (existingItem.quantity < stock) {
      existingItem.quantity++
      existingItem.subtotal = existingItem.quantity * existingItem.price
    } else {
      alert("Cannot exceed available stock")
    }
  } else {
    cart.push({
      product_id: productId,
      name: name,
      price: price,
      quantity: 1,
      subtotal: price,
    })
  }

  updateCart()
}

// Update cart display
function updateCart() {
  const cartItemsDiv = document.getElementById("cartItems")
  const cartCountSpan = document.getElementById("cartCount")

  if (cart.length === 0) {
    cartItemsDiv.innerHTML = '<p class="empty-cart">Cart is empty</p>'
    cartCountSpan.textContent = "0"
    updateTotal()
    return
  }

  let html = ""
  cart.forEach((item, index) => {
    html += `
            <div class="cart-item">
                <div class="cart-item-name">${item.name}</div>
                <div class="cart-item-price">₱${item.price.toFixed(2)}</div>
                <div class="cart-item-controls">
                    <button class="qty-btn" onclick="decreaseQty(${index})">-</button>
                    <input type="number" class="qty-input" value="${item.quantity}" onchange="setQty(${index}, this.value)">
                    <button class="qty-btn" onclick="increaseQty(${index})">+</button>
                    <button class="remove-btn" onclick="removeFromCart(${index})">Remove</button>
                </div>
            </div>
        `
  })

  cartItemsDiv.innerHTML = html
  cartCountSpan.textContent = cart.length
  updateTotal()
}

// Increase quantity
function increaseQty(index) {
  cart[index].quantity++
  cart[index].subtotal = cart[index].quantity * cart[index].price
  updateCart()
}

// Decrease quantity
function decreaseQty(index) {
  if (cart[index].quantity > 1) {
    cart[index].quantity--
    cart[index].subtotal = cart[index].quantity * cart[index].price
    updateCart()
  }
}

// Set quantity
function setQty(index, qty) {
  qty = Number.parseInt(qty)
  if (qty > 0) {
    cart[index].quantity = qty
    cart[index].subtotal = cart[index].quantity * cart[index].price
    updateCart()
  }
}

// Remove from cart
function removeFromCart(index) {
  cart.splice(index, 1)
  updateCart()
}

// Clear cart
function clearCart() {
  if (confirm("Are you sure you want to clear the cart?")) {
    cart = []
    updateCart()
  }
}

// Update total
function updateTotal() {
  let subtotal = 0
  cart.forEach((item) => {
    subtotal += item.subtotal
  })

  const discountAmount = Number.parseFloat(document.getElementById("discountAmount").value) || 0
  const total = subtotal - discountAmount

  document.getElementById("subtotal").textContent = "₱" + subtotal.toFixed(2)
  document.getElementById("total").textContent = "₱" + total.toFixed(2)
}

// Proceed to checkout
function proceedToCheckout() {
  if (cart.length === 0) {
    alert("Cart is empty")
    return
  }

  // Save cart to session via AJAX
  const discount = Number.parseFloat(document.getElementById("discountAmount").value) || 0

  fetch("save_cart.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      cart: cart,
      discount: discount,
    }),
  }).then((response) => {
    if (response.ok) {
      window.location.href = "checkout.php"
    }
  })
}

// Perform search
function performSearch() {
  const search = document.getElementById("searchInput").value
  window.location.href = "?search=" + encodeURIComponent(search)
}

// Keyboard shortcuts
document.addEventListener("keydown", (event) => {
  if (event.key === "F2") {
    event.preventDefault()
    document.getElementById("searchInput").focus()
  }
})
