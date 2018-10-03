<?php
namespace app\controller;

include('../app/model/EstacionamentoDAOImplementation.class.php');

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use app\model\EstacionamentoDAOImplementation as EstacionamentoDAO;
use app\classes\BadHttpRequest;
use app\classes\Estacionamento;

class ControladorApp
{

    public function lerTodosEstacionamentos( Request $request, Response $response, array $args )
    {
        $status = 200;

        try {
            $dao = new EstacionamentoDAO();
            $estacionamentoArray = $dao->getAllEstacionamentos();

            $corpoResp =  json_encode( array( "estacionamentos" =>$estacionamentoArray ) );
            $response = $response->withHeader('Content-type', 'application/json')
                                 ->write( $corpoResp );
        } catch ( \PDOException $e ) {
            $status = 500;
            $response->write('Exceção capturada: '.  $e->getMessage(). '\n');
        } 
        return $response->withStatus($status);
    }
/*
    public function CadastrarEstacionamentos( Request $request, Response $response, array $args )
    {

        $status = 200;
        try{
            if (!( isset( $objEntrada["idEstacionamento"] ) &&
            isset( $objEntrada["timestamp"] ) &&
            isset( $objEntrada["estado"])))
                throw new BadHttpRequest();

            $objEntrada = $request->getParsedBody();

            if ( is_null($objEntrada) )
                throw new BadHttpRequest();

            $arrayEstacionamento = array( "idEstacionamento"=>$objEntrada["idEstacionamento"],
                "timestamp"=>$objEntrada["timestamp"],
                "estado"=>$objEntrada["estado"]);
            
                $estacionamento = new Estacionamento($arrayEstacionamento);




        }
       
    }
*/

}