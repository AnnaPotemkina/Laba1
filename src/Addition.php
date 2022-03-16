<?php


namespace App;


class Addition
{
    private Database $database;

    private Session $session;

    public function __construct(Database $database, Session $session)
    {
        $this->database = $database;
        $this->session = $session;
    }

    public function addition(array $data): bool{
        if (empty($data['code'])){
            throw new AuthorisationException('Код товара не может быть пуст');
        }
        if (empty($data['price'])){
            throw new AuthorisationException('Цена не может быть пуста');
        }
        if (empty($data['type_furniture'])){
            throw new AuthorisationException('Тип товара не может быть пустым');
        }
        $statement = $this->database->getConnection()->prepare(
            'SELECT * FROM Product WHERE Code = :Product_id'
        );

        $statement->execute([
            'Product_id' => $data['code']
        ]);

        $product = $statement->fetch();
        if (!empty($product)){
            throw new AuthorisationException('Товар с таким кодом уже существует');
        }


        $size_ids = $this->database->getConnection()->query(
            "SELECT Size_id, Product_width, Product_height, Product_length FROM Sizes"
        )->fetchAll();
        $product_parametrs = explode(",", $data["size"]);
        $width = intval($product_parametrs[0]);
        $height = intval($product_parametrs[1]);
        $length = intval($product_parametrs[2]);
        foreach ($size_ids as $Size_id){
            if (($width == $Size_id["Product_width"]) and ($height == $Size_id["Product_height"]) and ($length == $Size_id["Product_length"])){
                $required_id2 = $Size_id["Size_id"];
                break;
            }
        }


        $type_ids = $this->database->getConnection()->query(
            "SELECT type_id, type_name FROM Types"
        )->fetchAll();
        foreach ($type_ids as $type_id){
            if ($data["type_name"] == $type_id["type_name"]){
                $required_id = $type_id["type_id"];
                break;
            }
        }

        $statement = $this->database->getConnection()->prepare(
            "INSERT INTO Product (Type_id, Code , Price , Available_number, Type_furniture, Size_id)
            VALUES (:type_id, :code, :price, :available_number, :type_furniture, :size_id)"
        );
        $statement->execute([
            "type_id" => $required_id,
            "code" => $data["code"],
            "price" => $data["price"],
            "available_number" => $data["available_number"],
            "type_furniture" => $data["type_furniture"],
            "size_id" => $required_id2
        ]);

        return true;
    }
}