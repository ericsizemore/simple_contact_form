<?php

/**
 * @author    Eric Sizemore <admin@secondversion.com>
 * @package   SV's Simple Contact
 * @link      http://www.secondversion.com/downloads/
 * @version   2.0.1
 * @copyright (C) 2005 - 2017 Eric Sizemore
 * @license
 *
 *    SV's Simple Contact is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by the 
 *    Free Software Foundation, either version 3 of the License, or (at your option) 
 *    any later version.
 *
 *    This program is distributed in the hope that it will be useful, but WITHOUT ANY 
 *    WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 *    PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License along with 
 *    this program.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace Esi\SimpleContact;

if (!defined('IN_SC')) {
    die('You are not supposed to be here.');
}

/**
 * Class to send email.
 */
class Mailer
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
    private $extraHeaders;

    /**
     * Constructor.
     *
     * Sets host and initiates extra_headers.
     */
    private function __construct()
    {
        $this->host = preg_replace('#^www\.#', '', $_SERVER['SERVER_NAME']);
        $this->extraHeaders = '';
    }

    /**
     * Creates an instance of the class.
     *
     * @return  object  \Esi\SimpleContact\Mailer
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     *
     */
    private function __clone()
    {
        //
    }

    /**
     * Sets email parameters (To, From, and Subject)
     *
     * @param  string  $to       Recipient email
     * @param  string  $from     Who the email is from
     * @param  string  $subject  Subject of the email
     */
    public function setParams($to, $from, $subject)
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
     */
    public function extraHeaders($headers = '')
    {
        $this->extraHeaders .= str_replace("\r\n", "\n", $headers);
    }

    /**
     * Allows us to use templates for the email body
     *
     * @param  array   $tplVars  An array of var => replacement
     * @param  string  $tplFile  Template filename
     */
    public function useTemplate(array $tplVars, $tplFile)
    {
        if (count($tplVars) == 0) {
            throw new \InvalidArgumentException('$tplVars is empty.');
        }

        if (!is_file($tplFile)) {
            throw new \InvalidArgumentException(sprintf("'%s' is not a file or does not exist.", $tplFile));
        }

        if (!($fp = @fopen($tplFile, 'r'))) {
            throw new \InvalidArgumentException(sprintf("Could not open template file: '%s'", $tplFile));
        }

        $this->body = fread($fp, filesize($tplFile));

        foreach ($tplVars AS $var => $content) {
            $this->body = str_replace('{' . $var . '}', $content, $this->body);
        }
        fclose($fp);
    }

    /**
     * Wrapper of the mail() function, which also sets standard email headers,
     * plus any extra ones we may add in script via the extra_headers() function.
     *
     * @return  bool  true if the email is sent, false if not.
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

        if ($this->extraHeaders != '') {
            $headers .= trim($this->extraHeaders) . "\n";
        }

        if (@mail($this->to, $this->subject, $this->body, $headers)) {
            return true;
        }
        return false;
    }
}
