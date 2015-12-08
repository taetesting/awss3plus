<?php
require_once __DIR__ . '/../vendor/autoload.php';

use AWSS3Plus\S3Plus;

$bucket            = 's3dummy.ap1.sys.am';
$setting["key"]    = 'AKIAJAR6EBWP55BNRSHA';
$setting["secret"] = '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';
$region            = 'ap-southeast-1';

$bucket = 's3dummy.ap1.sys.am';
$file   = __DIR__ . '/assets/koala.jpg';
$s3     = new S3Plus($setting["key"], $setting["secret"], $region);
// $result = $s3->uploadOneFile($bucket, $file);
// var_dump($result);die;

$objects = $s3->getListFromBucket($bucket);

foreach ($objects as $item) {
    $dObject = $s3->getObject($bucket, $item['Key']);
    // echo get_class($dObject);
    
    header("Content-Type: {$dObject['ContentType']}");
    echo $dObject['Body'];
    return;
}