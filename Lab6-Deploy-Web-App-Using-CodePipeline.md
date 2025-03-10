# Lab 6: Deploying a Web Application Using GitHub, CodeDeploy, and CodePipeline

## Overview

This activity demonstrates how to deploy a simple web application using GitHub, CodeDeploy, and CodePipeline.

## Prerequisites

- This activity requires a Cloud9 environment that runs on Ubuntu.

## 1: Create a GitHub Repository

**1-a. Navigate to GitHub**

   - Go to the GitHub console and create a new repository.

**1-b. Create and Download Sample Application**

   - Open your VS Code environment and create a folder (e.g., `demo-app`).
   - Download the sample application into this folder:

   ```sh
   wget https://docs.aws.amazon.com/codepipeline/latest/userguide/samples/SampleApp_Linux.zip
   ```

**1-c. Initialize Git and Push to Repository**

   - While in the `demo-app` folder, initialize Git and push the code to your GitHub repository.

   ![Git Push](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-01.png)

## 2: Create a Web Server

**2-a. Launch an EC2 Instance**

   - Navigate to the AWS Console and launch an EC2 instance with the following configuration:
     - AMI: Amazon Linux 2
     - Instance Type: t2.micro
     - Security Group: Allow HTTP (port 80) and SSH (port 22)
     - Subnet: Place the instance in a public subnet
     - Ensure Auto-assign Public IP is enabled

   ![EC2 Configuration](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-02.png)

## 3: Create an IAM Role for the Web Server

**3-a. Navigate to IAM**

   - Create a role that allows the EC2 instance to use CodeDeploy.

   ![Create IAM Role](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-03.png)

**3-b. Select Role for EC2**

   - Choose AWS Service, then EC2, and proceed.

   ![Select EC2 Service](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-04.png)

**3-c. Attach Policy**

   - Search for and select `AmazonEC2RoleforAWSCodeDeploy`, then proceed to the next steps.

   ![Attach Policy](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-05.png)

**3-d. Name and Create the Role**

   - Set the name of your role and create it.

**3-e. Attach Role to EC2 Instance**

   - Attach the newly created role to your EC2 instance.

   ![Attach Role](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-06.png)

## 4: Install the CodeDeploy Agent in the EC2 Instance

**4-a. Update the OS**

   - Connect to your EC2 instance via SSH and run:

   ```sh
   sudo yum update -y
   ```

**4-b. Install Ruby**

   - Install Ruby by running:

   ```sh
   sudo yum install ruby -y
   ```

   ![Install Ruby](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-08.png)

**4-c. Download and Install CodeDeploy Agent**

   - Download the CodeDeploy agent:

   ```sh
   wget https://aws-codedeploy-ap-southeast-1.s3.ap-southeast-1.amazonaws.com/latest/install
   ```

**4-d. Run the Installer**

   - Change permissions and run the installer:

   ```sh
   chmod +x ./install
   sudo ./install auto
   ```

   ![Install Agent](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-09.png)

## 5: Create an IAM Role for the CodeDeploy Service

**5-a. Create a New IAM Role**

   - Navigate back to the IAM console and create a role for CodeDeploy.

**5-b. Select Role for CodeDeploy**

   - Choose AWS Service, select CodeDeploy, and proceed.

   ![Role for CodeDeploy](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-10.png)

**5-c. Attach Policy**

   - The `AWSCodeDeployRole` is automatically attached. Proceed with the role creation.

   ![Attach Policy CodeDeploy](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-11.png)

**5-d. Name and Complete Role Creation**

   - Name your role and finish the creation process.

## 6: Configure CodeDeploy

**6-a. Create CodeDeploy Application**

   - Go to the CodeDeploy console and create an application.

   ![Create Application](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-13.png)

**6-b. Set Application Details**

   - Enter the name of your application and choose EC2/On-premises for the compute platform.

   ![Application Details](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-14.png)

**6-c. Create Deployment Group**

   - Click to create a deployment group, providing necessary configurations like deployment group name and role.

   ![Deployment Group](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-15.png)

**6-d. Set Deployment Type and Configuration**

   - Choose in-place deployment for deployment type and configure environment and deployment settings.

   ![Deployment Configuration](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-16.png)

**6-e. Finalize Deployment Group**

   - Finalize the settings and create the deployment group, ensuring proper tags and load balancer configurations if necessary.

   ![Finalize Deployment Group](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-18.png)

## Step 7: Create a CodePipeline

**7-a. Create a New Pipeline**

   - Navigate to CodePipeline and start creating a new pipeline.

   ![Create Pipeline](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-19.png)

**7-b. Set Pipeline Settings**

   - Choose 'Build Custom Pipeline', provide a name, and set the execution mode and service role.

![](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-20.png)


**7-c.Add Source Stage**

   - For the source stage, select GitHub (via GitHub App), and choose the repository you created at the beginning of the lab. Ensure the default branch is set to the main branch.

   ![Add Source Stage](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-22.png)

**7-d. Skip Build and Test Stages**

   - Since the application does not require a build service, skip the build and test stages in the pipeline setup.

   ![Skip Stages](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-23.png)

**7-e. Configure Deploy Stage**

   - For the deploy stage, select CodeDeploy and specify your deployment group.

   ![Configure Deploy Stage](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-24.png)

   - Click 'Next' to proceed.

**7-f. Review and Create Pipeline**

   - Briefly review your configuration and click 'Create Pipeline'. Once the pipeline is created, it will start deploying the application from your GitHub repository.

   ![Review and Create](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-25.png)

**7-g. Verify Deployment**

   - Once the pipeline is all green, grab the EC2 Public DNS and visit it in a web browser to verify the deployment.

   ![Verify Deployment](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-26.png)

## 8: Modify the Code

**8-a. Edit the HTML File**

   - Go back to your VS Code and modify `index.html`. You can add any HTML content; for example, change the title to "Updated Sample Deployment" and adjust the background color.

**8-b. Commit and Push Changes**

   - Once you've made changes, commit and push them to your GitHub repository:

   ```sh
   git add .
   git commit -m "Updated Sample Deployment"
   git push
   ```

   ![Commit and Push](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-29.png)

**8-b. Verify Updated Deployment**

   - CodePipeline automatically deploys your changes to the server. Once the pipeline is green, open the website again in a web browser.

   - If the website loads the old version, it might be cached. Add `?t=1` to the end of the URL to bypass the cache.

   ![Verify Updated Deployment](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-30.png)

---

## Conclusion

You have successfully created a working CodePipeline to deploy a web application using GitHub and CodeDeploy. Well done! Ensure all resources are accounted for and cleaned up as needed.
