<?php

require_once("Action.class.php");
require_once("ActionManager.class.php");

class StateDependingAction extends Action {

	private $stateId;

	private $statesAndActions;
	private $statesAndStateChanges;

	private $DEFAULT_ACTION = null;
	private $DEFAULT_STATE_CHANGE = null;

	public function __construct($id, $name, 
								$defaultAction = null, $defaultStateChange = null, 
								$stateId = null, $statesAndActions = [],
								$statesAndStateChanges) {
		parent::__construct($id, $name);

		if($defaultAction === null) {
			die("StateDependingAction needs a default action.");
		}
		$this->setDefaultAction($defaultAction);
		$this->setDefaultStateChange($defaultStateChange);
		$this->setStateId($stateId);
		$this->setStatesAndActions($statesAndActions);
		$this->setStatesAndStateChanges($statesAndStateChanges);
	}

	static public function createActionCreationFormBuilder($clazz, $action = null){
		$builder = CreationFormBuilder::createForAction($clazz, $action);
		$builder->addClass("shell");

		$builder->addLabel("actionName", "Name");
		$builder->addInput("actionName", "text", is_null($action) ? null : $action->getName());

		$stateOptions = StateManager::get()->getListOfAllStateIdsAndNames();
		$builder->addLabel("stateId", "Zusammenhängender Zustand");
		$builder->addSelection("stateId", 
			$stateOptions, 
			is_null($action) ? null : [$action->getStateId() => $stateOptions[$action->getStateId()]]);

		$actionOptions = ActionManager::get()->getListOfAllActionIdsAndNames();
		$stateAttr = ["placeholder" => "z.B. AUS"];
		$stateChangeAttr = ["placeholder" => "z.B. AN"];
		$actionSelectionAttr = ["tooltip" => "Auszuführende Action wenn der genannte Zustand präsent ist"];
		$defaultSelectionAttr = ["tooltip" => "Auszuführende Action wenn kein genannter Zustand präsent ist"];

		$builder->addLabel("defaultAction", "Default-Action");
		$builder->addSelection("defaultAction", 
			$actionOptions, 
			is_null($action) ? [] : [$action->getDefaultAction() => $actionOptions[$action->getDefaultAction()]],
			$defaultSelectionAttr);

		$builder->addLabel("defaultStateChange", "Zustand verändern");
		$builder->addInput("defaultStateChange", "text", is_null($action) ? null : $action->getDefaultStateChange(), $stateChangeAttr);

		if (is_null($action) || sizeof($action->getStatesAndActions()) == 0) {
			$builder->startMultipleFirst();

			$builder->addLabel("states[]", "Wenn Zustand");
			$builder->addInput("states[]", "text", null, $stateAttr);

			$builder->addLabel("actions[]", "dann Action");
			$builder->addSelection("actions[]", $actionOptions, [], $actionSelectionAttr);

			$builder->addLabel("stateChange[]", "und Zustand verändern auf");
			$builder->addInput("stateChange[]", "text", null, $stateChangeAttr);
			
			$builder->endMultipleFirst();
		} else {
			$first = true;
			foreach ($action->getStatesAndActions() as $state => $actionId) {
				$builder->startMultipleElement($first);

				$builder->addLabel("states[]", "Wenn Zustand");
				$builder->addInput("states[]", "text", $state, $stateAttr);

				$builder->addLabel("actions[]", "dann Action");
				$builder->addSelection("actions[]", 
					$actionOptions, 
					[$actionId => $actionOptions[$actionId]], 
					$actionSelectionAttr);

				$stateChange = $action->getStatesAndStateChanges()[$state];
				$builder->addLabel("stateChange[]", "und Zustand verändern auf");
				$builder->addInput("stateChange[]", "text", $stateChange, $stateChangeAttr);

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
		$stateId = $response["stateId"];
		$defaultAction = $response["defaultAction"];
		$defaultStateChange = $response["defaultStateChange"];
		$statesAndActions = array_combine($response["states"], $response["actions"]);
		$statesAndStateChanges = array_combine($response["states"], $response["stateChange"]);

		$action = new StateDependingAction($actionId, $name, 
											$defaultAction, $defaultStateChange,
											$stateId, $statesAndActions,
											$statesAndStateChanges);
		return $action;
	}

	protected function before(){
		//empty
	}

	protected function main(){
		$actionId = $this->DEFAULT_ACTION;
		$newState = $this->DEFAULT_STATE_CHANGE;

		if ($this->getStateId() !== null) {
			$stateManager = StateManager::get();
			$state = $stateManager->getStateById($this->getStateId())->getState();
			
			if (array_key_exists($state, $this->statesAndActions)) {
				$actionId = $this->statesAndActions[$state];
			}
			if (array_key_exists($state, $this->statesAndStateChanges)) {
				$newState = $this->statesAndStateChanges[$state];
			}
		}

		$this->runAction($actionId);
		$this->updateState($newState);
	}

	protected function after(){
		//empty
	}

	private function runAction($actionOrId= null){
		$action = $actionOrId;
		if (is_numeric($actionOrId)) {
			$action = ActionManager::get()->getActionById($actionOrId);
		}
		if ($action !== null) {
			$action->run();
		}
	}

	private function updateState($newState = null) {
		$stateManager = StateManager::get();
		$stateObj = $stateManager->getStateById($this->getStateId());
		if ($stateObj->setState($newState)) {
			$stateManager->save($stateObj);
		} else {
			die(sprintf("'%s' is no valid state of the state '%s' [id: %d]", $newState, $stateObj->getName(), $stateObj->getId()));
		}
	}

	public function setStateId($stateId) {
		$this->stateId = $stateId;
	}

	public function getStateId() {
		return $this->stateId;
	}

	public function setDefaultAction($action = null){
		$this->DEFAULT_ACTION = $action;
	}

	public function getDefaultAction(){
		return $this->DEFAULT_ACTION;
	}

	public function setDefaultStateChange($stateChange = null){
		$this->DEFAULT_STATE_CHANGE = $stateChange;
	}

	public function getDefaultStateChange(){
		return $this->DEFAULT_STATE_CHANGE;
	}

	public function addStateAndAction($state = null, $action = null) {
		$this->setStateAndAction($state, $action);
	}

	public function setStateAndAction($state = null, $action = null) {
		if ($state === null) {
			$this->setDefaultAction($action);
		} else {
			$this->statesAndActions[$state->getIdentifiingString()] = $action;
		}
	}

	public function setStatesAndActions($statesAndActions = []) {
		$this->statesAndActions = $statesAndActions;
	}

	public function getStatesAndActions() {
		return $this->statesAndActions;
	}

	public function setStatesAndStateChanges($statesAndStateChanges = []) {
		$this->statesAndStateChanges = $statesAndStateChanges;
	}

	public function getStatesAndStateChanges() {
		return $this->statesAndStateChanges;
	}

}

?>