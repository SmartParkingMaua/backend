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
            }
            
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

            $arrayResp = array("entrada" => $arr_entrada, "saida" => $arr_saida);
            $corpoRespEntrada =  json_encode($arrayResp);
            $response = $response->withHeader('Content-type', 'application/json')
            ->write( $corpoRespEntrada );

        }catch (BadHttpRequest $e) {
            $status = 400;
            $response->write('Exceção capturada: '.  $e->getMessage(). '\n');
        } catch (\PDOException $e) {
            $status = 500;
            $response->write('Exceção capturada: '.  $e->getMessage(). '\n');
        }

        return $response->withStatus($status);
    }
    
    public function findByDay( Request $request, Response $response, array $args, $id, $timestamp_query )
    {
        $status = 200;
        require("../model/connection.php");
        try{

            $timestamp_query = $_GET['timestamp'];
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
            $arr_entrada = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            
            $stmt_saida = $pdo->query($sql_saida);
            $stmt_saida->setFetchMode(PDO::FETCH_ASSOC);
            $arr_saida = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);

            while ($r_entrada = $stmt_entrada->fetch()){

                $dateValue_entrada = strtotime($r_entrada['timestamp']);  
                $hora_entrada = date('H', $dateValue_entrada);
                $hora_entrada = intval($hora_entrada);
                $dia_verif_entrada = date("d", $dateValue_entrada);
                $mes_verif_entrada = date("m", $dateValue_entrada);
                $ano_verif_entrada = date("Y", $dateValue_entrada);

                if ($dia_verif_entrada == $query_dia_ref && $mes_verif_entrada == $query_mes_ref && $ano_verif_entrada == $query_ano_ref) {
                    $arr_entrada[$hora_entrada] = $arr_entrada[$hora_entrada] + 1;
                }
            }
            while ($r_saida = $stmt_saida->fetch()){

                $dateValue_saida = strtotime($r_saida['timestamp']);   
                $hora_saida = date('H', $dateValue_saida);
                $hora_saida = intval($hora_saida);
                $dia_verif_saida = date("d", $dateValue_saida);
                $mes_verif_saida = date("m", $dateValue_saida);
                $ano_verif_saida = date("Y", $dateValue_saida);

                if ($dia_verif_saida == $query_dia_ref && $mes_verif_saida == $query_mes_ref && $ano_verif_saida == $query_ano_ref) {

                    $arr_saida[$hora_saida] = $arr_saida[$hora_saida] + 1;
                }
            }
            
            $arrayResp = array("entrada" => $arr_entrada, "saida" => $arr_saida);
            $corpoRespEntrada =  json_encode($arrayResp);
            $response = $response->withHeader('Content-type', 'application/json')
            ->write( $corpoRespEntrada );

        }catch (BadHttpRequest $e) {
            $status = 400;
            $response->write('Exceção capturada: '.  $e->getMessage(). '\n');
        } catch (\PDOException $e) {
            $status = 500;
            $response->write('Exceção capturada: '.  $e->getMessage(). '\n');
        }

        return $response->withStatus($status);

    }

    public function findByMonth( Request $request, Response $response, array $args, $id, $timestamp_query )
    {
        $status = 200;
        require("../model/connection.php");
        try{

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

            $stmt_saida = $pdo->query($sql_saida);
            $stmt_saida->setFetchMode(PDO::FETCH_ASSOC);

            $arr_entrada_domingo_1 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_entrada_segunda_1 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_entrada_terca_1 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_entrada_quarta_1 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_entrada_quinta_1 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_entrada_sexta_1 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_entrada_sabado_1 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);

            $arr_saida_domingo_1 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_saida_segunda_1 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_saida_terca_1 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_saida_quarta_1 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_saida_quinta_1 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_saida_sexta_1 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_saida_sabado_1 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);

            $arr_entrada_domingo_2 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_entrada_segunda_2 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_entrada_terca_2 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_entrada_quarta_2 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_entrada_quinta_2 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_entrada_sexta_2 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_entrada_sabado_2 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            
            $arr_saida_domingo_2 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_saida_segunda_2 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_saida_terca_2 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_saida_quarta_2 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_saida_quinta_2 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_saida_sexta_2 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_saida_sabado_2 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);

            $arr_entrada_domingo_3 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_entrada_segunda_3 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_entrada_terca_3 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_entrada_quarta_3 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_entrada_quinta_3 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_entrada_sexta_3 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_entrada_sabado_3 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);

            $arr_saida_domingo_3 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_saida_segunda_3 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_saida_terca_3 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_saida_quarta_3 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_saida_quinta_3 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_saida_sexta_3 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_saida_sabado_3 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);


            $arr_entrada_domingo_4 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_entrada_segunda_4 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_entrada_terca_4 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_entrada_quarta_4 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_entrada_quinta_4 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_entrada_sexta_4 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_entrada_sabado_4 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);

            $arr_saida_domingo_4 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_saida_segunda_4 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_saida_terca_4 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_saida_quarta_4 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_saida_quinta_4 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_saida_sexta_4 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_saida_sabado_4 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_entrada_domingo_4 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);

            $arr_entrada_domingo_5 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_entrada_segunda_5 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_entrada_terca_5 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_entrada_quarta_5 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_entrada_quinta_5 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_entrada_sexta_5 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_entrada_sabado_5 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);

            $arr_saida_domingo_5 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_saida_segunda_5 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_saida_terca_5 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_saida_quarta_5 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_saida_quinta_5 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_saida_sexta_5 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_saida_sabado_5 = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);

            while ($r_entrada = $stmt_entrada->fetch()){

                $dateValue_entrada = strtotime($r_entrada['timestamp']);   

                $mes_entrada = date('m', $dateValue_entrada);

                $dia_semana_entrada = date('w', $dateValue_entrada);
                $dia_semana_entrada = intval($dia_semana_entrada);

                $dia_entrada = date('d', $dateValue_entrada);
                $dia_entrada = intval($dia_entrada);

                $hora_entrada = date('H', $dateValue_entrada);
                $hora_entrada = intval($hora_entrada);

                if($mes_entrada == $query_mes_ref){
                    if(1 <= $dia_entrada && $dia_entrada <=7){
                        switch($dia_semana_entrada){
                            case 0:
                                $arr_entrada_domingo_1[$hora_entrada] = $arr_entrada_domingo_1[$hora_entrada] + 1;
                                break;
                            case 1:
                                $arr_entrada_segunda_1[$hora_entrada] = $arr_entrada_segunda_1[$hora_entrada] + 1;
                                break;
                            case 2:
                                $arr_entrada_terca_1[$hora_entrada] = $arr_entrada_terca_1[$hora_entrada] + 1;
                                break;
                            case 3:
                                $arr_entrada_quarta_1[$hora_entrada] = $arr_entrada_quarta_1[$hora_entrada] + 1;
                                break;
                            case 4:
                                $arr_entrada_quinta_1[$hora_entrada] = $arr_entrada_quinta_1[$hora_entrada] + 1;
                                break;
                            case 5:
                                $arr_entrada_sexta_1[$hora_entrada] = $arr_entrada_sexta_1[$hora_entrada] + 1;
                                break;
                            case 6:
                                $arr_entrada_sabado_1[$hora_entrada] = $arr_entrada_sabado_1[$hora_entrada] + 1;
                                break;
                        }
                    }
                    if(8 <= $dia_entrada && $dia_entrada <= 14){
                        switch($dia_semana_entrada){
                            case 0:
                                $arr_entrada_domingo_2[$hora_entrada] = $arr_entrada_domingo_2[$hora_entrada] + 1;
                                break;
                            case 1:
                                $arr_entrada_segunda_2[$hora_entrada] = $arr_entrada_segunda_2[$hora_entrada] + 1;
                                break;
                            case 2:
                                $arr_entrada_terca_2[$hora_entrada] = $arr_entrada_terca_2[$hora_entrada] + 1;
                                break;
                            case 3:
                                $arr_entrada_quarta_2[$hora_entrada] = $arr_entrada_quarta_2[$hora_entrada] + 1;
                                break;
                            case 4:
                                $arr_entrada_quinta_2[$hora_entrada] = $arr_entrada_quinta_2[$hora_entrada] + 1;
                                break;
                            case 5:
                                $arr_entrada_sexta_2[$hora_entrada] = $arr_entrada_sexta_2[$hora_entrada] + 1;
                                break;
                            case 6:
                                $arr_entrada_sabado_2[$hora_entrada] = $arr_entrada_sabado_2[$hora_entrada] + 1;
                                break;
                        }
                    }
                    if(15 <= $dia_entrada && $dia_entrada <= 21){
                        switch($dia_semana_entrada){
                            case 0:
                                $arr_entrada_domingo_3[$hora_entrada] = $arr_entrada_domingo_3[$hora_entrada] + 1;
                                break;
                            case 1:
                                $arr_entrada_segunda_3[$hora_entrada] = $arr_entrada_segunda_3[$hora_entrada] + 1;
                                break;
                            case 2:
                                $arr_entrada_terca_3[$hora_entrada] = $arr_entrada_terca_3[$hora_entrada] + 1;
                                break;
                            case 3:
                                $arr_entrada_quarta_3[$hora_entrada] = $arr_entrada_quarta_3[$hora_entrada] + 1;
                                break;
                            case 4:
                                $arr_entrada_quinta_3[$hora_entrada] = $arr_entrada_quinta_3[$hora_entrada] + 1;
                                break;
                            case 5:
                                $arr_entrada_sexta_3[$hora_entrada] = $arr_entrada_sexta_3[$hora_entrada] + 1;
                                break;
                            case 6:
                                $arr_entrada_sabado_3[$hora_entrada] = $arr_entrada_sabado_3[$hora_entrada] + 1;
                                break;
                        }
                    }
                    if(22 <= $dia_entrada && $dia_entrada <= 28){
                        switch($dia_semana_entrada){
                            case 0:
                                $arr_entrada_domingo_4[$hora_entrada] = $arr_entrada_domingo_4[$hora_entrada] + 1;
                                break;
                            case 1:
                                $arr_entrada_segunda_4[$hora_entrada] = $arr_entrada_segunda_4[$hora_entrada] + 1;
                                break;
                            case 2:
                                $arr_entrada_terca_4[$hora_entrada] = $arr_entrada_terca_4[$hora_entrada] + 1;
                                break;
                            case 3:
                                $arr_entrada_quarta_4[$hora_entrada] = $arr_entrada_quarta_4[$hora_entrada] + 1;
                                break;
                            case 4:
                                $arr_entrada_quinta_4[$hora_entrada] = $arr_entrada_quinta_4[$hora_entrada] + 1;
                                break;
                            case 5:
                                $arr_entrada_sexta_4[$hora_entrada] = $arr_entrada_sexta_4[$hora_entrada] + 1;
                                break;
                            case 6:
                                $arr_entrada_sabado_4[$hora_entrada] = $arr_entrada_sabado_4[$hora_entrada] + 1;
                                break;
                        }
                    }
                    if(29 <= $dia_entrada && $dia_entrada <= 31){
                        switch($dia_semana_entrada){
                            case 0:
                                $arr_entrada_domingo_5[$hora_entrada] = $arr_entrada_domingo_5[$hora_entrada] + 1;
                                break;
                            case 1:
                                $arr_entrada_segunda_5[$hora_entrada] = $arr_entrada_segunda_5[$hora_entrada] + 1;
                                break;
                            case 2:
                                $arr_entrada_terca_5[$hora_entrada] = $arr_entrada_terca_5[$hora_entrada] + 1;
                                break;
                            case 3:
                                $arr_entrada_quarta_5[$hora_entrada] = $arr_entrada_quarta_5[$hora_entrada] + 1;
                                break;
                            case 4:
                                $arr_entrada_quinta_5[$hora_entrada] = $arr_entrada_quinta_5[$hora_entrada] + 1;
                                break;
                            case 5:
                                $arr_entrada_sexta_5[$hora_entrada] = $arr_entrada_sexta_5[$hora_entrada] + 1;
                                break;
                            case 6:
                                $arr_entrada_sabado_5[$hora_entrada] = $arr_entrada_sabado_5[$hora_entrada] + 1;
                                break;
                        }
                    }
                }
                
            }

            while ($r_saida = $stmt_saida->fetch()){

                $dateValue_saida = strtotime($r_saida['timestamp']);   

                $mes_saida = date('m', $dateValue_saida);

                $dia_semana_saida = date('w', $dateValue_saida);
                $dia_semana_saida = intval($dia_semana_saida);

                $dia_saida = date('d', $dateValue_saida);
                $dia_saida = intval($dia_saida);

                $hora_saida = date('H', $dateValue_saida);
                $hora_saida = intval($hora_saida);

                if($mes_saida == $query_mes_ref){
                    if(1 <= $dia_saida && $dia_saida <=7){
                        switch($dia_semana_saida){
                            case 0:
                                $arr_saida_domingo_1[$hora_saida] = $arr_saida_domingo_1[$hora_saida] + 1;
                                break;
                            case 1:
                                $arr_saida_segunda_1[$hora_saida] = $arr_saida_segunda_1[$hora_saida] + 1;
                                break;
                            case 2:
                                $arr_saida_terca_1[$hora_saida] = $arr_saida_terca_1[$hora_saida] + 1;
                                break;
                                $arr_saida_quarta_1[$hora_saida] = $arr_saida_quarta_1[$hora_saida] + 1;
                            case 3:
                                break;
                            case 4:
                                $arr_saida_quinta_1[$hora_saida] = $arr_saida_quinta_1[$hora_saida] + 1;
                                break;
                            case 5:
                                $arr_saida_sexta_1[$hora_saida] = $arr_saida_sexta_1[$hora_saida] + 1;
                                break;
                            case 6:
                                $arr_saida_sabado_1[$hora_saida] = $arr_saida_sabado_1[$hora_saida] + 1;
                                break;
                        }
                    }
                    if(8 <= $dia_saida && $dia_saida <= 14){
                        switch($dia_semana_entrada){
                            case 0:
                                $arr_saida_domingo_2[$hora_saida] = $arr_saida_domingo_2[$hora_saida] + 1;
                                break;
                            case 1:
                                $arr_saida_segunda_2[$hora_saida] = $arr_saida_segunda_2[$hora_saida] + 1;
                                break;
                            case 2:
                                $arr_saida_terca_2[$hora_saida] = $arr_saida_terca_2[$hora_saida] + 1;
                                break;
                            case 3:
                                $arr_saida_quarta_2[$hora_saida] = $arr_saida_quarta_2[$hora_saida] + 1;
                                break;
                            case 4:
                                $arr_saida_quinta_2[$hora_saida] = $arr_saida_quinta_2[$hora_saida] + 1;
                                break;
                            case 5:
                                $arr_saida_sexta_2[$hora_saida] = $arr_saida_sexta_2[$hora_saida] + 1;
                                break;
                            case 6:
                                $arr_saida_sabado_2[$hora_saida] = $arr_saida_sabado_2[$hora_saida] + 1;
                                break;
                        }
                    }
                    if(15 <= $dia_saida && $dia_saida <= 21){
                        switch($dia_semana_saida){
                            case 0:
                                $arr_saida_domingo_3[$hora_saida] = $arr_saida_domingo_3[$hora_saida] + 1;
                                break;
                            case 1:
                                $arr_saida_segunda_3[$hora_saida] = $arr_saida_segunda_3[$hora_saida] + 1;
                                break;
                            case 2:
                                $arr_saida_terca_3[$hora_saida] = $arr_saida_terca_3[$hora_saida] + 1;
                                break;
                            case 3:
                                $arr_saida_quarta_3[$hora_saida] = $arr_saida_quarta_3[$hora_saida] + 1;
                                break;
                            case 4:
                                $arr_saida_quinta_3[$hora_saida] = $arr_saida_quinta_3[$hora_saida] + 1;
                                break;
                            case 5:
                                $arr_saida_sexta_3[$hora_saida] = $arr_saida_sexta_3[$hora_saida] + 1;
                                break;
                            case 6:
                                $arr_saida_sabado_3[$hora_saida] = $arr_saida_sabado_3[$hora_saida] + 1;
                                break;
                        }
                    }
                    if(22 <= $dia_saida && $dia_saida <= 28){
                        switch($dia_semana_saida){
                            case 0:
                                $arr_saida_domingo_4[$hora_saida] = $arr_saida_domingo_4[$hora_saida] + 1;
                                break;
                            case 1:
                                $arr_saida_segunda_4[$hora_saida] = $arr_saida_segunda_4[$hora_saida] + 1;
                                break;
                            case 2:
                                $arr_saida_terca_4[$hora_saida] = $arr_saida_terca_4[$hora_saida] + 1;
                                break;
                            case 3:
                                $arr_saida_quarta_4[$hora_saida] = $arr_saida_quarta_4[$hora_saida] + 1;
                                break;
                            case 4:
                                $arr_saida_quinta_4[$hora_saida] = $arr_saida_quinta_4[$hora_saida] + 1;
                                break;
                            case 5:
                                $arr_saida_sexta_4[$hora_saida] = $arr_saida_sexta_4[$hora_saida] + 1;
                                break;
                            case 6:
                                $arr_saida_sabado_4[$hora_saida] = $arr_saida_sabado_4[$hora_saida] + 1;
                                break;
                        }
                    }
                    if(29 <= $dia_saida && $dia_saida <= 31){
                        switch($dia_semana_saida){
                            case 0:
                                $arr_saida_domingo_5[$hora_saida] = $arr_saida_domingo_5[$hora_saida] + 1;
                                break;
                            case 1:
                                $arr_saida_segunda_5[$hora_saida] = $arr_saida_segunda_5[$hora_saida] + 1;
                                break;
                            case 2:
                                $arr_saida_terca_5[$hora_saida] = $arr_saida_terca_5[$hora_saida] + 1;
                                break;
                            case 3:
                                $arr_saida_quarta_5[$hora_saida] = $arr_saida_quarta_5[$hora_saida] + 1;
                                break;
                            case 4:
                                $arr_saida_quinta_5[$hora_saida] = $arr_saida_quinta_5[$hora_saida] + 1;
                                break;
                            case 5:
                                $arr_saida_sexta_5[$hora_saida] = $arr_saida_sexta_5[$hora_saida] + 1;
                                break;
                            case 6:
                                $arr_saida_sabado_5[$hora_saida] = $arr_saida_sabado_5[$hora_saida] + 1;
                                break;
                        }
                    }
                }
                
            }

            $arr_segunda_1 = array(
                'entrada' => $arr_entrada_segunda_1,
                'saida' => $arr_saida_segunda_1
            );
            $arr_terca_1 = array(
                'entrada' => $arr_entrada_terca_1,
                'saida' => $arr_saida_terca_1
            );
            $arr_quarta_1 = array(
                'entrada' => $arr_entrada_quarta_1,
                'saida' => $arr_saida_quarta_1
            );
            $arr_quinta_1 = array(
                'entrada' => $arr_entrada_quinta_1,
                'saida' => $arr_saida_quinta_1
            );
            $arr_sexta_1 = array(
                'entrada' => $arr_entrada_sexta_1,
                'saida' => $arr_saida_sexta_1
            );
            $arr_sabado_1 = array(
                'entrada' => $arr_entrada_sabado_1,
                'saida' => $arr_saida_sabado_1
            );
            $arr_domingo_1 = array(
                'entrada' => $arr_entrada_domingo_1,
                'saida' => $arr_saida_domingo_1
            );
            $resposta_1 = array(
                'segunda' => $arr_segunda_1,
                'terca' => $arr_terca_1,
                'quarta' => $arr_quarta_1,
                'quinta' => $arr_quinta_1,
                'sexta' => $arr_sexta_1,
                'sabado' => $arr_sabado_1,
                'domingo' => $arr_domingo_1
            );


            $arr_segunda_2 = array(
                'entrada' => $arr_entrada_segunda_2,
                'saida' => $arr_saida_segunda_2
            );
            $arr_terca_2 = array(
                'entrada' => $arr_entrada_terca_2,
                'saida' => $arr_saida_terca_2
            );
            $arr_quarta_2 = array(
                'entrada' => $arr_entrada_quarta_2,
                'saida' => $arr_saida_quarta_2
            );
            $arr_quinta_2 = array(
                'entrada' => $arr_entrada_quinta_2,
                'saida' => $arr_saida_quinta_2
            );
            $arr_sexta_2 = array(
                'entrada' => $arr_entrada_sexta_2,
                'saida' => $arr_saida_sexta_2
            );
            $arr_sabado_2 = array(
                'entrada' => $arr_entrada_sabado_2,
                'saida' => $arr_saida_sabado_2
            );
            $arr_domingo_2 = array(
                'entrada' => $arr_entrada_domingo_2,
                'saida' => $arr_saida_domingo_2
            );
            $resposta_2 = array(
                'segunda' => $arr_segunda_2,
                'terca' => $arr_terca_2,
                'quarta' => $arr_quarta_2,
                'quinta' => $arr_quinta_2,
                'sexta' => $arr_sexta_2,
                'sabado' => $arr_sabado_2,
                'domingo' => $arr_domingo_2
            );

            $arr_segunda_3 = array(
                'entrada' => $arr_entrada_segunda_3,
                'saida' => $arr_saida_segunda_3
            );
            $arr_terca_3 = array(
                'entrada' => $arr_entrada_terca_3,
                'saida' => $arr_saida_terca_3
            );
            $arr_quarta_3 = array(
                'entrada' => $arr_entrada_quarta_3,
                'saida' => $arr_saida_quarta_3
            );
            $arr_quinta_3 = array(
                'entrada' => $arr_entrada_quinta_3,
                'saida' => $arr_saida_quinta_3
            );
            $arr_sexta_3 = array(
                'entrada' => $arr_entrada_sexta_3,
                'saida' => $arr_saida_sexta_3
            );
            $arr_sabado_3 = array(
                'entrada' => $arr_entrada_sabado_3,
                'saida' => $arr_saida_sabado_3
            );
            $arr_domingo_3 = array(
                'entrada' => $arr_entrada_domingo_3,
                'saida' => $arr_saida_domingo_3
            );
            $resposta_3 = array(
                'segunda' => $arr_segunda_3,
                'terca' => $arr_terca_3,
                'quarta' => $arr_quarta_3,
                'quinta' => $arr_quinta_3,
                'sexta' => $arr_sexta_3,
                'sabado' => $arr_sabado_3,
                'domingo' => $arr_domingo_3
            );

            $arr_segunda_4 = array(
                'entrada' => $arr_entrada_segunda_4,
                'saida' => $arr_saida_segunda_4
            );
            $arr_terca_4 = array(
                'entrada' => $arr_entrada_terca_4,
                'saida' => $arr_saida_terca_4
            );
            $arr_quarta_4 = array(
                'entrada' => $arr_entrada_quarta_4,
                'saida' => $arr_saida_quarta_4
            );
            $arr_quinta_4 = array(
                'entrada' => $arr_entrada_quinta_4,
                'saida' => $arr_saida_quinta_4
            );
            $arr_sexta_4 = array(
                'entrada' => $arr_entrada_sexta_4,
                'saida' => $arr_saida_sexta_4
            );
            $arr_sabado_4 = array(
                'entrada' => $arr_entrada_sabado_4,
                'saida' => $arr_saida_sabado_4
            );
            $arr_domingo_4 = array(
                'entrada' => $arr_entrada_domingo_4,
                'saida' => $arr_saida_domingo_4
            );
            $resposta_4 = array(
                'segunda' => $arr_segunda_4,
                'terca' => $arr_terca_4,
                'quarta' => $arr_quarta_4,
                'quinta' => $arr_quinta_4,
                'sexta' => $arr_sexta_4,
                'sabado' => $arr_sabado_4,
                'domingo' => $arr_domingo_4
            );

            $arr_segunda_5 = array(
                'entrada' => $arr_entrada_segunda_5,
                'saida' => $arr_saida_segunda_5
            );
            $arr_terca_5 = array(
                'entrada' => $arr_entrada_terca_5,
                'saida' => $arr_saida_terca_5
            );
            $arr_quarta_5 = array(
                'entrada' => $arr_entrada_quarta_5,
                'saida' => $arr_saida_quarta_5
            );
            $arr_quinta_5 = array(
                'entrada' => $arr_entrada_quinta_5,
                'saida' => $arr_saida_quinta_5
            );
            $arr_sexta_5 = array(
                'entrada' => $arr_entrada_sexta_5,
                'saida' => $arr_saida_sexta_5
            );
            $arr_sabado_5 = array(
                'entrada' => $arr_entrada_sabado_5,
                'saida' => $arr_saida_sabado_5
            );
            $arr_domingo_5 = array(
                'entrada' => $arr_entrada_domingo_5,
                'saida' => $arr_saida_domingo_5
            );
            $resposta_5 = array(
                'segunda' => $arr_segunda_5,
                'terca' => $arr_terca_5,
                'quarta' => $arr_quarta_5,
                'quinta' => $arr_quinta_5,
                'sexta' => $arr_sexta_5,
                'sabado' => $arr_sabado_5,
                'domingo' => $arr_domingo_5
            );

            $resposta_final = array(
                '1' => $resposta_1,
                '2' => $resposta_2,
                '3' => $resposta_3,
                '4' => $resposta_4,
                '5' => $resposta_5
            );
            
            $arrayResp = array($resposta_final);
            $corpoRespEntrada =  json_encode($arrayResp);
            $response = $response->withHeader('Content-type', 'application/json')
            ->write( $corpoRespEntrada );

        }catch (BadHttpRequest $e) {
            $status = 400;
            $response->write('Exceção capturada: '.  $e->getMessage(). '\n');
        } catch (\PDOException $e) {
            $status = 500;
            $response->write('Exceção capturada: '.  $e->getMessage(). '\n');
        }

        return $response->withStatus($status);

    }

    public function findByWeek( Request $request, Response $response, array $args, $id, $timestamp_query )
    {
        $status = 200;
        require("../model/connection.php");
        try{

            $query_sem_ref = date("W", $timestamp_query);
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
            $arr_entrada_domingo = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_entrada_segunda = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_entrada_terca = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_entrada_quarta = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_entrada_quinta = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_entrada_sexta = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_entrada_sabado = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            

            $stmt_saida = $pdo->query($sql_saida);
            $stmt_saida->setFetchMode(PDO::FETCH_ASSOC);
            $arr_saida_domingo = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_saida_segunda = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_saida_terca = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_saida_quarta = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_saida_quinta = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_saida_sexta = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
            $arr_saida_sabado = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);


            while ($r_entrada = $stmt_entrada->fetch()){

                $dateValue_entrada = strtotime($r_entrada['timestamp']);   
                //pega um valor de 0 a 6 referente ao dia da semana 0=domingo     
                $dia_semana_entrada = date('w', $dateValue_entrada);
                $dia_semana_entrada = intval($dia_semana_entrada);

                $hora_entrada = date('H', $dateValue_entrada);
                $hora_entrada = intval($hora_entrada);

                $numero_semana_entrada = date('W', $dateValue_entrada);

                if($numero_semana_entrada == $query_sem_ref){
                    switch($dia_semana_entrada){
                        case 0:
                            $arr_entrada_domingo[$hora_entrada] = $arr_entrada_domingo[$hora_entrada] + 1;
                            break;
                        case 1:
                            $arr_entrada_segunda[$hora_entrada] = $arr_entrada_segunda[$hora_entrada] + 1;
                            break;
                        case 2:
                            $arr_entrada_terca[$hora_entrada] = $arr_entrada_terca[$hora_entrada] + 1;
                            break;
                        case 3:
                            $arr_entrada_quarta[$hora_entrada] = $arr_entrada_quarta[$hora_entrada] + 1;
                            break;
                        case 4:
                            $arr_entrada_quinta[$hora_entrada] = $arr_entrada_quinta[$hora_entrada] + 1;
                            break;
                        case 5:
                            $arr_entrada_sexta[$hora_entrada] = $arr_entrada_sexta[$hora_entrada] + 1;
                            break;
                        case 6:
                            $arr_entrada_sabado[$hora_entrada] = $arr_entrada_sabado[$hora_entrada] + 1;
                            break;
                    }
                }
                
            }

            while ($r_saida = $stmt_saida->fetch()){

                $dateValue_saida = strtotime($r_saida['timestamp']);   
                //pega um valor de 0 a 6 referente ao dia da semana 0=domingo     
                $dia_semana_saida = date('w', $dateValue_saida);
                $dia_semana_saida = intval($dia_semana_saida);

                $hora_saida = date('H', $dateValue_saida);
                $hora_saida = intval($hora_saida);

                $numero_semana_saida = date('W', $dateValue_saida);

                if($numero_semana_saida == $query_sem_ref){
                    switch($dia_semana_saida){
                        case 0:
                            $arr_saida_domingo[$hora_saida] = $arr_saida_domingo[$hora_saida] + 1;
                            break;
                        case 1:
                            $arr_saida_segunda[$hora_saida] = $arr_saida_segunda[$hora_saida] + 1;
                            break;
                        case 2:
                            $arr_saida_terca[$hora_saida] = $arr_saida_terca[$hora_saida] + 1;
                            break;
                        case 3:
                            $arr_saida_quarta[$hora_saida] = $arr_saida_quarta[$hora_saida] + 1;
                            break;
                        case 4:
                            $arr_saida_quinta[$hora_saida] = $arr_saida_quinta[$hora_saida] + 1;
                            break;
                        case 5:
                            $arr_saida_sexta[$hora_saida] = $arr_saida_sexta[$hora_saida] + 1;
                            break;
                        case 6:
                            $arr_saida_sabado[$hora_saida] = $arr_saida_sabado[$hora_saida] + 1;
                            break;
                    }
                }
            }
            
            //$res = new \stdClass();

            $arr_segunda = array(
                'entrada' => $arr_entrada_segunda,
                'saida' => $arr_saida_segunda
            );
            $arr_terca = array(
                'entrada' => $arr_entrada_terca,
                'saida' => $arr_saida_terca
            );
            $arr_quarta = array(
                'entrada' => $arr_entrada_quarta,
                'saida' => $arr_saida_quarta
            );
            $arr_quinta = array(
                'entrada' => $arr_entrada_quinta,
                'saida' => $arr_saida_quinta
            );
            $arr_sexta = array(
                'entrada' => $arr_entrada_sexta,
                'saida' => $arr_saida_sexta
            );
            $arr_sabado = array(
                'entrada' => $arr_entrada_sabado,
                'saida' => $arr_saida_sabado
            );
            $arr_domingo = array(
                'entrada' => $arr_entrada_domingo,
                'saida' => $arr_saida_domingo
            );
            $resposta = array(
                'segunda' => $arr_segunda,
                'terca' => $arr_terca,
                'quarta' => $arr_quarta,
                'quinta' => $arr_quinta,
                'sexta' => $arr_sexta,
                'sabado' => $arr_sabado,
                'domingo' => $arr_domingo
            );

            $arrayResp = array($resposta);
            $corpoRespEntrada =  json_encode($arrayResp);
            $response = $response->withHeader('Content-type', 'application/json')
            ->write( $corpoRespEntrada );

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