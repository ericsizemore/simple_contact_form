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

session_start();

define('IN_SC', true);
require_once('./sc_includes/sc_functions.php');
require_once('./sc_includes/sc_captcha/sc_captcha.class.php');

if (!sc_has_gd())
{
	exit;
}

// ################################################################
/**
* Determining the fonts this way will allow users to add their own fonts,
* without having to edit this file..
*/
$fonts = array();

$fontdir = dirname(__FILE__) . '/sc_includes/sc_captcha/sc_fonts/';

$tmpfonts = new GlobIterator("$fontdir*.ttf", FilesystemIterator::KEY_AS_FILENAME);

foreach ($tmpfonts AS $tmpfont)
{
	$fonts[] = str_replace($fontdir, '', $tmpfonts->key());
}
unset($tmpfonts);

// ################################################################
$captcha = new captcha($fontdir, $fonts);
$captcha->make_captcha();

$_SESSION['sc_captcha'] = md5($captcha->code);

session_write_close();

?>