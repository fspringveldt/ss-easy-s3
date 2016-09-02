<?php
	/**
	 * Created by PhpStorm.
	 * User: francospringveldt
	 * Date: 2016/07/14
	 * Time: 11:28 AM
	 */

	/**
	 * Converts all Image URL's to s3's version thereof
	 *
	 * @author  Franco Springveldt
	 *
	 * Class ImageURLRequestFilter
	 * @package
	 */
	class ImageURLRequestFilter implements RequestFilter
	{
		/**
		 * List of folders which should be rewritten
		 * @var array
		 */
		public $foldersForRewrite;

		/**
		 * List of folders to skip for rewrite
		 * @ar array
		 */
		public $foldersToSkipDuringRewrite;

		/**
		 * Filter executed before a request processes
		 *
		 * @param SS_HTTPRequest $request Request container object
		 * @param Session        $session Request session
		 * @param DataModel      $model   Current DataModel
		 *
		 * @return boolean Whether to continue processing other filters. Null or true will continue processing
		 *                 (optional)
		 */
		public function preRequest(SS_HTTPRequest $request, Session $session, DataModel $model)
		{

		}

		/**
		 * Filter executed AFTER a request using domdocument
		 *
		 * @param SS_HTTPRequest  $request  Request container object
		 * @param SS_HTTPResponse $response Response output object
		 * @param DataModel       $model    Current DataModel
		 *
		 * @return boolean Whether to continue processing other filters. Null or true will continue processing
		 *                 (optional)
		 */
		public function postRequest(SS_HTTPRequest $request, SS_HTTPResponse $response, DataModel $model)
		{
			$earlyExit = false;
			$body = $response->getBody();

			//Skip if response is JSON
			$tryJson = json_decode($body);
			if(!is_null($tryJson) && $tryJson !== false)
			{
				$earlyExit = true;
			}

			if($this->is_valid_xml($body))
			{
				$earlyExit = true;
			}

			if($request->isAjax())
			{
				$earlyExit = true;
			}

			if(!$earlyExit)
			{
				/** @var S3PageExtension $extension */
				$extension = singleton('S3PageExtension');

				$dom = new DOMDocument();
				@$dom->loadHTML(mb_convert_encoding($body, 'HTML-ENTITIES', 'UTF-8'));
				$images = $dom->getElementsByTagName('img');

				$searchArray = $this->foldersForRewrite;
				//Generally the _resampled folders are skipped.
				$skipFolders = $this->foldersToSkipDuringRewrite;
				/** @var DomElement $img */
				foreach($images as $img)
				{
					if($src = $img->getAttribute('src'))
					{
						$src = ltrim($src, '/');
						//First check if we should skip
						$pathInfo = pathinfo($src);
						$dirName = $pathInfo['dirname'];
						if(in_array($dirName, $skipFolders))
							continue;

						foreach($searchArray as $key => $searchStr)
						{

							if(substr($src, 0, strlen($searchStr)) === $searchStr)
							{
								$extension->addCDNToThemeDir($src);
								//Set src including S3 location
								$img->setAttribute('src', $src);
							}
						}
					}
				}
				$response->setBody($dom->saveHTML());
			}
		}

		/**
		 *  Takes XML string and returns a boolean result where valid XML returns true
		 */
		public function is_valid_xml($xml)
		{
			if(empty($xml))
				return false;
			libxml_use_internal_errors(true);

			$doc = new DOMDocument('1.0', 'utf-8');

			$doc->loadXML($xml);

			$errors = libxml_get_errors();

			return empty($errors);
		}
	}