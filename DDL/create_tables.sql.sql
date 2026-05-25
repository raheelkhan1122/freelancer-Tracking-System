CREATE DATABASE IF NOT EXISTS freelance_project_tracking;
USE freelance_project_tracking;

DROP TABLE IF EXISTS PAYMENT;
DROP TABLE IF EXISTS TASK;
DROP TABLE IF EXISTS PROJECT;
DROP TABLE IF EXISTS CLIENT;

CREATE TABLE CLIENT (
    ClientID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255) NOT NULL,
    Email VARCHAR(255) NOT NULL UNIQUE,
    Phone VARCHAR(50)
);

CREATE TABLE PROJECT (
    ProjectID INT AUTO_INCREMENT PRIMARY KEY,
    ClientID INT NOT NULL,
    Title VARCHAR(255) NOT NULL,
    Description TEXT,
    Deadline DATE NOT NULL,
    Status VARCHAR(50) DEFAULT 'Pending',
    CONSTRAINT FK_Project_Client FOREIGN KEY (ClientID) 
        REFERENCES CLIENT(ClientID) ON DELETE CASCADE,
    CONSTRAINT CHK_Project_Status CHECK (Status IN ('Pending', 'In Progress', 'Completed', 'Cancelled'))
);

CREATE TABLE TASK (
    TaskID INT AUTO_INCREMENT PRIMARY KEY,
    ProjectID INT NOT NULL,
    Title VARCHAR(255) NOT NULL,
    Description TEXT,
    Deadline DATE,
    Status VARCHAR(50) DEFAULT 'Pending',
    CONSTRAINT FK_Task_Project FOREIGN KEY (ProjectID) 
        REFERENCES PROJECT(ProjectID) ON DELETE CASCADE,
    CONSTRAINT CHK_Task_Status CHECK (Status IN ('Pending', 'In Progress', 'Completed'))
);

CREATE TABLE PAYMENT (
    PaymentID INT AUTO_INCREMENT PRIMARY KEY,
    ProjectID INT NOT NULL,
    Amount DECIMAL(10, 2) NOT NULL,
    PaymentDate DATE,
    Status VARCHAR(50) DEFAULT 'Pending',
    Method VARCHAR(50),
    CONSTRAINT FK_Payment_Project FOREIGN KEY (ProjectID) 
        REFERENCES PROJECT(ProjectID) ON DELETE CASCADE,
    CONSTRAINT CHK_Payment_Amount CHECK (Amount > 0),
    CONSTRAINT CHK_Payment_Status CHECK (Status IN ('Pending', 'Cleared', 'Failed', 'Refunded'))
);
