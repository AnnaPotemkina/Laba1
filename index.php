<?php

use App\Database;
use App\Authorization;
use App\AuthorisationException;
use App\Addition;
use App\Buy;
use App\BasketDelete;
use App\BasketAddition;
use App\CatalogDelete;
use App\Session;
use App\Editing;
use \Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\AppFactory;
use \Twig\Loader\FilesystemLoader;
use \Twig\Environment;

require __DIR__.'/vendor/autoload.php';

$loader = new FilesystemLoader('templates');
$twig = new Environment($loader);

$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);
$app->addBodyParsingMiddleware(); // $_POST

$session = new Session();

$sessionMiddleware = function (ServerRequestInterface $request, RequestHandlerInterface $handler) use($session){
    $session->start();
    $response = $handler->handle($request);
    $session->save();
    return $response;
};

$app->add($sessionMiddleware);

$config = include_once 'config/database.php';
$dsn = $config['dsn'];
$username = $config['username'];
$password = $config['password'];


$database = new Database($dsn, $username, $password);
$authorisation = new Authorization($database, $session);
$addition = new Addition($database, $session);
$BasketAddition = new BasketAddition($database, $session);
$Buy = new Buy($database, $session);
$BasketDelete = new BasketDelete($database, $session);
$CatalogDelete = new CatalogDelete($database, $session);
$editing = new Editing($database, $session);
require_once "/Users/annapotemkina/PhpstormProjects/furniture-shop/src/all_functions.php";

$app->get('/', function(ServerRequestInterface $request, ResponseInterface $response) use ($twig, $session) {

    $body = $twig->render('index.twig',[
        'user' => $session->getData('user')
    ]);
    $response->getBody()->write($body);

    return $response;
});

$app->get('/login/', function(ServerRequestInterface $request, ResponseInterface $response) use ($twig, $session) {

    $body = $twig->render('login.twig', [
        "message" => $session->flush('message'),
        "form" => $session->flush('form')
    ]);
    $response->getBody()->write($body);

   return $response;
});

$app->post('/login-post/', function(ServerRequestInterface $request, ResponseInterface $response) use($authorisation, $session) {
    $params = (array) $request->getParsedBody();

    try {
            $authorisation->login($params['email'], $params['password']);
    } catch (AuthorisationException $exception){
        $session->setData('message', $exception->getMessage());
        $session->setData('form', $params);

        return $response->withHeader('Location', '/login/')
            ->withStatus(302);
    }
    return $response->withHeader('Location', '/')
        ->withStatus(302);
});

$app->get('/register/', function(ServerRequestInterface $request, ResponseInterface $response) use ($twig, $database, $session) {
    $types = $database->getConnection()->query(
        "SELECT type_name FROM Types"
    )->fetchAll();
    $body = $twig->render('register.twig', [
        "types" => $types,
        "message" => $session->flush('message'),
        "form" => $session->flush('form')
    ]);

    $response->getBody()->write($body);

    return $response;
});

$app->post('/register-post/', function(ServerRequestInterface $request, ResponseInterface $response) use($authorisation, $session) {
    $params = (array) $request->getParsedBody();
    try {
        $authorisation->register($params);
    } catch (AuthorisationException $exception){
        $session->setData('message', $exception->getMessage());
        $session->setData('form', $params);
        return $response->withHeader('Location', '/register/')
            ->withStatus(302);
    }

    return $response->withHeader('Location', '/')
        ->withStatus(302);
});

$app->get('/logout/', function(ServerRequestInterface $request, ResponseInterface $response) use ($session) {
    $admin = 1;
    $session->setData('user', null);
    return $response->withHeader('Location','/')
        ->withStatus(302);
});

$app->get('/admin/', function(ServerRequestInterface $request, ResponseInterface $response) use ($twig, $session) {

    $body = $twig->render('admin.twig', ["user" => $session->getData("user")]);
    $response->getBody()->write($body);

    return $response;
});

$app->get('/addproduct/', function(ServerRequestInterface $request, ResponseInterface $response) use ($twig, $session, $database) {
    $types = $database->getConnection()->query(
        "SELECT type_name FROM Types"
    )->fetchAll();
    $Sizes = $database->getConnection()->query(
        "SELECT * FROM Sizes"
    )->fetchAll();
    $body = $twig->render('addproduct.twig',[
        "types" => $types,
        "Sizes" => $Sizes,
        "message" => $session->flush('message'),
        "user" => $session->getData("user")]);
    $response->getBody()->write($body);

    return $response;
});

$app->post('/addproduct-post/', function(ServerRequestInterface $request, ResponseInterface $response) use($addition, $session) {
    $params = (array) $request->getParsedBody();

    try {
        $addition->addition($params);
    } catch (AuthorisationException $exception){
        $session->setData('message', $exception->getMessage());
        $session->setData('form', $params);
        return $response->withHeader('Location', '/addproduct/')
            ->withStatus(302);
    }

    return $response->withHeader('Location', '/')
        ->withStatus(302);
});

$app->get('/showuser/', function(ServerRequestInterface $request, ResponseInterface $response) use ($twig, $database, $session) {
    $customer = $database->getConnection()->query(
        "SELECT Organisation_name, Phone_number, Customer_email, Type_name FROM Customer JOIN Types ON Customer.Type_id = Types.Type_id"
    )->fetchAll();
    $body = $twig->render('showuser.twig', [
        "customer" => $customer,
        "message" => $session->flush('message'),
        "form" => $session->flush('form'),
        "user" => $session->getData("user")
    ]);

    $response->getBody()->write($body);

    return $response;
});

$app->get('/catalog/', function(ServerRequestInterface $request, ResponseInterface $response) use ($twig, $database, $session) {
    $product = $database->getConnection()->query(
        "SELECT Code, Type_furniture, Price, Product_id FROM Product ORDER BY Code LIMIT 5"
    )->fetchAll();
    $body = $twig->render('catalog.twig', [
        "product" => $product,
        "message" => $session->flush('message'),
        "form" => $session->flush('form'),
        "user" => $session->getData("user")
    ]);

    $response->getBody()->write($body);

    return $response;
});

$app->get('/read-more-product/{product_id}/', function(ServerRequestInterface $request, ResponseInterface $response, $args) use ($twig, $database, $session) {
    $product = $database->getConnection()->query(
        "SELECT Code, Type_furniture, Price, Available_number, Product_id, Product_width, Product_height, Product_length FROM Product
                    JOIN Sizes on Product.Size_id = Sizes.Size_id
                    WHERE Product_id = {$args["product_id"]}"
    )->fetchAll();
    $body = $twig->render('read-more-product.twig', [
        "product" => $product,
        "message" => $session->flush('message'),
        "form" => $session->flush('form'),
        "user" => $session->getData("user")
    ]);

    $response->getBody()->write($body);

    return $response;
});

$app->get('/basket-person-post/{product_id}/', function(ServerRequestInterface $request, ResponseInterface $response, $args) use ($BasketAddition, $twig, $database, $session) {
    try {
        $BasketAddition->BasketAddition($args);
    } catch (AuthorisationException $exception){
        $session->setData('message', $exception->getMessage());
        return $response->withHeader('Location', "/read-more-product/{$args["product_id"]}/")
            ->withStatus(302);
    }

    return $response->withHeader('Location', "/read-more-product/{$args["product_id"]}/")
        ->withStatus(302);
});

$app->get('/basket/{user_id}/', function(ServerRequestInterface $request, ResponseInterface $response, $args) use ($twig, $database, $session) {
    $product = $database->getConnection()->query(
        "WITH basket_user as(
        SELECT Basket.Basket_id, Basket_Product.Product_id, Basket_Product.Basket_Product_id, Basket_product.Product_number FROM Basket 
        	JOIN Basket_Product on Basket.Basket_id = Basket_Product.Basket_id
        WHERE Basket.Customer_id = {$args["user_id"]}
        )
    SELECT Code, Type_furniture, Price, Product.Product_id, Available_number, Basket_Product_id, Product_number FROM Product 
    JOIN basket_user on basket_user.Product_id = Product.Product_id"
    )->fetchAll();
    $body = $twig->render('basket.twig', [
        "product" => $product,
        "message" => $session->flush('message'),
        "form" => $session->flush('form'),
        "user" => $session->getData("user")
    ]);

    $response->getBody()->write($body);

    return $response;
});

$app->post('/basket-post/{user_id}/', function(ServerRequestInterface $request, ResponseInterface $response, $args) use ($Buy, $BasketAddition, $twig, $database, $session) {
    $params = (array) $request->getParsedBody();
    try {
        $Buy->Buy($args, $params["block"], $params["wait"]);
    } catch (AuthorisationException $exception){
        $session->setData('message', $exception->getMessage());
        return $response->withHeader('Location', "/basket/{$args["user_id"]}/")
            ->withStatus(302);
    }

    return $response->withHeader('Location', "/basket/{$args["user_id"]}/")
        ->withStatus(302);
});

$app->get('/basket-post-delete/{ basket_product_id }/', function(ServerRequestInterface $request, ResponseInterface $response, $args) use ($BasketDelete, $twig, $database, $session) {
    try {
        $BasketDelete->BasketDelete($args);
    } catch (AuthorisationException $exception){
        $session->setData('message', $exception->getMessage());
        return $response->withHeader('Location', "/basket/{$session->getData("user")["user_id"]}/")
            ->withStatus(302);
    }

    return $response->withHeader('Location', "/basket/{$session->getData("user")["user_id"]}/")
        ->withStatus(302);
});

$app->get('/show-cheque/', function(ServerRequestInterface $request, ResponseInterface $response) use ($twig, $database, $session) {
    $cheque = $database->getConnection()->query(
        "WITH Cheque_Product as(
        SELECT Cheque.Customer_id, Summ, Date_of_cheque, Cheque.Product_id, Product.Code, Product.Type_furniture, Product.Price FROM Cheque
        JOIN Product on Product.Product_id = Cheque.Product_id
        )
        SELECT Customer.Customer_id, Summ, Date_of_cheque, Cheque_Product.Product_id, Cheque_Product.Code, 
        Cheque_Product.Type_furniture, Customer.Organisation_name, Customer.Customer_email,  Cheque_Product.Price FROM Customer
        Join Cheque_Product on Cheque_Product.Customer_id = Customer.Customer_id
            "
    )->fetchAll();


    $body = $twig->render('show-cheque.twig', [
        "cheque" => $cheque,
        "message" => $session->flush('message'),
        "form" => $session->flush('form'),
        "user" => $session->getData("user")
    ]);

    $response->getBody()->write($body);

    return $response;
});

$app->get('/catalog-post-delete/{ catalog_product_id }/', function(ServerRequestInterface $request, ResponseInterface $response, $args) use ($CatalogDelete, $BasketDelete, $twig, $database, $session) {
    try {
        $CatalogDelete->CatalogDelete($args);
    } catch (AuthorisationException $exception){
        $session->setData('message', $exception->getMessage());
        return $response->withHeader('Location', "/catalog/")
            ->withStatus(302);
    }

    return $response->withHeader('Location', "/catalog/")
        ->withStatus(302);
});

$app->get("/edit-product/{product_id}/",
    function (ServerRequestInterface $request, ResponseInterface $response, $args) use ($database, $session, $twig) {
        $query = $database->getConnection()->query(
            "SELECT Code, Price, Available_number, Type_furniture, Product_id
                       FROM Product
                       WHERE Product_id = {$args['product_id']}"
        )->fetch();
       // $session->setData("form", $query);
        $body = $twig->render("edit-product.twig", [
            "form" => $query,
            "user" => $session->getData("user"),
            "message" => $session->flush('message'),
            "status" => $session->flush("status")

            //"form" => $session->flush("form")
        ]);
        $response->getBody()->write($body);
        return $response;
    });

$app->post("/edit-product-post/{ product_id }/", function(ServerRequestInterface $request, ResponseInterface $response, $args) use($database, $editing, $session) {
    $params = (array) $request->getParsedBody();

    try {
        $editing->editing($params, $args);
    } catch (AuthorisationException $exception){
        $session->setData('message', $exception->getMessage());
        $session->setData('form', $params);
        return $response->withHeader('Location', "/edit-product/{$args["product_id"]}/")
            ->withStatus(302);
    }

    return $response->withHeader('Location', '/')
        ->withStatus(302);
});

$app->get('/cheques/{ user_id }/', function(ServerRequestInterface $request, ResponseInterface $response, $args) use ($twig, $database, $session) {
    $cheque = $database->getConnection()->query(
        "WITH Cheque_Product as(
        SELECT Cheque.Customer_id, Summ, Date_of_cheque, Cheque.Product_id, Product.Code, Product.Type_furniture, Product.Price FROM Cheque
        JOIN Product on Product.Product_id = Cheque.Product_id
        )
        SELECT Customer.Customer_id, Summ, Date_of_cheque, Cheque_Product.Product_id, Cheque_Product.Code, Cheque_Product.Type_furniture, Cheque_Product.Price, Customer.Organisation_name,  Customer.Customer_email FROM Customer
        Join Cheque_Product on Cheque_Product.Customer_id = Customer.Customer_id WHERE Customer.Customer_id = {$args["user_id"]}
            "
    )->fetchAll();


    $body = $twig->render('show-cheque.twig', [
        "cheque" => $cheque,
        "message" => $session->flush('message'),
        "form" => $session->flush('form'),
        "user" => $session->getData("user")
    ]);

    $response->getBody()->write($body);

    return $response;
});

$app->get('/personal-catalog/{user_id }/', function(ServerRequestInterface $request, ResponseInterface $response, $args) use ($twig, $database, $session) {
    $product = $database->getConnection()->query(
        "WITH Customers as(SELECT Type_id FROM Customer WHERE Customer_id = {$args["user_id"]})
SELECT Code, Type_furniture, Price, Product_id FROM Product JOIN Customers ON Product.Type_id = Customers.Type_id"
    )->fetchAll();
    $body = $twig->render('catalog2.twig', [
        "product" => $product,
        "message" => $session->flush('message'),
        "form" => $session->flush('form'),
        "user" => $session->getData("user")
    ]);

    $response->getBody()->write($body);

    return $response;
});

$app->run();

