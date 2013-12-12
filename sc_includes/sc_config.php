<?php

/**
* @author    Eric Sizemore <admin@secondversion.com>
* @package   SV's Simple Contact
* @link      http://www.secondversion.com
* @version   1.0.9
* @copyright (C) 2005 - 2014 Eric Sizemore
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

if (!defined('IN_SC'))
{
	die('You\'re not supposed to be here.');
}

$sc_config = array();

// The email address form submissions will be sent to
$sc_config['to_email'] = 'webmaster@example.com';

// Your site/domain name
$sc_config['site_name'] = 'example.com';

// The subject of the form submissions
$sc_config['subject'] = 'Message from ' . $sc_config['site_name'];

// This must be numeric, see www.php.net/wordwrap
$sc_config['msg_word_wrap'] = 75;

// Used for the is_spam function
// The number of links the message must contain to be flagged as spam
$sc_config['spam_num_links'] = 3;

/**
* Use CAPTCHA?
*
* If has_gd() determines CAPTCHA is available, do
* you want to use it, or not?
* 
* Set to either true or false 
*/
$sc_config['use_captcha'] = true;
