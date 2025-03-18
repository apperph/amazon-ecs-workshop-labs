# Lab 5: Creating Amazon ECS Fargate Service with CloudFormation

## Overview

This lab guides you through deploying an ECS service running Nginx on AWS Fargate using a CloudFormation template.

## 1. Prerequisites

**1. Install Required Tools**

- AWS CLI: Installed and configured.
- AWS Account: IAM permissions to create CloudFormation stacks.
- An existing VPC and subnet.

## 2. Configure AWS CLI

**1. Setup AWS CLI**

- Ensure your AWS CLI is configured properly by running:
   ```sh
   aws configure
   ```

## 3. Create CloudFormation Template

1. Save the CloudFormation Template**

- Save the following YAML template as ecs-fargate.yaml:

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

## 4. Deploy the CloudFormation Stack

1. Create the CloudFormation Stack**

- Run the following command, replacing ```<VPC_ID>``` and ```<SUBNET_ID>``` with your values:

   ```sh
   aws cloudformation create-stack \
     --stack-name ecs-fargate-lab \
     --template-body file://ecs-fargate.yaml \
     --parameters ParameterKey=VPCId,ParameterValue=<VPC_ID> \
                  ParameterKey=SubnetId,ParameterValue=<SUBNET_ID> \
     --capabilities CAPABILITY_NAMED_IAM
   ```

## 5. Monitor Deployment

1. Check Stack Creation Progress**

- Use the following command to check the stack creation progress:
  
```sh
aws cloudformation describe-stacks --stack-name ecs-fargate-lab
```

- Wait until the status changes to ```CREATE_COMPLETE```.
  
## 6. Validate ECS Service

1. List Running ECS Services**

- Check the list of running ECS services with:
  
```sh
aws ecs list-services --cluster MyECSCluster
```

2. Describe the Service**

- Describe the newly created ECS service using:
  
```sh
aws ecs describe-services --cluster MyECSCluster --services MyECSService
```

## 7. Test the Nginx Deployment

1. Retrieve Public IP**

- Retrieve the public IP of the running task:
```sh
aws ecs list-tasks --cluster MyECSCluster
```
```sh
aws ecs describe-tasks --cluster MyECSCluster --tasks <TASK_ID>
```

2. Verify Nginx Welcome Page**

- Open the public IP in a browser to verify the Nginx welcome page.

## 8. Cleanup

1. Delete the Deployed Resources**

- To delete the deployed resources, run:

```sh
aws cloudformation delete-stack --stack-name ecs-fargate-lab
```

2. Confirm Deletion**

- Confirm that the stack has been deleted with:

```sh
aws cloudformation describe-stacks --stack-name ecs-fargate-lab
```
__________________________________________________________________________

This concludes the lab exercise. If you have any questions or need further assistance, please consult AWS documentation or reach out to your administrator.
