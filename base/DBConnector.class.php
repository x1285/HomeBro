<?php

class DBConnector {

	private $servername = "127.0.0.1";
	private $username = "username";
	private $password = null;
	private $dbname = "dbname";
	//private $dbport = 43306;
	private $connection;

	function __construct(){
		$this->connection = new mysqli($this->servername, $this->username, $this->password, $this->dbname);//, $this->dbport);
		if($this->connection->connect_error){
			die("DB Connection failed: ".$this->connection->connect_error);
		}
	}

	function prepare($stmt){
		return $this->connection->prepare($stmt);
	}

	function query($sql){
		return $this->connection->query($sql);
	}

	function close(){
		$this->connection->close();
	}

}

?>