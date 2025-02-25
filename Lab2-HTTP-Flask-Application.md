# Lab 2: Deploying a Flask Application Using Amazon ECS

## Objective

In this lab, you will learn how to create a simple Flask application that processes data from query parameters. You will build the application into a Docker container, push it to Amazon Elastic Container Registry (ECR), and deploy it using Amazon Elastic Container Service (ECS).

## Prerequisites

Ensure you have the following prerequisites in place before starting the lab:

- AWS CLI is configured with the necessary permissions.
- Docker is installed on your Ubuntu system.
- AWS account ready to use.

## Steps

### Step 1: Create a Flask Application

1. **Create a new directory for the project:**

   ```bash
   mkdir flask-app
   cd flask-app
   ```

2. **Create a `Dockerfile` in the project directory:**

   ```Dockerfile
   # Use the official Python image from the Docker Hub
   FROM python:3.8-slim

   # Set the working directory
   WORKDIR /app

   # Install Flask
   RUN pip install flask

   # Copy the Flask app into the container
   COPY app.py /app/app.py

   # Define the environment variable
   ENV FLASK_APP=app.py

   # Expose the port the app runs on
   EXPOSE 5000

   # Run the Flask application
   CMD ["flask", "run", "--host=0.0.0.0"]
   ```

3. **Create a Flask application:**

   Create a file named `app.py` with the following content:

   ```python
   from flask import Flask, request

   app = Flask(__name__)

   @app.route('/')
   def hello_world():
       name = request.args.get('name', 'World')
       return f"Hello, {name}!"

   if __name__ == '__main__':
       app.run(host='0.0.0.0', port=5000)
   ```

### Step 2: Build the Docker Image

1. **Build the Docker image:**

   ```bash
   docker build -t flask-app .
   ```

2. **Verify the Docker image:**

   ```bash
   docker images
   ```

   You should see `flask-app` listed among the Docker images.

### Step 3: Push the Docker Image to Amazon ECR

1. **Create an ECR repository:**

   ```bash
   aws ecr create-repository --repository-name flask-app
   ```

2. **Log in to ECR:**

   ```bash
   aws ecr get-login-password --region <your-region> | docker login --username AWS --password-stdin <aws_account_id>.dkr.ecr.<your-region>.amazonaws.com
   ```

3. **Tag the Docker image for ECR:**

   ```bash
   docker tag flask-app:latest <aws_account_id>.dkr.ecr.<your-region>.amazonaws.com/flask-app:latest
   ```

4. **Push the Docker image to ECR:**

   ```bash
   docker push <aws_account_id>.dkr.ecr.<your-region>.amazonaws.com/flask-app:latest
   ```

### Step 4: Deploy to Amazon ECS

1. **Create an ECS cluster:**

   If you have not created an ECS cluster yet, you can create it with:

   ```bash
   aws ecs create-cluster --cluster-name flask-app-cluster
   ```

2. **Register a Task Definition:**

   Create a file named `task-definition.json` and define your task as follows:

   ```json
   {
     "family": "flask-app-task",
     "networkMode": "awsvpc",
     "executionRoleArn": "arn:aws:iam::<aws_account_id>:role/ECSExecutionRole",
     "containerDefinitions": [
       {
         "name": "flask-app-container",
         "image": "<aws_account_id>.dkr.ecr.<your-region>.amazonaws.com/flask-app:latest",
         "memory": 256,
         "cpu": 256,
         "essential": true,
         "portMappings": [
           {
             "containerPort": 5000,
             "hostPort": 5000
           }
         ],
         "logConfiguration": {
                "logDriver": "awslogs",
                "options": {
                    "awslogs-create-group": "true",
                    "awslogs-group": "wmundo",
                    "awslogs-region": "ap-southeast-1",
                    "awslogs-stream-prefix": "ecsworkshop"
                }
         }
       }
     ],
     "requiresCompatibilities": [
       "FARGATE"
     ],
     "cpu": "256",
     "memory": "512"
   }
   ```

   Register the task definition:

   ```bash
   aws ecs register-task-definition --cli-input-json file://task-definition.json
   ```

3. **Run the Task:**

   Deploy the task to ECS:

   ```bash
   aws ecs run-task --cluster flask-app-cluster --launch-type FARGATE --network-configuration "awsvpcConfiguration={subnets=[<your-subnet-id>],securityGroups=[<your-security-group-id>],assignPublicIp=ENABLED}" --task-definition flask-app-task
   ```

   Here is an example:

   ```bash
   aws ecs run-task --cluster cluster-wmundo --launch-type FARGATE --network-configuration "awsvpcConfiguration={subnets=[subnet-0ac39594c83295aa3],securityGroups=[sg-0f337d85f089a9cb0],assignPublicIp=ENABLED}" --task-definition flask-app-task
   ```

    Once the app runs, try opening the app by opening up the public IP of the Fargate task. Ensure that an incoming rule for port 5000 in the security group is setup.

## Conclusion

Congratulations! You have successfully created a Flask application that processes query parameters, containerized it with Docker, and deployed it using Amazon ECS.

### Additional Notes

- Ensure your network configurations (subnets and security groups) are set up correctly to allow traffic to the application.
- Replace placeholder values (`<your-region>`, `<aws_account_id>`, `<your-subnet-id>`, `<your-security-group-id>`) with actual values specific to your AWS account.

This completes the second lab. In future labs, we will explore more advanced features of ECS and container orchestration.