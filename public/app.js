const page = document.body.dataset.page;
let records = [];
let clients = [];
let projects = [];

const qs = (selector) => document.querySelector(selector);
const fmtDate = (value) => (value ? String(value).slice(0, 10) : '');
const money = (value) => `$${Number(value || 0).toFixed(2)}`;
const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
  '&': '&amp;',
  '<': '&lt;',
  '>': '&gt;',
  '"': '&quot;',
  "'": '&#039;'
}[char]));

function showMessage(message, type = 'success') {
  const box = qs('#message');
  if (!box) return;
  box.innerHTML = `<div class="alert alert-${type}">${escapeHtml(message)}</div>`;
  setTimeout(() => { box.innerHTML = ''; }, 3500);
}

async function api(path, options = {}) {
  try {
    const response = await fetch(path, {
      headers: { 'Content-Type': 'application/json' },
      ...options
    });
    const data = await response.json();
    if (!response.ok) throw new Error(data.error || 'Request failed');
    return data;
  } catch (err) {
    // Network errors (server down) produce a clearer message
    if (err instanceof TypeError && err.message.includes('fetch')) {
      throw new Error('Cannot connect to server. Make sure the Node.js server is running.');
    }
    throw err;
  }
}

function statusBadge(status) {
  const cls = String(status || '').toLowerCase().replace(/\s+/g, '-');
  return `<span class="status-badge status-${cls}">${escapeHtml(status)}</span>`;
}

function bindCommonForm(addTitle, editTitle, endpoint, idField, readForm, fillForm, render) {
  qs('#addBtn').addEventListener('click', () => {
    qs('#entityForm').reset();
    qs(`#${idField}`).value = '';
    qs('#formTitle').textContent = addTitle;
    qs('#formPanel').classList.remove('d-none');
  });

  qs('#cancelBtn').addEventListener('click', () => {
    qs('#formPanel').classList.add('d-none');
  });

  qs('#entityForm').addEventListener('submit', async (event) => {
    event.preventDefault();
    const id = qs(`#${idField}`).value;
    const payload = readForm();
    try {
      const result = await api(id ? `${endpoint}/${id}` : endpoint, {
        method: id ? 'PUT' : 'POST',
        body: JSON.stringify(payload)
      });
      showMessage(result.message);
      qs('#formPanel').classList.add('d-none');
      await render();
    } catch (err) {
      showMessage(err.message, 'danger');
    }
  });

  window.editRecord = (id) => {
    const record = records.find((item) => String(item[idField]) === String(id));
    if (!record) return;
    qs('#formTitle').textContent = editTitle;
    fillForm(record);
    qs('#formPanel').classList.remove('d-none');
  };

  window.deleteRecord = async (id) => {
    if (!confirm('Are you sure you want to delete this record?')) return;
    try {
      const result = await api(`${endpoint}/${id}`, { method: 'DELETE' });
      showMessage(result.message);
      await render();
    } catch (err) {
      showMessage(err.message, 'danger');
    }
  };
}

async function loadDashboard() {
  try {
    const data = await api('/api/dashboard');
    qs('#totalClients').textContent = data.stats.totalClients;
    qs('#totalProjects').textContent = data.stats.totalProjects;
    qs('#totalTasks').textContent = data.stats.totalTasks;
    qs('#totalEarned').textContent = money(data.stats.totalEarned);

    qs('#recentProjectsBody').innerHTML = data.recentProjects.length
      ? data.recentProjects.map((project) => `
          <tr>
            <td>${escapeHtml(project.Title)}</td>
            <td>${escapeHtml(project.ClientName)}</td>
            <td>${statusBadge(project.Status)}</td>
            <td>${fmtDate(project.Deadline)}</td>
          </tr>
        `).join('')
      : '<tr><td colspan="4" class="text-center text-muted">No projects yet</td></tr>';

    qs('#recentPaymentsBody').innerHTML = data.recentPayments.length
      ? data.recentPayments.map((payment) => `
          <tr>
            <td>${escapeHtml(payment.ProjectTitle)}</td>
            <td>${money(payment.Amount)}</td>
            <td>${statusBadge(payment.Status)}</td>
            <td>${escapeHtml(payment.Method || '')}</td>
          </tr>
        `).join('')
      : '<tr><td colspan="4" class="text-center text-muted">No payments yet</td></tr>';
  } catch (err) {
    showMessage('Dashboard failed to load: ' + err.message, 'danger');
  }
}

async function loadClients() {
  try {
    records = await api('/api/clients');
    qs('#clientsBody').innerHTML = records.length
      ? records.map((client) => `
          <tr>
            <td>${client.ClientID}</td>
            <td>${escapeHtml(client.Name)}</td>
            <td>${escapeHtml(client.Email)}</td>
            <td>${escapeHtml(client.Phone || '')}</td>
            <td><div class="actions">
              <button class="btn btn-sm btn-outline-primary" onclick="editRecord(${client.ClientID})">Edit</button>
              <button class="btn btn-sm btn-outline-danger" onclick="deleteRecord(${client.ClientID})">Delete</button>
            </div></td>
          </tr>
        `).join('')
      : '<tr><td colspan="5" class="text-center text-muted">No clients yet</td></tr>';
  } catch (err) {
    showMessage(err.message, 'danger');
  }
}

function setupClients() {
  bindCommonForm('Add Client', 'Edit Client', '/api/clients', 'ClientID', () => ({
    Name: qs('#Name').value.trim(),
    Email: qs('#Email').value.trim(),
    Phone: qs('#Phone').value.trim()
  }), (client) => {
    qs('#ClientID').value = client.ClientID;
    qs('#Name').value = client.Name;
    qs('#Email').value = client.Email;
    qs('#Phone').value = client.Phone || '';
  }, loadClients);
  loadClients();
}

async function loadClientOptions() {
  try {
    clients = await api('/api/clients');
    const sel = qs('#ClientID');
    if (!sel || sel.tagName !== 'SELECT') return;
    sel.innerHTML = '<option value="">Select Client</option>' + clients.map((client) =>
      `<option value="${client.ClientID}">${escapeHtml(client.Name)}</option>`
    ).join('');
  } catch (err) {
    showMessage('Failed to load clients: ' + err.message, 'danger');
  }
}

async function loadProjectOptions() {
  try {
    projects = await api('/api/projects');
    const sel = qs('#ProjectID');
    if (!sel || sel.tagName !== 'SELECT') return;
    sel.innerHTML = '<option value="">Select Project</option>' + projects.map((project) =>
      `<option value="${project.ProjectID}">${escapeHtml(project.Title)}</option>`
    ).join('');
  } catch (err) {
    showMessage('Failed to load projects: ' + err.message, 'danger');
  }
}

async function loadProjects() {
  try {
    records = await api('/api/projects');
    const status = qs('#statusFilter').value;
    const rows = status ? records.filter((project) => project.Status === status) : records;
    qs('#projectsBody').innerHTML = rows.length
      ? rows.map((project) => `
          <tr>
            <td>${project.ProjectID}</td>
            <td>${escapeHtml(project.Title)}</td>
            <td>${escapeHtml(project.ClientName)}</td>
            <td>${statusBadge(project.Status)}</td>
            <td>${fmtDate(project.Deadline)}</td>
            <td><div class="actions">
              <a class="btn btn-sm btn-outline-secondary" href="/tasks?projectId=${project.ProjectID}">Tasks</a>
              <a class="btn btn-sm btn-outline-secondary" href="/payments?projectId=${project.ProjectID}">Payments</a>
              <button class="btn btn-sm btn-outline-primary" onclick="editRecord(${project.ProjectID})">Edit</button>
              <button class="btn btn-sm btn-outline-danger" onclick="deleteRecord(${project.ProjectID})">Delete</button>
            </div></td>
          </tr>
        `).join('')
      : '<tr><td colspan="6" class="text-center text-muted">No projects found</td></tr>';
  } catch (err) {
    showMessage(err.message, 'danger');
  }
}

async function setupProjects() {
  // Wait for client options before setting up the form so edit works correctly
  await loadClientOptions();
  qs('#statusFilter').addEventListener('change', loadProjects);
  bindCommonForm('Add Project', 'Edit Project', '/api/projects', 'ProjectID', () => ({
    Title: qs('#Title').value.trim(),
    ClientID: qs('#ClientID').value,
    Description: qs('#Description').value.trim(),
    Deadline: qs('#Deadline').value,
    Status: qs('#Status').value
  }), (project) => {
    qs('#ProjectID').value = project.ProjectID;
    qs('#Title').value = project.Title;
    qs('#ClientID').value = project.ClientID;
    qs('#Description').value = project.Description || '';
    qs('#Deadline').value = fmtDate(project.Deadline);
    qs('#Status').value = project.Status;
  }, loadProjects);
  await loadProjects();
}

async function loadTasks() {
  try {
    records = await api('/api/tasks');
    const params = new URLSearchParams(location.search);
    const projectId = params.get('projectId');
    const status = qs('#statusFilter').value;
    let rows = projectId ? records.filter((task) => String(task.ProjectID) === projectId) : records;
    rows = status ? rows.filter((task) => task.Status === status) : rows;
    qs('#tasksBody').innerHTML = rows.length
      ? rows.map((task) => `
          <tr>
            <td>${task.TaskID}</td>
            <td>${escapeHtml(task.Title)}</td>
            <td>${escapeHtml(task.ProjectTitle)}</td>
            <td>${statusBadge(task.Status)}</td>
            <td>${fmtDate(task.Deadline)}</td>
            <td><div class="actions">
              <button class="btn btn-sm btn-outline-primary" onclick="editRecord(${task.TaskID})">Edit</button>
              <button class="btn btn-sm btn-outline-danger" onclick="deleteRecord(${task.TaskID})">Delete</button>
            </div></td>
          </tr>
        `).join('')
      : '<tr><td colspan="6" class="text-center text-muted">No tasks found</td></tr>';
  } catch (err) {
    showMessage(err.message, 'danger');
  }
}

async function setupTasks() {
  await loadProjectOptions();
  qs('#statusFilter').addEventListener('change', loadTasks);
  bindCommonForm('Add Task', 'Edit Task', '/api/tasks', 'TaskID', () => ({
    Title: qs('#Title').value.trim(),
    ProjectID: qs('#ProjectID').value,
    Description: qs('#Description').value.trim(),
    Deadline: qs('#Deadline').value,
    Status: qs('#Status').value
  }), (task) => {
    qs('#TaskID').value = task.TaskID;
    qs('#Title').value = task.Title;
    qs('#ProjectID').value = task.ProjectID;
    qs('#Description').value = task.Description || '';
    qs('#Deadline').value = fmtDate(task.Deadline);
    qs('#Status').value = task.Status;
  }, loadTasks);
  await loadTasks();
}

async function loadPayments() {
  try {
    records = await api('/api/payments');
    const params = new URLSearchParams(location.search);
    const projectId = params.get('projectId');
    const status = qs('#statusFilter').value;
    let rows = projectId ? records.filter((payment) => String(payment.ProjectID) === projectId) : records;
    rows = status ? rows.filter((payment) => payment.Status === status) : rows;
    qs('#paymentsBody').innerHTML = rows.length
      ? rows.map((payment) => `
          <tr>
            <td>${payment.PaymentID}</td>
            <td>${escapeHtml(payment.ProjectTitle)}</td>
            <td>${money(payment.Amount)}</td>
            <td>${fmtDate(payment.PaymentDate)}</td>
            <td>${statusBadge(payment.Status)}</td>
            <td>${escapeHtml(payment.Method || '')}</td>
            <td><div class="actions">
              <button class="btn btn-sm btn-outline-primary" onclick="editRecord(${payment.PaymentID})">Edit</button>
              <button class="btn btn-sm btn-outline-danger" onclick="deleteRecord(${payment.PaymentID})">Delete</button>
            </div></td>
          </tr>
        `).join('')
      : '<tr><td colspan="7" class="text-center text-muted">No payments found</td></tr>';
  } catch (err) {
    showMessage(err.message, 'danger');
  }
}

async function setupPayments() {
  await loadProjectOptions();
  qs('#statusFilter').addEventListener('change', loadPayments);
  bindCommonForm('Add Payment', 'Edit Payment', '/api/payments', 'PaymentID', () => ({
    ProjectID: qs('#ProjectID').value,
    Amount: qs('#Amount').value,
    PaymentDate: qs('#PaymentDate').value,
    Status: qs('#Status').value,
    Method: qs('#Method').value
  }), (payment) => {
    qs('#PaymentID').value = payment.PaymentID;
    qs('#ProjectID').value = payment.ProjectID;
    qs('#Amount').value = payment.Amount;
    qs('#PaymentDate').value = fmtDate(payment.PaymentDate);
    qs('#Status').value = payment.Status;
    qs('#Method').value = payment.Method || '';
  }, loadPayments);
  await loadPayments();
}

if (page === 'dashboard') loadDashboard();
if (page === 'clients') setupClients();
if (page === 'projects') setupProjects();
if (page === 'tasks') setupTasks();
if (page === 'payments') setupPayments();
