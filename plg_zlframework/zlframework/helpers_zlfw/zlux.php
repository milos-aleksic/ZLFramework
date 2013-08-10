<?php
/**
* @package		ZL Framework
* @author    	ZOOlanders http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

/*
	Class: zlfwHelperZLUX
		A class that contains ZLUX functions
*/
class zlfwHelperZLUX extends AppHelper {

	/**
	 * Load ZLUX Main assets
	 */
	public function loadMainAssets()
	{
		// ZLUX
		$this->app->document->addStylesheet('zlfw:zlux/zluxMain.css');
		$this->app->document->addScript('zlfw:zlux/zluxMain.min.js');

		// ZL Bootstrap
		$this->loadBootstrap(true);

		// load Variables
		$this->loadVariables();
	}

	/**
	 * Load ZL Bootstrap assets
	 */
	public function loadBootstrap($loadJS = false)
	{
		if ($this->app->joomla->isVersion('2.5')) {
			$this->app->document->addStylesheet('zlfw:zlux/assets/zlbootstrap/css/bootstrap-zl.min.css');
			$this->app->document->addStylesheet('zlfw:zlux/assets/zlbootstrap/css/bootstrap-zl-responsive.min.css');

			// if stated load JS too
			if ($loadJS) $this->app->document->addScript('zlfw:zlux/assets/zlbootstrap/js/bootstrap.min.js');

		// j3
		} else {
			$this->app->document->addStylesheet('zlfw:zlux/assets/zlbootstrap/css/font-awesome-zl.min.css');
		}
	}

	/**
	 * Load JS Variables
	 */
	public function loadVariables()
	{
		if (!defined('PLG_ZLFRAMEWORK_ZLUX_SCRIPT_DECLARATION'))
		{
			define('PLG_ZLFRAMEWORK_ZLUX_SCRIPT_DECLARATION', true);

			// init vars
			$javascript = '';
			$app_id = $this->app->zoo->getApplication() ? $this->app->zoo->getApplication()->id : '';

			// save Joomla! URLs
			$javascript .= 'jQuery.fn.zluxMain.prototype.JRoot = "' . JURI::root() . '";';
			$javascript .= 'jQuery.fn.zluxMain.prototype.JBase = "' . JURI::base() . '";';
			$javascript .= 'jQuery.fn.zluxMain.prototype.ajax = "' . JURI::base() 
				. 'index.php?option=com_zoo&controller=zlux&format=raw&app_id=' . $app_id  . '";';

			// set translations strings
			$translations = array
			(
				// ZLUX Main
				'APPLY_FILTERS' => 'PLG_ZLFRAMEWORK_ZLUX_APPLY_FILTERS',
				'REFRESH' => 'PLG_ZLFRAMEWORK_REFRESH',
				'DELETE' => 'PLG_ZLFRAMEWORK_DELETE',
				'RENAME' => 'PLG_ZLFRAMEWORK_RENAME',
				'NAME' => 'PLG_ZLFRAMEWORK_NAME',
				'TYPE' => 'PLG_ZLFRAMEWORK_TYPE',
				'SIZE' => 'PLG_ZLFRAMEWORK_SIZE',
				'CONFIRM' => 'PLG_ZLFRAMEWORK_CONFIRM',
				'AUTHOR' => 'PLG_ZLFRAMEWORK_AUTHOR',
				'CREATED' => 'PLG_ZLFRAMEWORK_CREATED',
				'ACCESS' => 'PLG_ZLFRAMEWORK_ACCESS',
				'ROUTE' => 'PLG_ZLFRAMEWORK_ROUTE',
				'ROOT' => 'PLG_ZLFRAMEWORK_ROOT',
				'SOMETHING_WENT_WRONG' => 'PLG_ZLFRAMEWORK_ZLUX_SOMETHING_WENT_WRONG',
				
				// ZLUX FilesManager
				'STORAGE_PARAM_MISSING' => 'PLG_ZLFRAMEWORK_ZLUX_FM_STORAGE_PARAM_MISSING',
				'INPUT_THE_NEW_NAME' => 'PLG_ZLFRAMEWORK_ZLUX_FM_INPUT_THE_NEW_NAME',
				'DELETE_THIS_FILE' => 'PLG_ZLFRAMEWORK_ZLUX_FM_DELETE_THIS_FILE',
				'DELETE_THIS_FOLDER' => 'PLG_ZLFRAMEWORK_ZLUX_FM_DELETE_THIS_FOLDER',
				'FOLDER_NAME' => 'PLG_ZLFRAMEWORK_ZLUX_FM_FOLDER_NAME',
				'EMPTY_FOLDER' => 'PLG_ZLFRAMEWORK_ZLUX_FM_EMPTY_FOLDER',

					// Upload
					'ADD_NEW_FILES' => 'PLG_ZLFRAMEWORK_ZLUX_FM_ADD_NEW_FILES',
					'START_UPLOADING' => 'PLG_ZLFRAMEWORK_ZLUX_FM_START_UPLOADING',
					'CANCEL_CURRENT_UPLOAD' => 'PLG_ZLFRAMEWORK_ZLUX_FM_CANCEL_CURRENT_UPLOAD',
					'NEW_FOLDER' => 'PLG_ZLFRAMEWORK_ZLUX_FM_NEW_FOLDER',
					'UPLOAD_FILES' => 'PLG_ZLFRAMEWORK_ZLUX_FM_UPLOAD_FILES',
					'DROP_FILES' => 'PLG_ZLFRAMEWORK_ZLUX_FM_DROP_FILES',
					
					// Errors
					'FILE_EXT_ERROR' => 'PLG_ZLFRAMEWORK_ZLUX_FM_ERR_FILE_EXT', 
					'FILE_SIZE_ERROR' => 'PLG_ZLFRAMEWORK_ZLUX_FM_ERR_FILE_SIZE',
					'RUNTIME_MEMORY_ERROR' => 'PLG_ZLFRAMEWORK_ZLUX_FM_ERR_RUNTIME_MEMORY',
					'S3_BUCKET_PERIOD_ERROR' => 'PLG_ZLFRAMEWORK_ZLUX_FM_ERR_S3_BUCKET_PERIOD',
					'S3_BUCKET_MISSCONFIG_ERROR' => 'PLG_ZLFRAMEWORK_ZLUX_FM_ERR_S3_BUCKET_MISSCONFIG',
					'UPLOAD_URL_ERROR' => 'PLG_ZLFRAMEWORK_ZLUX_FM_ERR_UPLOAD_URL',

					// plupload core strings
					'File extension error.' => 'PLG_ZLFRAMEWORK_FLP_FILE_EXTENSION_ERROR', 
					'File size error.' => 'PLG_ZLFRAMEWORK_FLP_FILE_SIZE_ERROR',
					'File count error.' => 'PLG_ZLFRAMEWORK_FLP_FILE_COUNT_ERROR',

				// ZLUX ItemsManager
				'IM_NO_ITEMS_FOUND' => 'PLG_ZLFRAMEWORK_ZLUX_IM_NO_ITEMS_FOUND',
				'IM_PAGINATION_INFO' => 'PLG_ZLFRAMEWORK_ZLUX_IM_PAGINATION_INFO',
				'IM_FILTER_BY_APP' => 'PLG_ZLFRAMEWORK_ZLUX_IM_FILTER_BY_APP',
				'IM_FILTER_BY_TYPE' => 'PLG_ZLFRAMEWORK_ZLUX_IM_FILTER_BY_TYPE',
				'IM_FILTER_BY_CATEGORY' => 'PLG_ZLFRAMEWORK_ZLUX_IM_FILTER_BY_CATEGORY',
				'IM_FILTER_BY_TAG' => 'PLG_ZLFRAMEWORK_ZLUX_IM_FILTER_BY_TAG'
			);

			// translate
			$translations = array_map(array('JText', '_'), $translations);

			// add to script
			$javascript .= "jQuery.fn.zluxMain.translations = " . json_encode($translations) . ";";

			// load the script
			$this->app->document->addScriptDeclaration($javascript);

			// execute zoo calendar field in order to auto translate the Datepicker vars
			$this->app->html->_('zoo.calendar', '', '', '');
		}
	}

	/**
	 * Get Amazon S3 signed policy
	 */
	public function getAmazonS3signedPolicy($bucket, $secretkey)
	{
		// prepare policy
		$policy = base64_encode(json_encode(array(
			// ISO 8601 - date('c'); generates uncompatible date, so better do it manually
			'expiration' => date('Y-m-d\TH:i:s.000\Z', strtotime('+1 day')),  
			'conditions' => array(
				array('bucket' => $bucket),
				array('acl' => 'public-read'),
				array('starts-with', '$key', ''),
				// "Some versions of the Adobe Flash Player do not properly handle HTTP responses that have an empty body. 
				// To configure POST to return a response that does not have an empty body, set success_action_status to 201.
				// When set, Amazon S3 returns an XML document with a 201 status code." 
				// http://docs.amazonwebservices.com/AmazonS3/latest/dev/HTTPPOSTFlash.html
				array('success_action_status' => '201'),
				// Plupload internally adds name field, so we need to mention it here
				array('starts-with', '$name', ''), 	
				// One more field to take into account: Filename - gets silently sent by FileReference.upload() in Flash
				// http://docs.amazonwebservices.com/AmazonS3/latest/dev/HTTPPOSTFlash.html
				array('starts-with', '$Filename', ''), 
			)
		)));

		// sign policy
		$signature = base64_encode(hash_hmac('sha1', $policy, $secretkey, true));

		// return
		return array('signature' => $signature, 'policy' => $policy);
	}
}