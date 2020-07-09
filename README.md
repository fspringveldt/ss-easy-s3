# Amazon S3 bucket integration
Seamlessly integrates assets,theme assets, css and js with S3 - with optional CloudFront CDN.
## Minimum requirements
```
silverstripe/framework: ^3.5.*
silverstripe/cms: ^3.5.*
aws/aws-sdk-php: ^3.18
```
## Installation and Setup
To install, run below from root of SilverStripe installation
```bash
> composer require fspringveldt/ss-easy-s3
```
http://**your-site-url**?flush=1 once composer is complete the flush the manifest.

Once installed and configured, head on over to Amazon, create an account and bucket. Below are some resources you can use to assist during this process.

* [Using CloudFront with Amazon S3](http://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/MigrateS3ToCloudFront.html)
* [Getting Started with CloudFront](http://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/GettingStarted.html)
* [Syncing data with Amazon S3](http://docs.aws.amazon.com/aws-sdk-php/v2/guide/service-s3.html#syncing-data-with-amazon-s3)
* [For S3 Regions!! - This one can get you hahaha](http://docs.aws.amazon.com/general/latest/gr/rande.html#s3_region)


Once your Amazon info is sorted, you'll need the following info from the Amazon console:

* key
* secret
* region
* bucket
* url
* distribution-id

...which is then input into your mysite the config file as:
```yaml
S3Facade:
  config:
    dev:
      key:
      secret:
      region:
      bucket:
      url:
      distribution-id:
    test:
      key:
      secret:
      region:
      bucket:
      url:
      distribution-id:
    live:
      key:
      secret:
      region:
      bucket:
      url:
      distribution-id:
```

_NB: You can setup multiple configs per environment_
. A simple flush should complete your setup.

This module will __automagically__ re-write all your URL's to point to the resources from either the S3 Bucket or CloudFront url you specified.

### Deleting local files
You now also have the option to delete local files and only keep those in S3, which is switched off by default. To enable this functionality, do the following:
1. Add this config entry
```yaml
Image:
    keepLocal: false
```
2. Then schedule the task called RemoveLocalCopies to run at a comfortable interval to remove these files.
An example cron entry reflects below, replacing everything in square brackets with correct values
```bash
*/5 * * * * [path-to-php-binary] [path-to-application]/framework/cli-script.php dev/build
```
An example on ubuntu: ``` */5 * * * * /usr/bin/php /var/www/example/framework/cli-script.php dev/build ```

## Migrate to S3 build-task
There is also a handy tool which will upload entire directories to your S3 bucket blisteringly fast. You can specify which directories to upload via the [_ _config/config.yml_](_config/config.yml) file by adding more entries to the ```migrationFolders``` property. The assets folder is added by default.

## Working with local environment
1. Download [certificate](https://curl.haxx.se/ca/cacert.pem) file
2. Copy it inside `ss-easy-s3/` root directory (assuming `ss-easy-s3` folder is inside project root directory)
3. Add some additional config below inside the config `array()` in `setupS3Client()` and `setupCloudFrontClient()` method in `code/classes/S3Facade.php` file

        $array = array(
            'version' => ...,
            'region' => ...,
            'credentials' => ...,
            // add these lines
            'scheme'  => 'http',
            'http'    => [
                'verify' => '../ss-easy-s3/cacert.pem'
            ]
        );


4. Refresh the page and test uploading image (in my case using `Files` page in CMS Admin)

_***Note:** You should disable all debug command such as `Debug::show` and `var_dump()`_

**References:**
- [AWS SSL security error : [curl] 60: blablabla](https://stackoverflow.com/questions/24620393/aws-ssl-security-error-curl-60-ssl-certificate-prob-unable-to-get-local?answertab=votes#tab-top)
- [Config to disable SSL](https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/faq.html#how-do-i-disable-ssl)
- [Pinging your S3 Bucket Service](https://aws.amazon.com/premiumsupport/knowledge-center/s3-could-not-connect-endpoint-url/)
