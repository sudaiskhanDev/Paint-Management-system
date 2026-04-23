
// -------- LOGOUT FUNCTION --------
document.getElementById('logoutBtn')?.addEventListener('click', async function() {
    const token = localStorage.getItem('token');
    if (!token) {
        window.location.href = '../../login.html';
        return;
    }

    try {
        await fetch('http://127.0.0.1:8000/api/user/logout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            }
        });
    } catch(err) {
        console.error('Logout failed', err);
    } finally {
        localStorage.removeItem('token');
        window.location.href = '../../login.html';
    }
});

// -------- DASHBOARD TOKEN CHECK --------
if (document.getElementById('logoutBtn')) {
    const token = localStorage.getItem('token');
    if (!token) {
        window.location.href = '../../login.html';
    }
}























const API_PRODUCTS = "http://127.0.0.1:8000/api/products";
const API_CUSTOMERS = "http://127.0.0.1:8000/api/customers";
const API_SUPPLIERS = "http://127.0.0.1:8000/api/suppliers";
const API_INVENTORY = "http://127.0.0.1:8000/api/inventory";
const API_SALES = "http://127.0.0.1:8000/api/sales";

window.onload = () => {
    loadDashboard();
};

async function loadDashboard() {

    const [products, customers, suppliers, inventory, sales] = await Promise.all([
        fetch(API_PRODUCTS).then(r => r.json()),
        fetch(API_CUSTOMERS).then(r => r.json()),
        fetch(API_SUPPLIERS).then(r => r.json()),
        fetch(API_INVENTORY).then(r => r.json()),
        fetch(API_SALES).then(r => r.json())
    ]);

    const p = products.data || products;
    const c = customers.data || customers;
    const s = suppliers.data || suppliers;
    const i = inventory.data || inventory;
    const sl = sales.data || sales;

    // ================= KPI COUNTS =================
    document.getElementById("kpiProducts").innerText = p.length;
    document.getElementById("kpiCustomers").innerText = c.length;
    document.getElementById("kpiSuppliers").innerText = s.length;

    // STOCK TOTAL
    let totalStock = i.reduce((sum, item) => sum + Number(item.stock || 0), 0);
    document.getElementById("kpiStock").innerText = totalStock;

    // LOW STOCK
    let low = i.filter(x => x.stock < 30);

    document.getElementById("lowStockTable").innerHTML =
    low.length
        ? low.map(x => {

            let image = x.image
                ? `http://127.0.0.1:8000/storage/${x.image}`
                : "https://via.placeholder.com/50";

            return `
                <tr>
                    <td>
                        <img src="${image}" style="
                            width:45px;
                            height:45px;
                            object-fit:cover;
                            border-radius:8px;
                            border:1px solid #ddd;
                        ">
                    </td>

                    <td>${x.product_name}</td>

                    <td style="
                        font-weight:bold;
                        color:${Number(x.stock) === 0 ? 'red' : '#e67e22'}
                    ">
                        ${Number(x.stock) === 0 ? '❌ Out (0)' : `⚠ ${x.stock}`}
                    </td>
                </tr>
            `;
        }).join("")
        : `
            <tr>
                <td colspan="3" style="text-align:center;padding:10px;color:green;">
                    ✅ No Low Stock Items
                </td>
            </tr>
        `;

    // ================= SALES TODAY =================
    let today = new Date().toISOString().split("T")[0];

    let todaySales = sl.filter(x => x.sale_date === today);

    let totalTodaySales = todaySales.reduce((sum, s) => sum + Number(s.total_amount || 0), 0);

    document.getElementById("todaySales").innerText = totalTodaySales;

    // fake profit logic (adjust later with real DB column)
    document.getElementById("todayProfit").innerText = (totalTodaySales * 0.2).toFixed(2);

    // ================= RECENT SALES =================
    document.getElementById("recentSales").innerHTML =
        sl.slice(0, 5).map(x => `
            <tr>
                <td>${x.id}</td>
                <td>${x.customer?.name || '-'}</td>
                <td>${x.total_amount}</td>
                <td>${x.sale_date}</td>
            </tr>
        `).join("");
}

