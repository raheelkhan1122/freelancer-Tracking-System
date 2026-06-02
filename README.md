# freelancer-Tracking-System
**Course:** Database Systems Lab
**Group Members:** Raheel Ahmed & Muhammad Rahimullah
**Group:** BS Computer Science — Group B

A normalized (3NF) relational database for managing freelance operations. It tracks clients, projects, tasks, and payments through a structured SQL schema. This project features a complete ERD, MySQL DDL scripts with robust constraints, and synthetic CSV datasets designed to validate dataflow and ensure strict data integrity.


---

## Project Description
The Freelance Project Tracking System is a web-based application that allows freelancers to manage their clients, projects, tasks, and payments. Clients are registered in the system, projects are created and linked to clients, tasks are assigned under projects, and payments are tracked against each project.

---

## Technology Stack
- **Frontend:** HTML, CSS, JavaScript
- **Backend:** Node.js with Express.js
- **Database:** MySQL
- **Tools:** VS Code, MySQL Workbench, GitHub

---
## Database Tables
- **CLIENT** — Stores client information
- **PROJECT** — Stores projects linked to clients
- **TASK** — Stores tasks linked to projects
- **PAYMENT** — Stores payments linked to projects

---

## Repository Structure
```
freelance-project-tracking/
│
├── README.md
├── NORMALIZATION.md
├── DATAFLOW.md
├── generate_data.py
├── insert_data.py
├── ERD/
│   └── erd_diagram.png
├── DDL/
│   └── create_tables.sql
├── DML/
│   └── validation.sql
└── CSV/
    ├── client.csv
    ├── project.csv
    ├── task.csv
    └── payment.csv
```

---

## Milestones
| Milestone | Description | Status |
|-----------|-------------|--------|
| Milestone 1 | ERD and Relational Schema | ✅ Done |
| Milestone 2 | Normalization (1NF to 3NF) | ✅ Done |
| Milestone 3 | Dataset and Dataflow | ✅ Done |
| Milestone 4 | DDL Scripts | ✅ Done |
| Milestone 5 | Data Population | ✅ Done |
---

## Database Setup
In MySQL Workbench, open and run:

```text
database_complete.sql
```

From the MySQL command-line client, run:

```sql
SOURCE database_complete.sql;
```

Or run the connected script from the project root:

```sql
SOURCE run_all.sql;
```

Script order:
1. `DDL/create_tables.sql.sql`
2. `DML/insert_data.sql`
3. `DML/validation.sql`

---
