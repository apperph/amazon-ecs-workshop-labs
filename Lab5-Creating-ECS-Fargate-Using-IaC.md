# Lab: Deploying an ECS Fargate Service with CloudFormation

## Objective
This lab will guide you through deploying an ECS service running Nginx on AWS Fargate using a CloudFormation template.

## Prerequisites
- An AWS account
- AWS CLI installed and configured
- CloudFormation permissions
- An existing VPC and subnet

## Step 1: Set Up AWS CLI
Ensure your AWS CLI is configured properly:
```sh
aws configure
```
Provide your AWS Access Key, Secret Key, default region, and output format.

## Step 2: Save the CloudFormation Template
Save the following CloudFormation YAML template as `ecs-fargate.yaml`:
```yaml
Parameters:
  VPCId:
    Type: AWS::EC2::VPC::Id
    Description: "ID of the existing VPC"
  
  SubnetId:
    Type: AWS::EC2::Subnet::Id
    Description: "ID of the existing subnet in the VPC"
  
  ECSClusterName:
    Type: String
    Description: "The name of the ECS Cluster"
    Default: "MyECSCluster"

Resources:
  MySecurityGroup:
    Type: AWS::EC2::SecurityGroup
    Properties:
      VpcId: !Ref VPCId
      GroupDescription: "Allow HTTP traffic"
      SecurityGroupIngress:
        - IpProtocol: "tcp"
          FromPort: 80
          ToPort: 80
          CidrIp: "0.0.0.0/0"

  ECSCluster:
    Type: AWS::ECS::Cluster
    Properties:
      ClusterName: !Ref ECSClusterName

  MyTaskDefinition:
    Type: AWS::ECS::TaskDefinition
    Properties:
      Family: "NGINXTaskFamily"
      Cpu: "256"
      Memory: "512"
      NetworkMode: "awsvpc"
      ContainerDefinitions:
        - Name: "nginx-container"
          Image: "nginx"
          Memory: 256
          Cpu: 128
          PortMappings:
            - ContainerPort: 80

  MyECSService:
    Type: AWS::ECS::Service
    Properties:
      Cluster: !Ref ECSCluster
      DesiredCount: 1
      TaskDefinition: !Ref MyTaskDefinition
      LaunchType: FARGATE
      NetworkConfiguration:
        AwsvpcConfiguration:
          Subnets:
            - !Ref SubnetId
          SecurityGroups:
            - !Ref MySecurityGroup
          AssignPublicIp: ENABLED
```

## Step 3: Deploy the CloudFormation Stack
Run the following command, replacing `<VPC_ID>` and `<SUBNET_ID>` with your values:
```sh
aws cloudformation create-stack \
  --stack-name ecs-fargate-lab \
  --template-body file://ecs-fargate.yaml \
  --parameters ParameterKey=VPCId,ParameterValue=<VPC_ID> \
               ParameterKey=SubnetId,ParameterValue=<SUBNET_ID> \
  --capabilities CAPABILITY_NAMED_IAM
```

## Step 4: Monitor Deployment
Check the stack creation progress:
```sh
aws cloudformation describe-stacks --stack-name ecs-fargate-lab
```
Wait until the status changes to `CREATE_COMPLETE`.

## Step 5: Validate ECS Service
List running ECS services:
```sh
aws ecs list-services --cluster MyECSCluster
```
Describe the service:
```sh
aws ecs describe-services --cluster MyECSCluster --services MyECSService
```

## Step 6: Test the Nginx Deployment
Retrieve the public IP of the running task:
```sh
aws ecs list-tasks --cluster MyECSCluster
```
```sh
aws ecs describe-tasks --cluster MyECSCluster --tasks <TASK_ID>
```
Open the public IP in a browser and verify the Nginx welcome page.

## Step 7: Cleanup Resources
To delete the deployed resources, run:
```sh
aws cloudformation delete-stack --stack-name ecs-fargate-lab
```
Confirm deletion using:
```sh
aws cloudformation describe-stacks --stack-name ecs-fargate-lab