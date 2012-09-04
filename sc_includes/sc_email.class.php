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

// Class to handle our Email
class emailer
{
	/**
	* The email recipient
	*
	* @var string
	*/
	var $to;

	/**
	* The email subject
	*
	* @var string
	*/
	var $subject;

	/**
	* The email body
	*
	* @var string
	*/
	var $body;

	/**
	* Who the email is from
	*
	* @var string
	*/
	var $from;

	/**
	* Host/domain
	*
	* @var string
	*/
	var $host;

	/**
	* Extra email headers
	*
	* @var string
	*/
	var $extra_headers;

	/**
	* Constructor. Sets who the email is to, who it's from, and subject
	*
	* @param  string  Recipient email
	* @param  string  Who the email is from
	* @param  string  Subject of the email
	* @return none
	*/
	function emailer($to, $from, $subject)
	{
		$this->host = preg_replace('#^www\.#', '', $_SERVER['SERVER_NAME']);
		$this->to = trim($to);
		$this->from = trim($from);
		$this->from = (is_null($from)) ? "noreply@{$this->host}" : $this->from;
		$this->subject = trim($subject);
		$this->extra_headers = '';
	}

	/**
	* Allows us to set extra headers aside from the standard ones in the send() function.
	*
	* @param  string  Extra headers seperated by \n
	* @return none
	*/
	function extra_headers($headers = '')
	{
		$this->extra_headers .= str_replace("\r\n", "\n", $headers);
	}

	/**
	* Allows us to use templates for the email body
	*
	* @param  array   An array of var => replacement
	* @param  string  Template filename
	* @return none
	*/
	function use_template($tpl_vars, $tpl_file)
	{
		if (!is_array($tpl_vars) OR sizeof($tpl_vars) == 0)
		{
			die('emailer->use_template - <code>$tpl_vars</code> must be an array, or is empty.');
		}

		if (!is_file($tpl_file))
		{
			die("emailer->use_template - '<code>$tpl_file</code>' is not a file or does not exist.");
		}

		if (!($fp = @fopen($tpl_file, 'r')))
		{
			die("emailer->use_template - Could not open template file: '<code>$tpl_file</code>'");
		}

		$this->body = @fread($fp, filesize($tpl_file));

		foreach ($tpl_vars AS $var => $content)
		{
			$this->body = str_replace('{' . $var . '}', $content, $this->body);
		}
		@fclose($fp);
	}

	/**
	* Wrapper of the mail() function, which also sets standard email headers,
	* plus any extra ones we may add in script via the extra_headers() function.
	*
	* @param  none
	* @return boolean
	*/
	function send()
	{
		$headers = "From: {$this->from}\n";
		$headers .= "Reply-To: {$this->from}\n";
		$headers .= "Return-Path: {$this->from}\n";
		$headers .= "Sender: {$this->from}\n";
		$headers .= "Message-ID: <" . md5(uniqid(time())) . "@{$this->host}>\n";
		$headers .= "MIME-Version: 1.0\n";
		$headers .= "Content-type: text/plain; charset=UTF-8\n";
		$headers .= "Content-transfer-encoding: 8bit\n";
		$headers .= "Date: " . date('r', time()) . "\n";
		$headers .= "X-Priority: 3\n";
		$headers .= "X-MSMail-Priority: Normal\n";
		$headers .= "X-Mailer: SV's Simple Contact Form via PHP/" . PHP_VERSION . "\n";
		$headers .= "X-MimeOLE: Produced By SV's Simple Contact Form\n";

		if ($this->extra_headers != '')
		{
			$headers .= trim($this->extra_headers) . "\n";
		}

		if (@mail($this->to, $this->subject, $this->body, $headers))
		{
			return true;
		}
		return false;
	}
}
