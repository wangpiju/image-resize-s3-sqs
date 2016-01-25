<?php
header("Content-type: text/html; charset=utf-8");
require('config.php');
require('../vendor/autoload.php');
use Aws\S3\S3Client;
use Aws\Sqs\SqsClient;

$sqs = new SqsClient([
    'version' => SQS_VERSION,
    'region'  => SQS_REGION
]);

$s3 = new S3Client([
    'version' => S3_VERSION,
    'region'  => S3_REGION
]);

if($s3->doesObjectExist(S3_BUCKET, IMAGELIST_FILE)){
    $txtfile = $s3->getObject([
        'Bucket'    => S3_BUCKET,
        'Key'       => IMAGELIST_FILE
    ]);
    $txtbody = $txtfile['Body'];
    $lines = explode(PHP_EOL, $txtbody);
}
else{
  $lines = array();
}

if($s3->doesObjectExist(S3_BUCKET, SMALLIMAGELIST_FILE)){
    $smalltxtfile = $s3->getObject([
        'Bucket'    => S3_BUCKET,
        'Key'       => SMALLIMAGELIST_FILE
    ]);
    $smalltxtbody = $smalltxtfile['Body'];
    $smalllines = explode(PHP_EOL, $smalltxtbody);
}
else{
  $smalllines = array();
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Image Upload S3 and SQS</title>
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
          <a class="navbar-brand" href="./index.php">Image Upload Component</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <li ><a href="./index.php">Image Upload</a></li>
            <li class="active"><a href="#">Image List</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>
	<div class="container">
      <div class="starter-template">
        <h1>Images List</h1>
        <p class="lead"><br> </p>
      </div>
  </div>
	<div class="container">
        <div class="col-md-1"></div>
    	<div class="col-md-10">     	   
    	    <table class="table">
                <tr>
                      <th>Bucket</th>
                    <th>File Name</th>
                    <th>File type</th>
                   
                    <th>Image</th>
                    <th>Resized Image</th> 
                </tr>
               <?php 
                foreach($lines as $key){
                   if($key != ''){
                       // only exsist on AWS S3
                       $splitKey = preg_split("/@#@/", $key);
    
                       $bucket = $splitKey[4];
                       $filename = $splitKey[0];
                       $filetype = $splitKey[1];
                       $filesize = $splitKey[2];
                       echo '<tr>';
                       echo '<td>'.$bucket.'</td>';
                       echo '<td>'.$filename.'</td>';
                       echo '<td>'.$filetype.'</td>';
                      
                       echo '<td><a href="https://s3.amazonaws.com/'.$bucket.'/'.$filename.'"><img src="https://s3.amazonaws.com/'.$bucket.'/'.$filename.'" width="100" /></a></td>';
                       $hassmallimage = false;
                       foreach($smalllines as $smallkey){
                          $smallsplitKey = preg_split("/@#@/", $smallkey);
                           if($filename == $smallsplitKey[0]){
                            echo '<td><a href="https://s3.amazonaws.com/'.$smallsplitKey[4].'/'.'thumn_'.$smallsplitKey[0].'"><img src="https://s3.amazonaws.com/'.$smallsplitKey[4].'/'.'thumn_'.$smallsplitKey[0].'" width="100" /></a></td>';
                            $hassmallimage = true;
                            break;
                           }
                       }

                       if(!$hassmallimage)
                        echo '<td></td>';
                       
                       echo '</tr>';
                   }
                }
              ?>
    	    </table>
    	
    	</div>
    	<div class="col-md-1"></div>
	</div>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.0/themes/smoothness/jquery-ui.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="./bootstrap-3.3.6/js/bootstrap.min.js"></script>
</body>
</html>