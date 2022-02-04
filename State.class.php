<?php

require_once("././base/HasIdAndName.class.php");

class State extends HasIdAndName {

	private $states;
	private $state;

	public function __construct($id, $name, $states = [], $state = null) {
		parent::__construct($id, $name);

		$this->setStates($states);

		if ($state === null || !setState($state)) {
			$this->state = $this->states[0];
		}
	}

	static public function createStateCreationFormBuilder($clazz, $state = null) {
		$builder = CreationFormBuilder::createForState($clazz, $state);
		$builder->addClass("state");

		$builder->addLabel("stateName", "Name");
		$builder->addInput("stateName", "text", is_null($state) ? null : $state->getName());

		$builder->addLabel("state", "Aktueller Zustand");
		$builder->addInput("state", "text", is_null($state) ? null : $state->getState());

		if($state === null) {
			$builder->startMultipleFirst();
			$builder->addLabel("states[]", "Zustand");
			$builder->addInput("states[]", "text", "", ["placeholder" => "z.B. 'Eingeschaltet'"]);
			$builder->endMultipleFirst();
		} else {
			$first = true;
			foreach ($state->getStates() as $aState) {
				$builder->startMultipleElement($first);
				$builder->addLabel("states[]", "Zustand");
				$builder->addInput("states[]", "text", $aState);
				$builder->endMultipleElement($first);
				$first = false;
			}
		}
		$builder->endMultiple();

		$builder->addSubmit(is_null($state) ? "Erzeugen" : "Änderung speichern");
		return $builder;
	}	

	static public function createByFormResponse($response) {
		$stateId = -1;
		if (isset($response["stateId"]) && is_numeric($response["stateId"])) {
			$stateId = $response["stateId"];
		}
		$state = new State($stateId, $response["stateName"], $response["states"]);
		if (isset($response["state"])) {
			$state->setState($response["state"]);
		}
		return $state;
	}

	final public function addState($newState = null){
		if($newState !== null) {
			$this->states[] = $newState;
		}
	}

	final public function setStates($states = []){
		if($states === null || !is_array($states) || sizeof($states) < 1){
			$states = [0 => "off", 1 => "on"];
		}
		$this->states = $states;

		//check if old state is still in our new states
		if(!in_array($this->state, $this->states)) {
			$this->setState($this->states[0]);
		}
	}

	final public function getStates() {
		return $this->states;
	}

	final public function getState() {
		return $this->state;
	}

	final public function setState($state) {
		if(in_array($state, $this->states)) {
			$this->state = $state;
			return true;
		}
		return false;
	}

}

?>