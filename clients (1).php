<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Clients · FPTS</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="app">

    <aside class="sidebar">
        <div class="brand">Freelance<span>Tracker</span></div>
        <nav>
            <a href="index.php"><span class="icon">◆</span> Dashboard</a>
            <a href="clients.php" class="active"><span class="icon">◇</span> Clients</a>
            <a href="projects.php"><span class="icon">▢</span> Projects</a>
            <a href="tasks.php"><span class="icon">☑</span> Tasks</a>
            <a href="payments.php"><span class="icon">◈</span> Payments</a>
        </nav>
    </aside>

    <div style="flex:1; min-width:0; display:flex; flex-direction:column;">

        <div class="topbar">
            <button class="menu-toggle" id="menuToggle">☰</button>
            <div class="project-name" style="display:flex;">Freelance Tracker</div>
            <div class="search-box">
                <span class="search-icon">⌕</span>
                <input type="text" id="globalSearch" placeholder="Search clients, projects...">
            </div>
            <div class="topbar-date" id="topbarDate"></div>
        </div>

        <main class="main">

            <div class="page-header">
                <div><h1>Clients</h1><p class="subtitle">Manage your client contact list</p></div>
            </div>

            <div class="toolbar">
                <div class="toolbar-filters">
                    <input type="text" id="searchInput" placeholder="Search by name or email...">
                </div>
                <button class="btn btn-primary" onclick="openAddModal()">+ Add Client</button>
            </div>

            <div class="table-wrap" id="tableWrap">
                <table>
                    <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Joined</th><th>Actions</th></tr></thead>
                    <tbody id="clientsBody"></tbody>
                </table>
                <div class="pagination-bar" id="paginationBar"></div>
            </div>

        </main>
    </div>
</div>

<div class="modal-overlay" id="clientModal">
    <div class="modal">
        <div class="modal-header"><h3 id="modalTitle">Add Client</h3><button class="modal-close" onclick="closeModal('clientModal')">&times;</button></div>
        <form id="clientForm">
            <input type="hidden" id="clientId">
            <div class="form-group"><label>Full Name *</label><input type="text" id="clientName" required><div class="form-error" id="err_clientName">Name is required</div></div>
            <div class="form-group"><label>Email *</label><input type="email" id="clientEmail" required><div class="form-error" id="err_clientEmail">A valid, unique email is required</div></div>
            <div class="form-group"><label>Phone</label><input type="text" id="clientPhone" placeholder="e.g. +92-300-1234567"></div>
            <div class="form-actions"><button type="button" class="btn btn-secondary" onclick="closeModal('clientModal')">Cancel</button><button type="submit" class="btn btn-primary">Save Client</button></div>
        </form>
    </div>
</div>

<script src="assets/app.js"></script>
<script>
    let currentPage = 1;
    const limit = 8;
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('search')) document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('searchInput').value = urlParams.get('search');
    });

    document.getElementById('globalSearch').addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && e.target.value.trim() !== '') {
            document.getElementById('searchInput').value = e.target.value.trim();
            currentPage = 1;
            loadClients();
        }
    });

    async function loadClients() {
        const tableWrap = document.getElementById('tableWrap');
        const body = document.getElementById('clientsBody');
        body.innerHTML = skeletonRows(5, 4);

        const search = document.getElementById('searchInput').value.trim();
        const params = new URLSearchParams({ page: currentPage, limit });
        if (search) params.set('search', search);

        const result = await apiGet(`api/clients.php?${params.toString()}`);
        if (!result.success) { showToast(result.message || 'Failed to load clients', 'error'); return; }

        const { records, pagination } = result.data;
        renderTable(body, records, (c) => `
            <tr>
                <td>${escapeHtml(c.ClientName)}</td>
                <td>${escapeHtml(c.Email)}</td>
                <td>${escapeHtml(c.Phone) || '—'}</td>
                <td>${formatDate(c.CreatedDate)}</td>
                <td class="actions-cell">
                    <button class="btn btn-secondary btn-sm" onclick="openEditModal(${c.ClientID})">Edit</button>
                    <button class="btn btn-danger btn-sm" onclick="deleteClient(${c.ClientID})">Delete</button>
                </td>
            </tr>
        `, 5, '👤', 'No clients found. Click "Add Client" to create one.');

        renderPagination(document.getElementById('paginationBar'), pagination, (p) => { currentPage = p; loadClients(); });
    }

    document.getElementById('searchInput').addEventListener('input', debounce(() => { currentPage = 1; loadClients(); }, 350));

    let cachedClients = {};
    function openAddModal() {
        document.getElementById('modalTitle').textContent = 'Add Client';
        document.getElementById('clientForm').reset();
        document.getElementById('clientId').value = '';
        clearErrors('clientForm');
        openModal('clientModal');
    }
    async function openEditModal(id) {
        const result = await apiGet(`api/clients.php?id=${id}`);
        if (!result.success) { showToast(result.message, 'error'); return; }
        const c = result.data;
        document.getElementById('modalTitle').textContent = 'Edit Client';
        document.getElementById('clientId').value = c.ClientID;
        document.getElementById('clientName').value = c.ClientName;
        document.getElementById('clientEmail').value = c.Email;
        document.getElementById('clientPhone').value = c.Phone || '';
        clearErrors('clientForm');
        openModal('clientModal');
    }
    function clearErrors(formId) { document.querySelectorAll('#' + formId + ' .form-error').forEach(el => el.style.display = 'none'); }

    document.getElementById('clientForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        clearErrors('clientForm');
        const id = document.getElementById('clientId').value;
        const name = document.getElementById('clientName').value.trim();
        const email = document.getElementById('clientEmail').value.trim();
        const phone = document.getElementById('clientPhone').value.trim();
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        let hasError = false;
        if (name === '') { document.getElementById('err_clientName').style.display = 'block'; hasError = true; }
        if (email === '' || !emailPattern.test(email)) { document.getElementById('err_clientEmail').style.display = 'block'; hasError = true; }
        if (hasError) return;

        const payload = { ClientName: name, Email: email, Phone: phone };
        const result = id ? await apiPut(`api/clients.php?id=${id}`, payload) : await apiPost('api/clients.php', payload);

        if (result.success) {
            showToast(result.message, 'success');
            closeModal('clientModal');
            loadClients();
        } else {
            if (result.message.toLowerCase().includes('email')) {
                document.getElementById('err_clientEmail').textContent = result.message;
                document.getElementById('err_clientEmail').style.display = 'block';
            } else {
                showToast(result.message || 'Something went wrong', 'error');
            }
        }
    });

    function deleteClient(id) {
        handleDelete(`api/clients.php?id=${id}`, loadClients, 'Delete this client? This will also delete all of their projects, tasks, and payments.');
    }

    loadClients();
</script>
</body>
</html>
