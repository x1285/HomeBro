<?php

require_once("././base/HasIdAndName.class.php");
require_once("CreationFormBuilder.class.php");

abstract class Action extends HasIdAndName {

	protected $skipBefore = false;
	protected $skipAfter = false;

	protected function __construct($id, $name) {
		parent::__construct($id, $name);
	}

	final public function run(){

		if(!$this->skipBefore){ $this->before(); }

		$this->main();

		if(!$this->skipAfter){ $this->after(); }

	}

	static abstract public function createActionCreationFormBuilder($clazz, $action = null);

	static abstract public function createByFormResponse($response);

	abstract protected function before();

	abstract protected function main();

	abstract protected function after();

	public function skipBefore($skip = true){
		$this->skipBefore = $skip;
	}

	public function skipAfter($skip = true){
		$this->skipAfter = $skip;
	}

}

?>