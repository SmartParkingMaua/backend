<?php

namespace app\classes;

class Estacionamento implements \JsonSerializable
{
    private $idEstacionamento;
    private $timestampRef;
    private $acao;

    function __construct( $argsDB = null )
    {
        if ( !is_null($argsDB) ) {
            $this->idEstacionamento = (isset($argsDB["idEstacionamento"])) ? $argsDB["idEstacionamento"] : null ;
            $this->timestampRef = $argsDB["timestamp"];
            $this->acao = $argsDB["estado"];
        }
    }

    public function getIdEstacionamento() {
        return $this->idEstacionamento;
    }

    public function setIdEstacionamento( $idEstacionamento ) {
        $this->idEstacionamento = $idEstacionamento;
    }

    public function getTimestampRef() {
        return $this->timestampRef;
    }
    
    public function setTimestampRef( $timestampRef ) {
        $this->timestampRef = $timestampRef;
    }
    
    public function getAcao() {
        return $this->acao;
    }
    
    public function setAcao( $acao ) {
        $this->acao = $acao;
    }
                                                                                                
    function __toString() {
        return json_encode( array( 'idEstacionamento' => $this->idEstacionamento,
                        'timestamp' => $this->timestampRef,
                        'estado' => $this->acao));
    }
    
    public function jsonSerialize()
    {
        return array( 'idEstacionamento' => $this->idEstacionamento,
                        'nome' => $this->nome,
                        'estado' => $this->acao);
    }


}