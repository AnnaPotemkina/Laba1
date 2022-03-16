<?php


namespace App;


class Editing
{
    private Database $database;

    private Session $session;

    public function __construct(Database $database, Session $session)
    {
        $this->database = $database;
        $this->session = $session;
    }


    public function editing(array $data, array $args): bool
    {
        $statement = $this->database->getConnection()->prepare(
            "UPDATE Product SET Price = :price, Available_number = :available_number, Type_furniture = :type_furniture, Code = :code
                        WHERE Product_id = {$args["product_id"]}
                        "
        );
        $statement->execute([
            "price" => $data["Price"],
            "available_number" => $data["Available_number"],
            "type_furniture" => $data["Type_furniture"],
            "code" => $data["Code"]
        ]);
        return true;
    }
}