<?php

/**
 * Page extension this module
 * Class S3PageExtension
 *
 * @package
 */
class S3PageExtension extends Extension
{
    /**
     * @var S3Facade
     */
    public $s3Facade;

    private static $db = array();

    private static $indexes = array();

    private static $has_one = array();

    private static $has_many = array();

    /**
     * Prepends the s3 bucket url to a string
     *
     * @param $themeDir
     */
    public function addCDNToThemeDir(&$themeDir)
    {
        $themeDir = sprintf(
            '%s/%s',
            $this->s3Facade->s3BucketURL(),
            $themeDir
        );
    }
}
