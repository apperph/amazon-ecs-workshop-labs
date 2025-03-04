## Overview:


In this lab, we will set up a **MySQL RDS** instance in a **private subnet** and **configure Multi-AZ**. Next, we will set up **phpMyAdmin** and test if the **Multi-AZ RDS** provides fault tolerance to phpMyAdmin.

**Before proceeding, you need to have:**
- A **VPC** with **at least two private** and **two public subnets**.
- If you already have a VPC with this configuration, proceed with the steps below.

## 1. Create a DB Subnet.

1-a. Navigate to the **RDS console** and click on **Subnet Groups**.

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-1.png)

1-b. Click **Create DB Subnet Group**.

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-2.png)

1-c. Set the **name of the subnet group** and **select your VPC**.

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-3.png)

1-d. Select the **Availability Zones** that are being used by your **private subnets**, then ensure you select your **private subnets**. Finally, click **Create**.

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-4.png)

## 2. Provision an RDS instance.

2-a. Navigate to the **RDS dashboard** and click on **Create database**.

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-5.png)

2-b. Choose **Standard** and select **MySQL** under the Engine options.

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-6.png)

2-c. Select **Dev/Test** for the Templates and choose **Multi-AZ** instance for high availability.

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-7.png)

2-d. Create your **username** and **master password** for the database.

*Important: Take note of your credentials, as you will need them later to access and manage your RDS instance.*

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-8.png)

2-e. Under **Cluster storage** configuration, choose **Aurora Standard**.

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-9.png)

2-f. Under **Instance configuration**, choose **Burstable instance type** and select **db.t3.medium**.

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-10.png)

2-g. Select your **VPC** and **DB subnet group**. Ensure you **do not allow public access** and **create a new security group**. Leave the default settings and proceed to create the database.

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-11.png)
![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-12.png)

2-h. Wait for a few minutes until the **status of the RDS instance** changes to **Available**.

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-13.png)

## 3. Create an EC2 instance and configure the PHP application.

3-a. Here are the configurations needed for the EC2 instance:

- Select the **Ubuntu Server 22.04 LTS AMI**.
- Choose the instance type as **t2.micro**.
- Select the **same VPC** that is in use by the RDS instance.
- Ensure the EC2 instance is placed in a **public subnet**.
- Enable a **public IP address** for the instance.
- In the security group settings, ensure **SSH traffic is allowed**.
- Once these settings are configured, you can **launch the EC2 instance**.

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-14.png)

3-b. **SSH** into the **EC2 instance** and run the following command to update the package list:

`sudo apt-get update -y`

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-15.png)
![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-16.png)

3-c. Once the update is finished, **install Apache2** by running the following command:

`sudo apt-get install apache2 -y`

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-17.png)

3-d. Navigate to the **/var/www/html** directory by running the following command:

`cd /var/www/html`

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-18.png)

3-e. Create the people directory by running the following command:

`sudo mkdir people`

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-19.png)

3-f. Make the ubuntu user the owner of the people folder by running the following command:

`sudo chown ubuntu people`

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-20.png)

This will set the ownership of the people directory to the ubuntu user and group.

To check if the command worked, run the following command:

`ls -la`

3-g. Download the sample **PHP application** on the server by running the following command:

**wget https://github.com/ApperPh/crud-php-simple/archive/master.zip**

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-21.png)

This will download the master.zip file from the GitHub repository to your current directory. After downloading, you can unzip it and set it up for use on your Apache server.

Before running the command, make sure you are in the /var/www/html/people directory by running:

 `cd /var/www/html/people`

3-h. To **unzip** the file, you first need to install unzip by running the following command:

`sudo apt-get install unzip -y`

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-22.png)

After installing unzip, you can run the following command to extract the contents of the master.zip file: 

`unzip master.zip`

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-23.png)

## 4. Integrate RDS with the PHP application

4-a. To install the MySQL client on the server, run the following command:

`sudo apt-get install mysql-client -y`

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-24.png)

4-b. To retrieve the RDS DNS endpoint:
- Go to the **RDS console**.
- Select your **RDS MySQL instance**.
- In the **Connectivity & security section**, copy the **Endpoint** (e.g., mydb-instance-name.xxxxxx.us-west-2.rds.amazonaws.com).

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-25.png)

4-c. To log in to the RDS MySQL instance from your EC2 instance, run the following command in your SSH session:

`mysql -u <RDS master username> -p -h <RDS DNS endpoint>`

Replace:
`<RDS master username>` with your RDS master username.
`<RDS DNS endpoint>` with the DNS endpoint you retrieved in step 4-b.

It will prompt you for the password. Enter the **RDS master password** to connect to the database.

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-26.png)

*It won't be able to connect because the RDS instance's security group has not been configured to allow incoming traffic from our web server.*

4-d. To resolve the issue, **configure the security group** of the RDS instance to allow incoming traffic from your web server.

Navigate to the **RDS console**, select your **instance**, click the **security group** link under **Connectivity & security**, and modify the **security group** in the **EC2 console**.

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-27.png)

**Modify the inbound rule** of the RDS security group to allow **MySQL/Aurora traffic** on **port 3306** from the web server's security group. 

*In this demo, I have selected the Private IP address of the WebServer Instance.*

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-28.png)

**This time we are connected to the database!**

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-29.png)

4-f. While connected to the MySQL database, execute the command: 

`create database peoplelab; `

to create the people database.

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-30.png)

- To exit from MySQL, simply use the command: `exit`

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-31.png)


4-g. We need to move the files from **crud-php-simple-master** to **/var/www/html/people**. While inside the **/var/www/html/people** directory, run the following command:

`mv crud-php-simple-master/* /var/www/html/people/`

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-32.png)

4-h. Set the correct DB settings in the **config.php** file.

Run the command: `nano config.php.`

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-33.png)

Supply the following variables inside the red line:

$databaseHost = `'<RDS DNS endpoint>'`

$databaseName = `'peoplelab'`

$databaseUsername = `'<RDS master username>'`

$databasePassword = `'<RDS password>'`

Once you have **updated the config.php** file, it should resemble the following:

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-34.png)

Press **ctrl + o** to save and **ctrl + x** to exit the file.

4-i. Update the **database.sql** file.

Run the command: **nano database.sql**

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-35.png)

The **database.sql** looks like this.

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-36.png)

Delete the line: **create database test;**

And replace **use test;** with use **peoplelab;**.

The updated database.sql file should look like this:

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-37.png)

Press **ctrl + o** to save and **ctrl + x** to exit the file.

4-j. Ensure you are in the **/var/www/html/people** directory.
To execute the **SQL code**, run the following command:

`mysql -u <RDS master username> -p -h <RDS DNS endpoint> peoplelab < database.sql`

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-38.png)

4-k. To render the **PHP website** and run the **mysqli_connect** function, install the following software by using the commands below:

`sudo apt install php libapache2-mod-php`

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-40.png)

`sudo apt-get install php-mysqli`

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-39.png)


4-l. Get the **public DNS of your EC2 instance** and append **/people/index.php** to it. Then, open the URL in your web browser.

*Ensure that HTTP traffic is allowed in the security group of the web server.*

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-41.png)

You may **add data** to check if the database is working.

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-42.png)

## 5. Test Multi-AZ RDS


5-a. Navigate to **RDS** in the AWS Management Console and **reboot your instance**.

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-43.png)


5-b. **Confirm** the prompt of **Reboot DB Instance.**

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-44.png)


5-c. Check the **status of the RDS instance** to see if it is rebooting, then try to access the website and add data to confirm if it still functions correctly after rebooting the database.

![](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/CAMP/Labs2025/Session3/Lab9/image-45.png)

**Thank you!** It indeed was a lengthy and detailed process, but **you've successfully completed each step.** This accomplishment is a testament to your perseverance and attention to detail. **Well done for following through and finishing the lab!**