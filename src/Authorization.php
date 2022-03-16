<?php


namespace App;



class Authorization
{
    /**
     * @var Database
     */

    private Database $database;

    private Session $session;


    /**
     * Authorization constructor.
     * @param Database $database
     */
    public function __construct(Database $database, Session $session)
    {
        $this->database = $database;
        $this->session = $session;
    }

    /**
     * @param array $data
     * @return bool
     */
    public function register(array $data): bool
    {
        if (empty($data['company_name'])){
            throw new AuthorisationException('Company name should not be empty');
        }
        if (empty($data['email'])){
            throw new AuthorisationException('Email should not be empty');
        }
        if (empty($data['phone_number'])){
            throw new AuthorisationException("Phone should not be empty");
        }
        if (!preg_match("/^([0-9])+$/", $data['phone_number'])){
            throw new AuthorisationException("Phone should consists only from numbers");
        }
        if (strlen($data['phone_number']) != 11){
            throw new AuthorisationException("There should be 11 numbers");
        }
        if (empty($data['password'])){
            throw new AuthorisationException('Password should not be empty');
        }
        if ($data['password'] !== $data['confirm_password']){
            throw new AuthorisationException('Password and confirm password should match');
        }

        $statement = $this->database->getConnection()->prepare(
            'SELECT * FROM Customer WHERE Customer_email = :Customer_email'
        );

        $statement->execute([
            'Customer_email' => $data['email']
        ]);

        $user = $statement->fetch();
        if (!empty($user)){
            throw new AuthorisationException('User with this email already exists');
        }

        $password_cookie_token = md5($data["password"]);

        $this->database->getConnection()->query(
                "UPDATE Customer SET Password_cookie_token='".$password_cookie_token."' 
                               WHERE Customer_email='".$data["email"]."'");

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
            "INSERT INTO Customer (Organisation_name, Phone_number, Customer_email, Customer_password, Type_id)
            VALUES (:company_name, :phone_number, :email, :password, :type_id)"
        );
        $statement->execute([
            "company_name" => $data["company_name"],
            "phone_number" => $data["phone_number"],
            "email" => $data["email"],
            "password" => password_hash($data["password"], PASSWORD_BCRYPT),
            "type_id" => $required_id
        ]);

        $us_ids = $this->database->getConnection()->query(
            "SELECT Customer_id, Customer_email FROM Customer"
        )->fetchAll();
        foreach ($us_ids as $us_id){
            if ($data["email"] == $us_id["Customer_email"]){
                $required_id = $us_id["Customer_id"];
                break;
            }
        }

        $statement = $this->database->getConnection()->prepare(
            "INSERT INTO Basket ( Customer_id)
            VALUES (:customer_id)"
        );
        $statement->execute([
            "customer_id" => $required_id
        ]);
        return true;
    }

    /**
     * @param string $email
     * @param $password
     * @throws AuthorisationException
     */

    public function login(string $email, $password){

        if (empty($email)){
            throw new AuthorisationException('Email should not be empty');
        }
        if (empty($password)){
            throw new AuthorisationException('Password should not be empty');
        }

        $statment = $this->database->getConnection()->prepare(
            'SELECT * from Customer WHERE Customer_email = :email'
        );
        $statment->execute([
            'email' => $email
        ]);

        $user = $statment->fetch();

        $statment = $this->database->getConnection()->prepare(
            'SELECT * from Administrator WHERE Login = :email'
        );
        $statment->execute([
            'email' => $email
        ]);

        $user2 = $statment->fetch();

        if(!empty($user2)){
           $admin = 0;
        } else{
           $admin = 1;
        }


        if(empty($user) and empty($user2)) {
            throw new AuthorisationException('User with such email does not exist');
        } elseif (empty($user2)) {
            if (password_verify($password, $user['Customer_password'])) {
                $this->session->setData('user', [
                    'user_id' => $user['Customer_id'],
                    'Company_name' => $user['Organisation_name'],
                    'email' => $user['Customer_email'],
                    'phone_number' => $user['Phone_number'],
                    'type_id' => $user['Type_id'],
                    'admin' => 0
                ]);
                return true;
            }
        } else {
            if (($password == $user2['Admin_password'])) {
                $this->session->setData('user', [
                    'user_id' => $user2['Admin_id'],
                    'email' => $user2['Login'],
                    'admin' => 1
                ]);
                return true;
            }
        }

        throw new AuthorisationException('Incorrect email or password');
    }
}