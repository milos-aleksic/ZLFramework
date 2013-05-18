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
	 * Load ZLUX Items Manager assets
	 *
	 * @since 3.0.14
	 */
	public function loadItemsManagerAssets()
	{
		// ZLUX Main
		$this->loadMainAssets();

		// dataTables
		if(!JDEBUG){
			$this->app->document->addScript('zlfw:zlux/assets/datatables/dataTables.with.plugins.min.js');
		} else {
			// Site in debug Mode
			$this->app->document->addScript('zlfw:zlux/assets/datatables/dataTables.js');
			$this->app->document->addScript('zlfw:zlux/assets/datatables/dataTables.plugins.js');
		}

		// perfect scrollbar
		$this->app->document->addStylesheet('zlfw:zlux/assets/perfect-scrollbar/perfect-scrollbar.min.css');
		$this->app->document->addScript('zlfw:zlux/assets/perfect-scrollbar/perfect-scrollbar.with-mousewheel.min.js');
	}

	/**
	 * Load ZLUX Files Manager assets
	 *
	 * @since 3.0.14
	 */
	public function loadFilesManagerAssets()
	{
		// ZLUX Core
		$this->loadMainAssets();

		// dataTables
		if(!JDEBUG) {
			$this->app->document->addScript('zlfw:zlux/assets/datatables/dataTables.with.plugins.min.js');
		} else {
			// Site in debug Mode
			$this->app->document->addScript('zlfw:zlux/assets/datatables/dataTables.js');
			$this->app->document->addScript('zlfw:zlux/assets/datatables/dataTables.plugins.js');
		}

		// plupload
		$this->app->document->addScript('zlfw:zlux/assets/plupload/plupload.full.min.js');

		// nanoScroller
		$this->app->document->addScript('zlfw:zlux/assets/nanoscroller/nanoscroller.min.js');
	}

	/**
	 * Load ZLUX Main assets
	 *
	 * @since 3.0.14
	 */
	public function loadMainAssets()
	{
		if(!JDEBUG) {
			$this->app->document->addStylesheet('zlfw:zlux/zlux.css');
			$this->app->document->addScript('zlfw:zlux/zlux.all.min.js');
		} else {
			// Site in debug Mode
			$this->app->document->addStylesheet('zlfw:zlux/zlux.css');
			$this->app->document->addScript('zlfw:zlux/zluxMain.js');
			$this->app->document->addScript('zlfw:zlux/zluxDialog.js');
			$this->app->document->addScript('zlfw:zlux/zluxFilesManager.js');
			$this->app->document->addScript('zlfw:zlux/zluxItemsManager.js');
		}

		// ZL Bootstrap
		$this->loadBootstrap(true);
	}

	/**
	 * Load ZL Bootstrap assets
	 *
	 * @since 3.0.14
	 */
	public function loadBootstrap($loadJS = false)
	{
		$this->app->document->addStylesheet('zlfw:zlux/zlbootstrap/css/bootstrap-zl.min.css');
		$this->app->document->addStylesheet('zlfw:zlux/zlbootstrap/css/bootstrap-zl-responsive.min.css');

		// if stated load JS too
		if ($loadJS) 
			$this->app->document->addScript('zlfw:zlux/zlbootstrap/js/bootstrap.min.js');
	}

	/**
	 * Get Amazon S3 signed policy
	 *
	 * @since 3.0.14
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
				// for demo purposes we are accepting only images
				array('starts-with', '$Content-Type', 'image/'),
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