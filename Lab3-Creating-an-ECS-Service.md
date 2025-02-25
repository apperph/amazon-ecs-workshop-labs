# Lab 3: Deploying a Flask Application as an ECS Service

## Objective

In this lab, you will take your Flask application from Lab 2 and deploy it as an Amazon ECS Service. By setting up an ECS service, you ensure that your application can be reliably run and scaled across multiple containers.

## Prerequisites

Before starting this lab, ensure you have completed Lab 2, where you created and deployed a Flask application as a task. You should also be familiar with Docker, AWS CLI, and ECS basics.

## Steps

### Step 1: Create a Load Balancer (Optional)

For better service availability and scalability, you may want to front your ECS service with a load balancer. You can skip this step if you're just experimenting with minimal configurations, but it's recommended for production scenarios.

1. **Create an Application Load Balancer using the AWS Management Console:**

   - Navigate to EC2 and select Load Balancers.
   - Click "Create Load Balancer" and choose "Application Load Balancer".
   - Configure with the following:
     - Name: `flask-app-lb`
     - Scheme: Internet-facing
     - Listeners: HTTP on port 80
     - Availability zones: Select at least two subnets in different AZs
   - Configure security groups to allow HTTP traffic.
   - Skip the target groups for now, you will create one while setting up the service.

### Step 2: Register an ECS Task Definition (using Flask App)

Ensure your task definition is similar to the following with your specifics:

```json
{
  "family": "flask-app-task",
  "networkMode": "awsvpc",
  "executionRoleArn": "arn:aws:iam::<your-account-id>:role/ECSExecutionRole",
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
      ]
    }
  ],
  "requiresCompatibilities": [
    "FARGATE"
  ],
  "cpu": "256",
  "memory": "512"
}
```

* Ensure you have registered this task definition with ECS using the AWS CLI as shown in Lab 2.

### Step 3: Create an ECS Service

1. **Create the service using the AWS CLI:**

   Determine your subnet and security group IDs for the service. Then, run:

   ```bash
   aws ecs create-service --cluster flask-app-cluster \
     --service-name flask-app-service \
     --task-definition flask-app-task \
     --launch-type FARGATE \
     --platform-version 1.4.0 \
     --desired-count 1 \
     --network-configuration "awsvpcConfiguration={subnets=[<your-subnet-id>],securityGroups=[<your-security-group-id>],assignPublicIp=ENABLED}" \
     --load-balancers "targetGroupArn=<your-target-group-arn>,containerName=flask-app-container,containerPort=5000"
   ```

   **Note:** Replace `<your-subnet-id>`, `<your-security-group-id>`, and `<your-target-group-arn>` with the appropriate IDs from your environment.

2. **Optional: Attach Load Balancer**

   If you created a load balancer, associate it with your service:

   - Create a target group in the EC2 Management Console with Protocol: HTTP and the port your application listens on (5000).
   - Register your ECS tasks to this target group.
   - Update the load balancer listeners to point to your target group.

### Step 4: Validate the Service

1. **Monitor the Service Using AWS CLI or Console:**

   - Use the AWS Management Console to monitor your service. Navigate to ECS, select Clusters > `flask-app-cluster` > Services.
   - Confirm that the desired number of tasks are running.

2. **Test the Flask Application:**

   - Obtain the public IP or DNS of your load balancer (if configured) or the ECS service.
   - Access your application through the browser or `curl` using the endpoint, for example:
   
     ```bash
     curl http://<load-balancer-or-ecs-endpoint>/?name=Student
     ```

   You should receive a response similar to `Hello, Student!`.

## Conclusion

Congratulations! You have successfully deployed a Flask application as an ECS service. This service can scale automatically, handle traffic through load balancing, and is monitored by ECS for high availability.

### Next Steps

In future labs, we will explore ECS service scaling, deployment strategies, and more advanced features like service mesh or CI/CD pipelines integrated with ECS.