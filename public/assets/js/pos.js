// POS front-end interactions
let cart = []
let paymentType = "Cash"
let installmentMonths = 6
const stateKey = "posCatalogState"
const suggestionLimit = 8
const holdKey = "posHeldCart"
const sessionCartKey = "posActiveCart"
const toastDuration = 3000

function saveCartToSession() {
  const discountInput = document.getElementById("discountAmount")
  const discount = Number.parseFloat(discountInput?.value) || 0
  const data = {
    cart,
    discount,
    paymentType,
    installmentMonths,
  }
  sessionStorage.setItem(sessionCartKey, JSON.stringify(data))
}

function loadCartFromSession() {
  try {
    const raw = sessionStorage.getItem(sessionCartKey)
    if (!raw) return
    const data = JSON.parse(raw)
    cart = Array.isArray(data.cart) ? data.cart : []
    paymentType = data.paymentType || "Cash"
    installmentMonths = data.installmentMonths || 6
    const discountInput = document.getElementById("discountAmount")
    if (discountInput) {
      discountInput.value = data.discount || ""
    }
  } catch (e) {
    console.error("Could not load cart from session:", e)
    cart = [] // Reset on error
  }
}

const peso = new Intl.NumberFormat("en-PH", {
  style: "currency",
  currency: "PHP",
  minimumFractionDigits: 2,
})

function calculateSubtotal() {
  return cart.reduce((sum, item) => sum + item.subtotal, 0)
}

function formatPeso(value) {
  return peso.format(value || 0)
}

function updateCart() {
  const cartItemsDiv = document.getElementById("cartItems")
  const cartCountSpan = document.getElementById("cartCount")

  if (!cartItemsDiv || !cartCountSpan) return

  if (cart.length === 0) {
    cartItemsDiv.innerHTML = `
      <div class="text-slate-500 text-sm text-center py-8">
        <svg class="w-10 h-10 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
        </svg>
        <p class="font-semibold mb-1">Your cart is empty.</p>
        <p class="text-xs">Add products from the catalog to get started!</p>
      </div>
    `;
    cartCountSpan.textContent = "0";
    updateTotal();
    return;
  }

  cartItemsDiv.innerHTML = cart
    .map(
      (item, index) => `
        <div class="rounded-2xl border border-slate-200 p-4 bg-slate-50/70">
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="text-sm font-semibold text-slate-900">${item.name}</p>
              <p class="text-xs text-slate-500">${formatPeso(item.price)}</p>
            </div>
            <button class="text-slate-400 hover:text-red-600 transition p-1" onclick="removeFromCart(${index})" title="Remove item">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12M10 11v6m4-6v6M9 7l1-3h4l1 3m-7 0h8l-1 12H10z"></path>
              </svg>
            </button>
          </div>
          <div class="mt-3 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2">
              <button class="w-8 h-8 rounded-full border border-slate-200 text-sm font-semibold text-slate-600 hover:border-slate-400" onclick="decreaseQty(${index})">-</button>
              <input type="number" class="w-14 text-center border border-slate-200 rounded-full text-sm h-8" value="${item.quantity}" onchange="setQty(${index}, this.value)">
              <button class="w-8 h-8 rounded-full border border-slate-200 text-sm font-semibold text-slate-600 hover:border-slate-400" onclick="increaseQty(${index})">+</button>
            </div>
            <p class="text-sm font-semibold text-slate-900">${formatPeso(item.subtotal)}</p>
          </div>
        </div>
      `,
    )
    .join("")

  cartCountSpan.textContent = cart.length
  updateTotal()
}

function addToCart(productId, name, price, stock) {
  if (stock <= 0) {
    showToast("Product is out of stock", "error");
    return;
  }

  const existingItem = cart.find((item) => item.product_id === productId);

  if (existingItem) {
    if (existingItem.quantity < stock) {
      existingItem.quantity++;
      existingItem.subtotal = existingItem.quantity * existingItem.price;
      showToast("Product quantity updated in cart!", "success");
    } else {
      showToast("Cannot exceed available stock", "error");
    }
  } else {
    cart.push({
      product_id: productId,
      name,
      price,
      quantity: 1,
      subtotal: price,
    });
    showToast("Product added to cart!", "success");
  }

  updateCart();
  saveCartToSession();
}

function increaseQty(index) {
  cart[index].quantity++
  cart[index].subtotal = cart[index].quantity * cart[index].price
  updateCart()
  saveCartToSession()
}

function decreaseQty(index) {
  if (cart[index].quantity > 1) {
    cart[index].quantity--
    cart[index].subtotal = cart[index].quantity * cart[index].price
    updateCart()
    saveCartToSession()
  }
}

function setQty(index, qty) {
  qty = Number.parseInt(qty, 10)
  if (qty > 0) {
    cart[index].quantity = qty
    cart[index].subtotal = cart[index].quantity * cart[index].price
    updateCart()
    saveCartToSession()
  }
}

function removeFromCart(index) {
  cart.splice(index, 1)
  updateCart()
  saveCartToSession()
}

function clearCart() {
  if (cart.length === 0) return
  if (confirm("Remove all items from the cart?")) {
    cart = []
    updateCart()
    saveCartToSession()
  }
}

function showDiscountError(message) {
  const errorEl = document.getElementById("discountError")
  const input = document.getElementById("discountAmount")
  if (!errorEl || !input) return
  if (message) {
    errorEl.textContent = message
    errorEl.classList.remove("hidden")
    input.classList.add("border-red-500", "ring-1", "ring-red-500/50")
  } else {
    errorEl.classList.add("hidden")
    input.classList.remove("border-red-500", "ring-1", "ring-red-500/50")
  }
}

function updateTotal() {
  const subtotal = calculateSubtotal()
  const discountInput = document.getElementById("discountAmount")
  const discount = Number.parseFloat(discountInput?.value) || 0
  const total = Math.max(subtotal - discount, 0)

  const subtotalEl = document.getElementById("subtotal")
  const totalEl = document.getElementById("total")
  const badgeEl = document.getElementById("discountBadge")
  if (subtotalEl) subtotalEl.textContent = formatPeso(subtotal)
  if (totalEl) totalEl.textContent = formatPeso(total)
  if (badgeEl) {
    if (discount > 0) {
      badgeEl.classList.remove("hidden")
    } else {
      badgeEl.classList.add("hidden")
    }
  }
}

function selectPayment(method) {
  paymentType = method
  const buttons = document.querySelectorAll(".payment-method-btn")
  buttons.forEach((btn) => {
    if (btn.dataset.method === method) {
      btn.classList.add("border-[#D00000]", "bg-red-50", "text-[#D00000]", "shadow-sm")
    } else {
      btn.classList.remove("border-[#D00000]", "bg-red-50", "text-[#D00000]", "shadow-sm")
    }
  })
  const installmentBlock = document.getElementById("installmentConfig")
  if (installmentBlock) {
    installmentBlock.classList.toggle("hidden", method !== "Installment")
  }
  saveCartToSession()
}

function persistCatalogState(category, search) {
  try {
    const state = { category, search }
    localStorage.setItem(stateKey, JSON.stringify(state))
  } catch (e) {
    /* ignore */
  }
}

function loadCatalogState() {
  try {
    const raw = localStorage.getItem(stateKey)
    if (!raw) return null
    return JSON.parse(raw)
  } catch (e) {
    return null
  }
}

function showToast(message, type = "info") {
  const container = document.getElementById("toastContainer")
  if (!container) return
  const el = document.createElement("div")
  const colors =
    type === "success"
      ? "bg-emerald-600 text-white border border-emerald-700"
      : type === "error"
        ? "bg-red-600 text-white border border-red-700"
        : "bg-slate-800 text-white border border-slate-900"
  el.className = `px-4 py-3 rounded-xl shadow-lg text-sm font-semibold ${colors}`
  el.textContent = message
  container.appendChild(el)
  setTimeout(() => {
    el.style.opacity = "0"
    el.style.transition = "opacity 0.3s ease"
    setTimeout(() => el.remove(), 300)
  }, toastDuration)
}

function holdCart() {
  try {
    const discountInput = document.getElementById("discountAmount")
    const discount = Number.parseFloat(discountInput?.value) || 0
    const payload = { cart, discount, paymentType, installmentMonths }
    localStorage.setItem(holdKey, JSON.stringify(payload))
    showToast("Cart held", "success")
  } catch (e) {
    showToast("Unable to hold cart", "error")
  }
}

function restoreCart() {
  try {
    const raw = localStorage.getItem(holdKey)
    if (!raw) {
      showToast("No held cart", "error")
      return
    }
    const data = JSON.parse(raw)
    cart = Array.isArray(data.cart) ? data.cart : []
    paymentType = data.paymentType || "Cash"
    installmentMonths = data.installmentMonths || 6
    const discountInput = document.getElementById("discountAmount")
    if (discountInput) {
      discountInput.value = data.discount || 0
    }
    updateCart()
    selectPayment(paymentType)
    showToast("Cart restored", "success")
  } catch (e) {
    showToast("Unable to restore cart", "error")
  }
}

async function proceedToCheckout(button) {
  const checkoutUrl = button.dataset.checkoutUrl;
  if (!checkoutUrl) {
      showToast("Checkout URL is missing.", "error");
      return;
  }
  if (cart.length === 0) {
    showToast("Cart is empty", "error")
    return
  }

  const discountInput = document.getElementById("discountAmount")
  const discount = Number.parseFloat(discountInput?.value) || 0
  const subtotal = calculateSubtotal()

  if (discount > subtotal) {
    showDiscountError("Discount cannot exceed subtotal.")
    return
  }
  showDiscountError("")

  const payload = {
    cart,
    discount,
    payment_type: paymentType,
    installment_months: paymentType === "Installment" ? installmentMonths : null,
  }

  const submitBtn = document.getElementById("completeSaleBtn")
  const originalHtml = submitBtn?.innerHTML
  if (submitBtn) {
    submitBtn.disabled = true
    submitBtn.innerHTML = `<svg class="animate-spin w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke-width="4"></circle><path class="opacity-75" d="M4 12a8 8 0 018-8" stroke-width="4"/></svg><span>Processing...</span>`
  }

  try {
    const response = await fetch(checkoutUrl, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    })
    const data = await response.json()
    if (!response.ok || !data.success) {
      throw new Error(data.message || "Unable to complete sale.")
    }
    cart = []
    updateCart()
    saveCartToSession()
    showToast("Sale completed successfully!", "success")
    setTimeout(() => {
      window.location.href = data.receipt_url
    }, toastDuration / 2) // Redirect after half the toast duration
  } catch (error) {
    showToast(error.message || "Something went wrong while completing the sale.", "error")
  } finally {
    if (submitBtn) {
      submitBtn.disabled = false
      submitBtn.innerHTML = originalHtml
    }
  }
}

function setupPaymentSelection() {
  const buttons = document.querySelectorAll(".payment-method-btn")
  buttons.forEach((btn) => {
    btn.addEventListener("click", () => selectPayment(btn.dataset.method))
  })
  selectPayment(paymentType)

  const installmentSelect = document.getElementById("installmentMonths")
  if (installmentSelect) {
    installmentMonths = Number.parseInt(installmentSelect.value, 10) || 6
    installmentSelect.addEventListener("change", (event) => {
      installmentMonths = Number.parseInt(event.target.value, 10) || 6
      saveCartToSession()
    })
  }
}

function toggleShortcutsModal() {
  const modal = document.getElementById("shortcutsModal")
  if (modal) {
    modal.classList.toggle("hidden")
  }
}

function setupKeyboardShortcuts() {
  document.addEventListener("keydown", (event) => {
    if (event.key === "F1") {
      event.preventDefault()
      toggleShortcutsModal()
      return
    }
    if (event.key === "F2") {
      event.preventDefault()
      document.getElementById("searchInput")?.focus()
      return
    }
    if (event.key === "F3") {
      event.preventDefault()
      selectPayment("Cash")
      return
    }
    if (event.key === "F4") {
      event.preventDefault()
      selectPayment("GCash")
      return
    }
    if (event.key === "F5") {
      event.preventDefault()
      selectPayment("Installment")
    }
    // Close modal on Escape key
    if (event.key === "Escape") {
        const modal = document.getElementById("shortcutsModal");
        if (modal && !modal.classList.contains("hidden")) {
            toggleShortcutsModal();
        }
    }
  })
  // Close modal on outside click
  const modal = document.getElementById("shortcutsModal");
  if (modal) {
      modal.addEventListener('click', (event) => {
          if (event.target === modal) {
              toggleShortcutsModal();
          }
      });
  }
}

function setupDiscountInput() {
  const discountInput = document.getElementById("discountAmount")
  if (!discountInput) return
  discountInput.addEventListener("input", () => {
    const value = Number.parseFloat(discountInput.value)
    if (Number.isNaN(value) || value < 0) {
      discountInput.value = ""
    }
    showDiscountError("")
    updateTotal()
    saveCartToSession()
  })
}

// Initialize interactions once DOM is ready
document.addEventListener("DOMContentLoaded", () => {
  loadCartFromSession()
  updateCart()
  setupPaymentSelection()
  setupDiscountInput()
  setupKeyboardShortcuts()
})

// Persist state + suggestions
document.addEventListener("DOMContentLoaded", () => {
  const params = new URLSearchParams(window.location.search)
  const initialCategory = params.get("category") || "All"
  const initialSearch = params.get("search") || ""

  persistCatalogState(initialCategory, initialSearch)

  document.querySelectorAll(".category-pill").forEach((pill) => {
    pill.addEventListener("click", () => {
      const cat = pill.dataset.category || "All"
      const searchVal = document.getElementById("searchInput")?.value || ""
      persistCatalogState(cat, searchVal)
    })
  })

  const searchInput = document.getElementById("searchInput")
  const searchBtn = document.getElementById("searchBtn")
  const suggestionBox = document.getElementById("searchSuggestions")
  const suggestionList = suggestionBox?.querySelector("ul")
  const products = Array.isArray(window.POS_PRODUCTS) ? window.POS_PRODUCTS : []

  const hideSuggestions = () => suggestionBox?.classList.add("hidden")

  const showSuggestions = (matches) => {
    if (!suggestionList || !suggestionBox) return
    if (!matches.length) {
      hideSuggestions()
      return
    }
    suggestionList.innerHTML = matches
      .slice(0, suggestionLimit)
      .map(
        (item) =>
          `<li class="px-4 py-2 hover:bg-slate-50 cursor-pointer" data-name="${item.name}">${item.name} <span class="text-xs text-slate-500">(${item.category || "Uncategorized"})</span></li>`,
      )
      .join("")
    suggestionBox.classList.remove("hidden")
  }

  if (suggestionList) {
    suggestionList.addEventListener("click", (e) => {
      const target = e.target.closest("li")
      if (!target) return
      const name = target.getAttribute("data-name") || ""
      if (searchInput) {
        searchInput.value = name
        persistCatalogState(initialCategory, name)
        hideSuggestions()
        document.getElementById("searchBtn")?.click()
      }
    })
  }

  if (searchInput) {
    searchInput.addEventListener("input", () => {
      const term = searchInput.value.trim().toLowerCase()
      if (!term) {
        hideSuggestions()
        return
      }
      const matches = products.filter(p => (p.name && p.name.toLowerCase().includes(term)) || (p.id && p.id.toString().toLowerCase().includes(term)))
      showSuggestions(matches)
    })
    searchInput.addEventListener("focus", () => {
      const term = searchInput.value.trim().toLowerCase()
      if (!term) return
      const matches = products.filter(p => (p.name && p.name.toLowerCase().includes(term)) || (p.id && p.id.toString().toLowerCase().includes(term)))
      showSuggestions(matches)
    })
    searchInput.addEventListener("blur", () => {
      setTimeout(hideSuggestions, 120)
    })
  }

  const applySearch = () => {
    const cat = initialCategory
    const searchVal = searchInput?.value || ""
    persistCatalogState(cat, searchVal)
  }
  if (searchInput) {
    searchInput.addEventListener("change", applySearch)
  }
  if (searchBtn) {
    searchBtn.addEventListener("click", applySearch)
  }
})
