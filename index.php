<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard · FPTS</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="app">

    <aside class="sidebar">
        <div class="brand">Freelance<span>Tracker</span></div>
        <nav>
            <a href="index.php" class="active"><span class="icon">◆</span> Dashboard</a>
            <a href="clients.php"><span class="icon">◇</span> Clients</a>
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
                <div>
                    <h1>Dashboard</h1>
                    <p class="subtitle">A quick overview of your freelance business</p>
                </div>
            </div>

            <!-- Stat cards -->
            <div class="stats-grid" id="statsGrid">
                <div class="stat-card"><div class="stat-icon">◇</div><div class="label">Total Clients</div><div class="value" id="statClients">—</div></div>
                <div class="stat-card"><div class="stat-icon">▢</div><div class="label">Total Projects</div><div class="value" id="statProjects">—</div></div>
                <div class="stat-card"><div class="stat-icon">☑</div><div class="label">Total Tasks</div><div class="value" id="statTasks">—</div></div>
                <div class="stat-card"><div class="stat-icon">◈</div><div class="label">Total Earnings</div><div class="value" id="statEarnings">—</div></div>
                <div class="stat-card"><div class="stat-icon">⧗</div><div class="label">Pending Payments</div><div class="value" id="statPending">—</div></div>
                <div class="stat-card"><div class="stat-icon">✓</div><div class="label">Completed Projects</div><div class="value" id="statCompleted">—</div></div>
            </div>

            <!-- Charts -->
            <div class="panels-row">
                <div class="panel chart-card">
                    <h2 style="align-self:flex-start;">Project Status</h2>
                    <canvas id="chartProjects" width="150" height="150"></canvas>
                    <div class="chart-legend" id="legendProjects"></div>
                </div>
                <div class="panel chart-card">
                    <h2 style="align-self:flex-start;">Task Status</h2>
                    <canvas id="chartTasks" width="150" height="150"></canvas>
                    <div class="chart-legend" id="legendTasks"></div>
                </div>
                <div class="panel chart-card">
                    <h2 style="align-self:flex-start;">Payment Status</h2>
                    <canvas id="chartPayments" width="150" height="150"></canvas>
                    <div class="chart-legend" id="legendPayments"></div>
                </div>
            </div>

            <!-- Recent activity -->
            <div class="two-col-row">
                <div class="panel">
                    <h2>Latest Clients</h2>
                    <div class="table-wrap"><table><thead><tr><th>Name</th><th>Email</th><th>Joined</th></tr></thead><tbody id="latestClientsBody"></tbody></table></div>
                </div>
                <div class="panel">
                    <h2>Latest Projects</h2>
                    <div class="table-wrap"><table><thead><tr><th>Title</th><th>Client</th><th>Status</th></tr></thead><tbody id="latestProjectsBody"></tbody></table></div>
                </div>
            </div>
            <div class="two-col-row">
                <div class="panel">
                    <h2>Latest Tasks</h2>
                    <div class="table-wrap"><table><thead><tr><th>Title</th><th>Project</th><th>Status</th></tr></thead><tbody id="latestTasksBody"></tbody></table></div>
                </div>
                <div class="panel">
                    <h2>Latest Payments</h2>
                    <div class="table-wrap"><table><thead><tr><th>Project</th><th>Amount</th><th>Status</th></tr></thead><tbody id="latestPaymentsBody"></tbody></table></div>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="assets/app.js"></script>
<script>
document.getElementById('globalSearch').addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && e.target.value.trim() !== '') {
        window.location.href = `clients.php?search=${encodeURIComponent(e.target.value.trim())}`;
    }
});

async function loadDashboard() {
    ['statClients','statProjects','statTasks','statEarnings','statPending','statCompleted'].forEach(id => {
        document.getElementById(id).innerHTML = '<div class="skeleton-bar" style="width:60px;height:22px;"></div>';
    });

    const result = await apiGet('api/dashboard.php');
    if (!result.success) { showToast(result.message || 'Failed to load dashboard', 'error'); return; }

    const { stats, charts, recent } = result.data;

    document.getElementById('statClients').textContent = stats.totalClients;
    document.getElementById('statProjects').textContent = stats.totalProjects;
    document.getElementById('statTasks').textContent = stats.totalTasks;
    document.getElementById('statEarnings').textContent = formatMoney(stats.totalEarnings);
    document.getElementById('statPending').textContent = formatMoney(stats.pendingPayments);
    document.getElementById('statCompleted').textContent = stats.completedProjects;

    drawDonutChart(document.getElementById('chartProjects'), charts.projectStatus, document.getElementById('legendProjects'));
    drawDonutChart(document.getElementById('chartTasks'), charts.taskStatus, document.getElementById('legendTasks'));
    drawDonutChart(document.getElementById('chartPayments'), charts.paymentStatus, document.getElementById('legendPayments'));

    renderTable(document.getElementById('latestClientsBody'), recent.clients, (c) => `
        <tr><td>${escapeHtml(c.ClientName)}</td><td>${escapeHtml(c.Email)}</td><td>${formatDate(c.CreatedDate)}</td></tr>
    `, 3, '👤', 'No clients yet');

    renderTable(document.getElementById('latestProjectsBody'), recent.projects, (p) => `
        <tr><td>${escapeHtml(p.Title)}</td><td>${escapeHtml(p.ClientName)}</td><td>${statusBadge(p.Status)}</td></tr>
    `, 3, '📁', 'No projects yet');

    renderTable(document.getElementById('latestTasksBody'), recent.tasks, (t) => `
        <tr><td>${escapeHtml(t.Title)}</td><td>${escapeHtml(t.ProjectTitle)}</td><td>${statusBadge(t.Status)}</td></tr>
    `, 3, '☑️', 'No tasks yet');

    renderTable(document.getElementById('latestPaymentsBody'), recent.payments, (p) => `
        <tr><td>${escapeHtml(p.ProjectTitle)}</td><td>${formatMoney(p.Amount)}</td><td>${statusBadge(p.Status)}</td></tr>
    `, 3, '💳', 'No payments yet');
}

loadDashboard();
window.addEventListener('resize', debounce(loadDashboard, 400));
</script>
</body>
</html>
