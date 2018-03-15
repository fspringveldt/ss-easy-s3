<?php
/**
 * Created by PhpStorm.
 * User: francospringveldt
 * Date: 2016/09/02
 * Time: 11:27 AM
 */

/**
 * Class S3Facade Provides utility methods for working with S3Client
 *
 * @package
 */
class S3Facade
{
    /**
     * @var array
     */
    public $migrationFolders;
    /**
     * @var array
     */
    private $s3Config;

    /**
     * @var \Aws\S3\S3Client
     */
    private $s3Client;


    /**
     * @var \Aws\S3\CloudFrontClient
     */
    private $cfClient;

    /**
     * Sets up and returns a new S3Client
     *
     * @return \Aws\S3\S3Client
     * @throws Exception
     */
    public function setupS3Client()
    {
        if (!$this->s3Client) {
            //Check if exists. Update if changed.
            $cfg = $this->s3Config();
            $array = array(
                'version' => 'latest',
                'region' => $cfg['region'],
                'credentials' => array(
                    'key' => $cfg['key'],
                    'secret' => $cfg['secret'],
                ),
            );
            $this->s3Client = new \Aws\S3\S3Client(
                $array
            );
        }

        return $this->s3Client;
    }

    /**
     * Sets up and returns and CloudFrontClientClient
     *
     * @return \Aws\CloudFront\CloudFrontClient
     */
    public function setupCloudFrontClient()
    {
        if (!$this->cfClient) {
            $cfg = $this->s3Config();
            $array = array(
                'version' => 'latest',
                'region' => $cfg['region'],
                'credentials' => array(
                    'key' => $cfg['key'],
                    'secret' => $cfg['secret'],
                ),
            );
            $this->cfClient = new Aws\CloudFront\CloudFrontClient($array);
        }

        return $this->cfClient;
    }

    /**
     * Returns the S3 config
     *
     * @return array
     * @throws Exception
     */
    private function s3Config()
    {
        if (!$this->s3Config) {
            $cfg = Config::inst()
                ->get('S3Facade', 'config');

            if (is_null($cfg)) {
                throw new Exception(
                    'Configuration not found in any yaml file. Please 
                    check the ss-easy-s3 README.md file for how to add it.'
                );
            }

            $this->s3Config = array_key_exists(
                Director::get_environment_type(),
                $cfg
            ) ? $cfg[Director::get_environment_type()] : array();
        }

        return $this->s3Config;
    }

    /**
     * Returns the S3 bucket url from config
     *
     * @return array
     */
    public function s3BucketURL()
    {
        $url = '';
        $cfg = $this->s3Config();
        if (isset($cfg['url'])) {
            $url = $cfg['url'];
        } else {
            //Build it up from the info we have
            $protocol = Director::is_https() ? 'https://' : 'http://';
            $region = 's3-' . $this->getRegion();//@todo remove hard coded s3-
            $url = sprintf(
                '%s%s.amazonaws.com/%s',
                $protocol,
                $region,
                $this->getBucket()
            );
        }

        return $url;
    }

    /**
     * Invalidates CloudFront cache
     *
     * @param  $key The item key to invalidate
     * @throws Exception
     */
    public function InvalidateCache($key)
    {
        $distributionID = $this->s3Config()['distribution-id'];

        if (!empty($distributionID)) {
            $cfClient = $this->setupCloudFrontClient();
            $cfClient->createInvalidation(
                array(
                    'DistributionId' => $distributionID,
                    'InvalidationBatch' => array(
                        'CallerReference' => sha1(date('H:i:s')),
                        'Paths' => array(
                            'Quantity' => 1,
                            'Items' => array('/' . $key),
                        ),
                    ),
                )
            );
        }
    }

    /**
     * Returns the name of the S3 bucket
     *
     * @return mixed
     * @throws Exception
     */
    public function getBucket()
    {
        $cfg = $this->s3Config();

        return $cfg['bucket'];
    }

    /**
     * Returns the region of the S3 bucket
     *
     * @return mixed
     * @throws Exception
     */
    public function getRegion()
    {
        $cfg = $this->s3Config();

        return $cfg['region'];
    }
}
