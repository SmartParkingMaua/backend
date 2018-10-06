<?php

namespace app\classes;

class EstacionamentoAtual implements \JsonSerializable
{
    private $idEstacionamento;
    private $nome;
    private $vagas_ocupadas;
    private $max_vagas;

    function __construct($argsDB)
    {
        if ( !is_null($argsDB) ) {
            $this->idEstacionamento = $argsDB["idbp"];
            $this->nome = $argsDB["nome"];
            $this->vagas_ocupadas = $argsDB["vagas_ocupadas"];
            $this->max_vagas = $argsDB["max_vagas"];
        }
    }

    public function getIdEstacionamento() {
        return $this->idEstacionamento;
    }

    public function setIdEstacionamento( $idEstacionamento ) {
        $this->idEstacionamento = $idEstacionamento;
    }

    public function getVagasOcupadas(){
        return $this->vagas_ocupadas = $vagas_ocupadas;
    }

    public function setVagasOcupadas($vagas_ocupadas){
        $this->vagas_ocupadas = $vagas_ocupadas;
    }

    public function getMaxVagas(){
        return $this->max_vagas = $max_vagas;
    }

    public function setMaxVagas($max_vagas){
        $this->max_vagas = $max_vagas;
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