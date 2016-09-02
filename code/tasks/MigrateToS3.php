<?php
	/**
	 * Created by PhpStorm.
	 * User: francospringveldt
	 * Date: 2016/07/13
	 * Time: 9:19 PM
	 */

	/**
	 * Class MigrateThemeToS3
	 *
	 * This BuildTask uploads an entire directory into an S3 Bucket
	 * @package
	 */
	class MigrateToS3 extends BuildTask
	{
		protected $title = 'Migrate theme to S3';
		protected $description = 'Send all stipulated theme files to S3';

		/**
		 * Implement this method in the task subclass to
		 * execute via the TaskRunner
		 */
		public function run($request)
		{
			/** @var S3Facade $facade */
			$facade = singleton('S3Facade');
			$folders = $facade->migrationFolders;

			foreach($folders as $key => $folder)
			{
				echo "Merging: $folder \n";
				/** @var S3Facade $facade */
				// @todo Implement SkipImprt
				//				$skipUponImport = Config::inst()
				//										->get('MigrateThemeToS3', 'skipFoldersDuringImport');
				$bucket = $facade->getBucket();
				$baseFolder = Director::baseFolder();
				$targetFolder = sprintf('%s/%s/', $baseFolder, $folder);

				$client = $facade->setupS3Client();
				$client->uploadDirectory(
					$targetFolder, $bucket, $folder, array(
						'params'      => array('ACL' => 'public-read'),
						'concurrency' => 100,
						'debug'       => true,
					)
				);
			}
			echo 'Migration completed!';
		}

	}