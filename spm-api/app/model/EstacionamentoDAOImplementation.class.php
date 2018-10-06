<?php

namespace app\model;

include('../app/model/EstacionamentoDAOInterface.class.php');
include('../app/model/ConexaoDB.class.php');
include('../app/classes/EstacionamentoAtual.class.php');

use app\classes\EstacionamentoAtual;
use app\model\EstacionamentoDAOInterface;
use app\model\ConexaoDB;

class EstacionamentoDAOImplementation implements EstacionamentoDAOInterface
{

    public function getAllEstacionamentos()
    {
        $connDB = new ConexaoDB();
        $stmt = $connDB->con->prepare("SELECT * FROM tbl_atual ");
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $arrayestacionamentos = null;

        if($stmt->rowCount() > 0){
            $arrayestacionamentos = array();
            foreach ($result as $row){
                $estacionamentoTemp = new EstacionamentoAtual($row);
                array_push($arrayestacionamentos, $estacionamentoTemp);
            }
        };
        
        return $arrayestacionamentos;
    }
/*
    public function createEstacionamento(Estacionamento $estacionamentoInst)
    {
        $connDB = new ConexaoDB();

        $id = $estacionamentoInst->getIdEstacionamento();




        $stmt = $connDB->con->prepare("INSERT INTO tbl_bolos (nome, sabor, cobertura, descricao) VALUES (:NOME, :SABOR, :COBERTURA, :DESCRICAO)");

        $stmt->bindParam(":NOME", $nome);
        $stmt->bindParam(":SABOR", $sabor);
        $stmt->bindParam(":COBERTURA", $cobertura);
        $stmt->bindParam(":DESCRICAO", $desc);

        $nome = $boloInstancia->getNome();
        $sabor = $boloInstancia->getSabor();
        $cobertura = $boloInstancia->getCobertura();
        $desc = $boloInstancia->getDescricao();
        $stmt->execute();

    }
    */
}