# Lab 13: Deploying a Secure Dockerized LAMP Stack with Amazon RDS and AWS Secrets Manager

This lab guides you through Dockerizing a LAMP stack using Amazon RDS as the database, replacing MySQL. AWS Secrets Manager is used to securely manage database credentials, enhancing security and scalability.

## Prerequisites

- **AWS CLI:** Installed and configured.
- **AWS Account:** IAM permissions to create RDS and Secrets Manager resources.
- **Docker & Docker Compose:** Installed.

  If Docker Compose is not installed, run:

  ```bash
  sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
  sudo chmod +x /usr/local/bin/docker-compose
  docker-compose version
  sudo systemctl enable docker
  ```

## Steps

### Step 1: Create an Amazon RDS MySQL Instance

1. **Access AWS Management Console**

   - Navigate to **RDS** and click on **Create database**.

2. **Configure RDS Instance**

   - Select **Standard create** and choose **MySQL**.
   - Choose **Free tier** for testing purposes.
   - Set database credentials:
     - **Username:** admin
     - **Password:** YourSecurePassword

3. **Network and Security Configuration**

   - Enable **Public Access** (temporarily, as security groups will restrict access later).
   - Configure security groups to allow access from your local machine.
   - Click **Create database** and wait for the instance to be available.
   - Note the **Endpoint** from the RDS console.

### Step 2: Store Database Credentials in AWS Secrets Manager

1. **Navigate to AWS Secrets Manager**

   - Click **Store a new secret**.

2. **Enter Secret Information**

   - Select **Credentials for RDS database**.
     - Enter:
       - **Username:** admin
       - **Password:** YourSecurePassword
   - Choose the RDS instance created earlier.
   - Click **Next**, name the secret as `lamp-rds-secret`.
   - Click **Store secret**.

### Step 3: Update `docker-compose.yml`

Modify your `docker-compose.yml` to remove MySQL and fetch credentials from AWS Secrets Manager.

```yaml
version: '3.8'

services:
  lamp_php:
    build: .
    container_name: lamp_php
    volumes:
      - ./src:/var/www/html
    networks:
      - lamp_network
    environment:
      DB_HOST: "${DB_HOST}"
      DB_USER: "${DB_USER}"
      DB_PASSWORD: "${DB_PASSWORD}"
      DB_NAME: "${DB_NAME}"

  lamp_nginx:
    image: nginx:latest
    container_name: lamp_nginx
    ports:
      - "8080:80"
    volumes:
      - ./src:/var/www/html
      - ./nginx/default.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - lamp_php
    networks:
      - lamp_network

  lamp_phpmyadmin:
    image: phpmyadmin/phpmyadmin
    container_name: lamp_phpmyadmin
    restart: always
    ports:
      - "8081:80"
    environment:
      PMA_HOST: "${DB_HOST}"
      PMA_PORT: "3306"
      PMA_ARBITRARY: "1"
    networks:
      - lamp_network

networks:
  lamp_network:
```

### Step 4: Fetch Secrets from AWS Secrets Manager

1. **Install AWS CLI if not installed**
   
2. **Retrieve Secrets**

   - Run the following command to retrieve the secrets and export them as environment variables:

     ```powershell
     $secret = aws secretsmanager get-secret-value --secret-id lamp-rds-secret --query SecretString --output text | ConvertFrom-Json
     $env:DB_HOST = "$($secret.host)"
     $env:DB_USER = "$($secret.username)"
     $env:DB_PASSWORD = "$($secret.password)"
     $env:DB_NAME = "$($secret.dbname)"
     ```

3. **Verify Environment Variables**

   - Verify if the environment variables are correctly set:

     ```powershell
     echo $env:DB_HOST
     ```

### Step 5: Start Docker Containers

- Start the updated stack with the following command:

  ```powershell
  docker-compose up -d --build
  ```

### Step 6: Test the Setup

1. **Verify PHP Application**

   - Open [http://localhost:8080](http://localhost:8080) in your browser to check if the PHP application is working.

2. **Access phpMyAdmin**

   - Open [http://localhost:8081](http://localhost:8081) for phpMyAdmin and log in with:
     - **Host:** The RDS endpoint
     - **Username:** admin
     - **Password:** The stored password

### Step 7: Cleanup

1. **Stop and Remove Containers**

   - Run the following command to stop and remove containers:
   
     ```powershell
     docker-compose down -v
     ```

2. **Delete AWS Resources**

   - Delete the RDS instance.
   - Remove the secret from AWS Secrets Manager.

---

This concludes the lab exercise. If you have any questions or need further assistance, please consult AWS documentation or reach out to your administrator.