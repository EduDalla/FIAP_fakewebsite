<?php
session_start();

require_once "database.php";
require_once "config.php";
require_once 'vendor/autoload.php';


$loader = new \Twig\Loader\FilesystemLoader('view');
$twig = new \Twig\Environment($loader, [
]);


if($_SERVER['REQUEST_METHOD'] == 'POST') {

    $username = $_POST['name'];
    $password = $_POST['password'];
    $status = "";

    if(empty($username)) {
        $status = "Coloque seu nome";
    }
    elseif (empty($password)) {
        $status =  "Coloque sua senha";
    }
    elseif (strlen($password) < 5) {
        $status = "Usermame precisa ter mais de 5 caracteres";
    }
    else {
        $username = filter_input(INPUT_POST, "name", FILTER_SANITIZE_SPECIAL_CHARS);
        $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_SPECIAL_CHARS);

        $hash = password_hash($password, PASSWORD_DEFAULT); // hasha o $password

        $sql = "SELECT user, password FROM `users` WHERE user = ?";
        $statement = $conn->prepare($sql);
        $statement->bind_param("s", $username);
        $statement->execute();

        $result = $statement->get_result();
        $row = $result->fetch_row();
        // $row[0] é o id pego no banco users;
        // $row[1] é a senha pega no banco users;

        if (isset($row[1]) && password_verify($password, $row[1])) {
            header('Location: '.$url_base.'fiapon.php');
            $_SESSION['REDIRECT'] = 'REDIRECT';
            $_SESSION['LOGGED_USER'] = $row[0];
        }
        else {
            $status = "Login inválido! Tente novamente!";
        }
        $statement->close();
    }
    echo $twig->render('logar.twig', ['status' => $status, 'url_base' => $url_base]);
    die();
}

echo $twig->render('logar.twig', ['url_base' => $url_base]);
die();

