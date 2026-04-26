# Dairy Farm Shop Management System

A PHP and MySQL web application for managing a small dairy shop. It supports admin login, products, categories, supplier companies, invoices, and sales reports.

## Features

- Admin authentication with bcrypt password verification
- Customer registration and login
- Customer storefront with product cards and visual sections
- Customer cart and order placement flow
- Dashboard with product, category, company, and order counts
- Category and company management
- Product create, edit, list, and delete workflows
- Invoice generation with multiple line items
- Printable invoice view
- Sales report with invoice links
- CSRF protection on form actions
- Prepared SQL statements on core write/read paths
- Cypress end-to-end test coverage for main workflows

## Tech Stack

| Layer | Technology |
| --- | --- |
| Backend | PHP 8.x |
| Database | MySQL |
| Frontend | HTML, CSS, vanilla JavaScript |
| Web server | Apache / XAMPP |
| Containers | Docker |
| Testing | Cypress |
| CI/CD | Jenkins |

## Local Setup

1. Install XAMPP with Apache and MySQL.
2. Start Apache and MySQL from the XAMPP Control Panel.
3. Open `http://localhost/phpmyadmin`.
4. Create a database named `dfsms`.
5. Import `database/schema.sql`.
6. Copy the `app/` folder into `C:\xampp\htdocs\dfsms\`.
7. Open `http://localhost/dfsms/`.

Default login:

```text
Username: admin
Password: admin123
```

Customer users can register from:

```text
http://localhost/dfsms/customer-register.php
```

If you already imported the old database before customer login was added, import this file once:

```text
database/customer-auth-migration.sql
```

Customer order flow:

```text
Customer login/register -> Shop -> Add to cart -> Cart -> Place order -> Invoice generated
```

## Configuration

The app reads these optional environment variables:

```text
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=dfsms
APP_ENV=development
```

See `.env.example` for the same values in template form.

## Database Notes

The schema creates:

- `tbladmin`
- `tblcategory`
- `tblcompany`
- `tblproducts`
- `tblorders`

Product prices use `DECIMAL(10,2)`, and customer contact numbers are stored as text so leading zeroes are preserved.

## Cypress Tests

Install Cypress in the project or globally, then run:

```bash
npx cypress open
```

Or run the E2E spec directly:

```bash
npx cypress run --browser chrome --spec cypress/e2e/dfsms.spec.cy.js
```

The tests expect the app at:

```text
http://localhost/dfsms
```

## Docker Local Setup

Run the full app with PHP-Apache and MySQL containers:

```bash
docker compose up -d --build
```

Open:

```text
http://localhost:8080/dfsms/
```

Stop containers:

```bash
docker compose down
```

Remove the database volume and start fresh:

```bash
docker compose down -v
docker compose up -d --build
```

## Docker Image CI/CD

This repo includes:

- `Dockerfile`
- `docker-compose.yml`
- `docker-compose.prod.yml`
- `.github/workflows/docker-image.yml`
- `Jenkinsfile.docker`
- `Jenkinsfile.docker-linux`

For GitHub Actions Docker Hub publishing, add these GitHub repository secrets:

```text
DOCKERHUB_USERNAME
DOCKERHUB_TOKEN
```

On every push to `main`, GitHub Actions builds and pushes:

```text
jatindutt/dairy-farm-shop:latest
```

For Jenkins Docker publishing, create a Jenkins credential:

```text
ID: dockerhub-creds
Type: Username with password
Username: Docker Hub username
Password: Docker Hub access token
```

Then use:

```text
Jenkinsfile.docker       # Windows Jenkins
Jenkinsfile.docker-linux # Ubuntu/Linux Jenkins
```

## EC2 Docker Deployment

Recommended Docker EC2 option: Ubuntu Server.

1. Launch an EC2 instance.
2. Open inbound security group ports:
   - `22` SSH from your IP
   - `80` HTTP from anywhere
3. SSH into EC2.
4. Install Docker and Docker Compose plugin.
5. Clone this repo.
6. Create `.env.docker` from `.env.docker.example`.
7. Pull and start containers.

Example EC2 commands:

```bash
sudo apt update
sudo apt install -y docker.io docker-compose-plugin git
sudo usermod -aG docker ubuntu
newgrp docker

git clone https://github.com/JatinDuttt/Dairy-Farm-Shop-Management-System.git
cd Dairy-Farm-Shop-Management-System

cp .env.docker.example .env.docker
nano .env.docker

docker compose --env-file .env.docker -f docker-compose.prod.yml pull
docker compose --env-file .env.docker -f docker-compose.prod.yml up -d
```

Open:

```text
http://EC2_PUBLIC_IP/dfsms/
```

To deploy a new image after CI pushes it:

```bash
cd Dairy-Farm-Shop-Management-System
docker compose --env-file .env.docker -f docker-compose.prod.yml pull
docker compose --env-file .env.docker -f docker-compose.prod.yml up -d
```

## XAMPP Deployment

For a simple Windows EC2 deployment:

1. Install XAMPP, Git, Node.js, and Jenkins.
2. Pull the repository onto the instance.
3. Import `database/schema.sql` into MySQL.
4. Copy `app/` into Apache's `htdocs/dfsms`.
5. Configure Jenkins jobs to pull, deploy, and run Cypress tests.
6. Open HTTP port `80` in the EC2 security group.

Restrict RDP port `3389` to your own IP address.
