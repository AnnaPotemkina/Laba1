<?php
header("Content-Type: text/html; charset=utf-8");
error_reporting(-1);


require_once "/Users/annapotemkina/PhpstormProjects/furniture-shop/src/Database.php";

use App\Database;
$config = include_once 'config/database.php';
$dsn = $config['dsn'];
$username = $config['username'];
$password = $config['password'];


$database = new Database($dsn, $username, $password);
