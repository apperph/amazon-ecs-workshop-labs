# Lab 8: Synthetic Monitoring 

## Objective

This lab demonstrates how to deploy a synthetic monitoring solution to monitor the performance, availability, and functionality of applications using Amazon CloudWatch. You'll deploy a canary to simulate user behavior and check an application’s health.

## Introduction to Synthetic Monitoring

Synthetic monitoring is a proactive approach that uses scripted tests, or "synthetic transactions," to simulate user interactions and continuously verify the performance and functionality of applications, APIs, or websites. In Amazon CloudWatch, synthetic monitoring is performed using canaries — scripts that mimic user actions.

AWS CloudWatch Synthetics allows you to:
- Test APIs and web applications for availability, latency, and functionality.
- Detect broken links, unexpected content changes, etc.
- Store monitoring results in Amazon S3 for analysis and integrate with CloudWatch Alarms for proactive alerts.

---

## Step 0: Deploy a Vulnerable Demo Website from OWASP

1. **Access CloudFormation**
   - Open AWS CloudFormation from the AWS Management Console.

2. **Deploy the ECS Cluster**
   - Deploy the ECS Cluster containing OWASP Juice Shop.
   - You can find the template here: [OWASP Juice Shop in ECS](https://github.com/olyvenbayani/JuiceShop-in-ECS).

3. **Access the Application**
   - Once deployed, try accessing the app using the following URL:
     ```
     http://<Your-ECS-IPV4>:3000
     ```

## Step 1: Set Up CloudWatch Synthetic Monitoring

1. **Access CloudWatch**
   - Open AWS Management Console and enter CloudWatch in the search bar.

2. **Navigate to Synthetics**
   - In the CloudWatch Console, under **Application Signals**, click on **Synthetic Canaries**.
   
![Synthetic Canaries](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/FECP/FECP4-1021/fecp-1021-lab2/img1021b-01.png)

3. **Create a Canary**
   - In the Canaries section, click on **Create Canary**.
   
![Create Canary](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/FECP/FECP4-1021/fecp-1021-lab2/img1021b-02.png)

4. **Configure Canary**
   - Under **Blueprint**, select **Heartbeat** and give it a name.
   - Alternatively, upload a custom Node.js-based script for advanced scenarios.
   - For **Application or Endpoint URL**, enter:
     ```
     http://<Your-ECS-Public-IP>:3000
     ```
   - Note: This website runs the OWASP Juice Shop, as provisioned earlier.

![Blueprint Configuration](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/FECP/FECP4-1021/fecp-1021-lab2/img1021b-03.png)

5. **Set Schedule**
   - Under the **Schedule** tab, choose a **Frequency** (e.g., every 5 minutes). Use a Cron Expression for granular scheduling if required.
   
![Scheduling](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/FECP/FECP4-1021/fecp-1021-lab2/img1021b-04.png)

6. **Set Retention Period**
   - Under **Retention Period**, choose **Custom** and set it to 1 day for lab purposes. Adjust as per company standards if needed.

7. **Select S3 Bucket**
   - Choose the default S3 bucket created for you or specify a different existing S3 bucket for logs.

![S3 Bucket Selection](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/FECP/FECP4-1021/fecp-1021-lab2/img1021b-06.png)

8. **Set Access Permissions**
   - Choose to create a new role for the service under **Access Permission**.

![Access Permission](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/FECP/FECP4-1021/fecp-1021-lab2/img1021b-07.png)

9. **Configure CloudWatch Alarms**
   - You can set a new alarm and connect it to an SNS topic. For this lab, omit this step as SNS is not configured.

![Alarms Configuration](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/FECP/FECP4-1021/fecp-1021-lab2/img1021b-08.png)

10. **Finish Creating the Canary**

![Finish Canary Creation](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/FECP/FECP4-1021/fecp-1021-lab2/img1021b-09.png)

---

## Step 2: Check the Results

1. **Monitor Canary Execution**
   - Once the Canary is running, monitor the success/failure status on the dashboard.

![Canary Status](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/FECP/FECP4-1021/fecp-1021-lab2/img1021b-10.png)

2. **View Canary Results**
   - Click on the Canary you created to view detailed results.

3. **Download Artifacts**
   - Under **Canary Artifacts and S3 Location**, click on **Download Artifacts**. Once downloaded, you will see several files including logs and HAR files.

![Download Artifacts](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/FECP/FECP4-1021/fecp-1021-lab2/img1021b-11.png)

4. **Examine HAR Files**
   - HAR (HTTP Archive) files help in troubleshooting by tracking web transactions. They are primarily used for identifying performance issues, such as bottlenecks and slow load times.

![HAR Files](https://sb-next-prod-image-bucket.s3.ap-southeast-1.amazonaws.com/public/FECP/FECP4-1021/fecp-1021-lab2/img1021b-12.png)

---

## Conclusion

Congratulations on completing the lab on synthetic monitoring! You have successfully created a canary in Amazon CloudWatch to monitor your application, analyze results, and understand its health proactively. Remember to adhere to best practices for retention and permissions in a production environment.
