<?php

namespace app\model;

use app\classes\Estacionamento;
use app\model\EstacionamentoDAOInterface;
use app\model\ConexaoDB;

class EstacionamentoDAOImplementation implements EstacionamentoDAOInterface
{

    public function getAllEstacionamentos()
    {
        $connDB = new ConexaoDB();
        $stmt = $connDB->con->prepare("SELECT idbp, nome FROM tbl_atual ");
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $arrayestacionamentos = null;

        if($stmt->rowCount() > 0){
            $arrayestacionamentos = array();
            foreach ($result as $row){
                $estacionamentoTemp = new Estacionamento($row);
                array_push($arrayestacionamentos, $estacionamentoTemp);
            }
        };
        
        return $arrayestacionamentos;
    }

    public function getEstacionamentoById($idEstacionamento)
    {

    }
}