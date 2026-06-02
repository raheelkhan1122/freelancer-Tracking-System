const express = require('express');
const db = require('../db');

const router = express.Router();

function query(sql, params = []) {
  return new Promise((resolve, reject) => {
    db.query(sql, params, (err, rows) => {
      if (err) reject(err);
      else resolve(rows);
    });
  });
}

router.get('/', async (req, res) => {
  try {
    const [statsRows, recentProjects, recentPayments] = await Promise.all([
      query(`
        SELECT 
          (SELECT COUNT(*) FROM CLIENT) AS totalClients,
          (SELECT COUNT(*) FROM PROJECT) AS totalProjects,
          (SELECT COUNT(*) FROM TASK) AS totalTasks,
          (SELECT COALESCE(SUM(Amount), 0) FROM PAYMENT WHERE Status = 'Cleared') AS totalEarned
      `),
      query(`
        SELECT p.ProjectID, p.Title, c.Name AS ClientName, p.Status, p.Deadline
        FROM PROJECT p
        JOIN CLIENT c ON p.ClientID = c.ClientID
        ORDER BY p.ProjectID DESC
        LIMIT 5
      `),
      query(`
        SELECT pay.PaymentID, p.Title AS ProjectTitle, pay.Amount, pay.Status, pay.Method
        FROM PAYMENT pay
        JOIN PROJECT p ON pay.ProjectID = p.ProjectID
        ORDER BY pay.PaymentID DESC
        LIMIT 5
      `)
    ]);

    res.json({
      stats: statsRows[0],
      recentProjects,
      recentPayments
    });
  } catch (err) {
    console.error('Dashboard query error:', err);
    res.status(500).json({ error: err.message });
  }
});

module.exports = router;
