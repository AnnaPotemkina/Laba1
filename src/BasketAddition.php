<?php


namespace App;


class BasketAddition
{
    private Database $database;

    private Session $session;

    public function __construct(Database $database, Session $session)
    {
        $this->database = $database;
        $this->session = $session;
    }

    public function BasketAddition(array $product): bool{

        $basket_ids = $this->database->getConnection()->query(
            "SELECT Basket_id, Customer_id FROM Basket"
        )->fetchAll();
        foreach ($basket_ids as $basket_id){
            if ($this->session->getData("user")["user_id"] == $basket_id["Customer_id"]){
                $required_id = $basket_id["Basket_id"];
                break;
            }
        }

        $statement = $this->database->getConnection()->prepare(
            'SELECT * FROM Basket_Product WHERE (Product_id = :Product_id) AND (Basket_id = :basket_id)'
        );

        $statement->execute([
            'Product_id' => $product["product_id"],
            "basket_id" => $required_id
        ]);

        $product1 = $statement->fetch();
        if (!empty($product1)){
            $statement = $this->database->getConnection()->prepare(
                "UPDATE Basket_Product SET Product_number = Product_number + 1 WHERE Product_id = :product_id AND (Basket_id = :basket_id)"
            );
            $statement->execute([
                "product_id" => $product["product_id"],
                "basket_id" => $required_id
            ]);

        }
        else {
            $statement = $this->database->getConnection()->prepare(
                "INSERT INTO Basket_Product (Product_id, Basket_id, Product_number)
            VALUES (:product_id, :basket_id, 1)"
            );
            $statement->execute([
                "product_id" => $product["product_id"],
                "basket_id" => $required_id
                //"size_id" => $required_id2
            ]);
        }
        return true;
    }
}