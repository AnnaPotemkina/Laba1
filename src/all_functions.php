<?php
use Psr\Http\Message\ResponseInterface;

use JetBrains\PhpStorm\ArrayShape;
function renderPageByQuery($query, $session, $twig, $response, $name_render_page, $name_form = "form", $need_one = 0): ResponseInterface
{
    if ($need_one == 1) {
        $rows = $query->fetch();
    } else {
        $rows = $query->fetchAll();
    }
    $session->setData($name_form, $rows);
    $body = $twig->render($name_render_page, [
        "user" => $session->getData("user"),
       /* "message" => $session->get_and_set_null("message"),*/
        "status" => $session->flush("status"),
        $name_form => $session->flush($name_form)
    ]);
    $response->getBody()->write($body);
    return $response;
}

function renderPage($session, $twig, ResponseInterface $response, $name_render_page, $name_form = "form"): ResponseInterface
{
    $body = $twig->render($name_render_page, [
        "user" => $session->getData("user"),
        "message" => $session->flush("message"),
        "status" => $session->flush("status"),
        $name_form => $session->flush($name_form)
    ]);
    $response->getBody()->write($body);
    return $response;
}