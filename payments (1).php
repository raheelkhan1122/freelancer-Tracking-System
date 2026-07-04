<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payments · FPTS</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="app">

    <aside class="sidebar">
        <div class="brand">Freelance<span>Tracker</span></div>
        <nav>
            <a href="index.php"><span class="icon">◆</span> Dashboard</a>
            <a href="clients.php"><span class="icon">◇</span> Clients</a>
            <a href="projects.php"><span class="icon">▢</span> Projects</a>
            <a href="tasks.php"><span class="icon">☑</span> Tasks</a>
            <a href="payments.php" class="active"><span class="icon">◈</span> Payments</a>
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
                <div><h1>Payments</h1><p class="subtitle" id="pageSubtitle">Track invoices and incoming payments</p></div>
            </div>

            <div class="stats-grid" style="grid-template-columns:repeat(3,1fr); margin-bottom:20px;">
                <div class="stat-card"><div class="label">Total Cleared</div><div class="value" id="statCleared">—</div></div>
                <div class="stat-card"><div class="label">Total Pending</div><div class="value" id="statPending">—</div></div>
                <div class="stat-card"><div class="label">Total Failed / Refunded</div><div class="value" id="statFailed">—</div></div>
            </div>

            <div class="toolbar">
                <div class="toolbar-filters">
                    <input type="text" id="searchInput" placeholder="Search by project...">
                    <select id="statusFilter">
                        <option value="">All Statuses</option>
                        <option>Pending</option><option>Cleared</option><option>Failed</option><option>Refunded</option>
                    </select>
                    <select id="methodFilter">
                        <option value="">All Methods</option>
                        <option>PayPal</option><option>Stripe</option><option>Bank Transfer</option><option>Crypto</option><option>Cash</option>
                    </select>
                </div>
                <button class="btn btn-primary" onclick="openAddModal()">+ Add Payment</button>
            </div>

            <div class="table-wrap">
                <table>
                    <thead><tr><th>Project</th><th>Amount</th><th>Method</th><th>Status</th><th>Payment Date</th><th>Actions</th></tr></thead>
                    <tbody id="paymentsBody"></tbody>
                </table>
            </div>

        </main>
    </div>
</div>

<div class="modal-overlay" id="paymentModal">
    <div class="modal">
        <div class="modal-header"><h3 id="modalTitle">Add Payment</h3><button class="modal-close" onclick="closeModal('paymentModal')">&times;</button></div>
        <form id="paymentForm">
            <input type="hidden" id="paymentId">
            <div class="form-group"><label>Project *</label><select id="paymentProject" required><option value="">Select a project...</option></select><div class="form-error" id="err_paymentProject">Please select a project</div></div>
            <div class="form-group"><label>Amount ($) *</label><input type="number" id="paymentAmount" min="0.01" step="0.01" required><div class="form-error" id="err_paymentAmount">Amount must be greater than zero</div></div>
            <div class="form-group"><label>Method *</label><select id="paymentMethod"><option>PayPal</option><option>Stripe</option><option>Bank Transfer</option><option>Crypto</option><option>Cash</option></select></div>
            <div class="form-group"><label>Status *</label><select id="paymentStatus"><option>Pending</option><option>Cleared</option><option>Failed</option><option>Refunded</option></select></div>
            <div class="form-group"><label>Payment Date</label><input type="date" id="paymentDate"></div>
            <div class="form-actions"><button type="button" class="btn btn-secondary" onclick="closeModal('paymentModal')">Cancel</button><button type="submit" class="btn btn-primary">Save Payment</button></div>
        </form>
    </div>
</div>

<script src="assets/app.js"></script>
<script>
    let allPayments = [];
    const urlParams = new URLSearchParams(window.location.search);
    const filterProjectId = urlParams.get('project_id');
    let filterProjectName = null;

    document.getElementById('globalSearch').addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && e.target.value.trim() !== '') {
            window.location.href = `clients.php?search=${encodeURIComponent(e.target.value.trim())}`;
        }
    });

    async function loadProjectDropdown() {
        const result = await apiGet('api/payments.php?dropdown=1');
        if (!result.success) return;
        const select = document.getElementById('paymentProject');
        result.data.forEach(p => {
            const opt = document.createElement('option');
            opt.value = p.ProjectID; opt.textContent = p.Title;
            select.appendChild(opt);
            if (filterProjectId && p.ProjectID == filterProjectId) filterProjectName = p.Title;
        });
        if (filterProjectId) {
            document.getElementById('paymentProject').value = filterProjectId;
            document.getElementById('pageSubtitle').innerHTML =
                `Showing payments for <strong>${escapeHtml(filterProjectName || 'this project')}</strong> · <a href="payments.php" style="color:var(--primary); font-weight:600;">Clear filter</a>`;
        }
    }

    function updateStatCards() {
        const cleared = allPayments.filter(p => p.Status === 'Cleared').reduce((s, p) => s + parseFloat(p.Amount), 0);
        const pending = allPayments.filter(p => p.Status === 'Pending').reduce((s, p) => s + parseFloat(p.Amount), 0);
        const failed = allPayments.filter(p => p.Status === 'Failed' || p.Status === 'Refunded').reduce((s, p) => s + parseFloat(p.Amount), 0);
        document.getElementById('statCleared').textContent = formatMoney(cleared);
        document.getElementById('statPending').textContent = formatMoney(pending);
        document.getElementById('statFailed').textContent = formatMoney(failed);
    }

    async function loadPayments() {
        const body = document.getElementById('paymentsBody');
        body.innerHTML = skeletonRows(5, 6);

        const search = document.getElementById('searchInput').value.trim();
        const status = document.getElementById('statusFilter').value;
        const method = document.getElementById('methodFilter').value;
        const params = new URLSearchParams();
        if (filterProjectId) params.set('project_id', filterProjectId);
        if (search) params.set('search', search);
        if (status) params.set('status', status);
        if (method) params.set('method', method);

        const result = await apiGet(`api/payments.php?${params.toString()}`);
        if (!result.success) { showToast(result.message || 'Failed to load payments', 'error'); return; }

        allPayments = result.data;
        updateStatCards();
        renderTable(body, allPayments, (p) => `
            <tr>
                <td>${escapeHtml(p.ProjectTitle)}</td>
                <td>${formatMoney(p.Amount)}</td>
                <td>${escapeHtml(p.Method)}</td>
                <td>${statusBadge(p.Status)}</td>
                <td>${formatDate(p.PaymentDate)}</td>
                <td class="actions-cell">
                    <button class="btn btn-secondary btn-sm" onclick="openEditModal(${p.PaymentID})">Edit</button>
                    <button class="btn btn-danger btn-sm" onclick="deletePayment(${p.PaymentID})">Delete</button>
                </td>
            </tr>
        `, 6, '💳', 'No payments found.');
    }

    document.getElementById('searchInput').addEventListener('input', debounce(loadPayments, 350));
    document.getElementById('statusFilter').addEventListener('change', loadPayments);
    document.getElementById('methodFilter').addEventListener('change', loadPayments);

    function openAddModal() {
        document.getElementById('modalTitle').textContent = 'Add Payment';
        document.getElementById('paymentForm').reset();
        document.getElementById('paymentId').value = '';
        if (filterProjectId) document.getElementById('paymentProject').value = filterProjectId;
        clearErrors('paymentForm');
        openModal('paymentModal');
    }
    function openEditModal(id) {
        const p = allPayments.find(x => x.PaymentID == id); if (!p) return;
        document.getElementById('modalTitle').textContent = 'Edit Payment';
        document.getElementById('paymentId').value = p.PaymentID;
        document.getElementById('paymentProject').value = p.ProjectID;
        document.getElementById('paymentAmount').value = p.Amount;
        document.getElementById('paymentMethod').value = p.Method;
        document.getElementById('paymentStatus').value = p.Status;
        document.getElementById('paymentDate').value = p.PaymentDate || '';
        clearErrors('paymentForm');
        openModal('paymentModal');
    }
    function clearErrors(formId) { document.querySelectorAll('#' + formId + ' .form-error').forEach(el => el.style.display = 'none'); }

    document.getElementById('paymentForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        clearErrors('paymentForm');
        const id = document.getElementById('paymentId').value;
        const projectId = document.getElementById('paymentProject').value;
        const amount = parseFloat(document.getElementById('paymentAmount').value);

        let hasError = false;
        if (!projectId) { document.getElementById('err_paymentProject').style.display = 'block'; hasError = true; }
        if (isNaN(amount) || amount <= 0) { document.getElementById('err_paymentAmount').style.display = 'block'; hasError = true; }
        if (hasError) return;

        const payload = {
            ProjectID: projectId, Amount: amount,
            Method: document.getElementById('paymentMethod').value,
            Status: document.getElementById('paymentStatus').value,
            PaymentDate: document.getElementById('paymentDate').value || null
        };
        const result = id ? await apiPut(`api/payments.php?id=${id}`, payload) : await apiPost('api/payments.php', payload);

        if (result.success) {
            showToast(result.message, 'success');
            closeModal('paymentModal');
            loadPayments();
        } else {
            showToast(result.message || 'Something went wrong', 'error');
        }
    });

    function deletePayment(id) {
        handleDelete(`api/payments.php?id=${id}`, loadPayments, 'Delete this payment record?');
    }

    loadProjectDropdown().then(loadPayments);
</script>
</body>
</html>
