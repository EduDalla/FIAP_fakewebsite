<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once "database.php";
require_once "config.php";
require_once 'vendor/autoload.php';
require_once "google_recapcha.php";


//Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);


$loader = new \Twig\Loader\FilesystemLoader('view');
$twig = new \Twig\Environment($loader, [
]);

// validando post ou validadando recaptcha

if ((!$_SERVER['REQUEST_METHOD'] == 'POST') || !$_POST) {
    $status = "";
    echo $twig->render('esqueci_senha.twig', ['status' => $status, 'url_base' => $url_base]);
    die();
}

google_recapcha();

$status = "";

$email = $_POST['email'];

// verificando no banco email
$sql = "SELECT * FROM `users` WHERE email = ?";
$statement = $conn->prepare($sql);

$statement->bind_param("s", $email);
$statement->execute();

$result = $statement->get_result();
$row = $result->fetch_row();
// $row[0] é o id pego no banco users
// $row[1] é o nome pego no banco users
// $row[3] é o email pego no banco users

// se encontrar email e recaptcha for válido
if (isset($row[3])) {
    try {
        $mail->isSMTP(); //Send using SMTP
        $mail->Host = 'localhost'; //Set the SMTP server to send through
        $mail->Port = 1025; //TCP port to connect to; use 587 if
        // you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        //Recipients
        $mail->setFrom('fiapon@fiap.com.br', 'FIAP');
        $mail->addAddress($row[3], $row[1]);     //Add a recipient

        $chave = hash('SHA256', microtime(uniqid()));
        $data_expira = date("Y-m-d H:i:s", time() + 60 * 15);

        // inserindo id, chave e data de acesso no banco
        // `reset_senha` para o usuário ter acesso a determinado período de tempo
        $sql_chave = "INSERT INTO `reset_senha`(`user_id`, `chave`, `data_acesso`) VALUES (?, ?, ?) ";
        $statement_chave = $conn->prepare($sql_chave);

        $statement_chave->bind_param("sss", $row[0], $chave, $data_expira);
        $statement_chave->execute();

        //Content
        $mail->isHTML(true); //Set email format to HTML
        $mail->Subject = 'Reset de senha';
        $mail->Body = $twig->render('mail.twig', ['chave' => $chave, 'url_base' => $url_base]);
        $mail->send();
        $status = "Seu email foi enviado!";
    } catch (Exception $e) {
        $status = "Digite seu email";
    }
} else {
    $status = "Email não encontrado... Verifique se digitou corretamente.";
}
echo $twig->render('esqueci_senha.twig', ['status' => $status, 'url_base' => $url_base]);
die();
