# Lab 7: Deploying Application to Amazon ECS with CI/CD

## Objective

This exercise demonstrates how to push a Docker image to Amazon Elastic Container Registry (ECR), create an Amazon ECS cluster using Infrastructure as Code (IaC), and set up a CI/CD pipeline using AWS CodePipeline and CodeBuild.

## Prerequisites

- An AWS account with necessary permissions.
- EC2 instance configured with Docker.
- Familiarity with AWS services: ECS, ECR, CodePipeline, and CodeBuild.

## Steps

### Step 1: Pushing Image to ECR

1. **Install Git and Docker on EC2**

   Ensure Git and Docker are installed on your EC2 instance. Use the following script during instance launch or execute it manually:

    ```bash
    #!/bin/bash
    sudo apt update -y
    sudo apt upgrade -y
    sudo apt install -y apt-transport-https ca-certificates curl software-properties-common
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo apt-key add -
    sudo add-apt-repository "deb [arch=amd64] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable"
    sudo apt update -y
    sudo apt install -y docker-ce
    sudo systemctl start docker
    sudo systemctl enable docker
    sudo usermod -aG docker ubuntu
    sudo apt install -y git
    ```

2. **Clone the Repository**

   Clone the repository with the application code:

    ```bash
    git clone https://github.com/olyvenbayani/Pipeline-with-Docker-App.git
    ```

3. **Install AWS CLI**

   If the AWS CLI is not installed, execute the following commands to install it:

    ```bash
    curl "https://awscli.amazonaws.com/awscli-exe-linux-x86_64.zip" -o "awscliv2.zip"
    unzip awscliv2.zip
    sudo ./aws/install
    ```

4. **Create an ECR Repository**

   Create a new ECR repository for your application image:

    ```bash
    aws ecr create-repository --repository-name my-app
    ```

5. **Authenticate Docker with ECR**

   Retrieve the login command to authenticate Docker with your ECR registry:

    ```bash
    aws ecr get-login-password --region <YOUR-AWS-REGION> | sudo docker login --username AWS --password-stdin <ACCOUNT-ID>.dkr.ecr.<REGION>.amazonaws.com
    ```

6. **Build the Docker Image**

   Navigate to the directory containing your Dockerfile and build the Docker image:

    ```bash
    sudo docker build -t my-app .
    ```

7. **Tag the Docker Image**

   Tag your Docker image to match the ECR repository URI:

    ```bash
    sudo docker tag my-app:latest <ACCOUNT-ID>.dkr.ecr.<REGION>.amazonaws.com/my-app:latest
    ```

8. **Push the Docker Image to ECR**

   Push the tagged Docker image to your ECR repository:

    ```bash
    sudo docker push <ACCOUNT-ID>.dkr.ecr.<REGION>.amazonaws.com/my-app:latest
    ```

### Step 2: Creating ECS Cluster Using IaC

1. **Create CloudFormation Stack**

   Use the following command to create a CloudFormation stack from a YAML file (`ecs-fargate.yaml`):

    ```sh
    aws cloudformation create-stack --stack-name my-ecs-fargate-stack \
        --template-body file://ecs-fargate.yaml \
        --parameters ParameterKey=VpcId,ParameterValue=vpc-xxxxxxxx \
            ParameterKey=SubnetIds,ParameterValue=subnet-xxxxxxx1\\,subnet-xxxxxxx2 \
            ParameterKey=ECRImageUri,ParameterValue=<ACCOUNT-ID>.dkr.ecr.<REGION>.amazonaws.com/my-app:latest \
        --capabilities CAPABILITY_IAM
    ```

### Step 3: Create CodeBuild Project

1. **Set Up CodeBuild Project**

   - Go to AWS CodeBuild to create a new build project.
   - Set the primary source as GitHub and connect your GitHub account.
   - Select the application repository and specify to use the `buildspec.yml` file from the repository.

2. **Edit CodeBuild Service Role**

   Attach the following policies to the CodeBuild service role:

   - AmazonS3FullAccess
   - AWSCodePipelineFullAccess
   - AmazonCloudwatchFullAccess
   - AmazonECSFullAccess
   - AmazonEC2ElasticContainerRegistryFullAccess

3. **Update buildspec.yml**

   Edit the `buildspec.yml` file to include your ECR name and URI.

### Step 4: Create the Pipeline

1. **Set Up AWS CodePipeline**

   - Go to AWS CodePipeline and create a new pipeline.
   - Choose 'Build Custom Pipeline' with the following configurations:
     - **Name:** Set the name to your repository name.
     - **Execution mode:** Superseded
     - **Service Role:** Create a new or use an existing role.
     - **Source:** GitHub (via GitHub App)
     - **Repository:** Select your GitHub repository.
     - **Build:** Choose AWS CodeBuild and select your build project.
     - **Test Stage:** Skip the test stage.
     - **Deploy:** Deploy to ECS.

2. **Complete Pipeline Setup**

   Follow the remaining steps on the console to finalize and create the pipeline.


## Conclusion

You have successfully pushed a Docker image to ECR, created an ECS cluster using IaC, and configured a CI/CD pipeline with AWS CodePipeline and CodeBuild. Well done! Ensure you clean up any resources to avoid additional charges.

