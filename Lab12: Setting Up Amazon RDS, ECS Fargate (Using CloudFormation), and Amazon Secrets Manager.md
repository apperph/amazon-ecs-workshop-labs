# Setting Up Amazon RDS, ECS Fargate (Using CloudFormation), and Amazon Secrets Manager

## **Step 1: Create an Amazon RDS Database**
We'll create an **Amazon RDS** instance using MySQL and ensure that it is accessible by ECS.

### **1.1 Create the RDS Instance**
1. **Go to AWS Console** → Navigate to **RDS**.
2. Click **Create database**.
3. **Choose a database creation method**: Select **Standard create**.
4. **Engine options**: Choose **MySQL**.
5. **Version**: Choose a recent version of MySQL.
6. **Templates**: Select **Free Tier** (if applicable) or **Production**.
7. **Instance specifications**:
   - DB instance identifier: `my-rds-instance`
   - Username: `admin`
   - Password: Choose Managed in AWS Secrets Manager
8. **Storage**:
   - Allocated storage: `20 GB` (or more, based on your needs)

9. **Connectivity**:
   - **VPC**: Choose your VPC
   - **Subnet group**: Select or create a **DB subnet group**.
   - **Public access**: **No** (For security)
   - **Security Group**: Create New and Allow inbound connections on **port 3306** from your ECS Fargate instances.

10. Click **Create Database** and wait for the instance to become available.

### **1.2 Configure RDS Security Group**
1. Navigate to **EC2** → **Security Groups**.
2. Find the security group attached to your **RDS instance**.
3. Click **Edit inbound rules** → **Add rule**:
   - Type: **MySQL/Aurora**
   - Port: **3306**
   - Source: **Security group of ECS Fargate service**
4. Save the changes.

---

## **Step 2: Store Database Credentials in AWS Secrets Manager**

### **2.1 Store Database Secrets**
1. Go to **AWS Console** → **Secrets Manager**.
2. Click **Store a new secret**.
3. Choose **Credentials for RDS database**.
4. Enter:
   - Username: `admin`
   - Password: `<your-password>`
5. **Choose database**: Select your **RDS instance**.
6. Click **Next**.
7. **Secret name**: `MyDatabaseSecret`
8. Enable **Automatic rotation** (Optional).
9. Click **Store secret**.

### **2.2 Configure IAM Role for ECS to Access Secrets**
1. Go to **IAM Console** → **Roles**.
2. Click **Create role** → Choose **AWS Service**.
3. Select **ECS** → **ECS Task**.
4. Click **Next** → Attach the **SecretsManagerReadWrite** policy.
5. Click **Next** → Name the role **ECSSecretManagerRole**.
6. Click **Create role**.

---

## **Step 3: Create an ECS Cluster with Fargate**
Now, we deploy an ECS cluster using AWS **CloudFormation**.

### **3.1 Create CloudFormation Template**
Create a `ecs-fargate.yaml` file:

```yaml
AWSTemplateFormatVersion: '2010-09-09'
Description: Simple ECS Application with RDS Integration (Using Existing RDS)

Parameters:
  VPCId:
    Type: AWS::EC2::VPC::Id
    Description: The VPC ID where the resources will be created.
  
  PublicSubnetId:
    Type: AWS::EC2::Subnet::Id
    Description: The Public Subnet ID for the ECS service.

  SecurityGroupId:
    Type: AWS::EC2::SecurityGroup::Id
    Description: The security group ID for the ECS service.

  DBHost:
    Type: String
    Description: The endpoint of the existing RDS instance (e.g., mydb.xxxxxxxxxxx.us-east-1.rds.amazonaws.com)

  DBSecretArn:
    Type: String
    Description: The ARN of the Secrets Manager secret that contains RDS credentials.

Resources:

  # ECS Cluster
  ECSCluster:
    Type: AWS::ECS::Cluster
    Properties:
      ClusterName: my-app-cluster

  # ECS Task Role
  ECSTaskRole:
    Type: AWS::IAM::Role
    Properties:
      AssumeRolePolicyDocument:
        Version: '2012-10-17'
        Statement:
          - Action: sts:AssumeRole
            Effect: Allow
            Principal:
              Service: ecs-tasks.amazonaws.com

  # ECS Task Definition
  ECSAppTaskDefinition:
    Type: AWS::ECS::TaskDefinition
    Properties:
      Family: carlo-task
      ExecutionRoleArn: !GetAtt ECSTaskRole.Arn
      NetworkMode: awsvpc  # Required for Fargate
      Cpu: "256"  # Set CPU to an appropriate value
      Memory: "512"  # Set Memory to an appropriate value
      ContainerDefinitions:
        - Name: nginx-container
          Image: "nginx:latest"  # Publicly available image
          Memory: 512
          Cpu: 256
          Essential: true
          Environment:
            - Name: DB_HOST
              Value: !Ref DBHost  # Using the DBHost parameter for the existing RDS instance endpoint
            - Name: DB_SECRET_ARN
              Value: !Ref DBSecretArn  # Reference the Secrets Manager ARN directly

  # ECS Service
  ECSAppService:
    Type: AWS::ECS::Service
    Properties:
      Cluster: !Ref ECSCluster
      TaskDefinition: !Ref ECSAppTaskDefinition
      DesiredCount: 1
      LaunchType: FARGATE  # Specify that it's a Fargate task
      NetworkConfiguration:
        AwsvpcConfiguration:
          Subnets:
            - !Ref PublicSubnetId
          SecurityGroups:
            - !Ref SecurityGroupId
          AssignPublicIp: ENABLED


```

### **3.2 Deploy CloudFormation Stack**
1. Go to **AWS Console** → **CloudFormation**.
2. Click **Create stack** → **With new resources**.
3. Upload the `ecs-fargate.yaml` file.
4. Click **Next** → Name the stack **ECSFargateStack**.
5. Click **Next** → Configure as needed.
6. Click **Create Stack** and wait for it to complete.

### Ensure enabled exec command

Make sure to rename your cluster and services.

1. Check if the exec command is enabled it should return *true*



```
aws ecs describe-services \
  --cluster <your cluster name> \
  --services <your ecs service name> \
  --query "services[0].enableExecuteCommand"
```

2.  Once inside run:

```
apt update
apt install -y default-mysql-client netcat-traditional
nc -zv database-1.xxxxxxxxxxxx.us-east-1.rds.amazonaws.com 3306  # MySQL
```
This code will tell you if your connection to RDS is successful.

4. 
---


### **Summary**
- ✅ Created **RDS** instance
- ✅ Stored **credentials in Secrets Manager**
- ✅ Used **CloudFormation** to deploy **ECS Fargate**
- ✅ Connected **ECS Fargate to RDS securely**

