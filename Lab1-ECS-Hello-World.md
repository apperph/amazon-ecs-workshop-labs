# Lab 1: Creating a Docker "Hello World" on Amazon ECS

## Objective

In this lab, you will learn how to create a simple "Hello World" Docker container, push it to Amazon Elastic Container Registry (ECR), and deploy it using Amazon Elastic Container Service (ECS).

## Prerequisites

Ensure you have the following prerequisites in place before starting the lab:

- AWS CLI is configured with the necessary permissions.
- Docker is installed on your Ubuntu system.
- AWS account ready to use.

## Steps

### Step 1: Create a Simple "Hello World" Docker Application

1. **Create a new directory for the project:**

   ```bash
   mkdir hello-world-docker
   cd hello-world-docker
   ```

2. **Create a `Dockerfile` in the project directory:**

   ```Dockerfile
   # Use the official Python image from the Docker Hub
   FROM python:3.8-slim

   # Set the working directory
   WORKDIR /app

   # Copy the hello.py file into the container
   COPY hello.py /app/hello.py

   # Run the Python application
   CMD ["python", "hello.py"]
   ```

3. **Create a simple Python script:**

   Create a file named `hello.py` with the following content:

   ```python
   print("Hello, World!")
   ```

### Step 2: Build the Docker Image

Create a variable for your ECR repository name to minimize conflicts.

```bash
DOCKER_REPO="hello-world-app-<yourname>"
export DOCKER_REPO
echo $DOCKER_REPO
``` 

1. **Build the Docker image:**

   ```bash
   docker build -t $DOCKER_REPO .
   ```

2. **Verify the Docker image:**

   ```bash
   docker images
   ```

   You should see $DOCKER_REPO listed among the Docker images.

### Step 3: Push the Docker Image to Amazon ECR

1. **Create an ECR repository:**

    Create variable for your ECR repository name to minimize conflicts .
    ```bash
    ECR_REPO="hello-world-app-<yourname>"
    export ECR_REPO
    echo $ECR_REPO
    ```

    Create an environment variable also for the AWS_ACCOUNT.
    ```bash
    AWS_ACCOUNT_ID="<aws-account-id>"
    export AWS_ACCOUNT_ID
    echo $AWS_ACCOUNT_ID
    ```

   ```bash
   aws ecr create-repository --repository-name $ECR_REPO
   ```

   Verify if the repository exists.

   ```bash
   aws ecr describe-repositories
   ```

2. **Log in to ECR:**

    Create an environment variable for the region.
    ```bash
    AWS_REGION="ap-southeast-1"
    export AWS_REGION
    echo $AWS_REGION
    ```

   Get the password for ECR.

   ```
   aws ecr get-login-password --region $AWS_REGION 
   ```

   Login to ECR repository.

   ```
   aws ecr get-login-password --region $AWS_REGION | docker login --username AWS --password-stdin $AWS_ACCOUNT_ID.dkr.ecr.$AWS_REGION.amazonaws.com
   ```

   If the command succeeds, you should be able to get the following message:

   ```
   WARNING! Your credentials are stored unencrypted in '/home/ubuntu/.docker/config.json'.
   Login Succeeded
    ```

3. **Tag the Docker image for ECR:**

   ```bash
   docker tag $ECR_REPO:latest $AWS_ACCOUNT_ID.dkr.ecr.$AWS_REGION.amazonaws.com/$ECR_REPO:latest
   ```

4. **Push the Docker image to ECR:**

   ```bash
   docker push $AWS_ACCOUNT_ID.dkr.ecr.$AWS_REGION.amazonaws.com/$ECR_REPO:latest
   ```

### Step 4: Deploy to Amazon ECS

Create variable for your ECR repository name to minimize conflicts.

```
    ECS_CLUSTER="cluster-<yourname>"
    export ECS_CLUSTER
    echo $ECS_CLUSTER
```

1. **Create an ECS cluster:**

   ```bash
   aws ecs create-cluster --cluster-name $ECS_CLUSTER
   ```

2. **Register a Task Definition:**

   Create a file named `task-definition.json` and define your task:

   ```json
   {
     "family": "hello-world-task-<your-name>",
     "networkMode": "awsvpc",
     "containerDefinitions": [
       {
         "name": "hello-world-container",
         "image": "<aws_account_id>.dkr.ecr.<your-region>.amazonaws.com/hello-world-app:latest",
         "memory": 256,
         "cpu": 256,
         "essential": true
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

   This will cause an error since it does not have an execution role. Edit the task definition and add the *executionRoleArn* property. 

```json
   {
     "family": "hello-world-task-<your-name>",
     "networkMode": "awsvpc",
     "executionRoleArn": "arn:aws:iam::<aws_account_id>:role/ECSExecutionRole",
     "containerDefinitions": [
       {
         "name": "hello-world-container",
         "image": "<aws_account_id>.dkr.ecr.<your-region>.amazonaws.com/hello-world-app:latest",
         "memory": 256,
         "cpu": 256,
         "essential": true
       }
     ],
     "requiresCompatibilities": [
       "FARGATE"
     ],
     "cpu": "256",
     "memory": "512"
   }
   ```

   Try to register the task definition again. The version should increment to two.

   ```bash
   aws ecs register-task-definition --cli-input-json file://task-definition.json
   ```

3. **Run the Task:**

Note: before running the following command, ensure that you have a valid subnet and security group.

   ```bash
   aws ecs run-task --cluster $ECS_CLUSTER --launch-type FARGATE --network-configuration "awsvpcConfiguration={subnets=[<your-subnet-id>],securityGroups=[<your-security-group-id>],assignPublicIp=ENABLED}" --task-definition hello-world-task-<your-name>
   ```

Here is a full example:

```
   aws ecs run-task --cluster $ECS_CLUSTER --launch-type FARGATE --network-configuration "awsvpcConfiguration={subnets=[subnet-0ac39594c83295aa3],securityGroups=[sg-0f337d85f089a9cb0],assignPublicIp=ENABLED}" --task-definition hello-world-task-wmundo
```

Go to the Amazon ECS console, and look for the Tasks under your cluster. You should be able to see that the container ran in your cluster with status Running or Stopped.

The task definition does not configure the log behavior. Modify the task definition by using awslogs. Here is an exerpt:

```
...
     "containerDefinitions": [
       {
         "name": "hello-world-container",
         "image": "010438472484.dkr.ecr.ap-southeast-1.amazonaws.com/hello-world-app-wmundo:latest",
         "memory": 256,
         "cpu": 256,
         "essential": true,
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
...
```

Register the task definition again and run the task. You should be able to see the "Hello World" under the Logs portion of the Task. You can also view the same logs by going to Amazon CloudWatch Logs.


## Conclusion

Congratulations! You have successfully created a Docker "Hello World" application, pushed it to Amazon ECR, and deployed it using Amazon ECS.

### Additional Notes

- Ensure you have the appropriate permissions and network configurations (subnets and security groups) to deploy and run your ECS tasks.
- Remember to replace placeholder values (`<your-region>`, `<aws_account_id>`, `<your-subnet-id>`, `<your-security-group-id>`) with actual values from your AWS account.
