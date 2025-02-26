# Step 1:Pushing image to ECR

1. Make sure git is installed in your EC2.

If not you can run a new instance with this as userdata or you can run this manually on your instance:

```bash
#!/bin/bash
# Update all installed packages
sudo apt update -y
sudo apt upgrade -y

# Install prerequisites for Docker
sudo apt install -y apt-transport-https ca-certificates curl software-properties-common

# Add Docker’s official GPG key
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo apt-key add -

# Add Docker APT repository
sudo add-apt-repository "deb [arch=amd64] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable"

# Update package database with Docker packages from the new repo
sudo apt update -y

# Install Docker
sudo apt install -y docker-ce

# Start Docker service
sudo systemctl start docker

# Ensure Docker starts on boot
sudo systemctl enable docker

# Add the `ubuntu` user to the `docker` group so you can execute Docker commands without using `sudo`
sudo usermod -aG docker ubuntu

# Install Git
sudo apt install -y git
```


2. Clone this repository:

https://github.com/olyvenbayani/Pipeline-with-Docker-App.git

```bash
git clone https://github.com/olyvenbayani/Pipeline-with-Docker-App.git
```



3. Install aws cli:

```bash
curl "https://awscli.amazonaws.com/awscli-exe-linux-x86_64.zip" -o "awscliv2.zip"

unzip awscliv2.zip

sudo ./aws/install
```

4. Create a new ECR repository:

   - Create a new ECR repository if you haven't already:

     ```bash
     aws ecr create-repository --repository-name my-app
     ```
    
5. **Authenticate Docker with ECR**:
   - Retrieve the login command to authenticate Docker with your ECR registry and execute it:
     ```bash
     aws ecr get-login-password --region YOUR-AWS-REGION | sudo docker login --username AWS --password-stdin [ACCOUNT-ID].dkr.ecr.[REGION].amazonaws.com
     ```

7. **Build the Docker Image**:
   - Navigate to the directory with your Dockerfile and build the Docker image:
     ```bash
     sudo docker build -t my-app .
     ```

8. **Tag the Docker Image**:
   - Tag your Docker image to match the ECR repository URI:
     ```bash
     sudo docker tag my-app:latest [ACCOUNT-ID].dkr.ecr.[REGION].amazonaws.com/my-app:latest
     ```

9. **Push the Docker Image to ECR**:
   - Push the tagged image to your ECR repository:
     ```bash
     sudo docker push [ACCOUNT-ID].dkr.ecr.[REGION].amazonaws.com/my-app:latest
     ```


# 2. Creating ECS using IaC

1. You can download the yaml file on the repo or you can run

```sh
aws cloudformation create-stack --stack-name my-ecs-fargate-stack \
    --template-body file://ecs-fargate.yaml \
    --parameters ParameterKey=VpcId,ParameterValue=vpc-xxxxxxxx \
        ParameterKey=SubnetIds,ParameterValue=subnet-xxxxxxx1\\,subnet-xxxxxxx2 \
        ParameterKey=ECRImageUri,ParameterValue=[ACCOUNT-ID].dkr.ecr.[REGION].amazonaws.com/my-app:latest \
    --capabilities CAPABILITY_IAM
```




# 3. Create Codebuild Project

1. Go to Codebuild and create new build project.

- Primary Source: Github
Make sure to connect your github account.
- Repository: Choose the github repository.
- Use buildspec.yml file and choose the yml file in the github

2.  Edit the service role oif the build project:
Attach the following:
- AmazonS3FullAccess
- AWSCodePipelineFullAccess
- AmazonCloudwatchFullAccess
- AmazonECSFullAccess
- AmazonEC2ElasticContainerRegistryFullAccess

3. Edit the buildspec.yml with your credentials (ECR name and URI)

# 4. Create the Pipeline

1. Go to AWS CodePipeline

2. Select Build Custom Pipeline and here are the things you should choose:

- Name: <Choose your repo>
- Execution mode: Superseded
- ServiceRolke: Create new role
- Source: Github (Via github App)
- Repository: <Choose your repo>
- Build: Other build providers > AWS Codebuild > <Choose your Build Project>
- Test Stage: Skip Test Stage
- Deploy: Deploy to ECS



