<?php

namespace app\classes;

class Estacionamento implements \JsonSerializable
{
    private $idbolsao;
    private $timestamp;
    private $acao;

    function __construct($argsDB)
    {
        if ( !is_null($argsDB) ) {
            $this->idbolsao = $argsDB["idbolsao"];
            $this->timestamp = $argsDB["timestamp"];
            $this->acao = $argsDB["acao"];
        }
    }

    public function getIdBolsao() {
        return $this->idbolsao;
    }

    public function setIdBolsao( $idbolsao ) {
        $this->idbolsao = $idbolsao;
    }
    
    public function getTimestamp() {
        return $this->timestamp;
    }

    public function setTimestamp( $timestamp ) {
        $this->timestamp = $timestamp;
    }

    public function getAcao() {
        return $this->acao;
    }

    public function setAcao( $acao ) {
        $this->acao = $acao;
    }

    function __toString() {
        return json_encode( array( 'idbolsao' => $this->idbolsao,
                        'timestamp' => $this->timestamp,
                        'acao' => $this->acao));
    }
    
    public function jsonSerialize()
    {
        return array( 'idbolsao' => $this->idbolsao,
                    'timestamp' => $this->timestamp,
                    'acao' => $this->acao);
    }


}