<?php
putenv("AWS_ACCESS_KEY_ID=AKIAIBWJGGPCGPJLVJVA");
putenv("AWS_SECRET_ACCESS_KEY=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");
putenv("S3_BUCKET=imguploadwang2");
$key = getenv('AWS_ACCESS_KEY_ID')?: die('No "AWS_ACCESS_KEY_ID" config var in found in env!');
$secret = getenv('AWS_SECRET_ACCESS_KEY')?: die('No "AWS_SECRET_ACCESS_KEY" config var in found in env!');
$default_bucket = getenv('S3_BUCKET')?: die('No "S3_BUCKET" config var in found in env!');

define("DATA_SEPARATOR", "@#@");
define("AWS_ACCESS_KEY_ID", $key);
define("AWS_SECRET_ACCESS_KEY", $secret);
define("S3_BUCKET", $default_bucket);
define("S3_VERSION", "latest");
define("S3_REGION", "us-east-1");
define("IMAGELIST_FILE", "imagelist.txt");
define("S3_PATH", "https://s3.amazonaws.com/");
define("SQS_VERSION", "latest");
define("SQS_REGION", "ap-northeast-1");
define("SQS_INBOX", "https://sqs.ap-northeast-1.amazonaws.com/285456268804/ProcessImages");
define("SQS_OUTBOX", "https://sqs.ap-northeast-1.amazonaws.com/285456268804/ProcessImages");
define("SMALLIMAGELIST_FILE", "smallimagelist.txt");

?>