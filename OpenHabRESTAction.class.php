<?php

class OpenHabRESTAction extends HttpPostAction {

	private $eventName;
	private $key;

	private $VARIABLENAME_OPENHAB_ITEM_NAME = "%itemName%";
	private $VARIABLENAME_OPENHAB_COMMAND = "%command%";

	private $UNFORMATTED_OPENHAB_URL = "http://192.168.1.37:8080/rest/items/%s";

	public function __construct($id, $name, $itemName = null, $command = ""){
		parent::__construct($id, $name, sprintf($this->UNFORMATTED_OPENHAB_URL, 
			$this->VARIABLENAME_OPENHAB_ITEM_NAME), array(), $this->VARIABLENAME_OPENHAB_COMMAND);

		$this->setItemName($itemName);
		$this->setCommand($command);
	}

	protected function before() {
		$this->addHttpHeader("Content-Type: text/plain");
		parent::before();
	}

	static public function createActionCreationFormBuilder($clazz, $action = null){
		$builder = CreationFormBuilder::createForAction($clazz, $action);
		$builder->addClass("openHabREST");

		$builder->addLabel("actionName", "Name");
		$builder->addInput("actionName", "text", is_null($action) ? null : $action->getName());

		$builder->addLabel("itemName", "Item Name");
		$builder->addInput("itemName", "text", is_null($action) ? null : $action->getItemName());

		$builder->addLabel("command", "Command");
		$builder->addInput("command", "text", is_null($action) ? null : $action->getCommand());

		$builder->addSubmit(is_null($action) || is_null($action->getId()) ? "Erzeugen" : "Änderung speichern");
		return $builder;
	}

	static public function createByFormResponse($response) {
		$actionId = -1;
		if (isset($response["actionId"]) && is_numeric($response["actionId"])) {
			$actionId = $response["actionId"];
		}
		$name = $response["actionName"];
		$itemName = $response["itemName"];
		$command = $response["command"];

		$action = new OpenHabRESTAction($actionId, $name, $itemName, $command);
		return $action;
	}

	public function setCommand($command) {
		$this->command = $command;
		$this->setVariable($this->VARIABLENAME_OPENHAB_COMMAND, $command);
	}

	public function getCommand() {
		return $this->command;
	}

	public function setItemName($itemName) {
		$this->itemName = $itemName;
		$this->setVariable($this->VARIABLENAME_OPENHAB_ITEM_NAME, $itemName);
	}

	public function getItemName() {
		return $this->itemName;
	}

}

?>