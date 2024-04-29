<?php

$db_server = "localhost";
$db_user = "root";
$db_pass = getenv('MYSQL_SECURE_PASSWORD');
$db_name = "form";
$conn = "";

$conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);
