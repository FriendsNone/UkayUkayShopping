<?php

use PHPMailer\PHPMailer\PHPMailer;

require "vendor/autoload.php";

$name = ""; // your business name
$username = ""; // your business email
$password = ""; // business email app password

$mail = new PHPMailer(true);

$mail->isSMTP();
$mail->Host = "smtp.gmail.com";
$mail->Port = 587;
$mail->SMTPAuth = true;
$mail->SMTPSecure = "tls";
$mail->Username = $username;
$mail->Password = $password;

$mail->setFrom($username, $name);
