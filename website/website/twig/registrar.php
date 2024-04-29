<?php
session_start();

require_once "database.php";
require_once "config.php";
require_once 'vendor/autoload.php';
require_once "google_recapcha.php";

$loader = new \Twig\Loader\FilesystemLoader('view');
$twig = new \Twig\Environment($loader, [
]);

if($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo $twig->render('registrar.twig', ['url_base' => $url_base]);
    die();
}

google_recapcha();

$username = $_POST['name'];
$password = $_POST['password'];
$email = $_POST['email'];
$_SESSION['name'] = $username;

$nomesalvo = "";

if (isset($_SESSION['name'])) {
    $nomesalvo = $username;
}
if (empty($email)) {
    $status = "Coloque seu email";
} elseif (empty($username)) {
    $status = "Coloque seu username";
} elseif (empty($password)) {
    $status = "Coloque sua senha";
} elseif (strlen($password) < 5) {
    $status = "Senha precisa ter pelo menos 5 caracteres";
} else {
    $username = filter_input(INPUT_POST, "name", FILTER_SANITIZE_SPECIAL_CHARS);
    $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_SPECIAL_CHARS);

    $sql = "SELECT * FROM users WHERE email = ?";
    $statement_email = $conn->prepare($sql);
    $statement_email->bind_param("s", $email);
    $statement_email->execute();

    $result = $statement_email->get_result();
    $row = $result->fetch_row();
    // $row[3] é o email pego no banco users

    // Define uma função que poderá ser usada para validar e-mails usando regexp
    function valida_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    // Define uma variável para testar o validador
    // Faz a verificação usando a função
    if (!valida_email($email)) {
        $status = "O e-mail inserido é invalido!";
    }
    if (isset($row[3]) && $row[3] == $email) { // verifica se já existe email no banco
        $status = "Esse email já foi resgistrado, digite outro";
    }

    if ((!valida_email($email)) || (isset($row[3]) && $row[3] == $email)) { // verifica se já existe email no banco
        echo $twig->render('registrar.twig', ['status' => $status,
            'nomesalvo' => $nomesalvo,
            'url_base' => $url_base]);
        die();
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO `users`(`user`, `password`, `email`)
    VALUES (?, ?, ?)"; // insere os dados do usuário
    $statement_info = $conn->prepare($sql);
    $statement_info->bind_param("sss", $username, $hash, $email);

    try {
        $statement_info->execute();
        $status = "Você está registrado!";
    } catch (Exception $e) {
        $status = 'Esse usuário já existe... Logue com sua conta
        <a href="' . $url_base . 'logar.php"> clicando aqui</a>!';
    }

}

echo $twig->render('registrar.twig', ['status' => $status, 'nomesalvo' => $nomesalvo]);
die();
