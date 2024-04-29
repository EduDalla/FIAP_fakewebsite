<?php

session_start();

require_once "database.php";
require_once "config.php";
require_once 'vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader('view');
$twig = new \Twig\Environment($loader, []);
$fiap_on = 'fiapon.twig';
if(!isset($_SESSION['REDIRECT'])) {
    $not_permitted = "Você não tem permissão para entrar nesse site!";
    echo $twig->render($fiap_on, ['not_permitted' => $not_permitted]);
    die();
}

$pagina = 1;
$limite = 3;
$inicio = ($pagina - 1) * $limite;

if (isset($_GET['pagina'])) {
    $pagina = filter_input(INPUT_GET, "pagina", FILTER_VALIDATE_INT);
}

if (!$pagina) {
    $pagina = 1;
}

$inicio = ($pagina - 1) * $limite;
$count = 0;

if (isset($_GET['search']) ) { // se a pessoa usar search na página
    $sql_2 = "SELECT COUNT(user) as 'count' FROM users WHERE user <> ? AND user LIKE CONCAT( '%',?,'%')";
    $statement_2 = $conn->prepare($sql_2);
    $statement_2->bind_param("ss", $_SESSION['LOGGED_USER'], $_GET['search']);
    $statement_2->execute();
    $count = $statement_2->get_result()->fetch_row()["0"];

    $sql = "SELECT id, user FROM `users` WHERE user <> ? AND user LIKE CONCAT( '%',?,'%') LIMIT ?, ?"; // <> == !=
    $statement = $conn->prepare($sql);
    $statement->bind_param("ssii", $_SESSION['LOGGED_USER'], $_GET['search'], $inicio, $limite);
    $statement->execute();
}
else {
    $sql = "SELECT id, user FROM `users` WHERE user <> ? LIMIT ?, ?"; // <> == !=
    $statement = $conn->prepare($sql);
    $count = $conn->query("SELECT COUNT(user) as 'count' FROM users WHERE user <> '"
        . $_SESSION['LOGGED_USER'] . "'")->fetch_row()["0"]; // prepare statement
    $statement->bind_param("sii", $_SESSION['LOGGED_USER'], $inicio, $limite);
    $statement->execute();
}

$sanitized_search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING); // sanitizando o search

$paginas = ceil($count / $limite); // função ceil não funciona, pois aredondapara o maior número de cima
$result = $statement->get_result();
$linhas = [];

while ($row = $result->fetch_assoc()) { // fetch_assoc acessa a coluna
    array_push($linhas, $row);
}

$rowthings = $result->num_rows; // pega o row do result['nums_row']
$sem_resultado = null;
if ($rowthings == null) { // checa se o result['nums_row'] é igual a verificação da palavra do search se ele existe
    $sem_resultado = "Não existe usuários com esse nome";

    if ($pagina > $limite) {
        header('Location: '. $url_base .'fiapon.php?pagina=&search=');
    }
}
echo $twig->render($fiap_on, [
    'usuarios_on' => $linhas,
    '_SESSION' => $_SESSION['LOGGED_USER'],
    'pagina' => $pagina,
    'limite' => $limite,
    'incio' => $inicio,
    'registros' => $count,
    'paginas' => $paginas,
    'sem_resultado' => $sem_resultado,
    'sanitized_search' => $sanitized_search ?? null, // Null coalescing operator
    'rowthings' => $rowthings]);

$statement->close();

