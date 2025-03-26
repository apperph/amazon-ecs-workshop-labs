# Lab 4: Static vs Dynamic IaC Template

## Objective

This lab activity will demonstrate how to create a CloudFormation stack using the AWS CLI. Once you have finished the lab, you need to commit the CloudFormation template to a GitHub repository.

## Prerequisites

- This activity requires a Cloud9 IDE running on either Ubuntu or Amazon Linux 2.

## Steps

### Step 1: Create a CloudFormation Stack

1. **Create and Edit Template File**

   - Create a file named `static-cf.yml` and copy the template from the following URL:

     - [Static Template URL](https://github.com/olyvenbayani/labs-IaC-Lab01.git)

   ```bash
   nano static-cf.yml
   ```

   - Set the name of your bucket and add random characters to ensure uniqueness.

   ![Bucket Configuration](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+1/Lab+2/image1.png)

2. **Run the Create Stack Command**

   - Execute the following command to create the stack:

   ```bash
   aws cloudformation create-stack --stack-name cf-demo2 --template-body file://static-cf.yml
   ```

   ![Create Stack Command](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+1/Lab+2/image2.png)

3. **Verify Stack Creation**

   - Navigate back to the CloudFormation console and verify your stack. It should have created an S3 bucket and EC2 instance.

   ![Stack Created](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+1/Lab+2/image3.png)

### Step 2: Create Another Stack with the Same Template

1. **Attempt to Reuse Template**

   - Try creating a new stack with the same template:

   ```bash
   nano static-cf.yml
   ```

   ```bash
   aws cloudformation create-stack --stack-name cf-demo2 --template-body file://static-cf.yml
   ```

   ![Error on Duplicate](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+1/Lab+2/image4.png)

   - **Note:** You’ll encounter an error because the S3 bucket name must be unique.

   ![Bucket Name Error](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+1/Lab+2/image5.png)

### Step 3: Use a Dynamic CloudFormation Template

1. **Create and Edit Dynamic Template File**

   - Create a file named `dynamic-cf.yml` and paste the template from the following URL:

     - [Dynamic Template URL](./source files/dynamic-template.yml)

   ```bash
   nano dynamic-cf.yml
   ```

   ![Dynamic Template](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+1/Lab+2/image6.png)

2. **Create Dynamic Stack**

   - Run this command to create a stack:

   ```bash
   aws cloudformation create-stack --stack-name dynamic-demo --template-body file://dynamic-cf.yml
   ```

   ![Dynamic Stack Created](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+1/Lab+2/image7.png)

3. **Verify Dynamic Stack**

   - Check the CloudFormation console to confirm the stack creation.

   ![Verify Stack](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+1/Lab+2/image8.png)

4. **Create Another Dynamic Stack**

   - Run the following command with dynamic parameters:

   ```bash
   aws cloudformation create-stack \
     --stack-name my-dynamic-ec2-s3-stack \
     --template-body file://dynamic-ec2-s3-template.yaml \
     --parameters ParameterKey=AmiId,ParameterValue=ami-0c55b159cbfafe1f0 \
                  ParameterKey=InstanceType,ParameterValue=t2.micro \
                  ParameterKey=BucketName,ParameterValue=my-dynamic-bucket-example \
                  ParameterKey=VpcId,ParameterValue=vpc-12345678 \
                  ParameterKey=SubnetId,ParameterValue=subnet-12345678
   ```

   ![Another Stack Created](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+1/Lab+2/image9.png)

5. **Verify New Dynamic Stack**

   - Go back to the CloudFormation console and verify the newly created stack.

   ![Verify New Stack](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CDMP/Session+1/Lab+2/image10.png)

---

## Challenge:


**Secure the EC2 Instance**

- Modify your dynamic CloudFormation template to include a security group for the EC2 instance.
- Configure the security group to allow port 80 and 443 in inbound traffic
- Update your EC2 instance in the dynamic template to use this security group.


## Conclusion

Well done! Please make sure to delete the stacks before ending this lab activity.
