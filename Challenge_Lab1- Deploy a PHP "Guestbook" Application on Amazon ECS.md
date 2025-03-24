# Challenge: Deploy a PHP "Guestbook" Application on Amazon ECS

In this challenge, you’ll create a simple PHP "Guestbook" application where users can submit messages that are stored and displayed on the page. You’ll containerize it using Docker, push it to Amazon ECR, and deploy it on your existing ECS cluster, making it publicly accessible.

Get the  [application code](./ChallengeLab01) for reference.

## Instructions

1. **Create the PHP Application:**
   - Set up a new directory for your project (e.g., `php-guestbook`).
   - Write a PHP script named `index.php` that:
     - Displays a form for users to submit a message.
     - Saves submitted messages to a file (e.g., `messages.txt`).
     - Reads and displays all saved messages in a list.
     - *Hint:* Use PHP’s file handling functions and basic HTML for the interface.

2. **Create a Dockerfile:**
   - In the same directory, create a `Dockerfile` to containerize the PHP app.
   - Use an official PHP image with a web server (e.g., Apache).
   - Copy your `index.php` into the appropriate directory for the web server.
   - Ensure the web server can write to the file system (e.g., adjust permissions).
   - Expose the port used by the web server (typically 80).

3. **Build and Push the Docker Image to ECR:**
   - Define unique variable names for your Docker and ECR repositories (e.g., include your name to avoid conflicts).
   - Build the Docker image locally using the Dockerfile.
   - Create an ECR repository if it doesn’t exist, then tag and push your image to it.
   - Reuse the AWS CLI and Docker login steps from the lab to authenticate with ECR.

4. **Update the ECS Task Definition:**
   - Create a `task-definition.json` file for your PHP application.
   - Configure it to:
     - Use the Fargate launch type with appropriate CPU and memory settings (e.g., 256 CPU, 512 MB memory).
     - Reference your ECR image.
     - Map the container’s web server port (e.g., 80) to the host.
     - Include an execution role ARN (from the lab).
     - Set up CloudWatch logging with a unique log group name.
   - Register the task definition with the AWS CLI.

5. **Deploy and Test:**
   - Deploy the task to your existing ECS cluster using the Fargate launch type.
   - Ensure the network configuration includes a valid subnet, security group, and public IP assignment (reuse values from the lab if applicable).
   - Once the task is running, find its public IP in the ECS console.
   - Access the application in a browser using the public IP (e.g., `http://<public-ip>`), submit a message, and verify it appears in the list.
   - Check the CloudWatch Logs for the task to ensure logging is working.


