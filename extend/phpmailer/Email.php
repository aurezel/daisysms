<?php
/**
 * 发送邮件类库
 */
namespace phpmailer;
use think\Exception;

class Email{

    /**
     * @param $to 发送给谁
     * @param $titl 标题
     * @param $content  内容
     * return boolean
     */
    public static function send($to,$title,$content){

        date_default_timezone_set('PRC');//set time
        //如果发送的给的邮箱是空的 返回错误
        if(empty($to)){
            return false;
        }
        try{
            $mail = new PHPMailer();
            $mail->isSMTP();
            $mail->SMTPDebug = 0;
            $mail->Debugoutput = 'html';
            //126邮箱的SMTP服务器: smtp.126.com
            $econfig = config('my.email');
            $mail->Host = $econfig['Host'];
            //Set the SMTP port number - likely to be 25, 465 or 587
            $mail->Port = $econfig['Port'];
            //Whether to use SMTP authentication
            $mail->SMTPAuth = true;
            //开通服务的邮箱
            $mail->Username =$econfig['Username'];
            //设置的授权码
            $mail->Password = $econfig['Password'];
            //发件邮箱和发件人名称
            $mail->setFrom($econfig['From'], $econfig['FromName']);
            //Set an alternative reply-to address
            //$mail->addReplyTo('replyto@example.com', 'First Last');
            //发送给谁和名字
            $mail->addAddress($to);
            //标题
            $mail->Subject = $title;
            //Read an HTML message body from an external file, convert referenced images to embedded,
            //发送的内容
            $mail->msgHTML($content);
            //Replace the plain text body with one created manually
            //$mail->AltBody = 'This is a plain-text message body';
            //Attach an image file
            //$mail->addAttachment('images/phpmailer_mini.png');

            //send the message, check for errors
            if (!$mail->send()) {
                return false;
            } else {
                return true;
            }
        }catch (phpmailerException $e){
            return false;
        }

    }
}
