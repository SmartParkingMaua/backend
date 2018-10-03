<?php

namespace app\model;

use app\classes\Estacionamento;

Interface EstacionamentoDAOInterface {

    public function getAllEstacionamentos( );
    public function getEstacionamentoById($idEstacionamento);

}