<?php

require_once "/Users/annapotemkina/PhpstormProjects/furniture-shop/src/connectDatabase.php";

$num = $_GET["num"];

$query = $database->getConnection()->query(
    "SELECT Code, Type_furniture, Price, Product_id FROM Product ORDER BY Code LIMIT $num, 3"
);
$product = $query->fetchAll();

echo json_encode($product, JSON_UNESCAPED_UNICODE);