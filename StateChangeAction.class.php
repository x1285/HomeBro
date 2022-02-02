<?php

require_once("Action.class.php");

class StateChangeAction extends Action {

	private $stateId = null;
	private $defaultStateChange = null;
	private $stateChanges = [];

	public function __construct($id, $name, $stateId = null, $defaultStateChange = null, $stateChanges = []) {
		parent::__construct($id, $name);

		$this->setStateId($stateId);
		if($stateChanges !== null) {
			$this->stateChanges = $stateChanges;
		}
		$this->defaultStateChange = $defaultStateChange;
	}

	static public function createActionCreationFormBuilder($clazz, $action = null){
		$builder = CreationFormBuilder::createForAction($clazz, $action);
		$builder->addClass("statechange");

		$builder->addLabel("actionName", "Name");
		$builder->addInput("actionName", "text", is_null($action) ? null : $action->getName());

		$states = StateManager::get()->getListOfAllStateIdsAndNames();
		$builder->addLabel("state", "Zustand");
		$builder->addSelection("state", $states, is_null($action) ? [] : [$action->getStateId() => $states[$action->getStateId()]]);

		$ifStateAttr = ["placeholder" => "z.B. AN"];
		$thenStateAttr = ["placeholder" => "z.B. AUS"];

		$builder->addLabel("defaultThenState", "Standardmäßig ändern zu");
		$builder->addInput("defaultThenState", "text", is_null($action) ? null : $action->getDefaultStateChange(), $thenStateAttr);

		if (is_null($action) || sizeof($action->getStateChanges()) == 0) {
			$builder->startMultipleFirst();

			$builder->addLabel("ifState[]", "Wenn");
			$builder->addInput("ifState[]", "text", null, $ifStateAttr);

			$builder->addLabel("thenState[]", "ändern zu");
			$builder->addInput("thenState[]", "text", null, $thenStateAttr);
			
			$builder->endMultipleFirst();
		} else {
			$first = true;
			foreach ($action->getStateChanges() as $ifState => $thenState) {
				$builder->startMultipleElement($first);

				$builder->addLabel("ifState[]", "Wenn");
				$builder->addInput("ifState[]", "text", $ifState, $ifStateAttr);

				$builder->addLabel("thenState[]", "ändern zu");
				$builder->addInput("thenState[]", "text", $thenState, $thenStateAttr);

				$builder->endMultipleElement($first);
				$first = false;
			}
		}
		$builder->endMultiple();

		$builder->addSubmit(is_null($action) || is_null($action->getId()) ? "Erzeugen" : "Änderung speichern");
		return $builder;
	}

	static public function createByFormResponse($response) {
		$actionId = -1;
		if (isset($response["actionId"]) && is_numeric($response["actionId"])) {
			$actionId = $response["actionId"];
		}
		$name = $response["actionName"];
		$state = $response["state"];
		$defaultStateChange = $response["defaultThenState"];
		$stateChanges = array_combine($response["ifState"], $response["thenState"]);

		$action = new StateChangeAction($actionId, $name, $state, $defaultStateChange, $stateChanges);
		return $action;
	}

	protected function before() {
		//empty
	}

	protected function main() {
		$stateManager = StateManager::get();
		$stateObj = $stateManager->getStateById($this->stateId);
		$oldState = $stateObj->getState();
		if(array_key_exists($oldState, $this->stateChanges)){
			$newState = $this->stateChanges[$oldState];
			if($stateObj->setState($newState)) {
				$stateManager->save($stateObj);
			}
		} elseif ($this->defaultStateChange !== null) {
			if($stateObj->setState($this->defaultStateChange)) {
				$stateManager->save($stateObj);
			}
		}
	}

	protected function after() {
		//empty
	}

	public function setStateId($stateId = null){
		if($stateId === null) {
			die("A StateChangeAction needs a valid stateId");
		}
		$this->stateId = $stateId;
	}

	public function getStateId(){
		return $this->stateId;
	}

	public function setDefaultStateChange($defaultStateChange = null){
		$this->defaultStateChange = $defaultStateChange;
	}

	public function getDefaultStateChange(){
		return $this->defaultStateChange;
	}

	public function setStateChanges($stateChanges = []){
		$this->stateChanges = $stateChanges;
	}

	public function addStateChange($ifState = null, $thenState = null){
		if($ifState === null) {
			die("'addStateChange' of StateChangeAction needs a valid ifState as first parameter.");
		}
		$this->stateChanges[$ifState] = $thenState;
	}

	public function getStateChanges() {
		return $this->stateChanges;
	}

	public function getStateChange($ifState){
		return $this->stateChanges[$ifState];
	}

}

?>