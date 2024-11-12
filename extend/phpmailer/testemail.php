<?php
/**
 * This example shows making an SMTP connection with authentication.
 */

//SMTP needs accurate times, and the PHP time zone MUST be set
//This should be done in your php.ini, but this is how to do it if you don't have access to that
header("content-type:text/html;charset=utf-8");
require 'PHPMailer.php';
require 'Smtp.php';
date_default_timezone_set('PRC');//set time

//Create a new PHPMailer instance
$mail = new PHPMailer;
//Tell PHPMailer to use SMTP
$mail->isSMTP();
//Enable SMTP debugging
// 0 = off (for production use)
// 1 = client messages
// 2 = client and server messages
$mail->SMTPDebug = 2;
$mail->Debugoutput = 'html';
//126邮箱的SMTP服务器: smtp.126.com
$mail->Host = "";
//Set the SMTP port number - likely to be 25, 465 or 587
$mail->Port = 25;
//Whether to use SMTP authentication
$mail->SMTPAuth = true;
//开通服务的邮箱
$mail->Username = "";
//设置的授权码
$mail->Password = "";
//发件邮箱和发件人名称
$mail->setFrom('', '');
//Set an alternative reply-to address
//$mail->addReplyTo('replyto@example.com', 'First Last');
//发送给谁和名字
$mail->addAddress('', ' ');
//标题
$mail->Subject = 'PHPMailer SMTP test';
//Read an HTML message body from an external file, convert referenced images to embedded,
//发送的内容
$mail->msgHTML('');
//Replace the plain text body with one created manually
//$mail->AltBody = 'This is a plain-text message body';
//Attach an image file
//$mail->addAttachment('images/phpmailer_mini.png');

//send the message, check for errors
if (!$mail->send()) {
    echo "Mailer Error: " . $mail->ErrorInfo;
} else {
    echo "Message sent success!";
}
