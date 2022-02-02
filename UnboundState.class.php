<?php

require_once("././base/HasIdAndName.class.php");

class UnboundState extends HasIdAndName {

	private $state;

	public function __construct($id, $name, $state = null) {
		parent::__construct($id, $name);

		$this->state = $state;
	}

	static public function createStateCreationFormBuilder($clazz, $state = null) {
		$builder = CreationFormBuilder::createForState($clazz, $state);
		$builder->addClass("state");

		$builder->addLabel("stateName", "Name");
		$builder->addInput("stateName", "text", is_null($state) ? null : $state->getName());

		$builder->addLabel("state", "Zustand");
		$builder->addInput("state", "text", is_null($state) ? "" : $state->getState(), ["placeholder" => "z.B. '21.0 Grad'"]);
	
		$builder->addSubmit(is_null($state) ? "Erzeugen" : "Änderung speichern");
		return $builder;
	}	

	static public function createByFormResponse($response) {
		$stateId = -1;
		if (isset($response["stateId"]) && is_numeric($response["stateId"])) {
			$stateId = $response["stateId"];
		}
		return new UnboundState($stateId, $response["stateName"], $response["state"]);
	}

	final public function getState() {
		return $this->state;
	}

	final public function setState($state) {
		$this->state = $state;
		return true;
	}

}

?>