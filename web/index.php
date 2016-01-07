<?php
header("Content-type: text/html; charset=utf-8");
require('config.php');
require('../vendor/autoload.php');
use Aws\S3\S3Client;
use Aws\Sqs\SqsClient;


$s3 = new S3Client([
    'version' => S3_VERSION,
    'region'  => S3_REGION
]);

$sqs = new SqsClient([
    'version' => SQS_VERSION,
    'region'  => SQS_REGION
]);

$message = "";
if(!empty($_POST['submit'])){
    if(!empty($_FILES["uploadfile"])){
        $filename = $_FILES["uploadfile"]["name"];       
        $file = $_FILES["uploadfile"]["tmp_name"];
        $filetype = $_FILES["uploadfile"]["type"];
        $filesize = $_FILES["uploadfile"]["size"];
        $filedata = file_get_contents($file);
        $bucket = $_POST['bucket'];
        $base64 = base64_encode(file_get_contents($file));
        $filekey = $filename.DATA_SEPARATOR.$filetype.DATA_SEPARATOR.$filesize.DATA_SEPARATOR.time().DATA_SEPARATOR.$bucket;
        // create or update imagelist.txt
        if($s3->doesObjectExist(S3_BUCKET, IMAGELIST_FILE)){
            // exsist
            $txtfile = $s3->getObject([
                'Bucket'    => S3_BUCKET,
                'Key'       => IMAGELIST_FILE
            ]);
            $txtbody = $txtfile['Body'].$filekey.PHP_EOL;
            try {
                $s3->deleteObject([
                    'Bucket' => S3_BUCKET,
                    'Key'    => IMAGELIST_FILE
                ]);
                $s3->putObject([
                    'Bucket' => S3_BUCKET,
                    'Key'    => IMAGELIST_FILE,
                    'Body'   => $txtbody,
                    'ACL'    => 'public-read-write',  // use read write
                ]);
            } catch (Aws\Exception\S3Exception $e) {
                $message .= "There was an error deleting and creating imagelist.txt.\r\n";
            }
        }else{
            // create imagelist.txt
            try {
                $s3->putObject([
                    'Bucket' => S3_BUCKET,
                    'Key'    => IMAGELIST_FILE,
                    'Body'   => $filekey.PHP_EOL,
                    'ACL'    => 'public-read-write',  // use read write
                ]);
            } catch (Aws\Exception\S3Exception $e) {
                $message .= "There was an error creating imagelist.txt.\r\n";
            }
        }
               
        // upload file to selected bucket
        try {
            $result = $sqs->sendMessage(array(
                'QueueUrl'      => SQS_INBOX,
                'MessageBody'   => 'Resize file',
                'MessageAttributes' => array(
                    's3path' => array(
                        'StringValue' => S3_PATH,
                        'DataType' => 'String',
                    ),
                    's3bucket' => array(
                        'StringValue' => $bucket,
                        'DataType' => 'String',
                    ),
                    'filename' => array(
                        'StringValue' => $filename,
                        'DataType' => 'String',
                    ),
                    'filetype' => array(
                        'StringValue' => $filetype,
                        'DataType' => 'String',
                    ),
                    'filesize' => array(
                        'StringValue' => $filesize,
                        'DataType' => 'String',
                    )
                ),
            ));
            $s3->putObject([
                'Bucket' => $bucket,
                'Key'    => $filename,
                'Body'   => $filedata,
                'ACL'    => 'public-read',  // use read
            ]);
             
            $message .= "Successfully uploaded file.\r\n";
        } catch (Aws\Exception\S3Exception $e) {
            $message .= "There was an error uploading the file.\r\n";
        }
        
    }else{
        $message .= "You have to choose a file, sorry.\r\n";
    }
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Image Upload S3 and SQS</title>
<!-- Bootstrap -->
<link href="./bootstrap-3.3.6/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
  padding-top: 50px;
}
.starter-template {
  padding: 40px 15px;
  text-align: center;
}
</style>
<link rel="Shortcut Icon" type="image/x-icon" href="smallicon.png" />
</head>

<body>
    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">Image Upload Component</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <li class="active"><a href="#">Image Upload</a></li>
            <li><a href="./filedownload.php">Image List</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>
    <div class="container">
      <div class="starter-template">
        <h1>You can start uploading your images<br/>(using s3 insert into SQS)</h1>
        <p class="lead"><br> </p>
      </div>
    </div>
    <div class="container">
        <div class="col-md-1"></div>
        <div class="col-md-10">
        <?php if($message != ''){ ?>
        <p class="bg-warning"><?=$message?></p>
        <?php } ?>
         <div class="image-proview" id="image-proview-layer">
            <img id="reg_pic" src=""/>
         </div>
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label for="exampleInputFile">Bucket</label>
                <select name="bucket" class="form-control">
                    <?php 
                    $buckets = $s3->listBuckets();
                    foreach ($buckets['Buckets'] as $bucket) {
                        echo '<option value="'.$bucket['Name'].'">'.$bucket['Name'].'</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="exampleInputFile">File upload</label>
                <input type="file" name="uploadfile" id="exampleInputFile" class="form-control" onchange="ImagesProview(this)">
            </div>
            <input type="submit" name="submit" class="btn btn-primary" value="Upload Image" />
        </form>
        </div>
        <div class="col-md-1"></div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="./bootstrap-3.3.6/js/bootstrap.min.js"></script>
    <!-- javascript 縮圖程式 -->
    <script type="text/javascript">
        var isIE=function() {
           return (document.all) ? true : false;
        }

        function ImagesProview(obj) {
            var newPreview = document.getElementById("image-proview-layer");
            var imagelayer = document.getElementById('image-proview') 
            if(imagelayer){
                newPreview.removeChild(imagelayer);
            }

            if (isIE()) {
                obj.select();  
                var imgSrc = document.selection.createRange().text;  
                var objPreviewFake = document.getElementById('image-proview-layer');
                objPreviewFake.filters.item('DXImageTransform.Microsoft.AlphaImageLoader').src = imgSrc;  
            } else {
                window.URL = window.URL || window.webkitURL;
                newPreview.innerHTML = "<img src='"+window.URL.createObjectURL(obj.files[0])+"' id='image-proview'/>"
            }
        }

       
      
    </script>
</body>
</html>