const express = require('express');
const db = require('../db');

const router = express.Router();
const PAYMENT_STATUSES = ['Pending', 'Cleared', 'Failed', 'Refunded'];
const PAYMENT_METHODS = ['PayPal', 'Stripe', 'Credit Card', 'Crypto', 'Bank Transfer'];

function validStatus(status) {
  return PAYMENT_STATUSES.includes(status || 'Pending');
}

function validMethod(method) {
  return !method || PAYMENT_METHODS.includes(method);
}

router.get('/', (req, res) => {
  const sql = `
    SELECT pay.PaymentID, pay.ProjectID, p.Title AS ProjectTitle, pay.Amount, pay.PaymentDate, pay.Status, pay.Method
    FROM PAYMENT pay
    JOIN PROJECT p ON pay.ProjectID = p.ProjectID
    ORDER BY pay.PaymentID DESC
  `;
  db.query(sql, (err, rows) => {
    if (err) return res.status(500).json({ error: err.message });
    res.json(rows);
  });
});

router.post('/', (req, res) => {
  const { ProjectID, Amount, PaymentDate, Status, Method } = req.body;
  const nextStatus = Status || 'Pending';
  if (!ProjectID || !Amount) return res.status(400).json({ error: 'Project and Amount are required' });
  if (Number(Amount) <= 0) return res.status(400).json({ error: 'Amount must be greater than 0' });
  if (!validStatus(nextStatus)) return res.status(400).json({ error: 'Invalid payment status' });
  if (!validMethod(Method)) return res.status(400).json({ error: 'Invalid payment method' });

  db.query(
    'INSERT INTO PAYMENT (ProjectID, Amount, PaymentDate, Status, Method) VALUES (?, ?, ?, ?, ?)',
    [ProjectID, Amount, PaymentDate || null, nextStatus, Method || null],
    (err, result) => {
      if (err) return res.status(500).json({ error: err.message });
      res.status(201).json({ message: 'Payment added successfully', PaymentID: result.insertId });
    }
  );
});

router.put('/:id', (req, res) => {
  const { ProjectID, Amount, PaymentDate, Status, Method } = req.body;
  const nextStatus = Status || 'Pending';
  if (!ProjectID || !Amount) return res.status(400).json({ error: 'Project and Amount are required' });
  if (Number(Amount) <= 0) return res.status(400).json({ error: 'Amount must be greater than 0' });
  if (!validStatus(nextStatus)) return res.status(400).json({ error: 'Invalid payment status' });
  if (!validMethod(Method)) return res.status(400).json({ error: 'Invalid payment method' });

  db.query(
    'UPDATE PAYMENT SET ProjectID = ?, Amount = ?, PaymentDate = ?, Status = ?, Method = ? WHERE PaymentID = ?',
    [ProjectID, Amount, PaymentDate || null, nextStatus, Method || null, req.params.id],
    (err, result) => {
      if (err) return res.status(500).json({ error: err.message });
      if (result.affectedRows === 0) return res.status(404).json({ error: 'Payment not found' });
      res.json({ message: 'Payment updated successfully' });
    }
  );
});

router.delete('/:id', (req, res) => {
  db.query('DELETE FROM PAYMENT WHERE PaymentID = ?', [req.params.id], (err, result) => {
    if (err) return res.status(500).json({ error: err.message });
    if (result.affectedRows === 0) return res.status(404).json({ error: 'Payment not found' });
    res.json({ message: 'Payment deleted successfully' });
  });
});

module.exports = router;
