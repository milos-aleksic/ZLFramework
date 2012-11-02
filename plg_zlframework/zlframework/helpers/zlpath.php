<?php
/**
* @package		ZL Framework
* @author    	JOOlanders, SL http://www.zoolanders.com
* @copyright 	Copyright (C) JOOlanders, SL
* @license   	http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
*/

// register FilesystemHelper class
App::getInstance('zoo')->loader->register('PathHelper', 'helpers:path.php');

/*
	Class: ZlPathHelper
		The ZL filesystem helper class
*/
class ZlpathHelper extends PathHelper
{
	/*
		Function: resources
			returns a list of resources to the given resource

		Parameters:
			$resource

		Returns:
			array
	*/
	public function resources($resource)
	{
		$paths = (array) $this->app->path->paths($resource);
		$parts = explode(':', $resource, 2);
		$file  = ltrim($parts[1], "\\/");
		
		$return = array();
		foreach ($paths as $path) {
			if (($fullpath = realpath("$path/$file")) && stripos($fullpath, JPATH_ROOT, 0) === 0) {
				$return[] = $fullpath;
			}
		}

		return $return;
	}

	public function path($resource)
	{
		if ($path = $this->pathZOO($resource))
		{
			return $path;
		}

		if ($path = $this->pathWK($resource))
		{
			return $path;
		}

		return null;
	}

	public function pathWK($resource)
	{
		// load widgetkit
		if (JFile::exists(JPATH_ADMINISTRATOR.'/components/com_widgetkit/widgetkit.php')) {
			require_once(JPATH_ADMINISTRATOR.'/components/com_widgetkit/widgetkit.php');
		}

		$widgetkit = Widgetkit::getInstance();
		return $widgetkit['path']->path($resource);
	}

	public function pathZOO($resource)
	{
		return $this->app->path->path($resource);
	}

	/*
     * Return the full directory path
	 *
	 * Original Credits:
	 * @package   	JCE
	 * @copyright 	Copyright ¬© 2009-2011 Ryan Demmer. All rights reserved.
	 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
	 * 
	 * Extended and adapted:
	 * by JOOlanders (zoolanders.com)
	 * Copyright 2011, JOOlanders SL
     */
	public function getDirectory($root, $allowroot = false)
	{
		$user 				= JFactory::getUser();
		$joomla_file_path 	= JComponentHelper::getParams('com_media')->get('file_path');

		// Restricted Joomla! folders
		$restricted = explode(',', 'administrator,cache,components,includes,language,libraries,logs,media,modules,plugins,templates,xmlrpc');

		// Remove whitespace
		$root = trim($root);
		// Convert slashes / Strip double slashes
		$root = preg_replace('/[\\\\]+/', '/', $root);
		// Remove first leading slash
		$root = ltrim($root, '/');
		
		// Split in parts to better manage
		$parts = explode('/', $root);
		// Force default directory if path starts with a variable, a . or is empty
		if (preg_match('/[\.\[]/', $parts[0]) || (empty($root) && !$allowroot)) {
			$parts[0] = $joomla_file_path;
		}
		// Force default if directory is a joomla directory conserving the variables
		if (!$allowroot && in_array(strtolower($parts[0]), $restricted)) {
			$parts[0] = $joomla_file_path;
		}
		// join back
		$root = implode('/', $parts);
		
		jimport('joomla.user.helper');
		// Joomla! 1.6+
		if (method_exists('JUserHelper', 'getUserGroups')) {
			$groups 	= JUserHelper::getUserGroups($user->id);
			$groups		= array_keys($groups);
			$usertype 	= array_shift($groups);												
		} else {
			$usertype 	= $user->usertype;
		}

		// Replace any path variables
		$pattern = array(
			'/\[userid\]/', '/\[username\]/', '/\[usertype\]/',
			'/\[day\]/', '/\[month\]/', '/\[year\]/'
		);
		$replace = array(
			$user->id, $user->username, $usertype,
			date('d'), date('m'), date('Y')
		);
		
		$root = preg_replace($pattern, $replace, $root);

		// split into path parts to preserve /
		$parts = explode('/', $root);
		// clean path parts
		$parts = $this->app->zlfilesystem->makeSafe($parts, 'utf-8');
		// join path parts
		$root = implode('/', $parts);
		
		// Create the folder
		$full = $this->app->zlfilesystem->makePath(JPATH_SITE, $root);
		if (!JFolder::exists($full)){
			$this->app->zlfilesystem->folderCreate($full);
		}
		
		return $root;
	}

}