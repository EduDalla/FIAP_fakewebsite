<?php

session_start();
session_destroy();

require_once 'config.php';

header('Location: '.$url_base.'logar.php');
exit();

