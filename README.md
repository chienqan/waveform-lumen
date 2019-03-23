# Waveform Lumen
A restful api to take file mp3 and return to geometric image

## Requirements
* NodeJS
* AWS Console
* IAM Role

## Installation
Install serverless-cli
````
npm i serverless -g
````

Install aws-cli
````
https://docs.aws.amazon.com/cli/latest/userguide/cli-chap-install.html
````

Clone repository include submodules
````
git clone --recurse-submodules -j8 git://github.com/quangchien/waveform-lumen
````

Install app dependencies
````
cd waveform-lumen
composer install
````

Generate app key
````
php artisan key:generate
````

## Getting Started

### Create an Policy:
* Choose IAM on AWS
* Click "Policies" on the left bar
* Click "Create policy"
* Click "JSON"
* Paste these code below:
````
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Sid": "VisualEditor0",
            "Effect": "Allow",
            "Action": "cloudformation:*",
            "Resource": "*"
        }
    ]
}
````
* Click "Review Policy"
* On the name, type "CloudFormationFullAccess"
* Click "Create Policy"

### Create an IAM users:
* Choose IAM on AWS
* Click "Users" on the left bar
* Click "Add user"
* Set "username" and choose "Programmatic access"
* Click "Next Permission"
* Click "Attach existing policies directly" and choose these: AWSLambdaFullAccess, IAMFullAccess, AmazonS3FullAccess, AmazonAPIGatewayInvokeFullAccess, AmazonAPIGatewayPushToCloudWatchLogs, CloudFormationFullAccess, AmazonAPIGatewayAdministrator, AWSCloudFormationReadOnlyAccess
* Click "Next: Tags"
* Click "Next: Review"
* Click "Create User"
* Copy Access Key ID and Secret access key for serverless and s3

### Config AWS CLI
* Open terminal
* Type "aws configure"
* Input Access Key and Secret Key
* Skip the other


### Create S3 Bucket
* Choose S3 on AWS
* Click "Create Bucket"
* Enter bucket name and choose region
* Click "Next"
* Click "Next"
* Untick all selected option
* Click "Next"
* Create "Bucket"

### Config S3 Bucket
* Click bucket name you have created
* Choose "Permissions" tab
* Choose "Bucket Policy"
* Paste json code into it:
````
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Sid": "AddPerm",
            "Effect": "Allow",
            "Principal": "*",
            "Action": "s3:GetObject",
            "Resource": "arn:aws:s3:::<YOUR_BUCKET_NAME>/*"
        }
    ]
}
````
* Change <YOUR_BUCKET_NAME> to your bucket name in the json code
* Choose "CORS Configuration"
* Paste json code into it:
````
<?xml version="1.0" encoding="UTF-8"?>
<CORSConfiguration xmlns="http://s3.amazonaws.com/doc/2006-03-01/">
<CORSRule>
    <AllowedOrigin>*</AllowedOrigin>
    <AllowedMethod>GET</AllowedMethod>
    <AllowedMethod>POST</AllowedMethod>
    <AllowedMethod>PUT</AllowedMethod>
    <AllowedHeader>*</AllowedHeader>
</CORSRule>
</CORSConfiguration>
````
* Click "Save"

### Config environment of app
* Copy .env.example to .env
* Change AWS_ACCESS_KEY, AWS_SECRET_KEY, AWS_DEFAULT_REGION, AWS_BUCKET to your key

### Upload php 7.1 layers to Lambda
* Download php71 zip into your local disk
````
https://github.com/code-runner-2017/php-lambda-layer/releases/download/V_1_0/php71.zip
````
* Go to Lambda
* Choose "Layers"
* Click "Create Layer"
* Put "php71" as an Name
* Click "Upload" and choose "php71.zip"
* Then click "Create"
* Select "php71"
* Look at "All versions" and copy ARN in the below "Version ARN"

### Config serverless.yaml of app
* Find the line have "layers"
* Change ARN to your ARN you have copied

### Run APP
In your app root directory, open terminal and type:
````
serverless deploy
````
