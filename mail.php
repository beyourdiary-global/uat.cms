<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './header/phpMailer/vendor/autoload.php';

$mail = new PHPMailer(true);

try {
	$mail->SMTPDebug = 2;									
	$mail->isSMTP();											
	$mail->Host	 = 'smtp-relay.brevo.com';					
	$mail->SMTPAuth = true;							
	$mail->Username = 'fankaixuan159@gmail.com';				
	$mail->Password = 'Jk2tZmVfvcUALOD7';						
	$mail->SMTPSecure = 'tls';							
	$mail->Port	 = 587;

	$mail->setFrom('fankaixuan000@gmail.com', 'Name');		
	$mail->addAddress('fankaixuan159@gmail.com');
	
	$mail->isHTML(true);								
	$mail->Subject = 'Employee Leave Application';
	$mail->Body = 'HTML message body in <b>bold</b> ';
	$mail->send();
	echo "Mail has been sent successfully!";
} catch (Exception $e) {
	echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}


?>