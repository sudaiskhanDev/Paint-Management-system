// addStaff.js - front-end folder ke liye

// Add Staff
document.getElementById('addStaffForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();
    const role = document.getElementById('role').value;

    const messageDiv = document.getElementById('message');
    messageDiv.textContent = '';

    if (!name || !email || !password) {
        messageDiv.style.color = 'red';
        messageDiv.textContent = 'All fields are required.';
        return;
    }

    try {
        const response = await fetch('http://127.0.0.1:8000/api/user/register', { // Fixed API URL
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + localStorage.getItem('admin_token') // Admin token localStorage se
            },
            body: JSON.stringify({ name, email, password, role })
        });

        const data = await response.json();

        if (data.success) {
            messageDiv.style.color = 'green';
            messageDiv.textContent = 'Staff added successfully!';
            document.getElementById('addStaffForm').reset();
        } else {
            messageDiv.style.color = 'red';
            if (data.errors) {
                messageDiv.textContent = Object.values(data.errors).flat().join(', ');
            } else {
                messageDiv.textContent = data.message || 'Error adding staff.';
            }
        }

    } catch (error) {
        messageDiv.style.color = 'red';
        messageDiv.textContent = 'Network error. Try again.';
        console.error(error);
    }
});











const apiUrl = 'http://localhost:8000/api/users';
const token = localStorage.getItem('token'); // JWT token

// Render table inside .satff-list-manage
function renderTable(users) {
    const container = document.querySelector('.satff-list-manage');
    container.innerHTML = ''; // clear previous

    const table = document.createElement('table');
    table.style.width = '80%';
    table.style.margin = '20px auto';
    table.style.borderCollapse = 'collapse';

    table.innerHTML = `
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        ${users.map(user => `
            <tr>
                <td>${user.id}</td>
                <td>${user.name}</td>
                <td>${user.email}</td>
                <td>${user.role}</td>
                <td>
                    <button onclick="editUser(${user.id})">Edit</button>
                    <button onclick="deleteUser(${user.id})">Delete</button>
                </td>
            </tr>
        `).join('')}
    </tbody>
    `;

    container.appendChild(table);
}


