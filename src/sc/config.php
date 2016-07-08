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

/** */
class Config {
	// The email address form submissions will be sent to
	CONST WEBMASTER = 'webmaster@example.com';

	// Your site/domain name
	CONST SITE_NAME = 'example.com';

	// The subject of the form submissions
	CONST SUBJECT = 'Message from ' . self::SITE_NAME;

	// This must be numeric, see www.php.net/wordwrap
	CONST WORD_WRAP = 75;

	// Used for the is_spam function
	// The number of links the message must contain to be flagged as spam
	CONST SPAM_NUM_LINKS = 3;

	/**
	* Use CAPTCHA?
	*
	* If has_gd() determines CAPTCHA is available, do
	* you want to use it, or not?
	* 
	* Set to either true or false 
	*/
	CONST USE_CAPTCHA = true;
}
