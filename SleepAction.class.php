<?php

require_once("Action.class.php");

class SleepAction extends Action {

	private $sleepingSeconds = 0;

	public function __construct($id, $name, $sleepingSeconds = 0) {
		parent::__construct($id, $name);

		if($sleepingSeconds !== null) {
			$this->sleepingSeconds = $sleepingSeconds;
		}
	}

	protected function before() {
		//empty
	}

	protected function main() {
		sleep($this->sleepingSeconds);
	}

	protected function after() {
		//empty
	}

	static public function createActionCreationFormBuilder($clazz, $action = null){
		$builder = CreationFormBuilder::createForAction($clazz, $action);
		$builder->addClass("sleep");

		$builder->addLabel("actionName", "Name");
		$builder->addInput("actionName", "text", is_null($action) ? null : $action->getName());

		$builder->addLabel("sleepingSeconds", "Wartezeit (in Sekunden)");
		$builder->addInput("sleepingSeconds", "number", is_null($action) ? null : $action->getSleepingSeconds());

		$builder->addSubmit(is_null($action) || is_null($action->getId()) ? "Erzeugen" : "Änderung speichern");
		return $builder;
	}

	static public function createByFormResponse($response) {
		$actionId = -1;
		if (isset($response["actionId"]) && is_numeric($response["actionId"])) {
			$actionId = $response["actionId"];
		}
		$name = $response["actionName"];
		$sleepingSeconds = $response["sleepingSeconds"];

		$action = new SleepAction($actionId, $name, $sleepingSeconds);
		return $action;
	}

	public function setSleepingSeconds($sleepingSeconds = 0){
		$this->sleepingSeconds = $sleepingSeconds;
	}

	public function getSleepingSeconds() {
		return $this->sleepingSeconds;
	}

}

?>