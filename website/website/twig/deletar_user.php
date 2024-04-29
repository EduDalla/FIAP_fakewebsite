<?php

require_once "database.php";
require_once 'vendor/autoload.php';

if (isset($_POST['id'])) {

    $id = $_POST['id'];

    $sql = "DELETE FROM `users` WHERE id = ?";
    $statement = $conn->prepare($sql);

    $statement->bind_param("s", $id);
    $statement->execute();

    $result = $statement->get_result();

    $statement->close();
    $conn->close();
    $url_atual = 'fiapon.php?pagina=' . $_GET['page'];
    header('Location: ' . $url_atual);
}
