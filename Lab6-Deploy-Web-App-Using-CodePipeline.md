## Overview:

This activity will demonstrate how to deploy a simple Web Application using Github, CodeDeploy, and CodePipeline.

Pre-requisites:
- This activity will require a Cloud9 environment that runs on Ubuntu.



## 1. Create a Github Repository.

1-a. Navigate to the Github console and create your repository.

1-b. Fire up your VS Code environment and create a folder (in this demo my folder name is demo-app). Once the folder is created download the sample application from this URL: 

https://docs.aws.amazon.com/codepipeline/latest/userguide/samples/SampleApp_Linux.zip into the recently created folder.

or using the terminal of your vscode:

```sh
wget https://docs.aws.amazon.com/codepipeline/latest/userguide/samples/SampleApp_Linux.zip
```



1-c. While in the demo-app folder (your folder name might be different) initialize git and push it to your Github repository.


![](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-01.png)




## 2. Create a Web Server


2-a. Navigate to the AWS Console and create an EC2 instance with the following configuration

- AMI: Amazon Linux 2
- Instance Type: t2.micro
- Security Group: Allow HTTP (port 80) and SSH (port 22) to the public
- Subnet: Place the EC2 instance inside a Public Subnet
- Make sure to enable Auto-assign Public IP (You know what will happen if you don’t do this)
- Make sure you have a copy of the SSH keys on your machine


![](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-02.png)



## 3. Create an IAM role for the Web Server

3-a. We need to allow our EC2 instance to use the CodeDeploy service, to do this we need to create a role that allows EC2 access to Code Deploy. Let’s navigate to IAM and create a role.



![](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-03.png)



3-b. Select AWS Service and EC2, and click next.



![](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-04.png)





3-c. Search for AmazonEC2RoleforAWSCodeDeploy and select it, then click next, it’s up to you if you want to set tags.


![](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-05.png)




3-d. Set the name of your role and click create.


3-e. Once the role is created, navigate back to your EC2 instance and attach the role.


![](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-06.png)


![](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-07.png)




## 4. Install the CodeDeploy agent in the EC2 instance.
4-a. Connect to your EC2 instance via SSH, then update the OS by running: 

```
sudo yum update -y
```

4-b. Install ruby by running: 

```
sudo yum install ruby -y
```


![](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-08.png)







4-c. Download the CodeDeploy agent by running: 

```
wget https://aws-codedeploy-ap-southeast-1.s3.ap-southeast-1.amazonaws.com/latest/install
```



4-d. To run the executable installer we need to change the permission of the file first, run the command: 

```
chmod +x ./install
```

Then to run the installer run: 

```

sudo ./install auto
```



![](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-09.png)











## 5. Create an IAM role for the CodeDeploy service.

5-a. Navigate back to the IAM console and create a role.



5-b. Select AWS Service and CodeDeploy. Scroll down and select Codedeploy and click next.


![](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-10.png)




5-c. AWSCodeDeployRole is automatically attached to the role, just click next and add tags if you want.



![](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-11.png)











5-d. Name your role and create

![](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-12.png)



## 6. Configure CodeDeploy


6-a. Navigate to the CodeDeploy console and click create application.

![](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-13.png)



6-b. Set the name of your application and select EC2/On-premises for the compute platform.

![](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-14.png)



6-c. Click on create deployment group.

In AWS CodeDeploy, a deployment group is a set of EC2 instances, on-premises servers, or Lambda functions where an application is deployed. It acts as a target environment for deployments.

![](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-15.png)





6-d. Set the name of your deployment group, select the role we have created earlier, and for the deployment type just select in-place

Which One to Use?
Use In-Place Deployment if you have a simple application with minimal downtime concerns.
Use Blue-Green Deployment for mission-critical applications where availability and rollback speed are priorities.


![](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-16.png)




6-e. For the environment configuration select Amazon EC2 Instances and for the tag, select the name of your EC2 instance.

If your EC2 instance doesn’t have the tag Name, please add it.



6-e. Leave the Agent configuration as default, for the deployment settings, select CodeDeployDefault.OnceAtATime. 


OneAtATime – Deploys one instance at a time, ensuring maximum stability but slow rollout.
HalfAtATime – Deploys to 50% of instances at once, balancing speed and risk.
AllAtOnce – Deploys to all instances simultaneously, fastest but riskiest.
Custom – Lets you define a specific batch size for a flexible rollout.

Then for the load balancer, remove the check that enables load balancing

Click create deployment group.


![](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-18.png)


## 7. Create a CodePipeline

7-a. Navigate to CodePipeline and click on create pipeline.

![](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-19.png)


7-b. Select Build Custom Pipeline

![](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-20.png)


7-c. On the Pipeline settings, give it a name.

Execution Mode: Superseded
Service Role: Create a new service role or use an existing one.


![](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-21.png)


7-d. On the "Add Source stage" select Github (via Github App). Choose the repo you created in the beginning of the lab and the default branch should be main branch.

![](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-22.png)

7-e. Skip the build stage and test stage, in this demo, we will deploy an app that does not require a build service.

![](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-23.png)



7-f. For the deploy stage, select your CodeDeploy and deployment group.

![](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-24.png)

Click next.



7-g. Briefly review your configuration and click create pipeline. Once the pipeline is created, it will start deploying our application from the Github repository.


![](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-25.png)


7-h. Once it’s all green grab the EC2 Public DNS and resolve it in a web browser.

![](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-26.png)



## 8. Modify the code

8-a. Go back to your VS Code and modify index.html
You can put anything there as long as it is HTML. If you don't have anything just change the title to "Updated Sample Deployment" and change the background-color.

Once you have made the changes, commit and push it to your Github Repository.

```
git add .
git commit -m "Updated Sample Deployment"
git push
```


![](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-29.png)







8-b. Once you have pushed your changes, CodePipeline will automatically deploy your changes to the Server. Once CodePipeline is all green, open the website again in a web browser.

There’s a chance that the website was cached on the web browser, if that happens just add ?t=1 at the end of the URL.

![](https://github.com/apperph/awslabs-markdown/blob/2e1bcd7098a7c9c6d9f5db7251149effc9211582/CDMP%201.0/CDMP%202.0/images/image7-30.png)


And there! You have successfully created a working CodePipeline!


Well done!
