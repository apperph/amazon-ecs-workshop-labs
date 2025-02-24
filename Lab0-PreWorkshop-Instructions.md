# Lab 0: Pre-Workshop Setup Instructions

## Objective

This lab guides you through setting up an Amazon EC2 instance and configuring it for the workshop. You'll install Docker, AWS CLI, and AWS Copilot, and configure necessary IAM roles and permissions. These steps should be completed before the workshop begins.

## Steps

### Step 1: Create an EC2 Instance

1. **Launch a `t3a.large` EC2 instance:**

   - Ensure your instance runs on Ubuntu.
   - During the setup, attach the IAM instance profile `ECS-Full-for-EC2`.

2. **Note on IAM Instance Profile Role:**

   The `ECS-Full-for-EC2` role has the following permissions, which are pre-configured by the instructor:

   - **Full Access Policies:**
     - ECS-Full-Access
     - S3-Full-Access
     - IAM-Full-Access
     - Amazon-SSM-Full-Access
     - CloudFormation-Full-Access
     - AWSKeyManagementServicePowerUser
     - AmazonEC2ContainerRegistryFullAccess

   - **Execution Role and Logging:**
     - ECSExecutionRole
     - AmazonEC2ContainerRegistryReadOnly
     - AmazonEC2ContainerServiceRole
     - CloudWatchLogsFullAccess

3. **Reboot the Instance:**

   After attaching the profile, reboot your EC2 instance to ensure the policies are correctly implemented.

### Step 2: Setup Workshop-Machine EC2 Instance (Ubuntu)

1. **Update your instance and install necessary packages:**

   ```bash
   sudo apt update
   sudo apt install unzip -y
   ```

### Step 3: Setup Docker Environment

1. **Add Docker's GPG Key:**

   ```bash
   sudo apt-get update
   sudo apt-get install ca-certificates curl
   sudo install -m 0755 -d /etc/apt/keyrings
   sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
   sudo chmod a+r /etc/apt/keyrings/docker.asc
   ```

2. **Add Docker Repository:**

   ```bash
   echo \
   "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] \
   https://download.docker.com/linux/ubuntu \
   $(. /etc/os-release && echo "${UBUNTU_CODENAME:-$VERSION_CODENAME}") stable" | \
   sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
   sudo apt-get update
   ```

3. **Install Docker:**

   ```bash
   sudo apt-get install docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
   ```

4. **Verify Docker Installation:**

   ```bash
   sudo docker run hello-world
   ```

5. **Configure Docker for Non-Root User Access:**

   ```bash
   sudo groupadd docker
   sudo usermod -aG docker $USER
   newgrp docker

   docker run hello-world
   ```

### Step 4: Install AWS CLI and AWS Copilot

1. **Install AWS CLI:**

   ```bash
   curl "https://awscli.amazonaws.com/awscli-exe-linux-x86_64.zip" -o "awscliv2.zip"
   unzip awscliv2.zip
   sudo ./aws/install
   ```

2. **Install Copilot CLI:**

   ```bash
   sudo curl -Lo /usr/local/bin/copilot https://github.com/aws/copilot-cli/releases/latest/download/copilot-linux && sudo chmod +x /usr/local/bin/copilot && copilot --help
   ```

### Step 5: Set Environment Variables

1. **Configure AWS Region:**

   ```bash
   echo "export AWS_DEFAULT_REGION=ap-southeast-1" >> ~/.bashrc
   source ~/.bashrc
   ```

2. **Create Environment Directory:**

   ```bash
   mkdir ~/environment
   ```

### Step 6: Verify Service Roles

1. **Ensure service roles exist for Load Balancing and ECS:**

   ```bash
   aws iam get-role --role-name "AWSServiceRoleForElasticLoadBalancing" || aws iam create-service-linked-role --aws-service-name "elasticloadbalancing.amazonaws.com"

   aws iam get-role --role-name "AWSServiceRoleForECS" || aws iam create-service-linked-role --aws-service-name "ecs.amazonaws.com"
   ```

## Conclusion

You now have a properly configured environment with Docker and AWS tools ready for the workshop. All necessary permissions and services should be in place. Please ensure these steps are completed to maximize the effectiveness of the workshop sessions.