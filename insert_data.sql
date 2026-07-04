-- =====================================================================
-- FREELANCE PROJECT TRACKING SYSTEM (FPTS)
-- File: database/insert_data.sql
-- Seed data: 20 clients, 30 projects, 60 tasks, 40 payments
-- =====================================================================

USE freelance_project_tracking;

-- CLIENT (20 rows)
INSERT INTO CLIENT (ClientName, Email, Phone, CreatedDate) VALUES
('Hamza Schmidt', 'hamza.schmidt1@hotmail.com', '+92-338-9520162', '2026-05-07'),
('Jennifer Muller', 'jennifer.muller2@hotmail.com', '+92-349-4123476', '2026-01-15'),
('Elizabeth Jones', 'elizabeth.jones3@gmail.com', '+92-344-1702635', '2026-05-13'),
('Hamza Rodriguez', 'hamza.rodriguez4@yahoo.com', '+92-349-1251670', '2026-05-03'),
('Mary Johnson', 'mary.johnson5@yahoo.com', '+92-325-1504702', '2026-04-11'),
('Hamza Garcia', 'hamza.garcia6@yahoo.com', '+92-343-4919923', '2026-06-10'),
('Zara Smith', 'zara.smith7@gmail.com', '+92-339-5666799', '2026-04-18'),
('Yusuf Williams', 'yusuf.williams8@outlook.com', '+92-330-4853154', '2026-05-10'),
('James Williams', 'james.williams9@freelancehub.com', '+92-316-7717594', '2026-01-28'),
('Elizabeth Wang', 'elizabeth.wang10@gmail.com', '+92-311-1009142', '2026-02-07'),
('Yusuf Johnson', 'yusuf.johnson11@hotmail.com', '+92-334-7667674', '2026-04-03'),
('Ali Malik', 'ali.malik12@outlook.com', '+92-331-2462037', '2026-03-11'),
('James Nguyen', 'james.nguyen13@gmail.com', '+92-318-5133703', '2026-06-04'),
('James Johnson', 'james.johnson14@hotmail.com', '+92-341-3980944', '2026-06-18'),
('Michael Silva', 'michael.silva15@freelancehub.com', '+92-322-3197263', '2026-04-21'),
('Bilal Brown', 'bilal.brown16@hotmail.com', '+92-336-4571293', '2026-01-09'),
('Kim Garcia', 'kim.garcia17@outlook.com', '+92-311-4535130', '2026-02-13'),
('Kim Rodriguez', 'kim.rodriguez18@freelancehub.com', '+92-316-1706451', '2026-02-07'),
('Hamza Sheikh', 'hamza.sheikh19@gmail.com', '+92-349-6518956', '2026-03-13'),
('John Williams', 'john.williams20@gmail.com', '+92-323-5077080', '2026-01-20');

-- PROJECT (30 rows)
INSERT INTO PROJECT (ClientID, Title, Description, Deadline, Status, CreatedDate) VALUES
(12, 'Chatbot Development #1', 'Full delivery scope for chatbot development #1.', '2026-05-19', 'Completed', '2026-04-27'),
(19, 'SEO Optimization Campaign #2', 'Full delivery scope for seo optimization campaign #2.', '2026-05-21', 'Completed', '2026-02-10'),
(8, 'Video Editing Project #3', 'Full delivery scope for video editing project #3.', '2026-06-06', 'In Progress', '2026-05-07'),
(13, 'Payment Gateway Integration #4', 'Full delivery scope for payment gateway integration #4.', '2026-09-02', 'Completed', '2026-01-04'),
(2, 'WordPress Theme Customization #5', 'Full delivery scope for wordpress theme customization #5.', '2026-06-24', 'Cancelled', '2026-04-09'),
(14, 'Video Editing Project #6', 'Full delivery scope for video editing project #6.', '2026-05-24', 'In Progress', '2026-01-05'),
(8, 'Payment Gateway Integration #7', 'Full delivery scope for payment gateway integration #7.', '2026-04-09', 'Completed', '2026-02-07'),
(1, 'Logo & Branding Package #8', 'Full delivery scope for logo & branding package #8.', '2026-10-08', 'In Progress', '2026-01-02'),
(6, 'CRM System Build #9', 'Full delivery scope for crm system build #9.', '2026-05-03', 'In Progress', '2026-03-05'),
(15, 'Portfolio Website #10', 'Full delivery scope for portfolio website #10.', '2026-05-19', 'Completed', '2026-01-01'),
(16, 'Chatbot Development #11', 'Full delivery scope for chatbot development #11.', '2026-03-01', 'Completed', '2026-05-21'),
(3, 'Payment Gateway Integration #12', 'Full delivery scope for payment gateway integration #12.', '2026-07-11', 'Pending', '2026-02-03'),
(3, 'Booking System Development #13', 'Full delivery scope for booking system development #13.', '2026-03-24', 'In Progress', '2026-02-26'),
(11, 'Chatbot Development #14', 'Full delivery scope for chatbot development #14.', '2026-10-03', 'Pending', '2026-04-26'),
(1, 'Payment Gateway Integration #15', 'Full delivery scope for payment gateway integration #15.', '2026-09-13', 'Completed', '2026-05-01'),
(20, 'Logo & Branding Package #16', 'Full delivery scope for logo & branding package #16.', '2026-04-09', 'Pending', '2026-04-24'),
(11, 'Cloud Server Migration #17', 'Full delivery scope for cloud server migration #17.', '2026-10-15', 'Cancelled', '2026-04-27'),
(18, 'Logo & Branding Package #18', 'Full delivery scope for logo & branding package #18.', '2026-03-10', 'In Progress', '2026-05-03'),
(16, 'Website Redesign #19', 'Full delivery scope for website redesign #19.', '2026-04-16', 'In Progress', '2026-05-22'),
(16, 'UI/UX Design Overhaul #20', 'Full delivery scope for ui/ux design overhaul #20.', '2026-08-10', 'Completed', '2026-02-22'),
(20, 'API Integration #21', 'Full delivery scope for api integration #21.', '2026-08-22', 'In Progress', '2026-04-16'),
(8, 'Portfolio Website #22', 'Full delivery scope for portfolio website #22.', '2026-07-07', 'In Progress', '2026-04-26'),
(7, 'API Integration #23', 'Full delivery scope for api integration #23.', '2026-08-07', 'In Progress', '2026-02-05'),
(16, 'Chatbot Development #24', 'Full delivery scope for chatbot development #24.', '2026-03-23', 'Completed', '2026-01-09'),
(6, 'E-commerce Store Setup #25', 'Full delivery scope for e-commerce store setup #25.', '2026-07-07', 'In Progress', '2026-04-13');

INSERT INTO PROJECT (ClientID, Title, Description, Deadline, Status, CreatedDate) VALUES
(17, 'Payment Gateway Integration #26', 'Full delivery scope for payment gateway integration #26.', '2026-10-11', 'Completed', '2026-01-27'),
(2, 'UI/UX Design Overhaul #27', 'Full delivery scope for ui/ux design overhaul #27.', '2026-03-22', 'Completed', '2026-03-19'),
(12, 'CRM System Build #28', 'Full delivery scope for crm system build #28.', '2026-03-21', 'Completed', '2026-02-13'),
(15, 'API Integration #29', 'Full delivery scope for api integration #29.', '2026-07-08', 'Pending', '2026-02-26'),
(2, 'E-commerce Store Setup #30', 'Full delivery scope for e-commerce store setup #30.', '2026-08-03', 'In Progress', '2026-02-07');

-- TASK (60 rows)
INSERT INTO TASK (ProjectID, Title, Description, Deadline, Status, CreatedDate) VALUES
(27, 'Bug Fixing Round 2', 'Bug Fixing Round 2 for the associated project deliverables.', '2026-08-18', 'Pending', '2026-06-02'),
(6, 'Frontend Development', 'Frontend Development for the associated project deliverables.', '2026-09-17', 'Pending', '2026-05-25'),
(6, 'Bug Fixing Round 1', 'Bug Fixing Round 1 for the associated project deliverables.', '2026-04-03', 'Completed', '2026-04-24'),
(13, 'Database Schema Design', 'Database Schema Design for the associated project deliverables.', '2026-01-13', 'In Progress', '2026-05-19'),
(21, 'Mobile Responsiveness Check', 'Mobile Responsiveness Check for the associated project deliverables.', NULL, 'In Progress', '2026-03-15'),
(11, 'SEO Audit', 'SEO Audit for the associated project deliverables.', '2026-11-23', 'Completed', '2026-01-07'),
(8, 'Mobile Responsiveness Check', 'Mobile Responsiveness Check for the associated project deliverables.', '2026-05-18', 'In Progress', '2026-03-09'),
(30, 'Final QA Pass', 'Final QA Pass for the associated project deliverables.', '2026-02-02', 'Completed', '2026-04-11'),
(18, 'Bug Fixing Round 1', 'Bug Fixing Round 1 for the associated project deliverables.', '2026-01-07', 'Completed', '2026-01-14'),
(26, 'Requirement Gathering', 'Requirement Gathering for the associated project deliverables.', '2026-03-16', 'Pending', '2026-02-17'),
(29, 'Final QA Pass', 'Final QA Pass for the associated project deliverables.', '2026-08-16', 'In Progress', '2026-05-23'),
(3, 'Documentation Writing', 'Documentation Writing for the associated project deliverables.', '2026-05-27', 'Pending', '2026-06-18'),
(21, 'Database Schema Design', 'Database Schema Design for the associated project deliverables.', NULL, 'In Progress', '2026-05-09'),
(10, 'SEO Audit', 'SEO Audit for the associated project deliverables.', NULL, 'In Progress', '2026-05-07'),
(10, 'UI Polish & Styling', 'UI Polish & Styling for the associated project deliverables.', '2026-05-19', 'Pending', '2026-04-07'),
(14, 'Deployment to Server', 'Deployment to Server for the associated project deliverables.', '2026-07-01', 'Pending', '2026-05-02'),
(17, 'Mobile Responsiveness Check', 'Mobile Responsiveness Check for the associated project deliverables.', NULL, 'In Progress', '2026-05-04'),
(16, 'Wireframe Design', 'Wireframe Design for the associated project deliverables.', '2026-09-15', 'Completed', '2026-04-26'),
(30, 'Bug Fixing Round 1', 'Bug Fixing Round 1 for the associated project deliverables.', '2026-03-11', 'Pending', '2026-04-27'),
(30, 'Mobile Responsiveness Check', 'Mobile Responsiveness Check for the associated project deliverables.', '2026-02-07', 'Completed', '2026-04-20'),
(1, 'Mobile Responsiveness Check', 'Mobile Responsiveness Check for the associated project deliverables.', '2026-01-02', 'Pending', '2026-02-05'),
(8, 'Requirement Gathering', 'Requirement Gathering for the associated project deliverables.', '2026-06-08', 'Completed', '2026-05-16'),
(4, 'Bug Fixing Round 2', 'Bug Fixing Round 2 for the associated project deliverables.', '2026-09-20', 'Completed', '2026-03-23'),
(7, 'Final QA Pass', 'Final QA Pass for the associated project deliverables.', '2026-07-21', 'In Progress', '2026-04-27'),
(17, 'Content Population', 'Content Population for the associated project deliverables.', '2026-11-18', 'Pending', '2026-06-07');

INSERT INTO TASK (ProjectID, Title, Description, Deadline, Status, CreatedDate) VALUES
(17, 'Frontend Development', 'Frontend Development for the associated project deliverables.', '2026-03-08', 'Completed', '2026-06-21'),
(26, 'Client Review Meeting', 'Client Review Meeting for the associated project deliverables.', '2026-06-07', 'Completed', '2026-02-25'),
(7, 'Mobile Responsiveness Check', 'Mobile Responsiveness Check for the associated project deliverables.', NULL, 'Pending', '2026-02-24'),
(3, 'Backend API Development', 'Backend API Development for the associated project deliverables.', '2026-07-18', 'In Progress', '2026-06-05'),
(7, 'Bug Fixing Round 1', 'Bug Fixing Round 1 for the associated project deliverables.', NULL, 'In Progress', '2026-01-07'),
(19, 'SEO Audit', 'SEO Audit for the associated project deliverables.', NULL, 'In Progress', '2026-03-04'),
(23, 'Deployment to Server', 'Deployment to Server for the associated project deliverables.', '2026-11-27', 'In Progress', '2026-02-26'),
(3, 'Bug Fixing Round 2', 'Bug Fixing Round 2 for the associated project deliverables.', '2026-09-20', 'Pending', '2026-05-19'),
(16, 'Database Schema Design', 'Database Schema Design for the associated project deliverables.', '2026-03-27', 'Pending', '2026-02-10'),
(30, 'SEO Audit', 'SEO Audit for the associated project deliverables.', '2026-11-15', 'Pending', '2026-01-25'),
(4, 'Client Review Meeting', 'Client Review Meeting for the associated project deliverables.', '2026-06-14', 'In Progress', '2026-02-20'),
(12, 'Requirement Gathering', 'Requirement Gathering for the associated project deliverables.', '2026-04-06', 'In Progress', '2026-04-15'),
(12, 'Final QA Pass', 'Final QA Pass for the associated project deliverables.', NULL, 'In Progress', '2026-05-06'),
(4, 'Deployment to Server', 'Deployment to Server for the associated project deliverables.', '2026-02-26', 'Completed', '2026-06-28'),
(30, 'SEO Audit', 'SEO Audit for the associated project deliverables.', '2026-04-17', 'Completed', '2026-05-11'),
(26, 'Documentation Writing', 'Documentation Writing for the associated project deliverables.', NULL, 'Pending', '2026-06-24'),
(25, 'Database Schema Design', 'Database Schema Design for the associated project deliverables.', '2026-06-18', 'Completed', '2026-06-12'),
(25, 'Bug Fixing Round 1', 'Bug Fixing Round 1 for the associated project deliverables.', '2026-03-03', 'Completed', '2026-03-10'),
(16, 'Wireframe Design', 'Wireframe Design for the associated project deliverables.', '2026-11-20', 'Pending', '2026-01-08'),
(9, 'SEO Audit', 'SEO Audit for the associated project deliverables.', '2026-07-19', 'Pending', '2026-02-18'),
(3, 'Bug Fixing Round 1', 'Bug Fixing Round 1 for the associated project deliverables.', '2026-04-23', 'In Progress', '2026-01-25'),
(13, 'Content Population', 'Content Population for the associated project deliverables.', '2026-04-09', 'Pending', '2026-04-13'),
(17, 'Requirement Gathering', 'Requirement Gathering for the associated project deliverables.', '2026-01-04', 'Completed', '2026-03-09'),
(9, 'Mobile Responsiveness Check', 'Mobile Responsiveness Check for the associated project deliverables.', '2026-07-17', 'In Progress', '2026-05-28'),
(4, 'SEO Audit', 'SEO Audit for the associated project deliverables.', '2026-09-20', 'In Progress', '2026-06-25');

INSERT INTO TASK (ProjectID, Title, Description, Deadline, Status, CreatedDate) VALUES
(2, 'Bug Fixing Round 1', 'Bug Fixing Round 1 for the associated project deliverables.', '2026-08-06', 'Completed', '2026-04-18'),
(20, 'Content Population', 'Content Population for the associated project deliverables.', NULL, 'Pending', '2026-04-10'),
(17, 'Bug Fixing Round 1', 'Bug Fixing Round 1 for the associated project deliverables.', '2026-05-16', 'In Progress', '2026-06-09'),
(18, 'Mobile Responsiveness Check', 'Mobile Responsiveness Check for the associated project deliverables.', '2026-05-01', 'Pending', '2026-01-25'),
(8, 'Content Population', 'Content Population for the associated project deliverables.', '2026-11-13', 'Pending', '2026-01-11'),
(24, 'Bug Fixing Round 1', 'Bug Fixing Round 1 for the associated project deliverables.', NULL, 'Pending', '2026-03-03'),
(26, 'Mobile Responsiveness Check', 'Mobile Responsiveness Check for the associated project deliverables.', '2026-05-25', 'Pending', '2026-02-02'),
(17, 'Wireframe Design', 'Wireframe Design for the associated project deliverables.', '2026-08-05', 'Completed', '2026-02-20'),
(23, 'Wireframe Design', 'Wireframe Design for the associated project deliverables.', '2026-02-07', 'Pending', '2026-01-12'),
(17, 'Database Schema Design', 'Database Schema Design for the associated project deliverables.', '2026-11-14', 'Pending', '2026-04-20');

-- PAYMENT (40 rows)
INSERT INTO PAYMENT (ProjectID, Amount, PaymentDate, Method, Status, CreatedDate) VALUES
(9, 3215.99, '2026-09-27', 'Crypto', 'Cleared', '2026-03-10'),
(24, 687.91, NULL, 'Crypto', 'Pending', '2026-09-20'),
(28, 1827.96, '2026-04-21', 'Bank Transfer', 'Cleared', '2026-05-25'),
(8, 989.59, '2026-04-22', 'Stripe', 'Cleared', '2026-01-21'),
(2, 96.91, '2026-01-04', 'Bank Transfer', 'Cleared', '2026-04-18'),
(9, 408.84, '2026-03-18', 'PayPal', 'Refunded', '2026-04-21'),
(12, 2445.85, '2026-03-27', 'Bank Transfer', 'Pending', '2026-02-26'),
(4, 2264.18, NULL, 'Cash', 'Failed', '2026-08-14'),
(24, 1327.46, '2026-06-10', 'Stripe', 'Cleared', '2026-09-25'),
(24, 4139.48, '2026-06-19', 'Stripe', 'Cleared', '2026-02-15'),
(29, 1654.56, '2026-08-27', 'Cash', 'Pending', '2026-08-10'),
(27, 3888.74, '2026-02-23', 'PayPal', 'Cleared', '2026-05-16'),
(24, 3832.54, '2026-03-08', 'Bank Transfer', 'Cleared', '2026-06-22'),
(24, 1668.98, '2026-08-19', 'PayPal', 'Cleared', '2026-05-15'),
(17, 4025.09, '2026-04-13', 'Bank Transfer', 'Cleared', '2026-09-08'),
(3, 1840.39, NULL, 'Bank Transfer', 'Pending', '2026-07-26'),
(7, 3707.22, '2026-03-06', 'Bank Transfer', 'Failed', '2026-02-25'),
(15, 4410.45, '2026-01-18', 'Bank Transfer', 'Failed', '2026-03-19'),
(10, 4833.76, '2026-09-03', 'Crypto', 'Pending', '2026-06-03'),
(9, 3698.45, NULL, 'Bank Transfer', 'Pending', '2026-03-04'),
(24, 4090.45, '2026-09-11', 'Crypto', 'Pending', '2026-08-13'),
(12, 1722.18, '2026-08-16', 'Bank Transfer', 'Cleared', '2026-09-26'),
(24, 408.72, '2026-07-25', 'Cash', 'Refunded', '2026-06-28'),
(1, 4039.24, NULL, 'PayPal', 'Failed', '2026-01-17'),
(12, 3564.93, '2026-07-05', 'PayPal', 'Cleared', '2026-04-24');

INSERT INTO PAYMENT (ProjectID, Amount, PaymentDate, Method, Status, CreatedDate) VALUES
(6, 4955.5, '2026-06-07', 'Crypto', 'Cleared', '2026-07-14'),
(17, 1495.52, '2026-02-10', 'PayPal', 'Cleared', '2026-03-25'),
(30, 938.19, '2026-08-26', 'PayPal', 'Failed', '2026-01-11'),
(4, 1028.24, '2026-08-20', 'Bank Transfer', 'Pending', '2026-09-08'),
(15, 965.69, '2026-05-21', 'Bank Transfer', 'Cleared', '2026-09-27'),
(18, 2423.48, '2026-07-11', 'Bank Transfer', 'Cleared', '2026-09-25'),
(3, 2438.94, '2026-04-05', 'Stripe', 'Cleared', '2026-07-17'),
(26, 2603.3, '2026-04-01', 'Bank Transfer', 'Failed', '2026-08-12'),
(15, 3994.03, '2026-03-14', 'Crypto', 'Cleared', '2026-03-25'),
(11, 766.46, NULL, 'Cash', 'Pending', '2026-05-17'),
(18, 312.06, '2026-03-27', 'Cash', 'Failed', '2026-03-03'),
(12, 1386.11, '2026-08-03', 'Cash', 'Pending', '2026-09-28'),
(17, 1552.79, '2026-02-26', 'Stripe', 'Cleared', '2026-08-07'),
(2, 2739.69, '2026-05-11', 'Cash', 'Cleared', '2026-05-13'),
(3, 2804.13, '2026-02-12', 'Crypto', 'Cleared', '2026-02-22');
