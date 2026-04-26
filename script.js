const products = [
    { id: "milk", name: "Fresh Milk", unit: "1 liter", price: 60, stock: 120, reorderLevel: 30 },
    { id: "curd", name: "Curd", unit: "500 gram", price: 45, stock: 80, reorderLevel: 25 },
    { id: "paneer", name: "Paneer", unit: "250 gram", price: 110, stock: 35, reorderLevel: 20 },
    { id: "butter", name: "Butter", unit: "200 gram", price: 95, stock: 22, reorderLevel: 18 },
    { id: "ghee", name: "Ghee", unit: "500 ml", price: 320, stock: 18, reorderLevel: 15 },
    { id: "lassi", name: "Lassi", unit: "1 bottle", price: 35, stock: 60, reorderLevel: 20 }
];

const productGrid = document.getElementById("productGrid");
const inventoryTable = document.getElementById("inventoryTable");
const quantityList = document.getElementById("quantityList");
const billItems = document.getElementById("billItems");
const subtotalElement = document.getElementById("subtotal");
const deliveryChargeElement = document.getElementById("deliveryCharge");
const grandTotalElement = document.getElementById("grandTotal");
const orderForm = document.getElementById("orderForm");
const orderHistory = document.getElementById("orderHistory");
const clearOrdersButton = document.getElementById("clearOrders");

const currency = new Intl.NumberFormat("en-IN", {
    style: "currency",
    currency: "INR",
    maximumFractionDigits: 0
});

function formatPrice(amount) {
    return currency.format(amount).replace("₹", "Rs. ");
}

function getOrders() {
    return JSON.parse(localStorage.getItem("dairyOrders")) || [];
}

function saveOrders(orders) {
    localStorage.setItem("dairyOrders", JSON.stringify(orders));
}

function getStockStatus(product) {
    return product.stock <= product.reorderLevel ? "Low Stock" : "Available";
}

function renderProducts() {
    productGrid.innerHTML = products.map((product) => {
        const status = getStockStatus(product);
        const badgeClass = status === "Available" ? "available" : "low";

        return `
            <article class="product-card">
                <span class="badge ${badgeClass}">${status}</span>
                <h3>${product.name}</h3>
                <p class="meta">${product.unit} | Stock: ${product.stock}</p>
                <p class="price">${formatPrice(product.price)}</p>
            </article>
        `;
    }).join("");
}

function renderInventory() {
    inventoryTable.innerHTML = products.map((product) => {
        const status = getStockStatus(product);
        const badgeClass = status === "Available" ? "available" : "low";

        return `
            <tr>
                <td>${product.name}</td>
                <td>${product.unit}</td>
                <td>${formatPrice(product.price)}</td>
                <td>${product.stock}</td>
                <td><span class="badge ${badgeClass}">${status}</span></td>
            </tr>
        `;
    }).join("");
}

function renderQuantityInputs() {
    quantityList.innerHTML = products.map((product) => `
        <label class="quantity-row" for="${product.id}">
            <div>
                ${product.name}
                <span>${product.unit} | ${formatPrice(product.price)} | Available: ${product.stock}</span>
            </div>
            <input id="${product.id}" name="${product.id}" type="number" min="0" max="${product.stock}" value="0">
        </label>
    `).join("");
}

function getSelectedItems() {
    return products
        .map((product) => {
            const quantity = Number(document.getElementById(product.id).value);
            return {
                ...product,
                quantity,
                total: quantity * product.price
            };
        })
        .filter((item) => item.quantity > 0);
}

function updateBill() {
    const selectedItems = getSelectedItems();
    const subtotal = selectedItems.reduce((sum, item) => sum + item.total, 0);
    const deliveryCharge = subtotal > 0 && subtotal < 500 ? 40 : 0;
    const grandTotal = subtotal + deliveryCharge;

    if (selectedItems.length === 0) {
        billItems.innerHTML = '<p class="empty-text">Select product quantities to generate bill.</p>';
    } else {
        billItems.innerHTML = selectedItems.map((item) => `
            <div class="bill-item">
                <span>${item.name} x ${item.quantity}</span>
                <strong>${formatPrice(item.total)}</strong>
            </div>
        `).join("");
    }

    subtotalElement.textContent = formatPrice(subtotal);
    deliveryChargeElement.textContent = formatPrice(deliveryCharge);
    grandTotalElement.textContent = formatPrice(grandTotal);
}

function renderDashboardSummary() {
    const orders = getOrders();
    const stockValue = products.reduce((sum, product) => sum + product.price * product.stock, 0);

    document.getElementById("productCount").textContent = products.length;
    document.getElementById("stockValue").textContent = formatPrice(stockValue);
    document.getElementById("orderCount").textContent = orders.length;
}

function renderOrders() {
    const orders = getOrders();

    if (orders.length === 0) {
        orderHistory.innerHTML = '<p class="empty-text">No orders saved yet.</p>';
        return;
    }

    orderHistory.innerHTML = orders.map((order) => `
        <article class="order-item">
            <header>
                <div>
                    <h3>${order.id}</h3>
                    <p>${order.customerName} | ${order.customerPhone}</p>
                </div>
                <strong>${formatPrice(order.grandTotal)}</strong>
            </header>
            <p>${order.customerAddress}</p>
            <p>${order.items.map((item) => `${item.name} x ${item.quantity}`).join(", ")}</p>
            <p>Order Date: ${order.date}</p>
        </article>
    `).join("");
}

function validatePhone(phone) {
    return /^[6-9]\d{9}$/.test(phone);
}

orderForm.addEventListener("input", updateBill);

orderForm.addEventListener("submit", (event) => {
    event.preventDefault();

    const customerName = document.getElementById("customerName").value.trim();
    const customerPhone = document.getElementById("customerPhone").value.trim();
    const customerAddress = document.getElementById("customerAddress").value.trim();
    const items = getSelectedItems();

    if (!validatePhone(customerPhone)) {
        alert("Please enter a valid 10 digit Indian mobile number.");
        return;
    }

    if (items.length === 0) {
        alert("Please select at least one product quantity.");
        return;
    }

    const subtotal = items.reduce((sum, item) => sum + item.total, 0);
    const deliveryCharge = subtotal < 500 ? 40 : 0;
    const order = {
        id: `ORD-${Date.now()}`,
        customerName,
        customerPhone,
        customerAddress,
        items,
        subtotal,
        deliveryCharge,
        grandTotal: subtotal + deliveryCharge,
        date: new Date().toLocaleString("en-IN")
    };

    const orders = getOrders();
    orders.unshift(order);
    saveOrders(orders);

    orderForm.reset();
    updateBill();
    renderOrders();
    renderDashboardSummary();
    location.hash = "#orders";
});

clearOrdersButton.addEventListener("click", () => {
    if (confirm("Do you want to clear all saved orders?")) {
        saveOrders([]);
        renderOrders();
        renderDashboardSummary();
    }
});

renderProducts();
renderInventory();
renderQuantityInputs();
updateBill();
renderOrders();
renderDashboardSummary();
