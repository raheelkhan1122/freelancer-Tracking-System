const express = require('express');
const db = require('../db');

const router = express.Router();

router.get('/', (req, res) => {
  const statsSql = `
    SELECT 
      (SELECT COUNT(*) FROM CLIENT) AS totalClients,
      (SELECT COUNT(*) FROM PROJECT) AS totalProjects,
      (SELECT COUNT(*) FROM TASK) AS totalTasks,
      (SELECT COALESCE(SUM(Amount),0) FROM PAYMENT WHERE Status = 'Cleared') AS totalEarned
  `;
  const recentProjectsSql = `
    SELECT p.ProjectID, p.Title, c.Name AS ClientName, p.Status, p.Deadline
    FROM PROJECT p
    JOIN CLIENT c ON p.ClientID = c.ClientID
    ORDER BY p.ProjectID DESC
    LIMIT 5
  `;
  const recentPaymentsSql = `
    SELECT pay.PaymentID, p.Title AS ProjectTitle, pay.Amount, pay.Status, pay.Method
    FROM PAYMENT pay
    JOIN PROJECT p ON pay.ProjectID = p.ProjectID
    ORDER BY pay.PaymentID DESC
    LIMIT 5
  `;

  db.query(statsSql, (statsErr, statsRows) => {
    if (statsErr) return res.status(500).json({ error: statsErr.message });
    db.query(recentProjectsSql, (projectsErr, recentProjects) => {
      if (projectsErr) return res.status(500).json({ error: projectsErr.message });
      db.query(recentPaymentsSql, (paymentsErr, recentPayments) => {
        if (paymentsErr) return res.status(500).json({ error: paymentsErr.message });
        res.json({
          stats: statsRows[0],
          recentProjects,
          recentPayments
        });
      });
    });
  });
});

module.exports = router;
