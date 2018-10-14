<?php
namespace controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \PDO;

class ControladorApp 
{

    public function lerTodosEstacionamentos( Request $request, Response $response, array $args )
    {
        $status = 200;
        require("../model/connection.php");
        try{

            $stmt = $pdo->query("SELECT idbp, nome FROM tbl_atual ");
            $stmt->setFetchMode(PDO::FETCH_ASSOC);

            $obj = array();
            $i = 0;

            foreach ($stmt as $row){

                $arr = array('id' => $row['idbp'], 'name' => $row['nome']);
                $obj[$i] = $arr;
                $i = $i + 1;

            }

            $corpoResp =  json_encode( array( "estacionamentos" => $obj ) );
            $response = $response->withHeader('Content-type', 'application/json')
            ->write( $corpoResp );
        }catch (\PDOException $e){
            $status = 500;
            $response->write('Exceção capturada: '.  $e->getMessage(). '\n');
        }
        return $response->withStatus($status);
    }

    public function cadastrarEstacionamentos( Request $request, Response $response, array $args )
    {
        $status = 200;
        require("../model/connection.php");
        try{

            $jsonObj = $request->getParsedBody();
            $id = $jsonObj['idEstacionamento'];

            $stmt = $pdo->prepare("SELECT vagas_ocupadas FROM tbl_atual WHERE idbp='$id' ");
            $stmt->execute();
            

            $vagas_ocupadas = $stmt->fetch(PDO::FETCH_ASSOC);

            if($jsonObj['estado'] == "saida"){
                $vagas_atual = $vagas_ocupadas['vagas_ocupadas'] - 1;
            }else{
                $vagas_atual = $vagas_ocupadas['vagas_ocupadas'] + 1;
            }
            

            $stmt2 = $pdo->prepare("UPDATE tbl_atual SET vagas_ocupadas = :vagas_ocupadas WHERE idbp = :idbp;");
            
            $stmt2->bindParam(':idbp', $jsonObj['idEstacionamento']);
            
            $stmt2->bindParam(':vagas_ocupadas', $vagas_atual);
            
            $stmt2->execute();
             
            if($jsonObj['idEstacionamento'] < 2){
               
                $sql = "INSERT INTO tbl_portaria (idportaria, timestamp, acao) VALUES (?, FROM_UNIXTIME(?), ?)";

            }else{
               
                $sql = "INSERT INTO tbl_bolsao (idbolsao, timestamp, acao) VALUES (?, FROM_UNIXTIME(?), ?)";
            }

            $stmt3 = $pdo->prepare($sql);
            
            $stmt3->bindValue(1, $jsonObj['idEstacionamento'], PDO::PARAM_INT);
            $stmt3->bindValue(2, $jsonObj['timestamp'], PDO::PARAM_STR);
            $stmt3->bindValue(3, $jsonObj['estado'], PDO::PARAM_STR);
            
            $stmt3->execute();
            

        } catch (BadHttpRequest $e) {
            $status = 400;
            $response->write('Exceção capturada: '.  $e->getMessage(). '\n');
        } catch (\PDOException $e) {
            $status = 500;
            $response->write('Exceção capturada: '.  $e->getMessage(). '\n');
        }

        return $response->withStatus($status);

    }
    
}