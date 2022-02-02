<?php

require_once("HasId.class.php");

abstract class HasIdAndName extends HasId {

	private $name;

	protected function __construct($id, $name) {
		parent::__construct($id);
		$this->setName($name);
	}

	final public function setName($name){
		$this->name = $name;
	}

	final public function getName(){
		return $this->name;
	}

}

?>