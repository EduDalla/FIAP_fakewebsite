<?php

require_once "database.php";
require_once "config.php";
require_once 'vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader('view');
$twig = new \Twig\Environment($loader, [
]);

$sql = "SELECT * FROM reset_senha WHERE chave = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $_GET['chave']);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user === null) {
    $not_permitted = true;
    echo $twig->render('resetar_senha.twig', ['not_permitted' => $not_permitted]);
    die();
}

if (strtotime($user["data_acesso"]) <= time()) {
    echo $twig->render('alteracao_feita.twig', ['url_base' => $url_base]);
    die();
}

if (isset($_POST['submit']) || isset($_GET['chave'])) {
    $sql_verificate = "SELECT user_id FROM reset_senha WHERE chave = ? AND ativo = 1";
    $statement_veri = $conn->prepare($sql_verificate);
    $statement_veri->bind_param("s", $_GET['chave']);
    $statement_veri->execute();

    $result = $statement_veri->get_result();
    $reset_senha = $result->fetch_row();

    if (!isset($reset_senha[0])) {
        echo $twig->render('alteracao_feita.twig', ['url_base' => $url_base]);
        die();
    }
    $status = "";
    if (empty($_POST['senha'])) {
        $status = "Coloque sua nova senha";
    } elseif (strlen($_POST['senha']) < 5) {
        $status = "Senha precisa ter pelo menos 5 caracteres";
    } else {
        $new_password = filter_input(INPUT_POST, "senha", FILTER_SANITIZE_SPECIAL_CHARS);
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);

        try {
            $sql_update = "UPDATE `users` SET `password` = ? WHERE `id` = ?";

            $statement_chave = $conn->prepare($sql_update);
            $statement_chave->bind_param("si", $new_hash,$reset_senha[0]);
            $statement_chave->execute();

            $status = "Sua senha foi alterada!";
        } catch (Exception $e) {
            $status = "Sua senha não foi alterada, tente novamente!";
        }

        $sql_update = "UPDATE reset_senha SET ativo = 0 WHERE user_id = ?"; // update da chave para NÃO ativo
        $statement_chave = $conn->prepare($sql_update);
        $statement_chave->bind_param("i", $reset_senha[0]);
        $statement_chave->execute();
    }
    echo $twig->render('resetar_senha.twig', ['status' => $status]);
    die();
}
