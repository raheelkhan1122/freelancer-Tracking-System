const express = require('express');
const db = require('../db');

const router = express.Router();
const PROJECT_STATUSES = ['Pending', 'In Progress', 'Completed', 'Cancelled'];

function validStatus(status) {
  return PROJECT_STATUSES.includes(status || 'Pending');
}

router.get('/', (req, res) => {
  const sql = `
    SELECT p.ProjectID, p.ClientID, p.Title, c.Name AS ClientName, p.Status, p.Deadline, p.Description
    FROM PROJECT p
    JOIN CLIENT c ON p.ClientID = c.ClientID
    ORDER BY p.ProjectID DESC
  `;
  db.query(sql, (err, rows) => {
    if (err) return res.status(500).json({ error: err.message });
    res.json(rows);
  });
});

router.post('/', (req, res) => {
  const { ClientID, Title, Description, Deadline, Status } = req.body;
  const nextStatus = Status || 'Pending';
  if (!ClientID || !Title || !Deadline) return res.status(400).json({ error: 'Client, Title, and Deadline are required' });
  if (!validStatus(nextStatus)) return res.status(400).json({ error: 'Invalid project status' });

  db.query(
    'INSERT INTO PROJECT (ClientID, Title, Description, Deadline, Status) VALUES (?, ?, ?, ?, ?)',
    [ClientID, Title, Description || null, Deadline, nextStatus],
    (err, result) => {
      if (err) return res.status(500).json({ error: err.message });
      res.status(201).json({ message: 'Project added successfully', ProjectID: result.insertId });
    }
  );
});

router.put('/:id', (req, res) => {
  const { ClientID, Title, Description, Deadline, Status } = req.body;
  const nextStatus = Status || 'Pending';
  if (!ClientID || !Title || !Deadline) return res.status(400).json({ error: 'Client, Title, and Deadline are required' });
  if (!validStatus(nextStatus)) return res.status(400).json({ error: 'Invalid project status' });

  db.query(
    'UPDATE PROJECT SET ClientID = ?, Title = ?, Description = ?, Deadline = ?, Status = ? WHERE ProjectID = ?',
    [ClientID, Title, Description || null, Deadline, nextStatus, req.params.id],
    (err, result) => {
      if (err) return res.status(500).json({ error: err.message });
      if (result.affectedRows === 0) return res.status(404).json({ error: 'Project not found' });
      res.json({ message: 'Project updated successfully' });
    }
  );
});

router.delete('/:id', (req, res) => {
  db.query('DELETE FROM PROJECT WHERE ProjectID = ?', [req.params.id], (err, result) => {
    if (err) return res.status(500).json({ error: err.message });
    if (result.affectedRows === 0) return res.status(404).json({ error: 'Project not found' });
    res.json({ message: 'Project deleted successfully' });
  });
});

module.exports = router;
