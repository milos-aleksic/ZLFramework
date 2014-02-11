<?php
/**
* @package		ZL Framework
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// register FilesystemHelper class
App::getInstance('zoo')->loader->register('FilesystemHelper', 'helpers:filesystem.php');

/*
	Class: zlfwHelperFileSystem
		The ZLFW filesystem helper class
*/
class zlfwHelperFileSystem extends FilesystemHelper {

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
	public function makeSafe($subject, $mode = 'utf-8', $allowspaces = false) {
		$search = array();

		// replace spaces with underscore
		if (!$allowspaces) {
			$subject = preg_replace('#[\s ]#', '_', $subject);
		}

		switch ($mode) {
			default:
			case 'utf-8':    
				$search[] = '#[^a-zA-Z0-9_\.\-~\p{L}\p{N}\s ]#u';
				$mode = 'utf-8';
				break;
			case 'ascii':
				$subject = $this->utf8_latin_to_ascii($subject);  
				$subject = $this->utf8_cyrillic_to_ascii($subject);  
				$search[] = '#[^a-zA-Z0-9_\.\-~\s ]#';
				break;
		}
		
		// remove multiple . characters
		$search[] = '#(\.){2,}#';

		// strip leading period
		$search[] = '#^\.#';
		
		// strip trailing period
		$search[] = '#\.$#';

		// strip whitespace
		$search[] = '#^\s*|\s*$#';

		// only for utf-8 to avoid PCRE errors - PCRE must be at least version 5
		if ($mode == 'utf-8') {
			try {                
				$result = preg_replace($search, '', $subject);                
			} catch (Exception $e) {
				// try ascii
				return $this->makeSafe($subject, 'ascii');
			}
			
			// try ascii
			if (is_null($result) || $result === false) {                
				return $this->makeSafe($subject, 'ascii');
			}

			return $result;
		}

		return preg_replace($search, '', $subject);
	}
	
	private function utf8_latin_to_ascii($subject) {

		static $CHARS = NULL;

		if (is_null($CHARS)) {
			$CHARS = array(
				'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE',
				'Ç' => 'C', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
				'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O',
				'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'ß' => 's',
				'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'ae',
				'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
				'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
				'ý' => 'y', 'ÿ' => 'y', 'Ā' => 'A', 'ā' => 'a', 'Ă' => 'A', 'ă' => 'a', 'Ą' => 'A', 'ą' => 'a',
				'Ć' => 'C', 'ć' => 'c', 'Ĉ' => 'C', 'ĉ' => 'c', 'Ċ' => 'C', 'ċ' => 'c', 'Č' => 'C', 'č' => 'c', 'Ď' => 'D', 'ď' => 'd', 'Đ' => 'D', 'đ' => 'd',
				'Ē' => 'E', 'ē' => 'e', 'Ĕ' => 'E', 'ĕ' => 'e', 'Ė' => 'E', 'ė' => 'e', 'Ę' => 'E', 'ę' => 'e', 'Ě' => 'E', 'ě' => 'e',
				'Ĝ' => 'G', 'ĝ' => 'g', 'Ğ' => 'G', 'ğ' => 'g', 'Ġ' => 'G', 'ġ' => 'g', 'Ģ' => 'G', 'ģ' => 'g', 'Ĥ' => 'H', 'ĥ' => 'h', 'Ħ' => 'H', 'ħ' => 'h',
				'Ĩ' => 'I', 'ĩ' => 'i', 'Ī' => 'I', 'ī' => 'i', 'Ĭ' => 'I', 'ĭ' => 'i', 'Į' => 'I', 'į' => 'i', 'İ' => 'I', 'ı' => 'i',
				'Ĳ' => 'IJ', 'ĳ' => 'ij', 'Ĵ' => 'J', 'ĵ' => 'j', 'Ķ' => 'K', 'ķ' => 'k', 'Ĺ' => 'L', 'ĺ' => 'l', 'Ļ' => 'L', 'ļ' => 'l', 'Ľ' => 'L', 'ľ' => 'l', 'Ŀ' => 'L', 'ŀ' => 'l', 'Ł' => 'l', 'ł' => 'l',
				'Ń' => 'N', 'ń' => 'n', 'Ņ' => 'N', 'ņ' => 'n', 'Ň' => 'N', 'ň' => 'n', 'ŉ' => 'n', 'Ō' => 'O', 'ō' => 'o', 'Ŏ' => 'O', 'ŏ' => 'o', 'Ő' => 'O', 'ő' => 'o', 'Œ' => 'OE', 'œ' => 'oe',
				'Ŕ' => 'R', 'ŕ' => 'r', 'Ŗ' => 'R', 'ŗ' => 'r', 'Ř' => 'R', 'ř' => 'r', 'Ś' => 'S', 'ś' => 's', 'Ŝ' => 'S', 'ŝ' => 's', 'Ş' => 'S', 'ş' => 's', 'Š' => 'S', 'š' => 's',
				'Ţ' => 'T', 'ţ' => 't', 'Ť' => 'T', 'ť' => 't', 'Ŧ' => 'T', 'ŧ' => 't', 'Ũ' => 'U', 'ũ' => 'u', 'Ū' => 'U', 'ū' => 'u', 'Ŭ' => 'U', 'ŭ' => 'u', 'Ů' => 'U', 'ů' => 'u', 'Ű' => 'U', 'ű' => 'u', 'Ų' => 'U', 'ų' => 'u',
				'Ŵ' => 'W', 'ŵ' => 'w', 'Ŷ' => 'Y', 'ŷ' => 'y', 'Ÿ' => 'Y', 'Ź' => 'Z', 'ź' => 'z', 'Ż' => 'Z', 'ż' => 'z', 'Ž' => 'Z', 'ž' => 'z', 'ſ' => 's', 'ƒ' => 'f', 'Ơ' => 'O', 'ơ' => 'o', 'Ư' => 'U', 'ư' => 'u',
				'Ǎ' => 'A', 'ǎ' => 'a', 'Ǐ' => 'I', 'ǐ' => 'i', 'Ǒ' => 'O', 'ǒ' => 'o', 'Ǔ' => 'U', 'ǔ' => 'u', 'Ǖ' => 'U', 'ǖ' => 'u', 'Ǘ' => 'U', 'ǘ' => 'u', 'Ǚ' => 'U', 'ǚ' => 'u', 'Ǜ' => 'U', 'ǜ' => 'u',
				'Ǻ' => 'A', 'ǻ' => 'a', 'Ǽ' => 'AE', 'ǽ' => 'ae', 'Ǿ' => 'O', 'ǿ' => 'o'
			);
		}

		return str_replace(array_keys($CHARS), array_values($CHARS), $subject);
	}

	private function utf8_cyrillic_to_ascii($subject) {
		static $CHARS = NULL;

		if (is_null($CHARS)) {
			$CHARS = array(
				'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ь' => '_', 'ы' => 'y', 'ъ' => '_', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
				'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I', 'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C', 'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch', 'Ь' => '_', 'Ы' => 'Y', 'Ъ' => '_', 'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya'
			);
		}

		return str_replace(array_keys($CHARS), array_values($CHARS), $subject);
	}
	
	public function cleanPath($path, $ds = DIRECTORY_SEPARATOR, $prefix = '') {
		$path = trim(urldecode($path));
		
		// check for UNC path on IIS and set prefix
		if ($ds == '\\' && $path[0] == '\\' && $path[1] == '\\') {
			$prefix = "\\";
		}
		// clean path, removing double slashes, replacing back/forward slashes with DIRECTORY_SEPARATOR
		$path = preg_replace('#[/\\\\]+#', $ds, $path);
		
		// return path with prefix if any
		return $prefix . $path;
	}
	
	/**
	 * Concat two paths together. Basically $a + $b
	 * @param string $a path one
	 * @param string $b path two
	 * @param string $ds optional directory seperator
	 * @return string $a DIRECTORY_SEPARATOR $b
	 */
	public function makePath($a, $b, $ds = DIRECTORY_SEPARATOR) {
		return $this->cleanPath($a . $ds . $b, $ds);
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

		if (($bytes >= 0) && ($bytes < $kilobyte) && !$format || $format == 'B') {
			return $bytes . ' B';

		} elseif (($bytes >= $kilobyte) && ($bytes < $megabyte) && !$format || $format == 'KB') {
			return round($bytes / $kilobyte, $precision) . ' KB';

		} elseif (($bytes >= $megabyte) && ($bytes < $gigabyte) && !$format || $format == 'MB') {
			return round($bytes / $megabyte, $precision) . ' MB';

		} elseif (($bytes >= $gigabyte) && ($bytes < $terabyte) && !$format || $format == 'GB') {
			return round($bytes / $gigabyte, $precision) . ' GB';

		} elseif ($bytes >= $terabyte && !$format || $format == 'TB') {
			return round($bytes / $terabyte, $precision) . ' TB';
		} else {
			return $bytes . ' B';
		}
	}

	/*
		Function: getSourceSize
			get the file or folder files size (with extension filter - incomplete)

		Parameters:
			$source - the source path string
			$format - Boolean, if true will return the result formated for better reading

		Returns:
			String
	*/
	public function getSourceSize($source = null, $format = true)
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

		// value check
		if (!$size) return 0;
		
		// return size
		return $format ? $this->formatFilesize($size) : $size;
	}

	/**
	 * Get the mapping from extension to mime type
	 *
	 * @return array The associative array of extension => mime type mapping
	 *
	 * @since 1.0.0
	 */
	public static function getMimeMapping()
	{
		// get current mimes
		$mimes = parent::getMimeMapping();

		// add missing mimes
		$mimes['mp3'][] = 'audio/mp3'; // necesary for Chrome unsolved bug

		return $mimes;
	}
}