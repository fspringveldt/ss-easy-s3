<?php
	/**
	 * Page extension this module
	 * Class S3PageExtension
	 * @package
	 *
	 */
	class S3PageExtension extends Extension
	{
		/**
		 * @var S3Facade
		 */
		public $s3Facade;

		static $db = array();
		
		static $indexes = array();
		
		static $has_one = array();
		
		static $has_many = array();
		
		public function updateCMSFields(FieldList $fields){
			
		}
		
		public function onBeforeWrite(){
			parent::onBeforeWrite();
		}
		
		public function onAfterWrite(){
			parent::onAfterWrite();
		}

		/**
		 * Prepends the s3 bucket url to a string
		 * @param $themeDir
		 */
		public function addCDNToThemeDir(&$themeDir){
			$themeDir = sprintf('%s/%s',$this->s3Facade->s3BucketURL(),$themeDir);
		}
	}

