-- =====================================================================
-- FREELANCE PROJECT TRACKING SYSTEM (FPTS)
-- File: database/create_tables.sql
-- 3NF schema: CLIENT, PROJECT, TASK, PAYMENT
-- =====================================================================

CREATE DATABASE IF NOT EXISTS freelance_project_tracking;
USE freelance_project_tracking;

DROP TABLE IF EXISTS PAYMENT;
DROP TABLE IF EXISTS TASK;
DROP TABLE IF EXISTS PROJECT;
DROP TABLE IF EXISTS CLIENT;

-- ---------------------------------------------------------------------
-- CLIENT
-- ---------------------------------------------------------------------
CREATE TABLE CLIENT (
    ClientID     INT AUTO_INCREMENT PRIMARY KEY,
    ClientName   VARCHAR(100) NOT NULL,
    Email        VARCHAR(100) NOT NULL UNIQUE,
    Phone        VARCHAR(20),
    CreatedDate  DATE NOT NULL DEFAULT (CURRENT_DATE)
);

-- ---------------------------------------------------------------------
-- PROJECT
-- ---------------------------------------------------------------------
CREATE TABLE PROJECT (
    ProjectID    INT AUTO_INCREMENT PRIMARY KEY,
    ClientID     INT NOT NULL,
    Title        VARCHAR(150) NOT NULL,
    Description  TEXT,
    Deadline     DATE,
    Status       VARCHAR(20) NOT NULL DEFAULT 'Pending',
    CreatedDate  DATE NOT NULL DEFAULT (CURRENT_DATE),

    CONSTRAINT fk_project_client
        FOREIGN KEY (ClientID) REFERENCES CLIENT(ClientID)
        ON DELETE CASCADE,

    CONSTRAINT chk_project_status
        CHECK (Status IN ('Pending','In Progress','Completed','Cancelled'))
);

-- ---------------------------------------------------------------------
-- TASK
-- ---------------------------------------------------------------------
CREATE TABLE TASK (
    TaskID       INT AUTO_INCREMENT PRIMARY KEY,
    ProjectID    INT NOT NULL,
    Title        VARCHAR(150) NOT NULL,
    Description  TEXT,
    Deadline     DATE,
    Status       VARCHAR(20) NOT NULL DEFAULT 'Pending',
    CreatedDate  DATE NOT NULL DEFAULT (CURRENT_DATE),

    CONSTRAINT fk_task_project
        FOREIGN KEY (ProjectID) REFERENCES PROJECT(ProjectID)
        ON DELETE CASCADE,

    CONSTRAINT chk_task_status
        CHECK (Status IN ('Pending','In Progress','Completed'))
);

-- ---------------------------------------------------------------------
-- PAYMENT
-- ---------------------------------------------------------------------
CREATE TABLE PAYMENT (
    PaymentID    INT AUTO_INCREMENT PRIMARY KEY,
    ProjectID    INT NOT NULL,
    Amount       DECIMAL(10,2) NOT NULL,
    PaymentDate  DATE,
    Method       VARCHAR(30) NOT NULL DEFAULT 'Bank Transfer',
    Status       VARCHAR(20) NOT NULL DEFAULT 'Pending',
    CreatedDate  DATE NOT NULL DEFAULT (CURRENT_DATE),

    CONSTRAINT fk_payment_project
        FOREIGN KEY (ProjectID) REFERENCES PROJECT(ProjectID)
        ON DELETE CASCADE,

    CONSTRAINT chk_payment_amount
        CHECK (Amount > 0),

    CONSTRAINT chk_payment_status
        CHECK (Status IN ('Pending','Cleared','Failed','Refunded')),

    CONSTRAINT chk_payment_method
        CHECK (Method IN ('PayPal','Stripe','Bank Transfer','Crypto','Cash'))
);

-- ---------------------------------------------------------------------
-- Indexes on foreign keys
-- ---------------------------------------------------------------------
CREATE INDEX idx_project_client  ON PROJECT(ClientID);
CREATE INDEX idx_task_project    ON TASK(ProjectID);
CREATE INDEX idx_payment_project ON PAYMENT(ProjectID);
