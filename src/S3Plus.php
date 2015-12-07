<?php
namespace AWSS3Plus;

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

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

    public function getListFromBucket($bucket)
    {
        
    }
}
