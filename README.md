# Dairy Farm Shop Management System

This project is a Dairy Farm Shop Management System built with HTML, CSS, and JavaScript. The application focuses on dairy shop operations such as product listing, inventory status, customer order creation, billing, and order history.

AWS and DevOps are used separately for deployment and CI/CD explanation. The application remains a normal Dairy Farm Shop Management System, and AWS is only the hosting platform.

## Application Modules

- Product catalog with price, unit, stock, and availability status
- Inventory table with low-stock alert logic
- Customer order form with name, phone, and address validation
- Dynamic bill calculation with subtotal, delivery charge, and grand total
- Order history saved in browser `localStorage`
- Responsive design for desktop and mobile screens

## Technology Used in Application

- HTML5 for structure
- CSS3 for styling and responsive layout
- JavaScript for product data, validation, billing, and order history
- Browser `localStorage` for storing orders without a backend

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

## AWS Deployment

AWS is used only to deploy and host the Dairy Farm Shop Management System.

Deployment platform:

```text
AWS EC2 Ubuntu Instance
Nginx Web Server
HTTP Port 80
```

Deployment architecture:

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
Dairy Farm Shop Management System Files
```

Basic EC2 deployment steps:

```bash
sudo apt update
sudo apt install nginx -y
sudo systemctl start nginx
sudo systemctl enable nginx
```

Upload files from local system to EC2:

```bash
scp -i your-key.pem index.html style.css script.js ubuntu@YOUR_EC2_PUBLIC_IP:/home/ubuntu/
```

Move files to Nginx root directory:

```bash
sudo cp index.html style.css script.js /var/www/html/
sudo systemctl restart nginx
```

Open the project:

```text
http://YOUR_EC2_PUBLIC_IP
```

## CI/CD Pipeline Explanation

The CI/CD pipeline is separate from the application. It explains how updates can move from development to deployment.

```text
Developer writes code
        |
        v
Push code to GitHub
        |
        v
CI stage checks project files
        |
        v
CD stage connects to AWS EC2
        |
        v
Updated files are copied to /var/www/html/
        |
        v
Nginx serves the latest website
```

## DevOps Tools Used for Explanation

- Git for version control
- GitHub for source code repository
- GitHub Actions or Jenkins for CI/CD pipeline demonstration
- AWS EC2 for cloud hosting
- Nginx for serving the website

## Viva Explanation

The main project is a Dairy Farm Shop Management System. It manages products, inventory, customer orders, billing, and order history. AWS is used separately to deploy the project on an EC2 instance. DevOps is explained through a CI/CD pipeline where code is pushed to GitHub, checked, and then deployed to the EC2 server.

## Future Scope

- Add backend using PHP, Node.js, or Java Spring Boot
- Store products and orders in MySQL or MongoDB
- Add admin login
- Generate printable invoice
- Automate EC2 deployment using GitHub Actions or Jenkins
- Add AWS CloudWatch monitoring
