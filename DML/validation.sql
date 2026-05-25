USE freelance_project_tracking;

SELECT 'CLIENT' AS TableName, COUNT(*) AS TotalRows FROM CLIENT
UNION ALL
SELECT 'PROJECT', COUNT(*) FROM PROJECT
UNION ALL
SELECT 'TASK', COUNT(*) FROM TASK
UNION ALL
SELECT 'PAYMENT', COUNT(*) FROM PAYMENT;

SELECT
    p.ProjectID,
    p.Title AS ProjectTitle,
    c.Name AS ClientName,
    p.Status,
    p.Deadline
FROM PROJECT p
JOIN CLIENT c ON p.ClientID = c.ClientID
ORDER BY p.ProjectID
LIMIT 20;

SELECT
    p.ProjectID,
    p.Title AS ProjectTitle,
    COUNT(t.TaskID) AS TotalTasks
FROM PROJECT p
LEFT JOIN TASK t ON p.ProjectID = t.ProjectID
GROUP BY p.ProjectID, p.Title
ORDER BY p.ProjectID;

SELECT
    p.ProjectID,
    p.Title AS ProjectTitle,
    COUNT(pay.PaymentID) AS TotalPayments,
    COALESCE(SUM(pay.Amount), 0) AS TotalAmount
FROM PROJECT p
LEFT JOIN PAYMENT pay ON p.ProjectID = pay.ProjectID
GROUP BY p.ProjectID, p.Title
ORDER BY p.ProjectID;

SELECT
    p.ProjectID,
    p.Title AS ProjectTitle
FROM PROJECT p
LEFT JOIN TASK t ON p.ProjectID = t.ProjectID
WHERE t.TaskID IS NULL
ORDER BY p.ProjectID;

SELECT
    p.ProjectID,
    p.Title AS ProjectTitle
FROM PROJECT p
LEFT JOIN PAYMENT pay ON p.ProjectID = pay.ProjectID
WHERE pay.PaymentID IS NULL
ORDER BY p.ProjectID;
