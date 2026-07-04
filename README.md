
# Freelance Project Tracking System

> A PHP & MySQL web application for managing freelance clients, projects, tasks, and payments.

## Overview

The **Freelance Project Tracking System (FPTS)** is a Database Systems Lab project developed to help freelancers organize their work through a simple, responsive web application. The system provides CRUD operations for Clients, Projects, Tasks, and Payments while storing all information in a normalized MySQL database.

## Technology Stack

| Layer | Technology |
|-------|------------|
| Frontend | HTML5, CSS3, JavaScript (Fetch API) |
| Backend | PHP 8 |
| Database | MySQL |
| Server | Apache (XAMPP) |

## Features

- Dashboard with project statistics
- Client Management
- Project Management
- Task Management
- Payment Management
- Responsive UI
- MySQL relational database
- Foreign Key constraints
- Cascading deletes

## Project Structure

```text
freelance-project-tracking/
├── index.php
├── config/
│   └── database.php
├── api/
├── pages/
├── assets/
│   ├── css/
│   └── js/
├── database/
│   ├── create_tables.sql
│   └── insert_data.sql
└── README.md
```

## Database

Tables:

- CLIENT
- PROJECT
- TASK
- PAYMENT

Relationships:

- Client → Projects (1:N)
- Project → Tasks (1:N)
- Project → Payments (1:N)

## Installation

1. Install XAMPP.
2. Start Apache and MySQL.
3. Copy the project into `C:\xampp\htdocs\freelance-project-tracking`.
4. Import `create_tables.sql` and `insert_data.sql` using phpMyAdmin.
5. Open:

```
http://localhost/freelance-project-tracking/
```

## API

```
GET    /api/clients/read.php
POST   /api/clients/create.php
PUT    /api/clients/update.php?id=1
DELETE /api/clients/delete.php?id=1
```

Equivalent endpoints exist for Projects, Tasks, Payments, and Dashboard.

## Contributors

- Raheel Ahmed
- Muhammad Rahimullah

## License

MIT License
