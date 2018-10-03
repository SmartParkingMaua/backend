<?php

namespace app\classes;

class Estacionamento implements \JsonSerializable
{
    private $idEstacionamento;
    private $nome;

    function __construct($argsDB)
    {
        if ( !is_null($argsDB) ) {
            $this->idEstacionamento = $argsDB["idbp"];
            $this->nome = $argsDB["nome"];
        }
    }

    public function getIdEstacionamento() {
        return $this->idEstacionamento;
    }

    public function setIdEstacionamento( $idEstacionamento ) {
        $this->idEstacionamento = $idEstacionamento;
    }
                                                                                            
    function __toString() {
        return json_encode( array( 'idEstacionamento' => $this->idEstacionamento,
                        'nome' => $this->nome));
    }
    
    public function jsonSerialize()
    {
        return array( 'idEstacionamento' => $this->idEstacionamento,
                        'nome' => $this->nome);
    }


}