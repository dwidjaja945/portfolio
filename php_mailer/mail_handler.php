<?php
require_once('email_config.php');
require('phpmailer/PHPMailer/PHPMailerAutoload.php');

// Validate POST inputs
$message = [];
$output = [
    'success' => null,
    'messages' => []
];

// Sanitize name field
$message['name'] = filter_var($_POST['contactName'], FILTER_SANITIZE_STRING);
if(empty($message['name'])) {
    $output['success'] = false;
    $output['messages'][] = 'missing name key';
}

// Validate email field
$message['email'] = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
if(empty($message['name'])) {
    $output['success'] = false;
    $output['messages'][] = 'missing email key';
}

// Validate message
$message['message'] = filter_var($_POST['comments'], FILTER_SANITIZE_STRING);
if(empty($message['message'])) {
    $output['success'] = false;
    $output['messages'][] = 'missing message key';
}

// Validate Phone Number
$message['phone'] = preg_replace('/[^0-9]/' , '', $_POST['phone_number']);
// if(empty($message['phone_number']) && count($message['phone']) <= 11 ) {
//     $output['success'] = false;
//     $output['messages'][] = 'missing phone key';
// };

if($output['success'] !== null) {
    http_response_code(400);
    echo json_encode($output);
    exit();
}

$mail = new PHPMailer;
$mail->SMTPDebug = 0;           // Enable verbose debug output. Change to 0 to disable debugging output.

$mail->isSMTP();                // Set mailer to use SMTP.
$mail->Host = 'smtp.gmail.com'; // Specify main and backup SMTP servers.
$mail->SMTPAuth = true;         // Enable SMTP authentication


$mail->Username = EMAIL_USER;   // SMTP username
$mail->Password = EMAIL_PASS;   // SMTP password
$mail->SMTPSecure = 'tls';      // Enable TLS encryption, `ssl` also accepted, but TLS is a newer more-secure encryption
$mail->Port = 587;              // TCP port to connect to
$options = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    )
);
$mail->smtpConnect($options);

$mail->From = $message['email'];  // sender's email address (shows in "From" field)
// $mail->From = 'dylanWidjajaServer@gmail.com';  // sender's email address (shows in "From" field)
$mail->FromName = $message['name'];   // sender's name (shows in "From" field)
$mail->addAddress(EMAIL_TO_ADDRESS);  // Add a recipient
//$mail->addAddress('ellen@example.com');                        // Name is optional
$mail->addReplyTo($message['email'] , $message['name']);                          // Add a reply-to address
//$mail->addCC('cc@example.com');
//$mail->addBCC('bcc@example.com');

//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
$mail->isHTML(true);                                  // Set email format to HTML

$message['subject'] = "{$message['name']} has sent you a message on your portfolio";

$message['message'] = nl2br($message['message']);
$mail->Subject = $message['subject'];
$mail->Body    = $message['message'];
$mail->AltBody = htmlentities($message['message']);

if(!$mail->send()) {
     $output['success'] = false;
     $output['messages'][] = $mail->ErrorInfo;
} else {
    $output['success'] = true;
}
echo json_encode($output);
?>
