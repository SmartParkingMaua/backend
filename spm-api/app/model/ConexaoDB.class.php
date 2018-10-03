<?php 

namespace app\model;

# Adicionado pois não encontra o PDO sem o namespace.
use \PDO;

class ConexaoDB {
	private $nomeServer;
	private $usuario;
	private $senha;
	private $dataBase;
    private $charset;
    public 	$con;

	function __construct() {

		$this->nomeServer = "localhost";
		$this->usuario = "root";
		$this->senha = "";
        $this->dataBase = "mydb";
        $this->charset = 'utf8mb4';
        $dsn = "mysql:host=$this->nomeServer;dbname=$this->dataBase;charset=$this->charset";

        $this->con = new PDO($dsn, $this->usuario, $this->senha);
	}
}