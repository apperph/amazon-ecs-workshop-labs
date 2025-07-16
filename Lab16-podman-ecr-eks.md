# Lab 15: Creating a Podman "Hello World" on Amazon EKS with Amazon ECR

## Objective

In this lab, you will learn how to create a simple "Hello World" container using Podman, push it to Amazon Elastic Container Registry (ECR), and deploy it to Amazon Elastic Kubernetes Service (EKS).

## Prerequisites

Ensure you have the following prerequisites in place before starting the lab:

- AWS CLI configured with necessary permissions.
- Podman installed on your Ubuntu system.
- `kubectl` and `eksctl` installed and configured.
- An existing Amazon EKS cluster with worker nodes.
- AWS account ready to use.

## Steps

### Step 1: Create a Simple "Hello World" Podman Application

Create a new directory for the project:

```bash
mkdir hello-world-podman
cd hello-world-podman
```

Create a `Dockerfile` in the project directory:

```dockerfile
FROM python:3.8-slim

WORKDIR /app

COPY hello.py /app/hello.py

CMD ["python", "hello.py"]
```

Create a simple Python script named `hello.py`:

```python
print("Hello, World!")
```

### Step 2: Build the Podman Image

Create a variable for your container repository name to minimize conflicts:

```bash
PODMAN_REPO="hello-world-app-<yourname>"
export PODMAN_REPO
echo $PODMAN_REPO
```

Build the Podman image:

```bash
podman build -t $PODMAN_REPO .
```

Verify the Podman image:

```bash
podman images
```

You should see `$PODMAN_REPO` listed among the Podman images.

### Step 3: Push the Podman Image to Amazon ECR

Create an ECR repository:

```bash
ECR_REPO="hello-world-app-<yourname>"
export ECR_REPO
echo $ECR_REPO
```

Create an environment variable for the AWS account:

```bash
AWS_ACCOUNT_ID="<aws-account-id>"
export AWS_ACCOUNT_ID
echo $AWS_ACCOUNT_ID
```

Create the ECR repository:

```bash
aws ecr create-repository --repository-name $ECR_REPO
```

Verify the repository exists:

```bash
aws ecr describe-repositories
```

Log in to ECR:

```bash
AWS_REGION="ap-southeast-1"
export AWS_REGION
echo $AWS_REGION
```

Get the password for ECR and log in:

```bash
aws ecr get-login-password --region $AWS_REGION | podman login --username AWS --password-stdin $AWS_ACCOUNT_ID.dkr.ecr.$AWS_REGION.amazonaws.com
```

If successful, you should see:

```
Login Succeeded
```

Tag the Podman image for ECR:

```bash
podman tag $ECR_REPO:latest $AWS_ACCOUNT_ID.dkr.ecr.$AWS_REGION.amazonaws.com/$ECR_REPO:latest
```

Push the Podman image to ECR:

```bash
podman push $AWS_ACCOUNT_ID.dkr.ecr.$AWS_REGION.amazonaws.com/$ECR_REPO:latest
```

### Step 4: Deploy to Amazon EKS

#### Step 4.1: Configure `kubectl` for Your EKS Cluster

Create a variable for your EKS cluster:

```bash
EKS_CLUSTER="eks-cluster-<yourname>"
export EKS_CLUSTER
echo $EKS_CLUSTER
```

Update your `kubectl` configuration to access the EKS cluster:

```bash
aws eks update-kubeconfig --region ap-southeast-1 --name apper-ckad-prep-cluster-2025
```

Verify access to the cluster:

```bash
kubectl get nodes
```

You should see a list of worker nodes in your EKS cluster.

#### Step 4.2: Create a Kubernetes Deployment

Create a file named `hello-world-deployment.yaml`:

```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: hello-world-deployment-<yourname>
  namespace: default
spec:
  replicas: 1
  selector:
    matchLabels:
      app: hello-world
  template:
    metadata:
      labels:
        app: hello-world
    spec:
      containers:
      - name: hello-world-container
        image: <aws-account-id>.dkr.ecr.ap-southeast-1.amazonaws.com/hello-world-app-<yourname>:latest
        resources:
          limits:
            cpu: "256m"
            memory: "512Mi"
          requests:
            cpu: "128m"
            memory: "256Mi"
```

> **Note**: Replace `<aws-account-id>` and `<yourname>` with your AWS account ID and unique identifier, respectively.

Apply the deployment to your EKS cluster:

```bash
kubectl apply -f hello-world-deployment.yaml
```

Verify the deployment:

```bash
kubectl get deployments
```

Check the pods to ensure they are running:

```bash
kubectl get pods
```

#### Step 4.3: Create a Kubernetes Service (Optional)

To access the "Hello World" application, create a service to expose the deployment. Create a file named `hello-world-service.yaml`:

```yaml
apiVersion: v1
kind: Service
metadata:
  name: hello-world-service-<yourname>
  namespace: default
spec:
  selector:
    app: hello-world
  ports:
    - protocol: TCP
      port: 80
      targetPort: 8080
  type: LoadBalancer
```

> **Important**: The basic `hello.py` script prints "Hello, World!" and exits, so it’s not a long-running server listening on a port like 8080. For this service to work, modify `hello.py` to include an HTTP server, such as:

```python
from http.server import BaseHTTPRequestHandler, HTTPServer

class HelloWorldHandler(BaseHTTPRequestHandler):
    def do_GET(self):
        self.send_response(200)
        self.send_header('Content-type', 'text/plain')
        self.end_headers()
        self.wfile.write(b'Hello, World!')

if __name__ == '__main__':
    server = HTTPServer(('0.0.0.0', 8080), HelloWorldHandler)
    print('Server running on port 8080...')
    server.serve_forever()
```

If you use the HTTP server version, rebuild and push the image (repeat **Step 2** and **Step 3**), then apply the service:

```bash
kubectl apply -f hello-world-service.yaml
```

Get the service’s external endpoint:

```bash
kubectl get services
```

Look for the `EXTERNAL-IP` or `LoadBalancer Ingress` address. Use `curl` or a browser to access it (e.g., `curl <external-ip>`). It may take a few minutes for the LoadBalancer to provision.

#### Step 4.4: View Pod Logs

To verify the "Hello, World!" output, check the pod logs:

```bash
POD_NAME=$(kubectl get pods -l app=hello-world -o jsonpath="{.items[0].metadata.name}")
kubectl logs $POD_NAME
```

You should see the "Hello, World!" output in the logs. If you used the HTTP server version, you might see server startup messages instead.

### Verification

1. Verify the deployment is running:

```bash
kubectl get deployments
```

2. Verify the pods are running:

```bash
kubectl get pods
```

3. If you created a service, verify the service is accessible:

```bash
kubectl get services
```

4. Check the pod logs to confirm the "Hello, World!" output:

```bash
kubectl logs $POD_NAME
```

If you used the HTTP server version, access the service’s external IP with `curl` or a browser to see the "Hello, World!" response.

## Cleanup

To clean up resources, delete the deployment and service:

```bash
kubectl delete -f hello-world-deployment.yaml
kubectl delete -f hello-world-service.yaml
```

Delete the ECR repository:

```bash
aws ecr delete-repository --repository-name $ECR_REPO --force
```
