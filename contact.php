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

define('IN_SC', true);
require_once './src/sc/global.php';

// Session so our captcha will work
session_start();

// ################################## HTML ##################################
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>SV's Simple Contact Form</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="Content-Language" content="en" />
<meta name="simple_contact_version" content="2.0.0" />
<script type="text/javascript" language="JavaScript">
<!--
function sc_validate_form() {
	var flag       = true;
	var usecaptcha = '<?= (has_gd()) ? 1 : ''; ?>';
	var error_msg  = 'The following errors occurred:\n';
	var sname      = document.getElementById('sender_name');
	var semail     = document.getElementById('sender_email');
	var smessage   = document.getElementById('sender_message');
	var scode      = document.getElementById('captcha');

	// Check Name
	if (sname.value == '' || sname.value == null || sname.length < 2) {
		flag = false;
		error_msg += '\n Please enter your name';
	}

	// Check email
	if (semail.value == '' || semail.value == null) {
		flag = false;
		error_msg += '\n Please enter a valid email address';
	}

	// Check Message
	if (smessage.value == '' || smessage.value == null) {
		flag = false;
		error_msg += '\n Please enter a message';
	}

	// Check CAPTCHA
	if (usecaptcha) {
		if (scode.value == '' || scode.value == null || scode.length < 5) {
			flag = false;
			error_msg += '\n Please enter the CAPTCHA code';
		}
	}

	if (!flag) {
		window.alert(error_msg + '\n\n');
	}
	return flag;
}

// Refresh CAPTCHA image
function sc_captcha_refresh() {
	var captcha = document.getElementById('sc-captcha-img');
	captcha.src = './src/sc/loading.gif';
	setTimeout('sc_new_captcha()', 1000);
}

function sc_new_captcha() {
	var captcha = document.getElementById('sc-captcha-img');
	captcha.src = 'captcha.php?rand=' + Math.ceil(Math.random() * 10000);
}
// -->
</script>
</head>

<body>

<div>
<?php

$result = '';

// ################################################################
// Process the form and send the email..
if (!empty($_POST['submit'])) {
	$name    = sanitize($_POST['sender_name']);
	$email   = sanitize($_POST['sender_email']);
	$message = str_replace("\r\n", "\n", $_POST['sender_message']);
	$message = wordwrap(sanitize($message, false), Config::WORD_WRAP);

	if (has_gd()) {
		$captcha = sanitize($_POST['captcha']);
	}

	/**
	* Let's create a session value for name, email, and message.
	* This way, if there's an error, they don't lose what they've entered.
	*/
	$_SESSION['sc_form'] = [
		'name'    => $name,
		'email'   => $email,
		'message' => $message
	];

	if (empty($name) OR is_email_injection($name)) {
		$result .= 'Your name is required, please go back and enter your name.';
	}
	else if (empty($email)) {
		$result .= 'Your email is required, please go back and enter your email.';
	}
	else if (empty($message)) {
		$result .= 'A message is required, please go back and enter a message.';
	}
	else if (!is_email($email) OR is_email_injection($email)) {
		$result .= 'Email is invalid. Please try again.';
	}
	else if (is_spam($message)) {
		$result .= 'Sorry, but your message seemed a bit like spam.';
	}
	else if (has_gd() AND md5($captcha) != $_SESSION['sc_captcha']) {
		$result .= 'Sorry, but the code you entered is incorrect. Please try again.';
	}
	else {
		require_once './src/sc/email.class.php';
		$emailer = Mailer::getInstance();
		$emailer->set_params(Config::WEBMASTER, $email, Config::SUBJECT);
		$emailer->use_template([
			'name'    => $name,
			'email'   => $email,
			'ip'      => get_ip(),
			'message' => $message
		], 'src/sc/email.tpl');

		if ($emailer->send()) {
			$result .=  "Thank you, $name, your enquiry was sent.";

			// Reset the session array
			$_SESSION['sc_form'] = [
				'name'    => '',
				'email'   => '',
				'message' => ''
			];
		}
		else {
			$result .= 'Seems to have been a problem sending the email. Please try again.';
		}
	}
?>
	<p><?php echo $result; ?></p>
<?php
}
else
{
	// Reset the session array
	$_SESSION['sc_form'] = [
		'name'    => '',
		'email'   => '',
		'message' => ''
	];
?>
	<h1>Contact</h1>
	<form action="<?= htmlspecialchars($_SERVER['SCRIPT_NAME']); ?>" method="post" style="display: inline;" onsubmit="return sc_validate_form();">
	<table border="0" cellpadding="2" cellspacing="1">
	<tbody>
	<tr>
		<td><label for="sender_name">Name:*</label></td>
		<td><input type="text" name="sender_name" id="sender_name" maxlength="100" value="<?php echo $_SESSION['sc_form']['name']; ?>" /></td>
	</tr>
	<tr>
		<td><label for="sender_email">E-mail:*</label></td>
		<td><input type="text" name="sender_email" id="sender_email" maxlength="100" value="<?php echo $_SESSION['sc_form']['email']; ?>" /></td>
	</tr>
	<tr>
		<td valign="top"><label for="sender_message">Message:*</label></td>
		<td><textarea name="sender_message" id="sender_message" rows="4" cols="35"><?php echo $_SESSION['sc_form']['message']; ?></textarea></td>
	</tr>
<?php if (has_gd()) { ?>
	<tr>
		<td>&nbsp;</td>
		<td>
			<img src="./captcha.php" alt="CAPTCHA Image" title="CAPTCHA Image" id="sc-captcha-img" /><br />
			<a href="javascript:sc_captcha_refresh();">Refresh</a>
		</td>
	</tr>
	<tr>
		<td><label for="captcha">Code:*</label> (above)</td>
		<td><input type="text" name="captcha" id="captcha" maxlength="5" /></td>
	</tr>
<?php } ?>
	<tr>
		<td colspan="2" align="center"><input type="submit" name="submit" value="Submit" /></td>
	</tr>
	</tbody>
	</table>
	</form>
	<br />
	<p>Powered by: <a href="http://www.secondversion.com/" title="SV's Simple Contact Form">SV's Simple Contact Form</a></p>
<?php } ?>
</div>

</body>
</html>