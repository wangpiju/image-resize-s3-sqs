<?php
header("Content-type: text/html; charset=utf-8");
require('config.php');
require('../vendor/autoload.php');
use Aws\S3\S3Client;

$redis = new Predis\Client(getenv('REDIS_URL'));

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
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Homework 3</title>
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
        <h1>You can start uploading your images<br/>(using s3 and heroku redis cache)</h1>
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
                    <th>File size</th>
                    <th>File</th>
                    <th>Place</th>
                </tr>
            <?php 
               $arList = $redis->keys("*");
               //若redis有存直接從redis取出, 若沒有則用key去s3找
               foreach($lines as $key){
                   if($redis->exists($key)){
                       // redis exsist
                       $splitKey = preg_split("/@#@/", $key);
                       
                       $filedata = $redis->get($key);
                       $bucket = $splitKey[4];
                       $filename = $splitKey[0];
                       $filetype = $splitKey[1];
                       $filesize = $splitKey[2];
                       echo '<tr>';
                       echo '<td>'.$bucket.'</td>';
                       echo '<td>'.$filename.'</td>';
                       echo '<td>'.$filetype.'</td>';
                       echo '<td>'.$filesize.'</td>';
                       echo '<td><a href="https://s3-us-west-2.amazonaws.com/'.$bucket.'/'.$filename.'"><img src="data:image/jpeg;base64,' . $filedata . '" width="100" /></a></td>';
                       echo '<td>Redis</td>';
                       echo '</tr>';
                   }else if($key != ''){
                       // only exsist on AWS S3
                       $splitKey = preg_split("/@#@/", $key);
                       
                       $filedata = $redis->get($key);
                       $bucket = $splitKey[4];
                       $filename = $splitKey[0];
                       $filetype = $splitKey[1];
                       $filesize = $splitKey[2];
                       echo '<tr>';
                       echo '<td>'.$bucket.'</td>';
                       echo '<td>'.$filename.'</td>';
                       echo '<td>'.$filetype.'</td>';
                       echo '<td>'.$filesize.'</td>';
                       echo '<td><a href="https://s3-us-west-2.amazonaws.com/'.$bucket.'/'.$filename.'"><img src="https://s3-us-west-2.amazonaws.com/'.$bucket.'/'.$filename.'" width="100" /></a></td>';
                       echo '<td>AWS S3</td>';
                       echo '</tr>';
                   }
               }
            ?>
    	    </table>
    	
    	</div>
    	<div class="col-md-1"></div>
	</div>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.4/jquery.min.js"></script>
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="./bootstrap-3.3.6/js/bootstrap.min.js"></script>
</body>
</html>