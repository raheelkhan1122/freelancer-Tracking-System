const express = require('express');
const path = require('path');

const clientsRoutes = require('./routes/clients');
const projectsRoutes = require('./routes/projects');
const tasksRoutes = require('./routes/tasks');
const paymentsRoutes = require('./routes/payments');
const dashboardRoutes = require('./routes/dashboard');

const app = express();
const PORT = 3000;

app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(express.static(path.join(__dirname, 'public')));

app.use('/api/clients', clientsRoutes);
app.use('/api/projects', projectsRoutes);
app.use('/api/tasks', tasksRoutes);
app.use('/api/payments', paymentsRoutes);
app.use('/api/dashboard', dashboardRoutes);

app.get(['/', '/dashboard'], (req, res) => {
  res.sendFile(path.join(__dirname, 'public', 'index.html'));
});

app.get('/clients', (req, res) => {
  res.sendFile(path.join(__dirname, 'public', 'clients.html'));
});

app.get('/projects', (req, res) => {
  res.sendFile(path.join(__dirname, 'public', 'projects.html'));
});

app.get('/tasks', (req, res) => {
  res.sendFile(path.join(__dirname, 'public', 'tasks.html'));
});

app.get('/payments', (req, res) => {
  res.sendFile(path.join(__dirname, 'public', 'payments.html'));
});

// 404 handler
app.use((req, res) => {
  res.status(404).json({ error: 'Not found' });
});

// Global error handler - prevents unhandled errors from crashing the server
app.use((err, req, res, next) => {
  console.error('Unhandled error:', err);
  res.status(500).json({ error: 'Internal server error' });
});

// Catch unhandled promise rejections
process.on('unhandledRejection', (reason) => {
  console.error('Unhandled Promise Rejection:', reason);
});

app.listen(PORT, () => {
  console.log(`Server running at http://localhost:${PORT}`);
});
