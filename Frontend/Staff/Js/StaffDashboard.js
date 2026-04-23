
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
const API_INVENTORY = "http://127.0.0.1:8000/api/inventory";
const API_SALES = "http://127.0.0.1:8000/api/sales";

window.onload = () => {
    loadStaffDashboard();
};

async function loadStaffDashboard() {

    const [products, inventory, sales] = await Promise.all([
        fetch(API_PRODUCTS).then(r => r.json()),
        fetch(API_INVENTORY).then(r => r.json()),
        fetch(API_SALES).then(r => r.json())
    ]);

    const p = products.data || products;
    const i = inventory.data || inventory;
    const s = sales.data || sales;

    // ================= PRODUCTS =================
    document.getElementById("staffProducts").innerText = p.length;

    // ================= STOCK =================
    let totalStock = i.reduce((sum, x) => sum + Number(x.stock || 0), 0);
    document.getElementById("staffStock").innerText = totalStock;

   // ================= LOW STOCK =================
let low = i.filter(x => Number(x.stock) <= 30);

document.getElementById("staffLowStock").innerHTML =
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
    // ================= TODAY SALES =================
    let today = new Date().toISOString().split("T")[0];

    let todaySales = s.filter(x => x.sale_date === today);

    let total = todaySales.reduce((a, b) => a + Number(b.total_amount || 0), 0);

    document.getElementById("staffSales").innerText = total;

    // fake profit
    document.getElementById("staffProfit").innerText = (total * 0.2).toFixed(2);

    // ================= RECENT SALES =================
    document.getElementById("staffRecentSales").innerHTML =
        s.slice(0, 5).map(x => `
            <tr>
                <td>${x.id}</td>
                <td>${x.customer?.name || '-'}</td>
                <td>${x.total_amount}</td>
                <td>${x.sale_date}</td>
            </tr>
        `).join("");
}