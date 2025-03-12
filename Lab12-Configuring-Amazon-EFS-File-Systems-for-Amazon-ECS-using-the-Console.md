## Lab 12: Configuring Amazon EFS File Systems for Amazon ECS using the Console

https://docs.aws.amazon.com/AmazonECS/latest/developerguide/tutorial-efs-volumes.html

## Objective
This lab will guide you through configuring an Amazon Elastic File System (EFS) for Amazon Elastic Container Service (ECS) using the AWS Management Console. You will deploy ECS on both EC2 and Fargate. At the end of this lab, you will generate a CloudFormation template to automate the setup.

---
## Steps

### Step 1: Create an Amazon EFS File System
1. Open the [Amazon EFS console](https://console.aws.amazon.com/efs/).

2. Click **Create file system**.

3.. Configure the following:
   - **Name**: `EFS-demo`
   - **Virtual Private Cloud (VPC)**: Select the VPC where your ECS cluster resides.
   - **Throughput Mode**: Bursting
   - **Security Group**: Choose the security group you created for the EFS for both subnets.
     
4. Click **Create**.

5. Note down the **File System ID** (e.g., `fs-xxxxxxxx`).

---

### Step 2: Create an ECS Cluster

1. Open the [Amazon ECS console](https://console.aws.amazon.com/ecs/).

2. Navigate to **Clusters**.

3. Click **Create Cluster**

4. Select **EC2 Linux + Networking** and click **Next step**.

5. Configure the cluster:
   - **Cluster name**: `EFS-demo`
   - **Provisioning Model**: On-Demand.
   - **EC2 instance type**: `t2.small` (or any appropriate type).
   - **Number of instances**: `1`.
   - **Key pair**: Choose an existing key pair or create a new one.
   - **Security Group**: Choose the security group you created for the ECS.
     
6. Click **Create**.

7. Once created, verify that your cluster is listed in the **Clusters** section.

---

### Step 3: Launch an EC2 Instance

1. Open the [Amazon EC2 console](https://console.aws.amazon.com/ec2/).

2. Click **Launch Instance**.

3. **Select an Amazon Machine Image (AMI):**
   - Choose **Amazon Linux 2 AMI**.
     
4. **Instance Type:** `t2.micro`.

5. **Configure Instance Details:**
   - **Network**: Select the VPC of your ECS cluster.
   - **Subnet**: Choose a subnet.
   - **IAM role**: Assign an IAM role with ECS permissions (`EC2InstanceProfileForECS`).
   - Under storage choose "Add Volume" and mount the filee system you created.
  
6. **Security Group:** Allow **HTTP (80)**, **HTTPS (443)**, and **SSH (22)**.

7. Click **Launch** and select a key pair.

8. Verify that the instance is running in **EC2 Instances**.

9. SSH to the instance and run:

```
#SSH
ssh -i charles-kp.pem ec2-user@<Public Address>

#Installing docker:
sudo yum update -y
sudo yum install -y docker
sudo systemctl start docker
sudo systemctl enable docker

#Install and Start the ECS Agent
sudo yum update -y
sudo amazon-linux-extras enable ecs
sudo yum install -y ecs-init
sudo systemctl enable --now ecs
echo "ECS_CLUSTER=<Your ECS Cluster Name>" | sudo tee -a /etc/ecs/ecs.config
sudo systemctl restart ecs
systemctl status ecs

```

### Step 4: Confirm EC2 Instance is Registered with ECS

1. Open the [ECS console](https://console.aws.amazon.com/ecs/).

2. Navigate to **Clusters** → `EFS-tutorial`.

3. Click the **ECS Instances** tab.

4. Ensure your EC2 instance appears as **ACTIVE**.

---

### Step 5: Create an IAM Role for ECS Task Execution

1. Open the [IAM console](https://console.aws.amazon.com/iam/).

2. Navigate to **Roles**.

3. Click **Create Role**.

4. **Select Trusted Entity:**
   - **Use case:** `Elastic Container Service Task`
   - Click **Next**.
     
5. **Attach Permissions Policies:**

![Screenshot 2025-03-10 202015](https://github.com/user-attachments/assets/2aae769b-c94b-406d-b9c3-affe251f8171)

6. **Create Inline Policy**
```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Sid": "VisualEditor0",
            "Effect": "Allow",
            "Action": [
                "iam:*",
                "logs:CreateLogStream",
                "iam:PassRole",
                "s3:*",
                "kms:*",
                "ecs:*",
                "cloudformation:*",
                "ecr:*",
                "ec2:*",
                "logs:CreateLogGroup",
                "logs:PutLogEvents",
                "ssm:GetParameters"
            ],
            "Resource": "*"
        }
    ]
}
```
7. **Role Name:** `ecsTaskExecutionRole`.

8. Click **Create Role**.

---

### Step 6: Create a Task Definition for ECS

1. Open the [Amazon ECS console](https://console.aws.amazon.com/ecs/).

2. Navigate to **Task definitions**.

3. Click **Create new task definition** → **Create new task definition with JSON**.

4. Paste the following JSON, replacing `fs-xxxxxxxx` with your EFS File System ID and `arn:aws:iam::<Account ID>:user/<IAM User>` with your Account ID and IAM User:

```json
{
    "family": "efs-charles",
    "executionRoleArn": "arn:aws:iam::010438472482:role/ecsTaskExecutionRole",
    "containerDefinitions": [
        {
            "name": "nginx",
            "image": "public.ecr.aws/docker/library/nginx:latest",
            "cpu": 0,
            "memory": 128,
            "portMappings": [
                {
                    "containerPort": 80,
                    "hostPort": 80,
                    "protocol": "tcp"
                }
            ],
            "essential": true,
            "environment": [],
            "mountPoints": [
                {
                    "sourceVolume": "efs-html",
                    "containerPath": "/usr/share/nginx/html"
                }
            ],
            "volumesFrom": [],
            "systemControls": []
        }
    ],
    "volumes": [
        {
            "name": "efs-html",
            "host": {
                "sourcePath": "/mnt/efs"
            }
        }
    ],
    "placementConstraints": [],
    "requiresCompatibilities": [
        "EC2"
    ]
}
```
5. Click **Create**.

---

### Step 7: Create and Deploy an ECS Service via Console

1. Open **Amazon ECS console** → **Clusters** → `EFS-tutorial`.

2. Navigate to **Services** → Click **Create**.

3. Configure:
   - **Launch type**: EC2
   - **Task Definition**: `efs-tutorial`
   - **Service Name**: `efs-service`
   - **Desired Tasks**: `1`
     
4. Click **Create Service**.

5. Monitor the service to ensure tasks are running.

## **via Terminal**
1. Open Terminal
```
aws ecs run-task --cluster <Cluster Name> --task-definition <Task Definition Name> --launch-type EC2
```
![image](https://github.com/user-attachments/assets/51feba16-fb70-4154-9dfd-93fbe6c7e736)
---

## **Optional: Deploy ECS Service on Fargate**
Update the Task Definition for Fargate Compatibility
1. Open the Amazon ECS console.
2. Navigate to Task definitions.
3. Click on your cluster and select Create new revision.
   
**Modify the JSON to include Fargate compatibility:**

 ```
{
    "family": "efs-charles",
    "containerDefinitions": [
        {
            "name": "nginx",
            "image": "public.ecr.aws/docker/library/nginx:latest",
            "cpu": 0,
            "memory": 128,
            "portMappings": [
                {
                    "containerPort": 80,
                    "hostPort": 80,
                    "protocol": "tcp"
                }
            ],
            "essential": true,
            "mountPoints": [
                {
                    "sourceVolume": "efs-html",
                    "containerPath": "/usr/share/nginx/html"
                }
            ]
        }
    ],
    "executionRoleArn": "arn:aws:iam::010438472482:role/ecsTaskExecutionRole",
    "volumes": [
        {
            "name": "efs-html",
            "efsVolumeConfiguration": {
                "fileSystemId": "fs-xxxxxxxx",
                "rootDirectory": "/",
                "transitEncryption": "ENABLED"
            }
        }
    ],
    "requiresCompatibilities": [
        "FARGATE"
    ],
    "networkMode": "awsvpc",
    "cpu": "256",
    "memory": "512"
}
 ```

4. Click Create.

**Deploy the Task Definition on Fargate**
1. Open the Amazon ECS console → Clusters → EFS-tutorial.
2. Navigate to Services → Click Create.
3. Configure:
   - Launch type: Fargate
   - Task Definition: your task definition
   - Service Name: efs-service-fargate
   - Desired Tasks: 1
   - VPC and Subnets: Select the VPC and private subnets
   - Security Group: Select the security group created for ECS tasks.
4. Click Create Service.
5. Monitor the service to ensure tasks are running.

**via Terminal**

Run the following command:
```
aws ecs run-task --cluster <Cluster Name> --task-definition <Task Definition Name> --launch-type FARGATE --network-configuration "awsvpcConfiguration={subnets=[<Subnet IDs>],securityGroups=[<Security Group IDs>],assignPublicIp="ENABLED"}"
```

![image](https://github.com/user-attachments/assets/4d44a337-77ba-479f-9ca9-e6ed2d9546f6)

## Step 8: Verify the Deployment

1. Open **Amazon ECS console** → **Clusters** → `EFS-tutorial`.

2. Navigate to **Tasks** → Check for a **RUNNING** task.

3. Open **EC2 console** → **Instances** → Locate the instance.

4. Click **Connect** → **EC2 Instance Connect**.

5. Run:
   ```bash
   ls /mnt/efs
   ```

6. Verify that the **nginx container** is running with EFS mounted.

![image](https://github.com/user-attachments/assets/0e6dbb40-a1b8-4b66-ad7b-3af5bde2a0e4)

---

### Step 9: Generate CloudFormation Template

1. Navigate to **AWS CloudFormation**.

2. Click **Create Stack** → **With new resources**.

3. Select **Template is ready** → **Upload a template file**.

4. Upload the exported JSON/YAML configuration.

5. Click **Next**, configure stack name, and follow the prompts.

6. Click **Create Stack** and monitor deployment.

---

## Conclusion
You have successfully configured Amazon EFS with Amazon ECS using the AWS Console, deploying on both EC2 and Fargate! 🚀
