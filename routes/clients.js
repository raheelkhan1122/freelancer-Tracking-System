const express = require('express');
const db = require('../db');

const router = express.Router();

router.get('/', (req, res) => {
  db.query('SELECT ClientID, Name, Email, Phone FROM CLIENT ORDER BY ClientID DESC', (err, rows) => {
    if (err) return res.status(500).json({ error: err.message });
    res.json(rows);
  });
});

router.post('/', (req, res) => {
  const { Name, Email, Phone } = req.body;
  if (!Name || !Email) return res.status(400).json({ error: 'Name and Email are required' });

  db.query(
    'INSERT INTO CLIENT (Name, Email, Phone) VALUES (?, ?, ?)',
    [Name, Email, Phone || null],
    (err, result) => {
      if (err) return res.status(500).json({ error: err.message });
      res.status(201).json({ message: 'Client added successfully', ClientID: result.insertId });
    }
  );
});

router.put('/:id', (req, res) => {
  const { Name, Email, Phone } = req.body;
  if (!Name || !Email) return res.status(400).json({ error: 'Name and Email are required' });

  db.query(
    'UPDATE CLIENT SET Name = ?, Email = ?, Phone = ? WHERE ClientID = ?',
    [Name, Email, Phone || null, req.params.id],
    (err, result) => {
      if (err) return res.status(500).json({ error: err.message });
      if (result.affectedRows === 0) return res.status(404).json({ error: 'Client not found' });
      res.json({ message: 'Client updated successfully' });
    }
  );
});

router.delete('/:id', (req, res) => {
  db.query('DELETE FROM CLIENT WHERE ClientID = ?', [req.params.id], (err, result) => {
    if (err) return res.status(500).json({ error: err.message });
    if (result.affectedRows === 0) return res.status(404).json({ error: 'Client not found' });
    res.json({ message: 'Client deleted successfully' });
  });
});

module.exports = router;
