<?php


namespace App;


class BasketDelete
{
    private Database $database;

    private Session $session;

    public function __construct(Database $database, Session $session)
    {
        $this->database = $database;
        $this->session = $session;
    }

    public function BasketDelete( array $args): bool{


            $statement = $this->database->getConnection()->prepare(
                " DELETE FROM Basket_Product
                        WHERE Basket_Product_id = :basket_id"
            );
            $statement->execute([
                "basket_id" => $args["basket_product_id"]
                //"size_id" => $required_id2
            ]);


        return true;
    }
}