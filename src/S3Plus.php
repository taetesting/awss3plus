<?php
namespace AWSS3Plus;

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Guzzle\Service\Resource\Model;

class S3Plus
{
    const ACL_PRIVATE           = 'private';
    const ACL_PUBLIC_READ       = 'public-read';
    const ACL_PUBLIC_READ_WRITE = 'public-read-write';
    
    private $s3Client;
    private $region;
    private $version;

    function __construct($key, $secret, $region, $version = '2006-03-01') {
        $this->s3Client = new S3Client([
                            'version'     => $version,
                            'region'      => $region,
                            'credentials' => [ 'key' => $key, 'secret' => $secret ]
                        ]);
    }

    public function uploadOneFile($bucket, $filePath)
    {
        if (!file_exists ($filePath)) {
            return "FILE NOT FOUND";
        }
        $date_utc = new \DateTime(null, new \DateTimeZone("UTC"));
        $time     = $date_utc->format('Ymd-His');
        $key = $time . '-' . basename($filePath);
        try {
            $result = $this->s3Client->putObject(array(
                                        'Bucket'       => $bucket,
                                        'SourceFile'   => $filePath,
                                        'ContentType'  => 'text/plain',
                                        'Key'          => $key,
                                        'StorageClass' => 'REDUCED_REDUNDANCY',
                                        'ACL'          => self::ACL_PUBLIC_READ
                                    ));
            // We can poll the object until it is accessible
            $this->s3Client->waitUntil('ObjectExists', array(
                'Bucket' => $bucket,
                'Key'    => $key
            ));
            if (!isset($result['ETag']) || empty($result['ETag'])) {
                return false;
            }
            
            return true;
        } catch (S3Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * object format
     * array(6) { 
     *  ["Key"]=> string
     *  ["LastModified"]=> object(Aws\Api\DateTimeResult)#128 (3) { 
     *                                      ["date"]=> string(26) "2015-12-04 10:39:06.000000" 
     *                                      ["timezone_type"]=> int(2) 
     *                                      ["timezone"]=> string(1) "Z" } 
     *  ["ETag"]=> string(34) ""d41d8cd98f00b204e9800998ecf8427e"" 
     *  ["Size"]=> int 
     *  ["StorageClass"]=> string(8) "STANDARD"
     *  ["Owner"]=> array(2) { 
     *              ["DisplayName"]=> string(11) "andrew.duck" 
     *              ["ID"]=> string(64) "60e436f8955b82fbc8fb5b4b4f41712975b04e500f0664d7477d1a0e1ed4a663" } 
     * } 
     */
    public function getListFromBucket($bucket)
    {
        $objects = $this->s3Client->getIterator('ListObjects', array('Bucket' => $bucket));
        return $objects;
    }

    public function getObject($bucket, $keyName)
    {
        $object = $this->s3Client->getObject(array(
                                    'Bucket' => $bucket,
                                    'Key'    => $keyName
                                ));
        return $object;
    }
}
