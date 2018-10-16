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

    public function findByHour( Request $request, Response $response, array $args, $id, $timestamp_query )
    {
        $status = 200;
        require("../model/connection.php");
        try{

            $query_hora_ref = date("H", $timestamp_query);
            $query_hora_ref = intval($query_hora_ref);
            $query_hora_ref = $query_hora_ref -2;
            $query_dia_ref = date("d", $timestamp_query);
            $query_mes_ref = date("m", $timestamp_query);
            $query_ano_ref = date("Y", $timestamp_query);

            if($id < 2){
                
                $sql_entrada = "SELECT timestamp FROM tbl_portaria WHERE idportaria=$id AND acao='entrada' "; 

                $sql_saida = "SELECT timestamp FROM tbl_portaria WHERE idportaria=$id AND acao='saida'  ";

            }else{
                
                $sql_entrada = "SELECT timestamp FROM tbl_bolsao WHERE idportaria=$id AND acao='entrada'  ";

                $sql_saida = "SELECT timestamp FROM tbl_bolsao WHERE idportaria=$id AND acao='saida'  ";

            }

            $stmt_entrada = $pdo->query($sql_entrada);
            $stmt_entrada->setFetchMode(PDO::FETCH_ASSOC);
            
            $arr_entrada = array(0,0,0,0,0,0,0,0,0,0,0,0);

            $stmt_saida = $pdo->query($sql_saida);
            $stmt_saida->setFetchMode(PDO::FETCH_ASSOC);
            
            $arr_saida = array(0,0,0,0,0,0,0,0,0,0,0,0);

            
            $i = 1;
            $j = 1;
            
            while ($r_entrada = $stmt_entrada->fetch()){
                while ($i <= 11){
                    
                    $dateValue_entrada = strtotime($r_entrada['timestamp']);  
                    $min_entrada = date('i', $dateValue_entrada);
                    $min_entrada = intval($min_entrada);
                    $hora_verif_entrada = date("H", $dateValue_entrada);
                    $hora_verif_entrada = intval($hora_verif_entrada);
                    $dia_verif_entrada = date("d", $dateValue_entrada);
                    $mes_verif_entrada = date("m", $dateValue_entrada);
                    $ano_verif_entrada = date("Y", $dateValue_entrada);
                    $menor = ($i-1) * 5;
                    $maior = $i * 5;
                    if ($dia_verif_entrada == $query_dia_ref && $mes_verif_entrada == $query_mes_ref && $ano_verif_entrada == $query_ano_ref) {

                        if($menor < $min_entrada && $min_entrada <= $maior){
    
                            $arr_entrada[$i-1] = $arr_entrada[$i-1] + 1;
                        }
                    }
                    $i = $i + 1;
                }
            $i = 1;
            
            while ($r_saida = $stmt_saida->fetch()){
                while ($j <= 11){
                    
                    $dateValue_saida = strtotime($r_saida['timestamp']); 
                    $min_saida = date('i', $dateValue_saida);
                    $min_saida = intval($min_saida);
                    $hora_verif_saida = date("H", $dateValue_saida);
                    $hora_verif_saida = intval($hora_verif_saida);
                    $dia_verif_saida = date("d", $dateValue_saida);

                    $mes_verif_saida = date("m", $dateValue_saida);

                    $ano_verif_saida = date("Y", $dateValue_saida);

                    $meno_saida = ($j-1) * 5;

                    $maior_saida = $j * 5;

                    if ($dia_verif_saida == $query_dia_ref && $mes_verif_saida == $query_mes_ref && $ano_verif_saida == $query_ano_ref && $hora_verif_saida == $query_hora_ref) {
    
                        if($meno_saida < $min_saida && $min_saida<= $maior_saida){
        
                            $arr_saida[$j-1] = $arr_saida[$j-1] + 1;
                        }
                    }
                    $j = $j + 1;
                }
                $j = 1;
            }

            $corpoRespEntrada =  json_encode( array( "entrada" => $arr_entrada ) );
            $response = $response->withHeader('Content-type', 'application/json')
            ->write( $corpoRespEntrada );

            $corpoRespSaida =  json_encode( array( "entrada" => $arr_entrada ) );
            $response = $response->withHeader('Content-type', 'application/json')
            ->write( $corpoRespSaida );

            }
        }catch (BadHttpRequest $e) {
            $status = 400;
            $response->write('Exceção capturada: '.  $e->getMessage(). '\n');
        } catch (\PDOException $e) {
            $status = 500;
            $response->write('Exceção capturada: '.  $e->getMessage(). '\n');
        }

        return $response->withStatus($status);
    }  
}