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
* Class to send email.
*/
class emailer
{
	/**
	* Class instance.
	*
	* @var object
	*/
	private static $instance;

	/**
	* The email recipient.
	*
	* @var string
	*/
	private $to;

	/**
	* The email subject.
	*
	* @var string
	*/
	private $subject;

	/**
	* The email body.
	*
	* @var string
	*/
	private $body;

	/**
	* Who the email is from.
	*
	* @var string
	*/
	private $from;

	/**
	* Host/domain.
	*
	* @var string
	*/
	private $host;

	/**
	* Extra email headers.
	*
	* @var string
	*/
	private $extra_headers;

	/**
	* Constructor. Sets host and initiates extra_headers.
	*
	* @param  void
	* @return void
	*/
	private function __construct()
	{
		$this->host = preg_replace('#^www\.#', '', $_SERVER['SERVER_NAME']);
		$this->extra_headers = '';
	}

	/**
	* Creates an instance of the class.
	*
	* @param  void
	* @return object
	*/
	public static function getInstance()
	{
		if (!self::$instance)
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	*/
	private function __clone() {}

	/**
	* Sets email parameters (To, From, and Subject)
	*
	* @param  string  $to       Recipient email
	* @param  string  $from     Who the email is from
	* @param  string  $subject  Subject of the email
	* @return void
	*/
	public function set_params($to, $from, $subject)
	{
		$this->to = trim($to);
		$this->from = trim($from);
		$this->from = (is_null($from)) ? "noreply@{$this->host}" : $this->from;
		$this->subject = trim($subject);
	}

	/**
	* Allows us to set extra headers aside from the standard ones in the send() function.
	*
	* @param  string  $headers  Extra headers seperated by \n
	* @return void
	*/
	public function extra_headers($headers = '')
	{
		$this->extra_headers .= str_replace("\r\n", "\n", $headers);
	}

	/**
	* Allows us to use templates for the email body
	*
	* @param  array   $tpl_vars  An array of var => replacement
	* @param  string  $tpl_file  Template filename
	* @return void
	*/
	public function use_template($tpl_vars, $tpl_file)
	{
		if (!is_array($tpl_vars) OR count($tpl_vars) == 0)
		{
			trigger_error('emailer::use_template() - <code>$tpl_vars</code> must be an array, or is empty.', E_USER_ERROR);
		}

		if (!is_file($tpl_file))
		{
			trigger_error("emailer::use_template() - '<code>$tpl_file</code>' is not a file or does not exist.", E_USER_ERROR);
		}

		if (!($fp = @fopen($tpl_file, 'r')))
		{
			trigger_error("emailer::use_template() - Could not open template file: '<code>$tpl_file</code>'", E_USER_ERROR);
		}

		$this->body = fread($fp, filesize($tpl_file));

		foreach ($tpl_vars AS $var => $content)
		{
			$this->body = str_replace('{' . $var . '}', $content, $this->body);
		}
		fclose($fp);
	}

	/**
	* Wrapper of the mail() function, which also sets standard email headers,
	* plus any extra ones we may add in script via the extra_headers() function.
	*
	* @param  void
	* @return boolean  true if the email is sent, false if not.
	*/
	public function send()
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
