<?php

require_once "database.php";
require_once 'vendor/autoload.php';

$pdo = new PDO($conn);
// set the PDO error mode to exception
$sanitized_search = filter_input(INPUT_GET, $_GET['search'], FILTER_SANITIZE_STRING); // sanitizando o search

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $pdo->prepare("SELECT * FROM users WHERE user LIKE ?");
$stmt->execute([
    "%" . $sanitized_search . "%" // sanitiza no fiapon.php também
]);

$results = $stmt->fetchAll();
