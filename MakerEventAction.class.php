<?php

class MakerEventAction extends HttpGetAction {

	private $eventName;
	private $key;

	private $VARIABLENAME_MAKER_EVENT = "%makerevent";
	private $VARIABLENAME_MAKER_KEY = "%key";

	private $MAKER_API_KEY = "g-HS7urj8F-1ZeGXCjLCCJYiTrFcZrga0zld-ol2tLE";

	private $UNFORMATTED_MAKER_URL = "https://maker.ifttt.com/trigger/%s/with/key/%s";

	public function __construct($id, $name, $eventName = null){
		parent::__construct($id, $name, sprintf($this->UNFORMATTED_MAKER_URL, $this->VARIABLENAME_MAKER_EVENT, $this->VARIABLENAME_MAKER_KEY));

		$this->setKey($this->MAKER_API_KEY);
		if($eventName !== null){
			$this->setEventName($eventName);
		}
	}

	static public function createActionCreationFormBuilder($clazz, $action = null){
		$builder = CreationFormBuilder::createForAction($clazz, $action);
		$builder->addClass("makerEvent");

		$builder->addLabel("actionName", "Name");
		$builder->addInput("actionName", "text", is_null($action) ? null : $action->getName());

		$builder->addLabel("eventName", "Eventname");
		$builder->addInput("eventName", "text", is_null($action) ? null : $action->getEventName());

		$builder->addSubmit(is_null($action) || is_null($action->getId()) ? "Erzeugen" : "Änderung speichern");
		return $builder;
	}

	static public function createByFormResponse($response) {
		$actionId = -1;
		if (isset($response["actionId"]) && is_numeric($response["actionId"])) {
			$actionId = $response["actionId"];
		}
		$name = $response["actionName"];
		$eventName = $response["eventName"];

		$action = new MakerEventAction($actionId, $name, $eventName);
		return $action;
	}

	public function setEventName($eventName) {
		$this->eventName = $eventName;
		$this->setVariable($this->VARIABLENAME_MAKER_EVENT, $eventName);
	}

	public function getEventName() {
		return $this->eventName;
	}

	public function setKey($key) {
		$this->key = $key;
		$this->setVariable($this->VARIABLENAME_MAKER_KEY, $key);
	}

	public function getKey() {
		return $this->key;
	}

}

?>