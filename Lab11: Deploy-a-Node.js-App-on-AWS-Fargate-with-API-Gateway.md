# Lab 9: Deploy a Node.js App on AWS Fargate with API Gateway

## Overview:
In this lab, we will deploy a Node.js application on AWS Fargate using Docker, ECS, ECR, and API Gateway. This lab is intended for users authenticating via IAM User and working from a local Windows machine.

## 1. Prerequisites

**1-a. Install Required Tools**

- AWS CLI v2
   - Download and install AWS CLI v2 from the official AWS website.
   - Verify installation: ```aws --version```
- Docker Desktop for Windows
   - Download and install Docker Desktop for Windows.
   - Verify installation: ```docker --version```
   - Ensure Docker Desktop is running Linux containers.
- Node.js
   - Download and install Node.js from the official Node.js website.
   - Verify installation: ```node -v```
  
**1-b. Create an IAM User with Programmatic Access (If No IAM Users Exist)**

- Go to AWS Console → IAM → Users → Add user.
- Enter a username (e.g., fargate-user).
- Attach the ```AdministratorAccess policy``` (or create custom permissions for ECS, ECR, and API Gateway).
- Copy the **Access Key ID & Secret Access Key.**

**1-c. Configure AWS CLI with the IAM User**

- Open PowerShell or Command Prompt.
- Run: ```aws configure```
- Enter the **Access Key ID & Secret Access Key.**
- Set your AWS region (e.g., ap-southeast-1).
- Choose ```json``` as the output format.

Verify configuration: aws sts get-caller-identity
If your backend is functioning properly, you should get a valid response from the endpoint.

---

This concludes the lab. If you encounter any issues, refer to AWS documentation or seek support. 
