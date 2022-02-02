<?php

require_once("ShellAction.class.php");

class Mhz433Action extends ShellAction {

	private $VARIABLENAME_GROUP = "%group";
	private $VARIABLENAME_SWITCH = "%switch";
	private $VARIABLENAME_ACTION = "%action";

	public function __construct($id, $name, $group = null, $switch = null, $action = null) {
		parent::__construct($id, $name, "");

		if($group !== null) {
			$this->setGroup($group);
		}
		if($switch !== null) {
			$this->setSwitch($switch);
		}
		if($action !== null) {
			$this->setAction($action);
		}
	}

	static public function createActionCreationFormBuilder($clazz, $action = null){
		$builder = CreationFormBuilder::createForAction($clazz, $action);
		$builder->addClass("mhz433");

		$builder->addLabel("actionName", "Name");
		$builder->addInput("actionName", "text", is_null($action) ? null : $action->getName());

		$builder->addLabel("group", "Funkgruppe");
		$builder->addInput("group", "number", is_null($action) ? null : $action->getGroup());

		$builder->addLabel("switch", "Funkschalter");
		$builder->addInput("switch", "number", is_null($action) ? null : $action->getSwitch());

		$onOff = is_null($action) ? false : $action->getAction();
		$builder->addRadio("action", "0", "Ausschalten", $onOff == 0);
		$builder->addRadio("action", "1", "Einschalten", $onOff == 1);

		$builder->addSubmit(is_null($action) || is_null($action->getId()) ? "Erzeugen" : "Änderung speichern");
		return $builder;
	}

	static public function createByFormResponse($response) {
		$actionId = -1;
		if (isset($response["actionId"]) && is_numeric($response["actionId"])) {
			$actionId = $response["actionId"];
		}
		$name = $response["actionName"];
		$group = $response["group"];
		$switch = $response["switch"];
		$onOff = $response["action"];

		$action = new Mhz433Action($actionId, $name, $group, $switch, $onOff);
		return $action;
	}

	public function setGroup($group){
		while (strlen($group) < 5) {
			$group = "0".$group;
		}
		$this->setShellVariable($this->VARIABLENAME_GROUP, $group);
	}

	public function getGroup() {
		return $this->getShellVariable($this->VARIABLENAME_GROUP);
	}

	public function setSwitch($switch){
		while (strlen($switch) < 2) {
			$switch = "0".$switch;
		}
		$this->setShellVariable($this->VARIABLENAME_SWITCH, $switch);
	}

	public function getSwitch() {
		return $this->getShellVariable($this->VARIABLENAME_SWITCH);
	}

	public function setAction($action){
		$this->setShellVariable($this->VARIABLENAME_ACTION, $action);
	}

	public function getAction(){
		return $this->getShellVariable($this->VARIABLENAME_ACTION);
	}

	public function getUnformattedShellCommand(){
		return "/home/pi/raspberry-remote/send ".$this->VARIABLENAME_GROUP
			." ".$this->VARIABLENAME_SWITCH
			." ".$this->VARIABLENAME_ACTION;
	}

}

?>