<?php

/**
* @author    Eric Sizemore <admin@secondversion.com>
* @package   SV's Simple Contact
* @link      http://www.secondversion.com
* @version   1.0.8
* @copyright (C) 2005 - 2012 Eric Sizemore
* @license
*
*	SV's Simple Contact is free software; you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by
*	the Free Software Foundation; either version 2 of the License, or
*	(at your option) any later version.
*
*	This program is distributed in the hope that it will be useful,
*	but WITHOUT ANY WARRANTY; without even the implied warranty of
*	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
*	GNU General Public License for more details.
*
*	You should have received a copy of the GNU Lesser General Public License 
*	along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

if (!defined('IN_SC'))
{
	die('You\'re not supposed to be here.');
}

// ################################################################
// Error reporting
error_reporting(E_ALL & ~E_NOTICE & ~8192);

// Better to be safe than sorry... :)
if (isset($_REQUEST['GLOBALS']) OR isset($_FILES['GLOBALS']))
{
	die('Request tainting attempted.');
}

// Reverse the effects of register_globals if neccessary.
if (ini_get('register_globals') OR strtolower(ini_get('register_globals')) == 'on')
{
	$supers = array(
		'_GET',
		'_POST',
		'_COOKIE',
		'_REQUEST',
		'_SERVER',
		'_SESSION',
		'_ENV',
		'_FILES'
	);

	if (!isset($_SESSION) OR !is_array($_SESSION))
	{
		$_SESSION = array();
	}

	foreach ($supers AS $arrayname)
	{
		foreach (array_keys($GLOBALS["$arrayname"]) AS $varname)
		{
			if (!in_array($varname, $supers))
			{
				$GLOBALS["$varname"] = NULL;
				unset($GLOBALS["$varname"]);
			}
		}
	}
}

// ################################################################
// Check PHP version
if (!version_compare(PHP_VERSION, '5.2', '>='))
{
	die('PHP 5.2 or greater is required, you\'re currently running PHP ' . PHP_VERSION);
}

// Include needed files
require_once('sc_config.php');
require_once('sc_functions.php');
