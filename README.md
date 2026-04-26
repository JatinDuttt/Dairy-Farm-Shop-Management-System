# Working on Cloud, AWS and DevOps with Dairy Farm Shop Management System

This project is a final-year B.Tech mini project that combines a Dairy Farm Shop Management System with a simple AWS cloud deployment workflow. The application is intentionally lightweight, so the main focus can be explained clearly: development, version control, cloud hosting, and deployment on an AWS EC2 instance.

## Project Overview

The Dairy Farm Shop Management System is a static web application built with HTML, CSS, and JavaScript. It manages dairy product information, inventory status, customer orders, billing, and local order history. The project can be hosted on AWS EC2 using Nginx, making it suitable for demonstrating basic cloud and DevOps concepts.

## Main Objectives

- Build a dairy shop management web application
- Track products, prices, stock, and low-stock status
- Create customer orders and calculate bills dynamically
- Store order history in browser `localStorage`
- Deploy the project on an AWS EC2 instance
- Explain a simple DevOps workflow using Git, EC2, Nginx, and manual deployment

## Project Modules

- Product catalog with price, unit, stock, and availability status
- Inventory table with low-stock alert logic
- Customer order form with name, phone, and address validation
- Dynamic bill calculation with subtotal, delivery charge, and grand total
- Order history saved in browser `localStorage`
- AWS and DevOps workflow section
- Responsive design for desktop and mobile screens

## Technology Used

- HTML5 for page structure
- CSS3 for responsive layout and styling
- JavaScript for product data, validation, billing, and order storage
- Git and GitHub for version control
- AWS EC2 for cloud hosting
- Nginx web server for deployment

## System Architecture

```text
User Browser
     |
     v
AWS EC2 Public IP
     |
     v
Nginx Web Server
     |
     v
Dairy Farm Website Files
```

## File Structure

```text
dairy-farm/
|-- index.html
|-- style.css
|-- script.js
`-- README.md
```

## How to Run Locally

Open `index.html` directly in any browser.

No database, Docker, Node.js, or package installation is required.

## AWS EC2 Deployment Steps

Launch an Ubuntu EC2 instance and allow these inbound rules in the security group:

```text
SSH  - Port 22 - Your IP
HTTP - Port 80 - Anywhere
```

Connect to EC2:

```bash
ssh -i your-key.pem ubuntu@YOUR_EC2_PUBLIC_IP
```

Install and start Nginx:

```bash
sudo apt update
sudo apt install nginx -y
sudo systemctl start nginx
sudo systemctl enable nginx
```

Upload the project files to the server using SCP:

```bash
scp -i your-key.pem index.html style.css script.js ubuntu@YOUR_EC2_PUBLIC_IP:/home/ubuntu/
```

Move files to the Nginx web directory:

```bash
sudo cp index.html style.css script.js /var/www/html/
sudo systemctl restart nginx
```

Open the deployed project:

```text
http://YOUR_EC2_PUBLIC_IP
```

## Simple DevOps Workflow

```text
Code Project
     |
     v
Test in Browser
     |
     v
Push to GitHub
     |
     v
Deploy to AWS EC2
     |
     v
Serve Website with Nginx
```

## Explanation for Viva

This project shows how a dairy farm shop can manage products, inventory, and customer orders using a web application. The cloud part is demonstrated by deploying the project on an AWS EC2 instance. The DevOps part is shown through version control with Git/GitHub and a deployment workflow where the latest project files are hosted through Nginx.

## Future Scope

- Add backend using PHP, Node.js, or Java Spring Boot
- Store products and orders in MySQL or MongoDB
- Add admin login
- Generate printable invoice
- Automate deployment using GitHub Actions or Jenkins
- Add monitoring using AWS CloudWatch
