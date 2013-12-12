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

/**
* Strip any unsafe tags/chars/attributes from input values.
*
* @param  string  Value to be cleaned
* @param  boolean Strip \r\n ?
* @return string
*/
function sc_sanitize($value, $strip_crlf = true)
{
	// Some of what we have in the $search array may not be needed, but let's be safe.
	$search = array(
		'@<script[^>]*?>.*?</script>@si',
		'@<applet[^>]*?>.*?</applet>@si',
		'@<object[^>]*?>.*?</object>@si',
		'@<iframe[^>]*?>.*?</iframe>@si',
		'@<style[^>]*?>.*?</style>@si',
		'@<form[^>]*?>.*?</form>@si',
		'@<[\/\!]*?[^<>]*?>@si',
		'@&(?!(#[0-9]+|[a-z]+);)@si'
	);

	if ($strip_crlf)
	{
		array_push($search, '@([\r\n])[\s]+@');
	}
	$value = preg_replace($search, '', $value);

	// Make sure we get everything..
	$value = strip_tags($value);

	return sc_clean($value);
}

/**
* Clean values pulled from the database, although could be used on anything.
*
* Cleans either a string, or can clean an entire array of values:
*	sc_clean($array);
*
* @param  mixed  Value to be cleaned
* @return mixed
*/
function sc_clean($value)
{
	if (is_array($value))
	{
		foreach ($value AS $key => $val)
		{
			if (is_string($val))
			{
				$value["$key"] = trim(stripslashes($val));
			}
			else if (is_array($val))
			{
				$value["$key"] = sc_clean($value["$key"]);
			}
		}
		return $value;
	}
	return trim(stripslashes($value));
}

/**
* Checks an email for valid format.
*
* @param  string  Email address
* @return boolean
*/
function sc_is_email($value)
{
	if (preg_match('#^[a-z0-9.!\#$%&\'*+-/=?^_`{|}~]+@([0-9.]+|([^\s\'"<>]+\.+[a-z]{2,6}))$#si', $value))
	{
		return true;
	}
	return false;
}

/**
* Tests input values from the contact form for email injection - very basic.
*
* @param  string  Value to check
* @return boolean
*/
function sc_is_email_injection($value)
{
	$value = urldecode($value);

	if (preg_match('#(To:|Bcc:|Cc:|Content-type:|Mime-version:|Content-Transfer-Encoding:|\\r\\n)#i', $value))
	{
		return true;
	}
	return false;
}

/**
* Checks input values from the contact form for a set number of links.
* Can be useful to catch someone trying to spam you.
*
* @param  string  Value to check
* @return boolean
*/
function sc_is_spam($value)
{
	global $sc_config;

	preg_match_all('#(<a href|\[url|http[s]?://)#i', $value, $matches, PREG_PATTERN_ORDER);
	return (bool)(count($matches[0]) > $sc_config['spam_num_links']);
}

/**
* Get the users ip address - for the contact form.
*
* @param  none
* @return string
*/
function sc_get_ip()
{
	$ip = $_SERVER['REMOTE_ADDR'];

	if ($_SERVER['HTTP_X_FORWARDED_FOR'])
	{
		if (preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches))
		{
			foreach ($matches[0] AS $match)
			{
				if (!preg_match('#^(10|172\.16|192\.168)\.#', $match))
				{
					$ip = $match;
					break;
				}
			}
			unset($matches);
		}
	}
	else if ($_SERVER['HTTP_CLIENT_IP'])
	{
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	}
	else if ($_SERVER['HTTP_FROM'])
	{
		$ip = $_SERVER['HTTP_FROM'];
	}
	return $ip;
}

/**
* Determines if we can use CAPTCHA
*
* @param  void
* @return integer
*/
function sc_has_gd()
{
	global $sc_config;

	static $gd_version = 0;

	if ($gd_version == 0)
	{
		ob_start();
		phpinfo(8);
		$modules = ob_get_contents();
		ob_end_clean();

    	$gd_version = (preg_match("#\bgd\s+version\b[^\d\n\r]+?([\d\.]+)#i", $modules, $matches)) ? $matches[1] : 0;
    }

	if ($sc_config['use_captcha'] === false)
	{
		return false;
	}
	return (bool)($gd_version > 0 AND function_exists('imagettftext'));
}
