<?php
/**
* @package		ZL Framework
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// register necesary classes
App::getInstance('zoo')->loader->register('AEUtilDropbox', 'classes:dropbox.php');

/**
 * ZLStorage Dropbox Adapter class
 */
class ZLStorageAdapterDropbox extends ZLStorageAdapterBase implements ZLStorageAdapter {

	/**
	 * A reference to the global App object
	 *
	 * @var App
	 */
	public $app;

	/**
	 * A reference to the Dropbox Utility class
	 *
	 * @var dropbox
	 */
	protected $dropbox;

	/**
	 * Class Constructor
	 */
	public function __construct($options) {

		// init vars
		$this->app = App::getInstance('zoo');

		// init Dropbox Utility
		$this->dropbox = new AEUtilDropbox();
		$this->dropbox->setAppKeys(json_decode(base64_decode('eyJhcHAiOiJqZng4enFwdGwyYXc1NGQiLCJzZWNyZXQiOiJuZ2prZmxkY2R3ZDhnd3EifQ==')));

		$token = (object)array(
			'oauth_token_secret'	=> $token_secret,
			'oauth_token'			=> $token,
			'uid'					=> $token_uid,
		);
		
		$this->dropbox->setToken($token);
	}

	/**
	 * Writes a file to the filesystem selected
	 * 
	 * @param string $file The filename (or path)
	 * @param mixed $content The content to write
	 * 
	 * @return boolean The success of the operation
	 */
	public function write($file, $content, $overwrite = true){
	}

	/**
	 * Reads a file content from the filesystem selected
	 * 
	 * @param string $file The filename (or path)
	 */
	public function read($file) {
	}

	/**
	 * Deletes a file from the filesystem selected
	 * 
	 * @param string $file The filename (or path)
	 * 
	 * @return boolean The success of the operation
	 */
	public function delete($file) {
	}

	/**
	 * Check if a file exists in the filesystem selected
	 * 
	 * @param string $file The filename (or path)
	 */
	public function exists($file) {
	}

	/**
	 * Moves an uploaded file to a destination folder
	 * 
	 * @param string $file The name of the php (temporary) uploaded file
	 * @param string $dest The path (including filename) to move the uploaded file to
	 */
	public function upload($file, $dest)
	{
	}

	/**
	 * Get a Folder/File tree list
	 * 
	 * @param string $root The path to the root folder
	 * 
	 * @return boolean The success of the operation
	 */
	public function getTree($root, $legalExt)
	{
	}
}