<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Projects · FPTS</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="app">

    <aside class="sidebar">
        <div class="brand">Freelance<span>Tracker</span></div>
        <nav>
            <a href="index.php"><span class="icon">◆</span> Dashboard</a>
            <a href="clients.php"><span class="icon">◇</span> Clients</a>
            <a href="projects.php" class="active"><span class="icon">▢</span> Projects</a>
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
                <div><h1>Projects</h1><p class="subtitle">Track every project from pending to completed</p></div>
            </div>

            <div class="toolbar">
                <div class="toolbar-filters">
                    <input type="text" id="searchInput" placeholder="Search by title or client...">
                    <select id="statusFilter">
                        <option value="">All Statuses</option>
                        <option>Pending</option><option>In Progress</option><option>Completed</option><option>Cancelled</option>
                    </select>
                    <select id="sortSelect">
                        <option value="newest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                        <option value="title_asc">Title A–Z</option>
                        <option value="title_desc">Title Z–A</option>
                        <option value="deadline_asc">Deadline (Soonest)</option>
                        <option value="deadline_desc">Deadline (Latest)</option>
                    </select>
                </div>
                <button class="btn btn-primary" onclick="openAddModal()">+ Add Project</button>
            </div>

            <div class="table-wrap">
                <table>
                    <thead><tr><th>Title</th><th>Client</th><th>Deadline</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody id="projectsBody"></tbody>
                </table>
            </div>

        </main>
    </div>
</div>

<div class="modal-overlay" id="projectModal">
    <div class="modal">
        <div class="modal-header"><h3 id="modalTitle">Add Project</h3><button class="modal-close" onclick="closeModal('projectModal')">&times;</button></div>
        <form id="projectForm">
            <input type="hidden" id="projectId">
            <div class="form-group"><label>Client *</label><select id="projectClient" required><option value="">Select a client...</option></select><div class="form-error" id="err_projectClient">Please select a client</div></div>
            <div class="form-group"><label>Project Title *</label><input type="text" id="projectTitle" required><div class="form-error" id="err_projectTitle">Title is required</div></div>
            <div class="form-group"><label>Description</label><textarea id="projectDescription" rows="3"></textarea></div>
            <div class="form-group"><label>Deadline</label><input type="date" id="projectDeadline"></div>
            <div class="form-group"><label>Status *</label><select id="projectStatus"><option>Pending</option><option>In Progress</option><option>Completed</option><option>Cancelled</option></select></div>
            <div class="form-actions"><button type="button" class="btn btn-secondary" onclick="closeModal('projectModal')">Cancel</button><button type="submit" class="btn btn-primary">Save Project</button></div>
        </form>
    </div>
</div>

<script src="assets/app.js"></script>
<script>
    let allProjects = [];

    document.getElementById('globalSearch').addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && e.target.value.trim() !== '') {
            window.location.href = `clients.php?search=${encodeURIComponent(e.target.value.trim())}`;
        }
    });

    async function loadClientDropdown() {
        const result = await apiGet('api/projects.php?dropdown=1');
        if (!result.success) return;
        const select = document.getElementById('projectClient');
        result.data.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.ClientID; opt.textContent = c.ClientName;
            select.appendChild(opt);
        });
    }

    async function loadProjects() {
        const body = document.getElementById('projectsBody');
        body.innerHTML = skeletonRows(5, 5);

        const search = document.getElementById('searchInput').value.trim();
        const status = document.getElementById('statusFilter').value;
        const sort = document.getElementById('sortSelect').value;
        const params = new URLSearchParams({ sort });
        if (search) params.set('search', search);
        if (status) params.set('status', status);

        const result = await apiGet(`api/projects.php?${params.toString()}`);
        if (!result.success) { showToast(result.message || 'Failed to load projects', 'error'); return; }

        allProjects = result.data;
        renderTable(body, allProjects, (p) => `
            <tr>
                <td>${escapeHtml(p.Title)}</td>
                <td>${escapeHtml(p.ClientName)}</td>
                <td>${formatDate(p.Deadline)}</td>
                <td>${statusBadge(p.Status)}</td>
                <td class="actions-cell">
                    <button class="btn btn-secondary btn-sm" onclick="viewTasks(${p.ProjectID})">Tasks</button>
                    <button class="btn btn-secondary btn-sm" onclick="viewPayments(${p.ProjectID})">Payments</button>
                    <button class="btn btn-secondary btn-sm" onclick="openEditModal(${p.ProjectID})">Edit</button>
                    <button class="btn btn-danger btn-sm" onclick="deleteProject(${p.ProjectID})">Delete</button>
                </td>
            </tr>
        `, 5, '📁', 'No projects found.');
    }

    function viewTasks(id) { window.location.href = `tasks.php?project_id=${id}`; }
    function viewPayments(id) { window.location.href = `payments.php?project_id=${id}`; }

    document.getElementById('searchInput').addEventListener('input', debounce(loadProjects, 350));
    document.getElementById('statusFilter').addEventListener('change', loadProjects);
    document.getElementById('sortSelect').addEventListener('change', loadProjects);

    function openAddModal() {
        document.getElementById('modalTitle').textContent = 'Add Project';
        document.getElementById('projectForm').reset();
        document.getElementById('projectId').value = '';
        clearErrors('projectForm');
        openModal('projectModal');
    }
    function openEditModal(id) {
        const p = allProjects.find(x => x.ProjectID == id); if (!p) return;
        document.getElementById('modalTitle').textContent = 'Edit Project';
        document.getElementById('projectId').value = p.ProjectID;
        document.getElementById('projectClient').value = p.ClientID;
        document.getElementById('projectTitle').value = p.Title;
        document.getElementById('projectDescription').value = p.Description || '';
        document.getElementById('projectDeadline').value = p.Deadline || '';
        document.getElementById('projectStatus').value = p.Status;
        clearErrors('projectForm');
        openModal('projectModal');
    }
    function clearErrors(formId) { document.querySelectorAll('#' + formId + ' .form-error').forEach(el => el.style.display = 'none'); }

    document.getElementById('projectForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        clearErrors('projectForm');
        const id = document.getElementById('projectId').value;
        const clientId = document.getElementById('projectClient').value;
        const title = document.getElementById('projectTitle').value.trim();

        let hasError = false;
        if (!clientId) { document.getElementById('err_projectClient').style.display = 'block'; hasError = true; }
        if (title === '') { document.getElementById('err_projectTitle').style.display = 'block'; hasError = true; }
        if (hasError) return;

        const payload = {
            ClientID: clientId, Title: title,
            Description: document.getElementById('projectDescription').value.trim(),
            Deadline: document.getElementById('projectDeadline').value || null,
            Status: document.getElementById('projectStatus').value
        };
        const result = id ? await apiPut(`api/projects.php?id=${id}`, payload) : await apiPost('api/projects.php', payload);

        if (result.success) {
            showToast(result.message, 'success');
            closeModal('projectModal');
            loadProjects();
        } else {
            showToast(result.message || 'Something went wrong', 'error');
        }
    });

    function deleteProject(id) {
        handleDelete(`api/projects.php?id=${id}`, loadProjects, 'Delete this project? This will also delete its tasks and payments.');
    }

    loadClientDropdown();
    loadProjects();
</script>
</body>
</html>
