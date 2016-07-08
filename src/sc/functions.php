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

/**
* Strip any unsafe tags/chars/attributes from input values.
*
* @param  string  Value to be cleaned
* @param  boolean Strip \r\n ?
* @return string
*/
function sanitize($value, $strip_crlf = true) {
	$flags = FILTER_FLAG_STRIP_HIGH;

	if ($strip_crlf === true) {
		$flags |= FILTER_FLAG_STRIP_LOW;
	}

	$value = preg_replace('@&(?!(#[0-9]+|[a-z]+);)@si', '', $value);
	$value = filter_var($value, FILTER_SANITIZE_STRING, $flags);
	$value = str_replace(chr(0), '', $value);

	return clean($value);
}

/**
* Clean values pulled from the database, although could be used on anything.
*
* Cleans either a string, or can clean an entire array of values:
*	clean($array);
*
* @param  mixed  Value to be cleaned
* @return mixed
*/
function clean($value) {
	if (is_array($value)) {
		foreach ($value AS $key => $val) {
			if (is_string($val)) {
				$value["$key"] = trim(stripslashes($val));
			}
			else if (is_array($val)) {
				$value["$key"] = clean($value["$key"]);
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
function is_email($value) {
	return (bool)filter_var($value, FILTER_VALIDATE_EMAIL);
}

/**
* Tests input values from the contact form for email injection - very basic.
*
* @param  string  Value to check
* @return boolean
*/
function is_email_injection($value) {
	$value = urldecode($value);
	return (bool)(preg_match('#(To:|Bcc:|Cc:|Content-type:|Mime-version:|Content-Transfer-Encoding:|\\r\\n)#i', $value));
}

/**
* Checks input values from the contact form for a set number of links.
* Can be useful to catch someone trying to spam you.
*
* @param  string  Value to check
* @return boolean
*/
function is_spam($value){
	preg_match_all('#(<a href|\[url|http[s]?://)#i', $value, $matches, PREG_PATTERN_ORDER);
	return (bool)(count($matches[0]) > Config::SPAM_NUM_LINKS);
}

/**
* Return the visitor's IP address.
*
* @param	$trust_proxy_headers	Whether or not to trust the proxy headers HTTP_CLIENT_IP
*									and HTTP_X_FORWARDED_FOR.
* @return	string
*/
function get_ip($trust_proxy_headers = false) {
	$ip = $_SERVER['REMOTE_ADDR'];

	if ($trust_proxy_headers === false) {
		return $ip;
	}

	$ips = [];

	if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
	}
	else if (isset($_SERVER['HTTP_X_REAL_IP'])) {
		$ips = explode(',', $_SERVER['HTTP_X_REAL_IP']);
	}

	if (!empty($ips)) {
		foreach ($ips AS $val) {
			$val = trim($val);

			if (inet_ntop(inet_pton($val)) == $val AND !preg_match("#^(10\.|172\.(1[6-9]|2[0-9]|3[0-1])\.|192\.168\.|fe80:|fe[c-f][0-f]:|f[c-d][0-f]{2}:)#i", $val)) {
				$ip = $val;
				break;
			}
		}
	}

	if (!$ip AND isset($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	}
	return $ip;
}

/**
* Determines if we can use CAPTCHA
*
* @param  void
* @return integer
*/
function has_gd() {
	static $gd_version;

	if (Config::USE_CAPTCHA === false) {
		return false;
	}

	if (is_null($gd_version)) {
		if (!function_exists('gd_info') OR !function_exists('imagettftext')) {
			$gd_version = 0;
		}
		else {
			preg_match('#\d+#', gd_info()['GD Version'], $matches);
			$gd_version = $matches[0];
		}
    }
	return (bool)($gd_version > 0);
}
