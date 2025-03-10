# Lab 9: Deploy a Node.js App on AWS Fargate with API Gateway

## Overview:
In this lab, we will deploy a Node.js application on AWS Fargate using Docker, ECS, ECR, and API Gateway. This lab is intended for users authenticating via IAM User and working from a local Windows machine.

## 1. Prerequisites

**1-a. Install Required Tools**

- AWS CLI v2
   - Download and install AWS CLI v2 from the official AWS website.
   - Verify installation: ```aws --version```
- Docker Desktop for Windows
   - Download and install Docker Desktop for Windows.
   - Verify installation: ```docker --version```
   - Ensure Docker Desktop is running Linux containers.
- Node.js
   - Download and install Node.js from the official Node.js website.
   - Verify installation: ```node -v```
  
**1-b. Create an IAM User with Programmatic Access (If No IAM Users Exist)**

- Go to AWS Console → IAM → Users → Add user.
- Enter a username (e.g., fargate-user).
- Attach the ```AdministratorAccess policy``` (or create custom permissions for ECS, ECR, and API Gateway).
- Copy the **Access Key ID & Secret Access Key.**

**1-c. Configure AWS CLI with the IAM User**

- Open PowerShell or Command Prompt.
- Run: ```aws configure```
- Enter the **Access Key ID & Secret Access Key.**
- Set your AWS region (e.g., ap-southeast-1).
- Choose ```json``` as the output format.
- Verify configuration: ```aws sts get-caller-identity```

## 2. Create a Simple Node.js App

**2-a. Initialize the Project**

- Create a project folder:

```bash
mkdir my-fargate-app && cd my-fargate-app
```
- Initialize a Node.js project:
```bash
npm init -y
```
**2-b. Install Dependencies and Setup**
- Install Express.js:
```bash
npm install express
```
- Create ```server.js``` file and add the following code:

**2-c. Test Locally**

- Run the server: ```node server.js```
- Open ```http://localhost:3000``` in your browser to test.

## 3. Build and Push Docker Image to ECR

**3-a. Prepare Dockerfile**

- Create a ```Dockerfile``` in the ```my-fargate-app``` directory with the following content:
```bash
FROM node:18-alpine
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
EXPOSE 3000
CMD ["node", "server.js"]
```
**3-b. Create an ECR Repository**

- Run:
```bash
aws ecr create-repository --repository-name my-node-app --region ap-southeast-1
```

**3-c. Authenticate Docker with ECR**
- Describe ECR repositories to find your repository URI:
```bash
aws ecr describe-repositories --region ap-southeast-1 | findstr repositoryUri
```
**3-d. Build and Push Docker Image**
- Build the image:
```bash
docker build -t my-node-app .
```
- Tag the image:
```bash
docker tag my-node-app:latest <aws_account_id>.dkr.ecr.ap-southeast-1.amazonaws.com/my-node-app:latest
```
- Push the image to ECR:
```bash
docker push <aws_account_id>.dkr.ecr.ap-southeast-1.amazonaws.com/my-node-app:latest
```
## 4. Deploy to AWS Fargate (ECS)

**4-a. Create ECS Cluster**
```bash
aws ecs create-cluster --cluster-name my-fargate-cluster --region ap-southeast-1
```
**4-b. Create Task Definition**

- Create ```task-def.json``` with the necessary configuration.

**4-c. Create ECS Task Execution Role**

- Go to IAM → Roles → Create Role
- Select AWS Service → ECS Task
- Attach ```AmazonECSTaskExecutionRolePolicy``` and name the role ```ecsTaskExecutionRole.```

**4-d. Register the Task Definition**
```aws ecs register-task-definition --cli-input-json file://task-def.json --region ap-southeast-1```

**4-e. Create Security Group**

- In EC2 Console, create a security group in the same VPC as your subnets.
- Allow inbound TCP traffic on port 3000 from 0.0.0.0/0.

**4-f. Run the Task on Fargate**
```aws ecs run-task --cluster my-fargate-cluster --task-definition my-node-app --launch-type FARGATE --network-configuration "awsvpcConfiguration={subnets=[subnet-xxxxx, subnet-xxxxx],securityGroups=[sg-xxxxx],assignPublicIp=ENABLED}" --region ap-southeast-1```

## 5. Create the HTTP API

**5-a. Create a New HTTP API**
```aws apigatewayv2 create-api --name my-http-api --protocol-type HTTP```

**5-b. Create HTTP Proxy Integration**
```aws apigatewayv2 create-integration --api-id <api-id> --integration-type HTTP_PROXY --integration-uri http://<your-backend-ip>:3000 --integration-method GET --payload-format-version "1.0"```

**5-c. Create a Route and Deploy**
```
aws apigatewayv2 create-route --api-id <api-id> --route-key "GET /my-endpoint" --target "integrations/<integration-id>"
aws apigatewayv2 create-stage --api-id <api-id> --stage-name prod --auto-deploy
```

**5-d. Test the API**
```
curl https://<your-api-id>.execute-api.ap-southeast-1.amazonaws.com/prod/my-endpoint
```


If your backend is functioning properly, you should get a valid response from the endpoint.

---

This concludes the lab. If you encounter any issues, refer to AWS documentation or seek support. 
