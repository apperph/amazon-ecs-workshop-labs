# Lab 9: Deploy a Node.js App on AWS Fargate with API Gateway

This lab guides you through the process of deploying a Node.js application on AWS Fargate using Docker, ECS, ECR, and API Gateway. This lab is intended for users authenticating via IAM User and working from a local Windows machine.

## Step 1: Prerequisites

1. **Install Required Tools**

   a. **AWS CLI v2**

   - Download and install the AWS CLI v2 from the [official AWS website](https://aws.amazon.com/cli/).
   - Verify installation:
     ```sh
     aws --version
     ```

   b. **Docker Desktop for Windows**

   - Download and install Docker Desktop for Windows.
   - Verify installation:
     ```sh
     docker --version
     ```
   - Ensure Docker Desktop is running Linux containers. Right-click the Docker icon in the system tray and verify "Switch to Linux Containers" is not greyed out.

   c. **Node.js**

   - Download and install Node.js from the [official Node.js website](https://nodejs.org/).
   - Verify installation:
     ```sh
     node -v
     ```

2. **Create an IAM User with Programmatic Access (Only If No IAM Users Exist)**

   - Go to AWS Console → IAM → Users → Add user.
   - Enter a username (e.g., `fargate-user`).
   - Attach the `AdministratorAccess` policy (for simplicity, or create custom permissions for ECS, ECR, and API Gateway). **Important:** Use the least privilege principle in production.
   - Click Create user, then copy the Access Key ID & Secret Access Key.

3. **Configure AWS CLI with the IAM User**

   - Open PowerShell or Command Prompt.
   - Run:
     ```sh
     aws configure
     ```
   - Enter the Access Key ID & Secret Access Key.
   - Set your AWS region (e.g., `ap-southeast-1`).
   - Choose `json` as the output format.
   - Verify configuration:
     ```sh
     aws sts get-caller-identity
     ```
     - You should see your IAM User ARN.

## Step 2: Create a Simple Node.js App

1. **Initialize the Project**

   - Create a project folder:
     ```sh
     mkdir my-fargate-app && cd my-fargate-app
     ```
   - Initialize a Node.js project:
     ```sh
     npm init -y
     ```

2. **Install Dependencies and Setup**

   - Install Express.js:
     ```sh
     npm install express
     ```
   - Create `server.js` file:
     ```sh
     notepad server.js
     ```
   - Add the following code to `server.js`:
     ```javascript
     const express = require('express');
     const app = express();
     const port = process.env.PORT || 3000;

     app.get('/', (req, res) => {
         res.send('Hello from Fargate!');
     });

     app.listen(port, () => {
         console.log(`Server running on port ${port}`);
     });
     ```

3. **Test Locally**

   - Run the server:
     ```sh
     node server.js
     ```
   - Open [http://localhost:3000](http://localhost:3000) in your browser to test.

## Step 3: Build and Push Docker Image to ECR

1. **Prepare Dockerfile**

   - Create a `Dockerfile` in the `my-fargate-app` directory and add the following content:

     ```dockerfile
     FROM node:18-alpine
     WORKDIR /app
     COPY package*.json ./
     RUN npm install
     COPY . .
     EXPOSE 3000
     CMD ["node", "server.js"]
     ```

2. **Create an ECR Repository**

   - Execute:
     ```sh
     aws ecr create-repository --repository-name my-node-app --region ap-southeast-1
     ```

3. **Find ECR URL and Authenticate Docker**

   - Describe ECR repositories to find your repository URI:
     ```sh
     aws ecr describe-repositories --region ap-southeast-1 | findstr repositoryUri
     ```
   - Authenticate Docker with ECR (replace `<aws_account_id>` with your AWS account ID):
     ```sh
     aws ecr get-login-password --region ap-southeast-1 | docker login --username AWS --password-stdin <aws_account_id>.dkr.ecr.ap-southeast-1.amazonaws.com
     ```

4. **Build and Push Docker Image**

   - Build the image:
     ```sh
     docker build -t my-node-app .
     ```
   - Tag the image:
     ```sh
     docker tag my-node-app:latest <aws_account_id>.dkr.ecr.ap-southeast-1.amazonaws.com/my-node-app:latest
     ```
   - Push the image to ECR:
     ```sh
     docker push <aws_account_id>.dkr.ecr.ap-southeast-1.amazonaws.com/my-node-app:latest
     ```

## Step 4: Deploy to AWS Fargate (ECS)

1. **Create ECS Cluster**

   - Execute:
     ```sh
     aws ecs create-cluster --cluster-name my-fargate-cluster --region ap-southeast-1
     ```

2. **Create Task Definition**

   - Create a file named `task-def.json` and add this content (replace `<aws_account_id>`):

     ```json
     {
       "family": "my-node-app",
       "networkMode": "awsvpc",
       "executionRoleArn": "arn:aws:iam::<aws_account_id>:role/ecsTaskExecutionRole",
       "containerDefinitions": [
         {
           "name": "my-node-app",
           "image": "<aws_account_id>.dkr.ecr.ap-southeast-1.amazonaws.com/my-node-app:latest",
           "portMappings": [
             {
               "containerPort": 3000,
               "hostPort": 3000
             }
           ]
         }
       ],
       "requiresCompatibilities": ["FARGATE"],
       "cpu": "256",
       "memory": "512"
     }
     ```

3. **Create ECS Task Execution Role**

   - In IAM, go to Roles, create a new role, select AWS Service, select Elastic Container Service, and Task.
   - Search for and attach `AmazonECSTaskExecutionRolePolicy`.
   - Name the role `ecsTaskExecutionRole`.

4. **Register the Task Definition**

   - Update `executionRoleArn` in `task-def.json` with your account ID and run:
     ```sh
     aws ecs register-task-definition --cli-input-json file://task-def.json --region ap-southeast-1
     ```

5. **Create Security Group**

   - In the EC2 console, create a security group in the same VPC as your subnets.
   - Allow inbound TCP traffic on port 3000 from `0.0.0.0/0`.

6. **Verify Subnet Settings**

   - In the VPC console, select subnets in different Availability Zones.
   - For each subnet, enable "Auto-assign public IPv4 address".

7. **Run the Task on Fargate**

   - Replace `subnet-xxxxx` and `sg-xxxxx` with your subnet and security group IDs, then run:
     ```sh
     aws ecs run-task --cluster my-fargate-cluster --task-definition my-node-app --launch-type FARGATE --network-configuration "awsvpcConfiguration={subnets=[subnet-xxxxx, subnet-xxxxx],securityGroups=[sg-xxxxx],assignPublicIp=ENABLED}" --region ap-southeast-1
     ```

8. **Get the Public IP Address**

   - Go to the AWS ECS console → cluster `my-fargate-cluster`.
   - Go to the "Tasks" tab and find your running task.
   - Note the "Public IP address" in the "Configuration" tab.

9. **Test the Application**

   - Open a web browser and navigate to `http://<public-ip-of-fargate-task>:3000`.

## Step 5: Create the HTTP API

1. **Create a New HTTP API**

   - Run:
     ```powershell
     aws apigatewayv2 create-api --name my-http-api --protocol-type HTTP
     ```

2. **Create an HTTP Proxy Integration**

   - Create an integration that forwards requests to your backend:
     ```powershell
     aws apigatewayv2 create-integration --api-id <api-id> --integration-type HTTP_PROXY --integration-uri http://<your-backend-ip>:3000 --integration-method GET --payload-format-version "1.0"
     ```

3. **Create a Route to Attach the Integration**

   - Define a route to handle requests:
     ```powershell
     aws apigatewayv2 create-route --api-id <api-id> --route-key "GET /my-endpoint" --target "integrations/<integration-id>"
     ```

4. **Create a Stage & Deploy the API**

   - Deploy with auto-deploy:
     ```powershell
     aws apigatewayv2 create-stage --api-id <api-id> --stage-name prod --auto-deploy
     ```

5. **Test the API**

   - Ensure API Gateway forwards requests:
     ```powershell
     aws apigatewayv2 get-api --api-id <your-api-id>
     curl https://<your-api-id>.execute-api.ap-southeast-1.amazonaws.com/prod/my-endpoint
     ```

If your backend is functioning properly, you should get a valid response from the endpoint.

---

This concludes the lab. If you encounter any issues, refer to AWS documentation or seek support. 