# Secrets Manager Lab

This lab provides a step-by-step introduction to using a Secrets Manager to securely store, retrieve, and utilize sensitive information, such as passwords or API keys. The instructions are designed for beginners and include visual aids to ensure clarity.

## Instructions

**Step 1: Configure the Secrets Manager**

1. Log in to the AWS Management Console (or your chosen cloud provider’s equivalent).

2. In the search bar, type “Secrets Manager” and select the service from the results.

3. On the Secrets Manager dashboard, locate and click the Create secret button (labeled as “Store a new secret” or similar).

4. Select the secret type:
   - Choose “Other type of secret” for a custom key-value pair.

5. Enter the secret details:
  - Key: `my_password`
  - Value: `SuperSecret123!`

6. Assign a name to the secret, such as my-first-secret.

7. Proceed by clicking Next, then Store, leaving optional settings at their defaults.

**Step 2: Retrieve the Secret**

1. Return to the Secrets Manager dashboard.

2. Locate `my-first-secret` in the list of secrets and click its name to view details.

3. Select the option to Retrieve secret value (or a similar command) to display the secret.

4. Note the secret value (`SuperSecret123!`) or copy the secret’s ARN (Amazon Resource Name) for later use.

**Step 3: Access the Secret Programmatically**

1. Open a text editor and create a new file named `get_secret.py`.

2. Insert the following Python code to retrieve the secret from AWS Secrets Manager:

```
import boto3

# Initialize a Secrets Manager client
client = boto3.client('secretsmanager', region_name='us-east-1')

# Specify the secret name and retrieve its value
secret_name = "my-first-secret"
response = client.get_secret_value(SecretId=secret_name)
secret = response['SecretString']

# Display the secret
print("Retrieved secret:", secret)
```
3. Save the file.

4. Install the required Python library:
  - Open a terminal and execute: `pip install boto3`.

5. Configure your AWS credentials:
  - Run `aws configure` in the terminal and provide your AWS Access Key, Secret Key, and region.

6. Execute the script by running: `python get_secret.py`.

## Expected Output

Upon successful execution, the terminal will display:
```
Retrieved secret: SuperSecret123!
```

**Step 4: Cleanup**
1. Navigate back to the Secrets Manager dashboard.

2. Select `my-first-secret` and choose the option to delete it.

3. Confirm the deletion to maintain a tidy environment.

## Conclusion

This lab demonstrates the fundamental operations of a Secrets Manager in a straightforward manner. For further assistance or advanced configurations, feel free to reach out.
