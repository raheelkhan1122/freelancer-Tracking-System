# Freelance Project Tracking System — Web App Specification

**Course:** Database Systems Lab  
**Group Members:** Raheel Ahmed & Muhammad Rahimullah  
**Group:** BS Computer Science — Group B  
**Stack:** Node.js + Express.js (Backend), HTML/CSS/JavaScript (Frontend), MySQL (Database)

---

## 1. Project Overview

The Freelance Project Tracking System is a simple academic web application that allows a freelancer to manage their clients, projects, tasks, and payments from a single dashboard. The system is built around four core entities — **CLIENT**, **PROJECT**, **TASK**, and **PAYMENT** — all connected through foreign key relationships in a normalized (3NF) MySQL database.

The app does not require authentication (no login system). It is a straightforward CRUD application with a clean dashboard view.

---

## 2. Technology Stack

| Layer      | Technology                          |
|------------|-------------------------------------|
| Frontend   | HTML, CSS (plain), Vanilla JavaScript (fetch API) |
| Backend    | Node.js with Express.js             |
| Database   | MySQL                               |
| DB Driver  | `mysql2` npm package                |

No frontend frameworks (no React, no Vue). Keep it simple — plain HTML pages or a single-page app using fetch + DOM manipulation.

---

## 3. Database Schema

### Database Name
```sql
freelance_project_tracking
```

### Tables

#### 3.1 CLIENT
```sql
CREATE TABLE CLIENT (
    ClientID INT AUTO_INCREMENT PRIMARY KEY,
    Name     VARCHAR(255) NOT NULL,
    Email    VARCHAR(255) NOT NULL UNIQUE,
    Phone    VARCHAR(50)
);
```

#### 3.2 PROJECT
```sql
CREATE TABLE PROJECT (
    ProjectID   INT AUTO_INCREMENT PRIMARY KEY,
    ClientID    INT NOT NULL,
    Title       VARCHAR(255) NOT NULL,
    Description TEXT,
    Deadline    DATE NOT NULL,
    Status      VARCHAR(50) DEFAULT 'Pending',
    CONSTRAINT FK_Project_Client FOREIGN KEY (ClientID)
        REFERENCES CLIENT(ClientID) ON DELETE CASCADE,
    CONSTRAINT CHK_Project_Status CHECK (Status IN ('Pending', 'In Progress', 'Completed', 'Cancelled'))
);
```

#### 3.3 TASK
```sql
CREATE TABLE TASK (
    TaskID      INT AUTO_INCREMENT PRIMARY KEY,
    ProjectID   INT NOT NULL,
    Title       VARCHAR(255) NOT NULL,
    Description TEXT,
    Deadline    DATE,
    Status      VARCHAR(50) DEFAULT 'Pending',
    CONSTRAINT FK_Task_Project FOREIGN KEY (ProjectID)
        REFERENCES PROJECT(ProjectID) ON DELETE CASCADE,
    CONSTRAINT CHK_Task_Status CHECK (Status IN ('Pending', 'In Progress', 'Completed'))
);
```

#### 3.4 PAYMENT
```sql
CREATE TABLE PAYMENT (
    PaymentID   INT AUTO_INCREMENT PRIMARY KEY,
    ProjectID   INT NOT NULL,
    Amount      DECIMAL(10, 2) NOT NULL,
    PaymentDate DATE,
    Status      VARCHAR(50) DEFAULT 'Pending',
    Method      VARCHAR(50),
    CONSTRAINT FK_Payment_Project FOREIGN KEY (ProjectID)
        REFERENCES PROJECT(ProjectID) ON DELETE CASCADE,
    CONSTRAINT CHK_Payment_Amount CHECK (Amount > 0),
    CONSTRAINT CHK_Payment_Status CHECK (Status IN ('Pending', 'Cleared', 'Failed', 'Refunded'))
);
```

### Relationships Summary
- One CLIENT can have many PROJECTs (1:N)
- One PROJECT can have many TASKs (1:N)
- One PROJECT can have many PAYMENTs (1:N)
- Deleting a CLIENT cascades and deletes all their PROJECTs, TASKs, and PAYMENTs

---

## 4. Seed Data

The database should be pre-populated with the following synthetic data on first setup:

| Table   | Row Count |
|---------|-----------|
| CLIENT  | 54 rows   |
| PROJECT | 75 rows   |
| TASK    | 200 rows  |
| PAYMENT | 100 rows  |

### Sample CLIENT rows
```
ClientID | Name                    | Email                                  | Phone
---------|-------------------------|----------------------------------------|---------------
1        | Acme Corp 95            | acme.corp.9536@example.com             | 555-350-4657
2        | Soylent 14              | soylent.1487@example.com               | 555-858-9935
5        | Linda Anderson          | linda.anderson76@example.com           | 555-384-1106
8        | Stark Industries 45     | stark.industries.4578@example.com      | 555-370-1711
```

### Sample PROJECT rows
```
ProjectID | ClientID | Title                              | Status      | Deadline
----------|----------|------------------------------------|-------------|----------
101       | 26       | Logo Design for Tech               | Completed   | 2026-06-06
103       | 37       | API Integration for Finance        | In Progress | 2026-06-13
105       | 43       | Database Migration for Education   | Cancelled   | 2026-07-11
```

Project Status values: `Pending`, `In Progress`, `Completed`, `Cancelled`

### Sample TASK rows
```
TaskID | ProjectID | Title              | Status      | Deadline
-------|-----------|--------------------|-------------|----------
1001   | 115       | Testing & QA       | Pending     | 2026-01-06
1002   | 175       | Final Handover     | In Progress | 2026-08-05
1003   | 151       | Backend Development| Completed   | 2026-09-01
```

Task Status values: `Pending`, `In Progress`, `Completed`

### Sample PAYMENT rows
```
PaymentID | ProjectID | Amount  | PaymentDate | Status  | Method
----------|-----------|---------|-------------|---------|------------
5001      | 111       | 2099.68 | 2026-05-18  | Cleared | PayPal
5003      | 115       | 2746.90 | 2026-01-03  | Pending | Credit Card
5006      | 160       | 3821.34 | 2026-09-28  | Refunded| Stripe
```

Payment Status values: `Pending`, `Cleared`, `Failed`, `Refunded`  
Payment Method values: `PayPal`, `Stripe`, `Credit Card`, `Crypto`, `Bank Transfer`

---

## 5. Application Pages & Features

The app should have **5 main sections**, accessible via a top navigation bar or sidebar.

---

### 5.1 Dashboard (Home Page)

**URL:** `/` or `/dashboard`

Display summary statistics at the top as simple stat cards:
- Total Clients
- Total Projects
- Total Tasks
- Total Payments collected (sum of Cleared payments)

Below the stats, show two tables:
1. **Recent Projects** — last 5 projects (with client name, status, deadline)
2. **Recent Payments** — last 5 payments (with project title, amount, status, method)

---

### 5.2 Clients Page

**URL:** `/clients`

**List View:**
- Table with columns: ClientID, Name, Email, Phone, Actions
- Actions per row: Edit, Delete
- Button at top: **Add Client**

**Add/Edit Client Form (modal or separate page):**
- Fields: Name (text, required), Email (text, required), Phone (text, optional)

**Delete Client:**
- Confirm before deleting
- Deleting a client cascades and removes all their projects, tasks, payments

---

### 5.3 Projects Page

**URL:** `/projects`

**List View:**
- Table with columns: ProjectID, Title, Client Name, Status, Deadline, Actions
- Actions per row: View Tasks, View Payments, Edit, Delete
- Button at top: **Add Project**
- Optional filter dropdown by Status: All / Pending / In Progress / Completed / Cancelled

**Add/Edit Project Form:**
- Fields:
  - Title (text, required)
  - Client (dropdown — populated from CLIENT table, required)
  - Description (textarea, optional)
  - Deadline (date picker, required)
  - Status (dropdown: Pending / In Progress / Completed / Cancelled, default: Pending)

**Delete Project:**
- Confirm before deleting
- Cascades to tasks and payments

---

### 5.4 Tasks Page

**URL:** `/tasks`

**List View:**
- Table with columns: TaskID, Title, Project Title, Status, Deadline, Actions
- Actions per row: Edit, Delete
- Button at top: **Add Task**
- Optional filter dropdown by Status: All / Pending / In Progress / Completed

**Add/Edit Task Form:**
- Fields:
  - Title (text, required)
  - Project (dropdown — populated from PROJECT table, required)
  - Description (textarea, optional)
  - Deadline (date picker, optional)
  - Status (dropdown: Pending / In Progress / Completed, default: Pending)

---

### 5.5 Payments Page

**URL:** `/payments`

**List View:**
- Table with columns: PaymentID, Project Title, Amount, Payment Date, Status, Method, Actions
- Actions per row: Edit, Delete
- Button at top: **Add Payment**
- Optional filter dropdown by Status: All / Pending / Cleared / Failed / Refunded

**Add/Edit Payment Form:**
- Fields:
  - Project (dropdown — populated from PROJECT table, required)
  - Amount (number, required, must be > 0)
  - Payment Date (date picker, optional)
  - Status (dropdown: Pending / Cleared / Failed / Refunded, default: Pending)
  - Method (dropdown: PayPal / Stripe / Credit Card / Crypto / Bank Transfer, optional)

---

## 6. Backend API Endpoints

All endpoints return JSON. Base path: `/api`

### Clients
| Method | Endpoint            | Description          |
|--------|---------------------|----------------------|
| GET    | `/api/clients`      | Get all clients      |
| POST   | `/api/clients`      | Add a new client     |
| PUT    | `/api/clients/:id`  | Update a client      |
| DELETE | `/api/clients/:id`  | Delete a client      |

### Projects
| Method | Endpoint             | Description               |
|--------|----------------------|---------------------------|
| GET    | `/api/projects`      | Get all projects (with client name via JOIN) |
| POST   | `/api/projects`      | Add a new project         |
| PUT    | `/api/projects/:id`  | Update a project          |
| DELETE | `/api/projects/:id`  | Delete a project          |

### Tasks
| Method | Endpoint          | Description              |
|--------|-------------------|--------------------------|
| GET    | `/api/tasks`      | Get all tasks (with project title via JOIN) |
| POST   | `/api/tasks`      | Add a new task           |
| PUT    | `/api/tasks/:id`  | Update a task            |
| DELETE | `/api/tasks/:id`  | Delete a task            |

### Payments
| Method | Endpoint             | Description               |
|--------|----------------------|---------------------------|
| GET    | `/api/payments`      | Get all payments (with project title via JOIN) |
| POST   | `/api/payments`      | Add a new payment         |
| PUT    | `/api/payments/:id`  | Update a payment          |
| DELETE | `/api/payments/:id`  | Delete a payment          |

### Dashboard
| Method | Endpoint          | Description                                              |
|--------|-------------------|----------------------------------------------------------|
| GET    | `/api/dashboard`  | Returns counts + recent 5 projects + recent 5 payments  |

---

## 7. Key SQL Queries Used in Backend

### Dashboard stats
```sql
SELECT 
  (SELECT COUNT(*) FROM CLIENT) AS totalClients,
  (SELECT COUNT(*) FROM PROJECT) AS totalProjects,
  (SELECT COUNT(*) FROM TASK) AS totalTasks,
  (SELECT COALESCE(SUM(Amount),0) FROM PAYMENT WHERE Status = 'Cleared') AS totalEarned;
```

### Projects with client name
```sql
SELECT p.ProjectID, p.Title, c.Name AS ClientName, p.Status, p.Deadline, p.Description
FROM PROJECT p
JOIN CLIENT c ON p.ClientID = c.ClientID
ORDER BY p.ProjectID DESC;
```

### Tasks with project title
```sql
SELECT t.TaskID, t.Title, p.Title AS ProjectTitle, t.Status, t.Deadline, t.Description
FROM TASK t
JOIN PROJECT p ON t.ProjectID = p.ProjectID
ORDER BY t.TaskID DESC;
```

### Payments with project title
```sql
SELECT pay.PaymentID, p.Title AS ProjectTitle, pay.Amount, pay.PaymentDate, pay.Status, pay.Method
FROM PAYMENT pay
JOIN PROJECT p ON pay.ProjectID = p.ProjectID
ORDER BY pay.PaymentID DESC;
```

### Projects summary (task count + payment total)
```sql
SELECT p.ProjectID, p.Title,
  COUNT(DISTINCT t.TaskID) AS TotalTasks,
  COALESCE(SUM(pay.Amount), 0) AS TotalPayments
FROM PROJECT p
LEFT JOIN TASK t ON p.ProjectID = t.ProjectID
LEFT JOIN PAYMENT pay ON p.ProjectID = pay.ProjectID
GROUP BY p.ProjectID, p.Title;
```

---

## 8. Project File Structure

```
freelance-tracking-app/
│
├── server.js                  # Express app entry point
├── package.json
├── db.js                      # MySQL connection config
│
├── routes/
│   ├── clients.js
│   ├── projects.js
│   ├── tasks.js
│   ├── payments.js
│   └── dashboard.js
│
├── public/                    # Static frontend files
│   ├── index.html             # Dashboard
│   ├── clients.html
│   ├── projects.html
│   ├── tasks.html
│   ├── payments.html
│   ├── style.css
│   └── app.js                 # Shared JS (fetch calls, table rendering)
│
└── database/
    ├── create_tables.sql      # DDL — creates all 4 tables
    └── insert_data.sql        # DML — inserts seed data
```

---

## 9. Database Connection Config (`db.js`)

```javascript
const mysql = require('mysql2');

const db = mysql.createConnection({
  host: 'localhost',
  user: 'root',
  password: '',          // set your MySQL password here
  database: 'freelance_project_tracking'
});

db.connect((err) => {
  if (err) throw err;
  console.log('MySQL connected');
});

module.exports = db;
```

---

## 10. Setup Instructions

1. Create MySQL database and run DDL:
   ```sql
   SOURCE database/create_tables.sql;
   SOURCE database/insert_data.sql;
   ```

2. Install Node.js dependencies:
   ```bash
   npm install express mysql2
   ```

3. Start the server:
   ```bash
   node server.js
   ```

4. Open browser at `http://localhost:3000`

---

## 11. Design Guidelines (Keep It Simple)

- Use a clean, minimal CSS design — white background, simple table layout
- Navigation: top navbar or left sidebar with links to Dashboard, Clients, Projects, Tasks, Payments
- Status badges: use colored text or small badges (green for Completed/Cleared, yellow for Pending, blue for In Progress, red for Cancelled/Failed)
- No CSS frameworks required, but Bootstrap CDN is acceptable if desired
- All forms should validate required fields before submitting
- Show success/error messages after add/edit/delete operations

---

## 12. Constraints & Business Rules

- A project must belong to an existing client
- A task must belong to an existing project
- A payment must belong to an existing project
- Project status must be one of: `Pending`, `In Progress`, `Completed`, `Cancelled`
- Task status must be one of: `Pending`, `In Progress`, `Completed`
- Payment status must be one of: `Pending`, `Cleared`, `Failed`, `Refunded`
- Payment amount must be greater than 0
- Client email must be unique
- Deleting a client removes all related projects, tasks, and payments (CASCADE)
- Deleting a project removes all related tasks and payments (CASCADE)

---

*This specification is based on the Freelance Project Tracking System database project built for the Database Systems Lab course at the University of Kohat.*
