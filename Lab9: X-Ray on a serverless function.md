# Lab 9: Using AWS X-Ray on a Serverless Function

## Overview

This lab demonstrates how to instrument a serverless application with AWS X-Ray to monitor and trace requests. You will deploy an application using the AWS SAM CLI and use X-Ray to analyze its performance.

## Steps

### Step 1: Create an Application Using SAM CLI

1. **Create an S3 Bucket**

   - Open AWS CloudShell and create an S3 bucket:
   
     ```sh
     aws s3 mb s3://sagesoft-<last name>-03042025
     ```

2. **Initialize the Application**

   - Run the following command to update SAM and initialize your application:
   
     ```sh
     pip install --upgrade aws-sam-cli
     sam init --runtime python3.9 --name xray-demo-app
     ```

   - Follow the configuration settings as shown in the image below:

   ![SAM Init Configuration](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+5/Lab+23/image2.png)

3. **Build the Application**

   - Build the application:
   
     ```sh
     cd xray-demo-app/
     sam build
     ```

   ![SAM Build](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+5/Lab+23/image3.png)

4. **Package the Application**

   - Package the application for deployment:
   
     ```sh
     sam package --output-template-file packaged.yaml --s3-bucket <your-bucket-name> --no-resolve-s3
     ```

   ![SAM Package](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+5/Lab+23/image4.png)

5. **Deploy the Application**

   - Deploy the packaged application:
   
     ```sh
     sam deploy --template-file packaged.yaml --capabilities CAPABILITY_IAM --stack-name <your-stack-name>
     ```

   ![SAM Deploy](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+5/Lab+23/image5.png)

### Step 2: Create a Single Script to Deploy the Application

1. **Create a Deployment Script**

   - In the parent directory of your application, create a file named `deploy.sh` and add the following content:
   
     ```sh
     set -e
     S3_BUCKET="<your-bucket-name>"
     sam build
     sam package --output-template-file packaged.yaml --s3-bucket $S3_BUCKET --no-resolve-s3
     sam deploy --template-file packaged.yaml --capabilities CAPABILITY_IAM --stack-name <your-stack-name>
     ```

   ![Deploy Script](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+5/Lab+23/image6.png)

2. **Make the Script Executable**

   - Make the script executable by running:
   
     ```sh
     chmod +x deploy.sh
     ```

   ![Make Executable](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+5/Lab+23/image7.png)

3. **Test the Deployment Script**

   - Execute the script to deploy the application:
   
     ```sh
     ./deploy.sh
     ```

   ![Test Script](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+5/Lab+23/image8.png)

### Step 3: Update the Application

1. **Modify the Timeout Setting**

   - In the `template.yaml` file, set the `Timeout` value to 30.

2. **Update `app.py`**

   - Update `app.py` with new code from this Gist: [Gist Link](https://gist.github.com/mikerayco/18396083d1296145d4010a8359020085).

   ![Modify Template](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+5/Lab+23/image10.png)

3. **Deploy the Changes**

   - Deploy the updated application using:
   
     ```sh
     ./deploy.sh
     ```

   ![Deploy Updates](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+5/Lab+23/image11.png)

4. **Enable X-Ray Tracing**

   - In the AWS Lambda console, navigate to Monitoring and Operations tools, click edit, and enable active tracing.

   ![Enable Tracing](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+5/Lab+23/image12.png)

### Step 4: Test the Application

1. **Create a Test Event**

   - In the Lambda console, create a new test event, name your event, and click create.

   ![Create Test Event](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+5/Lab+23/image14.png)
   ![Name Test Event](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+5/Lab+23/image15.png)

2. **Run the Test**

   - Click the test button three times to execute the function.

   ![Run Test](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+5/Lab+23/image16.png)

3. **Resolve Python Dependency Error**

   - If you encounter an error stating "no module named ‘aws_xray_sdk’," add the module to `requirements.txt` in the `hello_world` directory, then redeploy the application using your deployment script.

4. **View X-Ray Traces**

   - Once the function is successfully invoked, navigate to Monitor > Traces in the Lambda console to view the function traces.

   ![View Traces](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+5/Lab+23/image20.png)

---

## Clean-Up

After completing the lab, it's essential to clean up resources to avoid unnecessary costs. Create a `cleanup.sh` script and add the following code:

```sh
#!/bin/bash

# Define AWS region
AWS_REGION="ap-southeast-1"

# Set the name of your S3 bucket and CloudFormation stack
S3_BUCKET_NAME="your-s3-bucket-name"
STACK_NAME="your-cloudformation-stack-name"

# Step 1: Delete the S3 Bucket
echo "Deleting S3 bucket: $S3_BUCKET_NAME"
aws s3 rb s3://$S3_BUCKET_NAME --force --region $AWS_REGION

# Step 2: Delete the CloudFormation Stack
echo "Deleting CloudFormation stack: $STACK_NAME"
aws cloudformation delete-stack --stack-name $STACK_NAME --region $AWS_REGION

# Step 3: Wait for the stack deletion to complete
echo "Waiting for stack deletion to complete..."
aws cloudformation wait stack-delete-complete --stack-name $STACK_NAME --region $AWS_REGION

# Step 4: Confirm resources are deleted
echo "Listing remaining CloudFormation stacks..."
aws cloudformation describe-stacks --region $AWS_REGION

echo "Cleanup completed!"
```

To execute the cleanup, run:

```sh
chmod +x cleanup.sh
./cleanup.sh
```

**Thank you for completing the lab! Great job!**
