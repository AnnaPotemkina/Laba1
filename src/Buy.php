<?php


namespace App;


use mysql_xdevapi\Exception;

class Buy
{
    private Database $database;

    private Session $session;

    public function __construct(Database $database, Session $session)
    {
        $this->database = $database;
        $this->session = $session;
    }

    public function Buy(array $args, $block, $wait): bool
    {

        $basket_ids = $this->database->getConnection()->query(
            "WITH basket_user as(
        SELECT Basket.Basket_id, Basket_Product.Product_id, Basket_Product.Product_number FROM Basket 
        	JOIN Basket_Product on Basket.Basket_id = Basket_Product.Basket_id
        WHERE Basket.Customer_id = {$args["user_id"]}
        )
        SELECT Code, Type_furniture, Price, Product.Product_id, Available_number, Product_number FROM Product 
        JOIN basket_user on basket_user.Product_id = Product.Product_id"
        )->fetchAll();
        if ($block === "on") {
            $this->database->getConnection()->query("LOCK TABLES Product WRITE, Basket WRITE, Basket_Product WRITE, Cheque WRITE;");
        }

        if ($wait == 'on'){
            sleep(15);
        }

        foreach ($basket_ids as $basket_id) {
            if ($basket_id["Product_number"] > $basket_id["Available_number"]){
                throw new AuthorisationException('Товар был разобран!');
            }
            else{
                $statement = $this->database->getConnection()->prepare(
                    "UPDATE Product SET Available_number = Available_number - :Product_number 
                        WHERE Product_id = :basket_id"
                );
                $statement->execute([
                    "basket_id" => $basket_id["Product_id"],
                    "Product_number" => $basket_id["Product_number"]
                    //"size_id" => $required_id2
                ]);

                $statement = $this->database->getConnection()->prepare(
                    "INSERT INTO Cheque (Customer_id, Summ, Summ_one, Amount, Date_of_cheque, Product_id)
                        VALUES (:customer_id, :summ, :summ_one, :amount, :date_of_cheque, :prod_id)"
                );
                $statement->execute([
                    "customer_id" => $args["user_id"],
                    "summ" => ($basket_id["Price"] * $basket_id["Product_number"]),
                    "summ_one" => $basket_id["Price"],
                    "amount" => $basket_id["Product_number"],
                    "date_of_cheque" => date('Y-m-d'),
                    "prod_id" => $basket_id["Product_id"]
                    //"size_id" => $required_id2
                ]);
            }
        }

        if ($block == 'on') {
            $this->database->getConnection()->query("UNLOCK TABLES;");
        }
        return true;
    }
}