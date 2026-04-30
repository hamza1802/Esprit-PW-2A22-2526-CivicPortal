<?php
require_once __DIR__ . '/lib/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/lib/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/lib/PHPMailer/src/Exception.php';

$mailConfig = require __DIR__ . '/config/mail.php';

$mail = new PHPMailer\PHPMailer\PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $mailConfig['username'];
    $mail->Password   = $mailConfig['password'];
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    $mail->SMTPDebug = 2;

    $mail->setFrom($mailConfig['username'], 'Test Mailer');
    $mail->addAddress($mailConfig['username']); // Send to yourself

    $mail->Subject = 'Test Connection';
    $mail->Body    = 'If you see this, the connection is working!';

    echo "Tentative d'envoi...\n";
    $mail->send();
    echo "SUCCÈS : L'email a été envoyé !\n";
} catch (Exception $e) {
    echo "ÉCHEC : " . $mail->ErrorInfo . "\n";
}
