<?php


namespace App;


class CatalogDelete
{
    private Database $database;

    private Session $session;

    public function __construct(Database $database, Session $session)
    {
        $this->database = $database;
        $this->session = $session;
    }

    public function CatalogDelete( array $args): bool{


        $statement = $this->database->getConnection()->prepare(
            " DELETE FROM Product
                        WHERE Product_id = :basket_id"
        );
        $statement->execute([
            "basket_id" => $args["catalog_product_id"]
            //"size_id" => $required_id2
        ]);


        return true;
    }
}