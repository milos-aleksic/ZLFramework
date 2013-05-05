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
App::getInstance('zoo')->loader->register('ZLUtilAmazons3', 'classes:amazons3.php');

/**
 * ZLStorage Amazon S3 Adapter class
 */
class ZLStorageAdapterAmazonS3 extends ZLStorageAdapterBase implements ZLStorageAdapter {


	// Create an S3 instance with the required credentials
		


	/**
	 * Writes a file to the filesystem selected
	 * 
	 * @param  string  $file      The filename (or path)
	 * @param  mixed   $content   The content to write
	 * @param  boolean $overwrite If we have to overwrite the file (default: true)
	 * 
	 * @return boolean             The success of the operation
	 */
	public function write($file, $content, $overwrite = true){
	}

	/**
	 * Reads a file content from the filesystem selected
	 * 
	 * @param  string  $file      The filename (or path)
	 * 
	 * @return mixed             The content of the file
	 */
	public function read($file) {

	}

	/**
	 * Deletes a file from the filesystem selected
	 * 
	 * @param  string  $file      The filename (or path)
	 * 
	 * @return boolean             The success of the operation
	 */
	public function delete($file) {

	}

	/**
	 * Check if a file exists in the filesystem selected
	 * 
	 * @param  string  $file      The filename (or path)
	 * 
	 * @return boolean             The success of the operation
	 */
	public function exists($file) {

	}

	/**
	 * Moves an uploaded file to a destination folder
	 * 
	 * @param   string   $file         The name of the php (temporary) uploaded file
	 * @param   string   $dest         The path (including filename) to move the uploaded file to
	 */
	public function upload($file, $dest) {
		
		$s3 = AEUtilAmazons3::getInstance('AKIAJBGAQYDO6Z76KIGQ', '014/6Ig4f2MTXmZWvFndZYJkhKdRW2DY3L+qqh+c', false);

		
		$rrs = false;
		$absolute_filename = $this->app->path->path('zlfw:changelog.txt');
		// Legacy single part uploads
		$result = $s3->putObject(
			AEUtilAmazons3::inputFile( $absolute_filename, false ),		// File to read from
			'milcom.testing',													// Bucket name
			'changelog.txt',													// Remote relative filename, including directory
			AEUtilAmazons3::ACL_BUCKET_OWNER_FULL_CONTROL,				// ACL (bucket owner has full control, file owner gets full control)
			array(),													// Meta headers
			// Other request headers
			array(
				// Amazon storage class (support for RRS - Reduced Redundancy Storage)
				'x-amz-storage-class'	=> $rrs ? 'REDUCED_REDUNDANCY' : 'STANDARD'
			)
		);

		// $s3->setError('You have not set up your Amazon S3 Access Key');
	}
}