<?php

include('../controller/ControladorApp.class.php');

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \controller\ControladorApp;


require_once 'config.php';

mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');


$app = new \Slim\App;

# Desabilita tratamento automatico de exceção do Slim, as execeções são tratados
# pela aplicação no controller (ControllerApp), em que os HTTP status code são configurados
# corretamente para resposta.
$c = $app->getContainer();
unset($c['phpErrorHandler']);



# ROTAS
$app->group('/v1',function ( ) {

    $this->post('/carros', function (Request $request, Response $response, array $args) {
        $controlador = new ControladorApp();
        return $controlador->cadastrarEstacionamentos($request, $response, $args);
    });

    $this->get('/estacionamentos', function (Request $request, Response $response, array $args) {
        $controlador = new ControladorApp();
        return $controlador->lerTodosEstacionamentos($request, $response, $args);
    });

    $this->get('/estacionamentos/{id}/findByDay', function (Request $request, Response $response, array $args) {
        $id = $args['id'];
        $timestamp_query = $_GET['timestamp'];
        $controlador = new ControladorApp();
        return $controlador->findByHour($request, $response, $args, $id, $timestamp_query);
    });
    
    $this->delete('/estacionamentos/{id}/findByHour', function (Request $request, Response $response, array $args) {
        
    });

    $this->put('/estacionamentos/{id}/findByMonth', function (Request $request, Response $response, array $args) {
       
    });


    $this->get('/estacionamentos/{id}/findByWeek', function (Request $request, Response $response, array $args) {
    
    });

});

# Criando uma função especifica para o Erro 404 "not found". Por padrão é enviado um
# html com um texto padrão. Por se tratar de uma API, irei devolver apenas o status code
# 404 sem nenhum corpo na mensagem
$c['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        return $response->withStatus(404);
    };
};

$app->run();