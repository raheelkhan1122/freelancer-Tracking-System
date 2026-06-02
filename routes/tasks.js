const express = require('express');
const db = require('../db');

const router = express.Router();
const TASK_STATUSES = ['Pending', 'In Progress', 'Completed'];

function validStatus(status) {
  return TASK_STATUSES.includes(status || 'Pending');
}

router.get('/', (req, res) => {
  const sql = `
    SELECT t.TaskID, t.ProjectID, t.Title, p.Title AS ProjectTitle, t.Status, t.Deadline, t.Description
    FROM TASK t
    JOIN PROJECT p ON t.ProjectID = p.ProjectID
    ORDER BY t.TaskID DESC
  `;
  db.query(sql, (err, rows) => {
    if (err) return res.status(500).json({ error: err.message });
    res.json(rows);
  });
});

router.post('/', (req, res) => {
  const { ProjectID, Title, Description, Deadline, Status } = req.body;
  const nextStatus = Status || 'Pending';
  if (!ProjectID || !Title) return res.status(400).json({ error: 'Project and Title are required' });
  if (!validStatus(nextStatus)) return res.status(400).json({ error: 'Invalid task status' });

  db.query(
    'INSERT INTO TASK (ProjectID, Title, Description, Deadline, Status) VALUES (?, ?, ?, ?, ?)',
    [ProjectID, Title, Description || null, Deadline || null, nextStatus],
    (err, result) => {
      if (err) return res.status(500).json({ error: err.message });
      res.status(201).json({ message: 'Task added successfully', TaskID: result.insertId });
    }
  );
});

router.put('/:id', (req, res) => {
  const { ProjectID, Title, Description, Deadline, Status } = req.body;
  const nextStatus = Status || 'Pending';
  if (!ProjectID || !Title) return res.status(400).json({ error: 'Project and Title are required' });
  if (!validStatus(nextStatus)) return res.status(400).json({ error: 'Invalid task status' });

  db.query(
    'UPDATE TASK SET ProjectID = ?, Title = ?, Description = ?, Deadline = ?, Status = ? WHERE TaskID = ?',
    [ProjectID, Title, Description || null, Deadline || null, nextStatus, req.params.id],
    (err, result) => {
      if (err) return res.status(500).json({ error: err.message });
      if (result.affectedRows === 0) return res.status(404).json({ error: 'Task not found' });
      res.json({ message: 'Task updated successfully' });
    }
  );
});

router.delete('/:id', (req, res) => {
  db.query('DELETE FROM TASK WHERE TaskID = ?', [req.params.id], (err, result) => {
    if (err) return res.status(500).json({ error: err.message });
    if (result.affectedRows === 0) return res.status(404).json({ error: 'Task not found' });
    res.json({ message: 'Task deleted successfully' });
  });
});

module.exports = router;
