<?php

/**
* @author    Eric Sizemore <admin@secondversion.com>
* @package   SV's Simple Contact
* @link      http://www.secondversion.com/downloads/
* @version   2.0.0
* @copyright (C) 2005 - 2016 Eric Sizemore
* @license
*
*	SV's Simple Contact is free software: you can redistribute it and/or modify
*	it under the terms of the GNU General Public License as published by the 
*	Free Software Foundation, either version 3 of the License, or (at your option) 
*	any later version.
*
*	This program is distributed in the hope that it will be useful, but WITHOUT ANY 
*	WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
*	PARTICULAR PURPOSE.  See the GNU General Public License for more details.
*
*	You should have received a copy of the GNU General Public License along with 
*	this program.  If not, see <http://www.gnu.org/licenses/>.
*/
namespace Esi\SimpleContact;

if (!defined('IN_SC')) {
	die('You are not supposed to be here.');
}

// ################################################################
// Error reporting
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

// Check PHP version
if (!version_compare(PHP_VERSION, '5.4', '>='))
{
	die('PHP 5.4 or greater is required, you are currently running PHP ' . PHP_VERSION);
}

// Better to be safe than sorry... :)
if (isset($_REQUEST['GLOBALS']) OR isset($_FILES['GLOBALS'])) {
	die('Request tainting attempted.');
}

// ################################################################
// Include needed files
require_once 'config.php';
require_once 'functions.php';
