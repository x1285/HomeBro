<?php

require_once("Action.class.php");
require_once("ActionManager.class.php");

class Sequence extends Action {

	protected $actionIds = [];

	public function __construct($id, $name, $actionIds = []) {
		parent::__construct($id, $name);

		$this->setActionIds($actionIds);
	}

	static public function createActionCreationFormBuilder($clazz, $action = null){
		$builder = CreationFormBuilder::createForAction($clazz, $action);
		$builder->addClass("sequence");

		$builder->addLabel("actionName", "Name");
		$builder->addInput("actionName", "text", is_null($action) ? null : $action->getName());

		$actionOptions = ActionManager::get()->getListOfAllActionIdsAndNamesPrefixedByClass();
		if($action !== null){
			unset($actionOptions[$action->getId()]);
		}
		asort($actionOptions);

		if (is_null($action) || sizeof($action->getActionIds()) == 0) {
			$builder->startMultipleFirst();

			$builder->addLabel("action[]", "Aktion");
			$builder->addSelection("action[]", $actionOptions);
			
			$builder->endMultipleFirst();
		} else {
			$first = true;
			foreach ($action->getActionIds() as $actionId) {
				$builder->startMultipleElement($first);

				$builder->addLabel("action[]", "Aktion");
				$builder->addSelection("action[]", $actionOptions, [$actionId => $actionOptions[$actionId]]);
				
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
		$actionIds = $response["action"];

		$action = new Sequence($actionId, $name, $actionIds);
		return $action;
	}

	protected function before() {
		//empty.
	}

	protected function after() {
		//empty.
	}

	protected function main() {
		$actionManager = ActionManager::get();
		$actionsToRun = [];
		foreach ($this->actionIds as $actionId) {
			$action = null;
			$action = $actionManager->getActionById($actionId);
			
			if ($action === null) {
				echo sprintf("Keine ausführbare Action mit Id \"%d\" für Sequenz \"%s\" (id:%d) gefunden.", 
					$actionId, 
					$this->getName(), 
					$this->getId()
				);
			} else {
				$actionsToRun[] = $action;
			}
		}
		foreach ($actionsToRun as $action) {
			$action->run();
		}
	}

	public function addActionId($actionId){
		$this->actionIds[] = $actionId;
	}

	public function setActionIds($actionIds) {
		$this->actionIds = $actionIds;
	}

	public function getActionIds() {
		return $this->actionIds;
	}

}

?>