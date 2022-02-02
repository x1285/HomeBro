<?php

require_once("Action.class.php");

class ShellAction extends Action {

	private $unformattedShellCommand;
	private $shellResult;

	private $shellVariables = [];

	public function __construct($id, $name, $unformattedShellCommand = "", $shellVariablesArr = []){
		parent::__construct($id, $name);

		$this->setUnformattedShellCommand($unformattedShellCommand);
		$this->setShellVariables($shellVariablesArr);
	}

	protected function before() {
		//empty
	}

	protected function main() {
		$this->shellResult = shell_exec($this->getFormattedShellCommand());
	}

	protected function after() {
		//empty
	}

	static public function createActionCreationFormBuilder($clazz, $action = null){
		$builder = CreationFormBuilder::createForAction($clazz, $action);
		$builder->addClass("shell");

		$builder->addLabel("actionName", "Name");
		$builder->addInput("actionName", "text", is_null($action) ? null : $action->getName());

		$builder->addLabel("shellcmd", "Shell-Befehl");
		$builder->addInput("shellcmd", "textarea", is_null($action) ? null : $action->getUnformattedShellCommand());

		if (is_null($action) || sizeof($action->getShellVariables() == 0)) {
			$builder->startMultipleFirst();

			$builder->addLabel("varname[]", "Variablenname");
			$moreAttributes = ["placeholder" => "%name"];
			$builder->addInput("varname[]", "text", null, $moreAttributes);

			$builder->addLabel("varvalue[]", "Variablenwert");
			$moreAttributes = ["placeholder" => "Ein beliebiger Wert"];
			$builder->addInput("varvalue[]", "text", null, $moreAttributes);
			
			$builder->endMultipleFirst();
		} else {
			$first = true;
			foreach ($action->getShellVariables() as $varName => $varValue) {
				$builder->startMultipleElement($first);

				$builder->addLabel("varname[]", "Variablenname");
				$moreAttributes = ["placeholder" => "%name"];
				$builder->addInput("varname[]", "text", $varName, $moreAttributes);

				$builder->addLabel("varvalue[]", "Variablenwert");
				$moreAttributes = ["placeholder" => "Ein beliebiger Wert"];
				$builder->addInput("varvalue[]", "text", $varValue, $moreAttributes);

				$builder->endMultipleElement();
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
		$shellcmd = $response["shellcmd"];
		$shellVariablesArr = array_combine($response["varname"], $response["varvalue"]);

		$action = new ShellAction($actionId, $name, $shellcmd, $shellVariablesArr);
		return $action;
	}

	public function setUnformattedShellCommand($unformattedShellCommand){
		$this->unformattedShellCommand = $unformattedShellCommand;
	}

	public function getUnformattedShellCommand(){
		return $this->unformattedShellCommand;
	}

	public function getFormattedShellCommand(){
		return str_replace(array_keys($this->shellVariables), array_values($this->shellVariables), $this->getUnformattedShellCommand());
	}

	public function setShellVariable($key, $stringValue) { //encoded String values!
		$this->addShellVariable($key, $stringValue);
	}

	public function addShellVariable($key, $stringValue) { //ncoded String values!
		$this->shellVariables[$key] = $stringValue;
	}

	public function setShellVariables($shellVariables) {
		$this->shellVariables = $shellVariables;
	}

	public function getShellVariables() {
		return $this->shellVariables;
	}

	public function getShellVariable($key) {
		if (array_key_exists($key, $this->shellVariables)) {
			return $this->shellVariables[$key];
		}
	}
}

?>