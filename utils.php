<?php

function sendNotificationEmail($memberID, $subject, $transaction_id)
{
    global $dbs, $sysconf;

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        //Server settings
        $mail->SMTPDebug = PHPMailer\PHPMailer\SMTP::DEBUG_SERVER;                      // Enable verbose debug output
        $mail->isSMTP();                                                                // Send using SMTP
        $mail->Host = $sysconf['mail']['server'];                                       // Set the SMTP server to send through
        $mail->SMTPAuth = $sysconf['mail']['auth_enable'];                              // Enable SMTP authentication
        $mail->Username = $sysconf['mail']['auth_username'];                            // SMTP username
        $mail->Password = $sysconf['mail']['auth_password'];                            // SMTP password
        if ($sysconf['mail']['SMTPSecure'] === 'tls') {                                 // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        } else if ($sysconf['mail']['SMTPSecure'] === 'ssl') {
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        }
        $mail->Port = $sysconf['mail']['server_port'];                                  // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

        //Recipients
        $mail->setFrom($sysconf['mail']['from'], $sysconf['mail']['from_name']);
        $mail->addReplyTo($sysconf['mail']['reply_to'], $sysconf['mail']['reply_to_name']);
        $mail->addAddress($sysconf['mail']['from'], $sysconf['mail']['from_name']);
        // additional recipient
        if (isset($sysconf['mail']['add_recipients'])) {
            foreach ($sysconf['mail']['add_recipients'] as $_recps) {
                $mail->AddAddress($_recps['from'], $_recps['from_name']);
            }
        }
        // query
        $query = $dbs->query("SELECT member_name, member_email FROM member WHERE member_id = '$memberID'");
        $data = $query->fetch_object();
        $mail->addCC($data->member_email, $member_name);

        // Content
        // get message template
        $_msg_tpl = @file_get_contents(SB . 'template/reserve-mail-tpl.html');

        // date
        $_curr_date = date('Y-m-d H:i:s');

        // compile reservation data
    
        $_data .= $subject;

        // message
        $_message = str_ireplace(array('<!--MEMBER_ID-->', '<!--MEMBER_NAME-->', '<!--DATA-->', '<!--DATE-->'),
            array($memberID, $data->member_name, $_data, $_curr_date), $_msg_tpl);

        // Set email format to HTML
        $mail->Subject = $subject;
        $mail->msgHTML($_message);
        $mail->AltBody = strip_tags($_message);

        $mail->send();

        utility::writeLogs($dbs, 'payment_transactions', $memberID, 'slembar', $subject.": ". $transaction_id, 'send email');
        return array('status' => 'SENT', 'message' => $subject);
    } catch (Exception $exception) {
        utility::writeLogs($dbs, 'payment_transactions', $memberID, 'slembar', 'FAILED to send reservation e-mail to ' . $memberID .": ". $transaction_id, 'failed send email');
        return array('status' => 'ERROR', 'message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}