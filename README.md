<h3>PHP Redis and AWS S3 using Heroku Web service</h3><br/><br/>
Heroku needs setting "AWS_ACCESS_KEY_ID" and "AWS_SECRET_ACCESS_KEY" from your AWS credential.<br>
[Heroku](https://devcenter.heroku.com/articles/s3-upload-php)<br><br/>
At AWS you need to set "Users" > "Permission" > "Attach Policy" to let your account access the S3 project.<br>
"Attach Policy" must choose "AmazonS3FullAccess".<br><br/>
If you need using multiple regions, you have to change setting<br>
<a href='https://docs.aws.amazon.com/AmazonS3/latest/dev/WebsiteEndpoints.html'>https://docs.aws.amazon.com/AmazonS3/latest/dev/WebsiteEndpoints.html</a>
