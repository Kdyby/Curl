<?php

header('Content-Type: text/plain; charset=utf-8');
echo $_SERVER['REQUEST_METHOD'], "\n";

$_GET && print_r($_GET);
$_POST && print_r($_POST);
$_FILES && print_r($_FILES);
