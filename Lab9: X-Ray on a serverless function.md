## Overview:

This activity will demonstrate how to use X-Ray on a serverless function.


## 1. Create an application using SAM CLI

1-a. Go to Cloudshell and create an S3 bucket by running this command: 

```
aws s3 mb s3://sagesoft-<last name>-03042025
```


1-b. Create an application by running:


```
#update sam

pip install --upgrade aws-sam-cli
sam init --runtime python3.9 --name xray-demo-app
```

Follow the configuration from the image below

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+5/Lab+23/image2.png)

1-c build the application by running:

```
cd xray-demo-app/
sam build
```

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+5/Lab+23/image3.png)



1-d. Package the application by running: 

```
sam package --output-template-file packaged.yaml --s3-bucket <your-bucket-name> --no-resolve-s3
```


![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+5/Lab+23/image4.png)




1-e. Deploy the application with this command: 

```
sam deploy --template-file packaged.yaml --capabilities CAPABILITY_IAM --stack-name <your-stack-name>
```


![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+5/Lab+23/image5.png)











## 2. Create a single script to deploy the application 

2-a. To make the deployment faster create a bash script inside the parent directory of your application and name it deploy.sh


Then paste this in the deploy.sh file, make sure to change the values for the S3_BUCKET and --stack-name



```
set -e
S3_BUCKET="<your-bucket-name>"
sam build
sam package --output-template-file packaged.yaml --s3-bucket $S3_BUCKET --no-resolve-s3
sam deploy --template-file packaged.yaml --capabilities CAPABILITY_IAM --stack-name <your-stack-name>
```


![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+5/Lab+23/image6.png)



2-b. To make the bash script executable, run the command:

```
chmod +x deploy.sh
```

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+5/Lab+23/image7.png)


2-c. Test the bash script by running: 

```
./deploy.sh
```

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+5/Lab+23/image8.png)



## 3. Update the application

3-a. Update the template.yaml file by setting the Timeout value to 30.

3-b. Update the contents of the app.py with the code from this Gist file by going to the lambda function and pasting the code:

https://gist.github.com/mikerayco/18396083d1296145d4010a8359020085

3-b. Update the template.yaml file by setting the Timeout value to 30.


![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+5/Lab+23/image10.png)



3-c. Deploy the changes by running using our bash script: 

```
./deploy.sh
```

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+5/Lab+23/image11.png)



3-d. Once it’s deployed, navigate to the Lambda console on your web browser and go to Monitoring and Operations tools, click edit.


![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+5/Lab+23/image12.png)





3-e. Enable active tracing and click save.

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+5/Lab+23/image13.png)





## 4 Test the application

4-a. On the Lambda console, create a new test event.

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+5/Lab+23/image14.png)


And name your event, then click create.


![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+5/Lab+23/image15.png)





4-b. Run the test by clicking the test button 3 times.

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+5/Lab+23/image16.png)



4-c. You will encounter an error that says: no module named ‘aws_xray_sdk’

It means that the said library is not yet installed in our package, without SAM CLI, you need to install the library and repackage your app. Move to the next step to resolve the issue.

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+5/Lab+23/image17.png)


4-d. To install and include the aws_xray_sdk in our library just add it to the requirements.txt file on the codebase. (You may find the file under hello_world > requirements.txt)

Save the requirements.txt file and redeploy the app using our bash deployment script.

4-e. Once it’s deployed, the code might be hidden from the console due to a larger package size, to invoke it go to test and click invoke 3 times

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+5/Lab+23/image19.png)


4-e. Navigate to Monitor > Traces and you will see what happened to the functions that ran during our test.


![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+5/Lab+23/image20.png)



----------
```
## Clean-up

After submitting, please run the following to delete everything. Make sure to replace the AWS_Region. s3 bucket name and stack name with your own.

Create a cleanup.sh file and enter the following code:

```
#!/bin/bash

# Define AWS region
AWS_REGION="ap-southeast-1"

# Optionally, set the name of your S3 bucket and CloudFormation stack
S3_BUCKET_NAME="your-s3-bucket-name"
STACK_NAME="your-cloudformation-stack-name"

# Step 1: Delete the S3 Bucket
echo "Deleting S3 bucket: $S3_BUCKET_NAME"
aws s3 rb s3://$S3_BUCKET_NAME --force --region $AWS_REGION

# Step 2: Delete the CloudFormation Stack
echo "Deleting CloudFormation stack: $STACK_NAME"
aws cloudformation delete-stack --stack-name $STACK_NAME --region $AWS_REGION

# Step 3: Wait for the stack deletion to complete (if required)
echo "Waiting for stack deletion to complete..."
aws cloudformation wait stack-delete-complete --stack-name $STACK_NAME --region $AWS_REGION

# Step 4: Confirm resources are deleted
echo "Listing remaining CloudFormation stacks..."
aws cloudformation describe-stacks --region $AWS_REGION

echo "Cleanup completed!"

```


Once done, run

```
chmod +x cleanup.sh
./cleanup.sh
```

**Thank you! Good job!**
