-- Dairy Farm Shop Management System
-- Database: dfsms

CREATE DATABASE IF NOT EXISTS dfsms;
USE dfsms;

-- Admin table
CREATE TABLE IF NOT EXISTS tbladmin (
    ID INT(5) AUTO_INCREMENT PRIMARY KEY,
    AdminName VARCHAR(45) NOT NULL,
    UserName CHAR(45) NOT NULL UNIQUE,
    MobileNumber BIGINT(11),
    Email VARCHAR(120),
    Password VARCHAR(120) NOT NULL,
    AdminRegdate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Customer table
CREATE TABLE IF NOT EXISTS tblcustomers (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    FullName VARCHAR(150) NOT NULL,
    Email VARCHAR(150) NOT NULL UNIQUE,
    MobileNumber VARCHAR(20) NOT NULL,
    Password VARCHAR(255) NOT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Category table
CREATE TABLE IF NOT EXISTS tblcategory (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    CategoryName VARCHAR(200) NOT NULL,
    CategoryCode VARCHAR(50),
    PostingDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Company table
CREATE TABLE IF NOT EXISTS tblcompany (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    CompanyName VARCHAR(150) NOT NULL,
    PostingDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE IF NOT EXISTS tblproducts (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    CategoryName VARCHAR(150),
    CompanyName VARCHAR(150),
    ProductName VARCHAR(150) NOT NULL,
    ProductPrice DECIMAL(10,2),
    PostingDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdationDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE IF NOT EXISTS tblorders (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    ProductId INT(11),
    Quantity INT(11),
    InvoiceNumber INT(11),
    CustomerName VARCHAR(150),
    CustomerContactNo VARCHAR(20),
    PaymentMode VARCHAR(100),
    InvoiceGenDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default admin (password: admin123 hashed with bcrypt cost 12)
-- To generate hash: password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 12])
INSERT INTO tbladmin (AdminName, UserName, Email, Password)
VALUES ('Administrator', 'admin', 'admin@dfsms.com', '$2y$12$yj3BF7tO7Q.S336s1fvqwOQP.L/ENHEY0vfmnj.RnJXbByN/x4q5G');

-- Sample categories
INSERT INTO tblcategory (CategoryName, CategoryCode) VALUES
('Milk Products', 'MILK'),
('Cheese & Paneer', 'CHEESE'),
('Butter & Ghee', 'BUTTER'),
('Yogurt & Curd', 'YOGURT'),
('Ice Cream', 'ICECREAM');

-- Sample companies
INSERT INTO tblcompany (CompanyName) VALUES
('Amul'), ('Mother Dairy'), ('Nandini'), ('Nestle'), ('Britannia');

-- Sample products
INSERT INTO tblproducts (CategoryName, CompanyName, ProductName, ProductPrice) VALUES
('Milk Products', 'Amul', 'Full Cream Milk 1L', 60),
('Milk Products', 'Mother Dairy', 'Toned Milk 500ml', 26),
('Cheese & Paneer', 'Amul', 'Processed Cheese 200g', 110),
('Butter & Ghee', 'Amul', 'Butter Salted 100g', 55),
('Yogurt & Curd', 'Mother Dairy', 'Mishti Doi 200ml', 35),
('Ice Cream', 'Amul', 'Vanilla Ice Cream 500ml', 120);
