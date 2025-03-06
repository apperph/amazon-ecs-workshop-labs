# Lab 13: Configuring Amazon EFS File Systems for Amazon ECS using the Console

https://docs.aws.amazon.com/AmazonECS/latest/developerguide/tutorial-efs-volumes.html

## **Objective**
This lab will guide you through configuring an Amazon Elastic File System (EFS) for Amazon Elastic Container Service (ECS) using the AWS Management Console. At the end of this lab, you will also generate a CloudFormation template to automate the setup.

---

## **Step 1: Create an Amazon EFS File System**
1. Open the [Amazon EFS console](https://console.aws.amazon.com/efs/).
2. Click **Create file system**.
3. Configure the following:
   - **Name**: `EFS-tutorial`
   - **Virtual Private Cloud (VPC)**: Select the VPC where your ECS cluster resides.
4. Click **Create**.
5. Note down the **File System ID** (e.g., `fs-xxxxxxxx`).

---

## **Step 2: Create an ECS Cluster**
1. Open the [Amazon ECS console](https://console.aws.amazon.com/ecs/).
2. Navigate to **Clusters**.
3. Click **Create Cluster**.
4. Select **EC2 Linux + Networking** and click **Next step**.
5. Configure the cluster:
   - **Cluster name**: `EFS-tutorial`
   - **Provisioning Model**: On-Demand.
   - **EC2 instance type**: `t2.micro` (or any appropriate type).
   - **Number of instances**: `1`.
   - **Key pair**: Choose an existing key pair or create a new one.
6. Click **Create**.
7. Once created, verify that your cluster is listed in the **Clusters** section.

---

## **Step 3: Launch an EC2 Instance and Register it with ECS**
### **Step 3.1: Launch an EC2 Instance**
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

9. Connect to the instance and run:

```
#Installing docker:
sudo yum update -y
sudo yum install -y docker
sudo systemctl start docker
sudo systemctl enable docker

#Installing ecs agent
sudo yum install -y ecs-init
sudo systemctl start ecs
sudo systemctl enable ecs

#configure ecs
echo "ECS_CLUSTER=your-cluster-name" | sudo tee -a /etc/ecs/ecs.config
sudo systemctl restart ecs

```

### **Step 3.2: Confirm EC2 Instance is Registered with ECS**
1. Open the [ECS console](https://console.aws.amazon.com/ecs/).
2. Navigate to **Clusters** → `EFS-tutorial`.
3. Click the **ECS Instances** tab.
4. Ensure your EC2 instance appears as **ACTIVE**.

---

## **Step 4: Create an IAM Role for ECS Task Execution**
1. Open the [IAM console](https://console.aws.amazon.com/iam/).
2. Navigate to **Roles**.
3. Click **Create Role**.
4. **Select Trusted Entity:**
   - **Use case:** `Elastic Container Service Task`
   - Click **Next**.
5. **Attach Permissions Policies:**
   - `AmazonECSTaskExecutionRolePolicy`
   - `AmazonElasticFileSystemFullAccess`
6. **Role Name:** `ecsTaskExecutionRole`.
7. Click **Create Role**.

---

## **Step 5: Create a Task Definition for ECS**
1. Open the [Amazon ECS console](https://console.aws.amazon.com/ecs/).
2. Navigate to **Task definitions**.
3. Click **Create new task definition** → **Create new task definition with JSON**.
4. Paste the following JSON, replacing `fs-xxxxxxxx` with your EFS File System ID:
   ```json
   {
       "containerDefinitions": [
           {
               "memory": 128,
               "portMappings": [
                   {
                       "hostPort": 80,
                       "containerPort": 80,
                       "protocol": "tcp"
                   }
               ],
               "essential": true,
               "mountPoints": [
                   {
                       "containerPath": "/usr/share/nginx/html",
                       "sourceVolume": "efs-html"
                   }
               ],
               "name": "nginx",
               "image": "public.ecr.aws/docker/library/nginx:latest"
           }
       ],
       "volumes": [
           {
               "name": "efs-html",
               "efsVolumeConfiguration": {
                   "fileSystemId": "fs-xxxxxxxx",
                   "transitEncryption": "ENABLED"
               }
           }
       ],
       "family": "efs-tutorial",
       "executionRoleArn": "arn:aws:iam::111122223333:role/ecsTaskExecutionRole"
   }
   ```
5. Click **Create**.

---

## **Step 6: Create and Deploy an ECS Service**
1. Open **Amazon ECS console** → **Clusters** → `EFS-tutorial`.
2. Navigate to **Services** → Click **Create**.
3. Configure:
   - **Launch type**: EC2
   - **Task Definition**: `efs-tutorial`
   - **Service Name**: `efs-service`
   - **Desired Tasks**: `1`
4. Click **Create Service**.
5. Monitor the service to ensure tasks are running.

---

## **Step 7: Verify the Deployment**
1. Open **Amazon ECS console** → **Clusters** → `EFS-tutorial`.
2. Navigate to **Tasks** → Check for a **RUNNING** task.
3. Open **EC2 console** → **Instances** → Locate the instance.
4. Click **Connect** → **EC2 Instance Connect**.
5. Run:
   ```bash
   ls /mnt/efs
   ```
6. Verify that the **nginx container** is running with EFS mounted.

---

## **Step 8: Generate CloudFormation Template**
1. Navigate to **AWS CloudFormation**.
2. Click **Create Stack** → **With new resources**.
3. Select **Template is ready** → **Upload a template file**.
4. Upload the exported JSON/YAML configuration.
5. Click **Next**, configure stack name, and follow the prompts.
6. Click **Create Stack** and monitor deployment.

---

## **Conclusion**
You have successfully configured **Amazon EFS with Amazon ECS** using the AWS Console! 🚀
