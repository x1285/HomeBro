<?php

abstract class HasId {

	private $id;

	protected function __construct($id) {
		$this->setId($id);
	}

	final public function setId($id){
		$this->id = $id;
	}

	final public function getId(){
		return $this->id;
	}

}

?>