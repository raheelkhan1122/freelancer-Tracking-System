<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tasks · FPTS</title>
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
            <a href="tasks.php" class="active"><span class="icon">☑</span> Tasks</a>
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
                <div><h1>Tasks</h1><p class="subtitle" id="pageSubtitle">Track individual work items per project</p></div>
            </div>

            <div class="toolbar">
                <div class="toolbar-filters">
                    <input type="text" id="searchInput" placeholder="Search by task or project...">
                    <select id="statusFilter">
                        <option value="">All Statuses</option>
                        <option>Pending</option><option>In Progress</option><option>Completed</option>
                    </select>
                </div>
                <button class="btn btn-primary" onclick="openAddModal()">+ Add Task</button>
            </div>

            <div class="table-wrap">
                <table>
                    <thead><tr><th>Task</th><th>Project</th><th>Deadline</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody id="tasksBody"></tbody>
                </table>
            </div>

        </main>
    </div>
</div>

<div class="modal-overlay" id="taskModal">
    <div class="modal">
        <div class="modal-header"><h3 id="modalTitle">Add Task</h3><button class="modal-close" onclick="closeModal('taskModal')">&times;</button></div>
        <form id="taskForm">
            <input type="hidden" id="taskId">
            <div class="form-group"><label>Project *</label><select id="taskProject" required><option value="">Select a project...</option></select><div class="form-error" id="err_taskProject">Please select a project</div></div>
            <div class="form-group"><label>Task Title *</label><input type="text" id="taskTitle" required><div class="form-error" id="err_taskTitle">Task title is required</div></div>
            <div class="form-group"><label>Description</label><textarea id="taskDescription" rows="3"></textarea></div>
            <div class="form-group"><label>Deadline</label><input type="date" id="taskDeadline"></div>
            <div class="form-group"><label>Status *</label><select id="taskStatus"><option>Pending</option><option>In Progress</option><option>Completed</option></select></div>
            <div class="form-actions"><button type="button" class="btn btn-secondary" onclick="closeModal('taskModal')">Cancel</button><button type="submit" class="btn btn-primary">Save Task</button></div>
        </form>
    </div>
</div>

<script src="assets/app.js"></script>
<script>
    let allTasks = [];
    const urlParams = new URLSearchParams(window.location.search);
    const filterProjectId = urlParams.get('project_id');
    let filterProjectName = null;

    document.getElementById('globalSearch').addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && e.target.value.trim() !== '') {
            window.location.href = `clients.php?search=${encodeURIComponent(e.target.value.trim())}`;
        }
    });

    async function loadProjectDropdown() {
        const result = await apiGet('api/tasks.php?dropdown=1');
        if (!result.success) return;
        const select = document.getElementById('taskProject');
        result.data.forEach(p => {
            const opt = document.createElement('option');
            opt.value = p.ProjectID; opt.textContent = p.Title;
            select.appendChild(opt);
            if (filterProjectId && p.ProjectID == filterProjectId) filterProjectName = p.Title;
        });
        if (filterProjectId) {
            document.getElementById('taskProject').value = filterProjectId;
            document.getElementById('pageSubtitle').innerHTML =
                `Showing tasks for <strong>${escapeHtml(filterProjectName || 'this project')}</strong> · <a href="tasks.php" style="color:var(--primary); font-weight:600;">Clear filter</a>`;
        }
    }

    async function loadTasks() {
        const body = document.getElementById('tasksBody');
        body.innerHTML = skeletonRows(5, 5);

        const search = document.getElementById('searchInput').value.trim();
        const status = document.getElementById('statusFilter').value;
        const params = new URLSearchParams();
        if (filterProjectId) params.set('project_id', filterProjectId);
        if (search) params.set('search', search);
        if (status) params.set('status', status);

        const result = await apiGet(`api/tasks.php?${params.toString()}`);
        if (!result.success) { showToast(result.message || 'Failed to load tasks', 'error'); return; }

        allTasks = result.data;
        renderTable(body, allTasks, (t) => `
            <tr>
                <td>${escapeHtml(t.Title)}</td>
                <td>${escapeHtml(t.ProjectTitle)}</td>
                <td>${deadlineCell(t.Deadline, t.IsOverdue)}</td>
                <td>${statusBadge(t.Status)}</td>
                <td class="actions-cell">
                    <button class="btn btn-secondary btn-sm" onclick="openEditModal(${t.TaskID})">Edit</button>
                    <button class="btn btn-danger btn-sm" onclick="deleteTask(${t.TaskID})">Delete</button>
                </td>
            </tr>
        `, 5, '☑️', 'No tasks found.');
    }

    document.getElementById('searchInput').addEventListener('input', debounce(loadTasks, 350));
    document.getElementById('statusFilter').addEventListener('change', loadTasks);

    function openAddModal() {
        document.getElementById('modalTitle').textContent = 'Add Task';
        document.getElementById('taskForm').reset();
        document.getElementById('taskId').value = '';
        if (filterProjectId) document.getElementById('taskProject').value = filterProjectId;
        clearErrors('taskForm');
        openModal('taskModal');
    }
    function openEditModal(id) {
        const t = allTasks.find(x => x.TaskID == id); if (!t) return;
        document.getElementById('modalTitle').textContent = 'Edit Task';
        document.getElementById('taskId').value = t.TaskID;
        document.getElementById('taskProject').value = t.ProjectID;
        document.getElementById('taskTitle').value = t.Title;
        document.getElementById('taskDescription').value = t.Description || '';
        document.getElementById('taskDeadline').value = t.Deadline || '';
        document.getElementById('taskStatus').value = t.Status;
        clearErrors('taskForm');
        openModal('taskModal');
    }
    function clearErrors(formId) { document.querySelectorAll('#' + formId + ' .form-error').forEach(el => el.style.display = 'none'); }

    document.getElementById('taskForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        clearErrors('taskForm');
        const id = document.getElementById('taskId').value;
        const projectId = document.getElementById('taskProject').value;
        const title = document.getElementById('taskTitle').value.trim();

        let hasError = false;
        if (!projectId) { document.getElementById('err_taskProject').style.display = 'block'; hasError = true; }
        if (title === '') { document.getElementById('err_taskTitle').style.display = 'block'; hasError = true; }
        if (hasError) return;

        const payload = {
            ProjectID: projectId, Title: title,
            Description: document.getElementById('taskDescription').value.trim(),
            Deadline: document.getElementById('taskDeadline').value || null,
            Status: document.getElementById('taskStatus').value
        };
        const result = id ? await apiPut(`api/tasks.php?id=${id}`, payload) : await apiPost('api/tasks.php', payload);

        if (result.success) {
            showToast(result.message, 'success');
            closeModal('taskModal');
            loadTasks();
        } else {
            showToast(result.message || 'Something went wrong', 'error');
        }
    });

    function deleteTask(id) {
        handleDelete(`api/tasks.php?id=${id}`, loadTasks, 'Delete this task?');
    }

    loadProjectDropdown().then(loadTasks);
</script>
</body>
</html>
