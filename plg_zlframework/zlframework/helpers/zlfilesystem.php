<?php
/**
* @package		ZL Framework
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// register FilesystemHelper class
App::getInstance('zoo')->loader->register('FilesystemHelper', 'helpers:filesystem.php');

/*
	Class: ZlFilesystemHelper
		The ZL filesystem helper class
*/
class ZlFilesystemHelper extends FilesystemHelper
{
	/**
	 * Makes file name safe to use
	 * @param mixed The name of the file (not full path)
	 * @return mixed The sanitised string or array
	 *
	 * Original Credits:
	 * @package   	JCE
	 * @copyright 	Copyright �� 2009-2011 Ryan Demmer. All rights reserved.
	 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
	 * 
	 * Adapted to ZOO (ZOOlanders.com)
	 * Copyright 2011, ZOOlanders.com
	 */
	public static function makeSafe($subject, $mode = 'utf-8')
	{		
		// remove multiple . characters
		$search = array('#(\.){2,}#');		

		switch($mode) {
			default:
			case 'utf-8':
				$search[] 	= '#[^a-zA-Z0-9_\.\-\s~ \p{L}\p{N}]#u';
				$mode 		= 'utf-8';
				break;
			case 'ascii':
				$subject 	= self::utf8_latin_to_ascii($subject);
				$search[] 	= '#[^a-zA-Z0-9_\.\-\s~ ]#';
				break;
		}
		
		// strip leading .
		$search[] = '#^\.*#';
		// strip whitespace
		$saerch[] = '#^\s*|\s*$#';
		
		// only for utf-8 to avoid PCRE errors - PCRE must be at least version 5
		if ($mode == 'utf-8') {
			try {
				return preg_replace($search, '', $subject);
			} catch (Exception $e) {
				// try ascii
				return self::makeSafe($subject, 'ascii');
			}
		}
		
		return preg_replace($search, '', $subject);
	}
	
	private static function utf8_latin_to_ascii( $subject ){

		static $CHARS = NULL;

		if (is_null($CHARS)) {
			$CHARS = array(
				'�'=>'A','�'=>'A','�'=>'A','�'=>'A','�'=>'A','�'=>'A','�'=>'AE',
				'�'=>'C','�'=>'E','�'=>'E','�'=>'E','�'=>'E','�'=>'I','�'=>'I','�'=>'I','�'=>'I',
				'�'=>'D','�'=>'N','�'=>'O','�'=>'O','�'=>'O','�'=>'O','�'=>'O','�'=>'O',
				'�'=>'U','�'=>'U','�'=>'U','�'=>'U','�'=>'Y','�'=>'s',
				'�'=>'a','�'=>'a','�'=>'a','�'=>'a','�'=>'a','�'=>'a','�'=>'ae',
				'�'=>'c','�'=>'e','�'=>'e','�'=>'e','�'=>'e','�'=>'i','�'=>'i','�'=>'i','�'=>'i',
				'�'=>'n','�'=>'o','�'=>'o','�'=>'o','�'=>'o','�'=>'o','�'=>'o','�'=>'u','�'=>'u','�'=>'u','�'=>'u',
				'�'=>'y','�'=>'y','A'=>'A','a'=>'a','A'=>'A','a'=>'a','A'=>'A','a'=>'a',
				'C'=>'C','c'=>'c','C'=>'C','c'=>'c','C'=>'C','c'=>'c','C'=>'C','c'=>'c','D'=>'D','d'=>'d','�'=>'D','d'=>'d',
				'E'=>'E','e'=>'e','E'=>'E','e'=>'e','E'=>'E','e'=>'e','E'=>'E','e'=>'e','E'=>'E','e'=>'e',
				'G'=>'G','g'=>'g','G'=>'G','g'=>'g','G'=>'G','g'=>'g','G'=>'G','g'=>'g','H'=>'H','h'=>'h','H'=>'H','h'=>'h',
				'I'=>'I','i'=>'i','I'=>'I','i'=>'i','I'=>'I','i'=>'i','I'=>'I','i'=>'i','I'=>'I','i'=>'i',
				'?'=>'IJ','?'=>'ij','J'=>'J','j'=>'j','K'=>'K','k'=>'k','L'=>'L','l'=>'l','L'=>'L','l'=>'l','L'=>'L','l'=>'l','?'=>'L','?'=>'l','L'=>'l','l'=>'l',
				'N'=>'N','n'=>'n','N'=>'N','n'=>'n','N'=>'N','n'=>'n','?'=>'n','O'=>'O','o'=>'o','O'=>'O','o'=>'o','O'=>'O','o'=>'o','�'=>'OE','�'=>'oe',
				'R'=>'R','r'=>'r','R'=>'R','r'=>'r','R'=>'R','r'=>'r','S'=>'S','s'=>'s','S'=>'S','s'=>'s','S'=>'S','s'=>'s','�'=>'S','�'=>'s',
				'T'=>'T','t'=>'t','T'=>'T','t'=>'t','T'=>'T','t'=>'t','U'=>'U','u'=>'u','U'=>'U','u'=>'u','U'=>'U','u'=>'u','U'=>'U','u'=>'u','U'=>'U','u'=>'u','U'=>'U','u'=>'u',
				'W'=>'W','w'=>'w','Y'=>'Y','y'=>'y','�'=>'Y','Z'=>'Z','z'=>'z','Z'=>'Z','z'=>'z','�'=>'Z','�'=>'z','?'=>'s','�'=>'f','O'=>'O','o'=>'o','U'=>'U','u'=>'u',
				'A'=>'A','a'=>'a','I'=>'I','i'=>'i','O'=>'O','o'=>'o','U'=>'U','u'=>'u','U'=>'U','u'=>'u','U'=>'U','u'=>'u','U'=>'U','u'=>'u','U'=>'U','u'=>'u',
				'?'=>'A','?'=>'a','?'=>'AE','?'=>'ae','?'=>'O','?'=>'o'
			);
		}
			
		return str_replace(array_keys($CHARS), array_values($CHARS), $subject);
	}
	
	public static function cleanPath($path){
		return preg_replace('#[/\\\\]+#', '/', trim($path));
	}
	
	/*
		Function: makePath
			Concat two paths together. Basically $a + $b
		Parameters:
			$a string Path one
			$b string Path two
		Returns:
			string $a DIRECTORY_SEPARATOR $b
	*/
	public static function makePath($a, $b){
		return self::cleanPath($a . '/' . $b);
	}
	
	/*
		Function: folderCreate
			New folder base function. A wrapper for the JFolder::create function
		Parameters:
			$folder string The folder to create
		Returns:
			boolean true on success
		Original Credits:
			@package   	JCE
			@copyright 	Copyright �� 2009-2011 Ryan Demmer. All rights reserved.
			@license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
	*/
	public function folderCreate($folder)
	{
		if (@JFolder::create($folder)) {
			$buffer = '<html><body bgcolor="#FFFFFF"></body></html>';
			JFile::write($folder.'/index.html', $buffer);
		} else {
			return false;
		}
		return true;
	}
	
	/**
	 * Original Credits:
	 * @package   	JCE
	 * @copyright 	Copyright �� 2009-2011 Ryan Demmer. All rights reserved.
	 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
	 * 
	 * Adapted to ZOO by ZOOlanders
	 * Copyright 2011, ZOOlanders.com
	 */
	public function getUploadValue() {
		$upload = trim(ini_get('upload_max_filesize'));
		$post 	= trim(ini_get('post_max_size'));	
			
		$upload = $this->returnBytes($upload);
		$post 	= $this->returnBytes($post);
		
		$result = $post;
		if (intval($upload) <= intval($post)) {
			$result = $upload;
		}
		
		return $this->formatFilesize($result, 'KB');
	}
	
	/*
		Function: returnBytes
			Output size in bytes

		Parameters:
			$size_str - size string

		Returns:
			String
	*/
	public function returnBytes($size_str) {
	    switch (substr ($size_str, -1)) {
	        case 'M': case 'm': return (int)$size_str * 1048576;
	        case 'K': case 'k': return (int)$size_str * 1024;
	        case 'G': case 'g': return (int)$size_str * 1073741824;
	        default: return $size_str;
	    }
	}
	
	/*
		Function: formatFilesize
			Output filesize with suffix.

		Parameters:
			$bytes - byte size
			$format - the size format
			$precision - the number precision

		Returns:
			String - Filesize
	*/
	public function formatFilesize($bytes, $format = false, $precision = 2)
	{  
		$kilobyte = 1024;
		$megabyte = $kilobyte * 1024;
		$gigabyte = $megabyte * 1024;
		$terabyte = $gigabyte * 1024;

		if (($bytes >= 0) && ($bytes < $kilobyte)) {
			return $bytes . ' B';

		} elseif (($bytes >= $kilobyte) && ($bytes < $megabyte) || $format == 'KB') {
			return round($bytes / $kilobyte, $precision) . ' KB';

		} elseif (($bytes >= $megabyte) && ($bytes < $gigabyte) || $format == 'MB') {
			return round($bytes / $megabyte, $precision) . ' MB';

		} elseif (($bytes >= $gigabyte) && ($bytes < $terabyte) || $format == 'GB') {
			return round($bytes / $gigabyte, $precision) . ' GB';

		} elseif ($bytes >= $terabyte || $format == 'TB') {
			return round($bytes / $terabyte, $precision) . ' TB';
		} else {
			return $bytes . ' B';
		}
	}

	/*
		Function: getSourceSize
			get the file or folder files size (with extension filter - incomplete)

		Returns:
			Array
	*/
	public function getSourceSize($source = null)
	{
		// init vars
		$sourcepath = $this->app->path->path('root:'.$source);
		$size = '';
		
		if (strpos($source, 'http') === 0) // external source
		{
			$ch = curl_init(); 
			curl_setopt($ch, CURLOPT_HEADER, true); 
			curl_setopt($ch, CURLOPT_NOBODY, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
			curl_setopt($ch, CURLOPT_URL, $source); //specify the url
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
			$head = curl_exec($ch);
			
			$size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
		} 
		if (is_file($sourcepath))
		{
			$size = filesize($sourcepath);
		}
		else if(is_dir($sourcepath)) foreach ($this->app->path->files('root:'.$source, false, '/^.*()$/i') as $file){
			$size += filesize($this->app->path->path("root:{$source}/{$file}"));
		}
		
		return ($size ? self::formatFilesize($size) : 0);
	}
}