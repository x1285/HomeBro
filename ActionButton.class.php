<?php

require_once("././base/HasIdAndName.class.php");
require_once("ActionManager.class.php");
require_once("ActionButtonPrinter.class.php");
require_once("XhrResponseBuilder.class.php");

class ActionButton extends HasIdAndName {

	private $actionId;
	private $cssClasses = [];
	private $sortValue = "";
	private $hidden = false;

	public function __construct($id, $name, $actionId = null) {
		parent::__construct($id, $name);
		if (is_null($actionId) || !is_numeric($actionId)) {
			die("ActionButton needs a numeric actionId as constructor parameter.");
		}
		$this->actionId = $actionId;
	}

	static public function createActionButtonCreationFormBuilder($clazz, $button = null){
		$builder = CreationFormBuilder::createForActionButton($clazz, $button);
		$builder->addClass("actionButton");

		$builder->addLabel("actionButtonName", "Name");
		$builder->addInput("actionButtonName", "text", is_null($button) ? null : $button->getName());

		$builder->addLabel("sortValue", "Sortierungswert");
		$builder->addInput("sortValue", "text", is_null($button) ? null : $button->getSortValue());

		$builder->addCheckbox("hidden", "Verstecken", is_null($button) ? null : $button->isHidden());

		$actionOptions = ActionManager::get()->getListOfAllActionIdsAndNames();
		$builder->addLabel("actionId", "Aktion");
		$builder->addSelection("actionId", $actionOptions, is_null($button) ? [] : [$button->getActionId() => $actionOptions[$button->getActionId()]]);

		$builder->addHtml("<a href=\"icons/showcase.html\" target=\"_blank\" class=\"button fr\">Icons</a>");

		$builder->addLabel("cssClasses", "CSS Klassen");
		$builder->addInput("cssClasses", "text", is_null($button) ? null : implode(" ", $button->getCssClasses()));

		$builder->addSubmit(is_null($button) || is_null($button->getId()) ? "Erzeugen" : "Änderung speichern");
		return $builder;
	}

	static public function createByFormResponse($response) {
		$actionButtonId = -1;
		if (isset($response["actionButtonId"]) && is_numeric($response["actionButtonId"])) {
			$actionButtonId = $response["actionButtonId"];
		}
		$actionButtonName = $response["actionButtonName"];
		$actionId = $response["actionId"];
		$sortValue = $response["sortValue"];
		$hidden = isset($response["hidden"]) && $response["hidden"] === "on";
		$cssClasses = explode(" ", $response["cssClasses"]);

		$actionButton = new ActionButton($actionButtonId, $actionButtonName, $actionId);
		$actionButton->setSortValue($sortValue);
		$actionButton->setHidden($hidden);
		$actionButton->setCssClasses($cssClasses);
		return $actionButton;
	}

	public function createButtonPrinter() {
		return new ActionButtonPrinter($this, true);
	}

	public function beforeRunAction($isXHR) {
		//empty.
	}

	public final function runAction() {
		$action = ActionManager::get()->getActionById($this->getActionId());
		if (!is_null($action)) {
			$action->run();
		}
		return $action;
	}

	public function afterRunAction($isXHR, $action = null) {
		if (is_null($action)) {
			http_response_code(500);
			echo sprintf("Action button's '%s' (id: %d) onclick executed, but no action found for id '%d'.", $this->getName(), $this->getId(), $this->getActionId());
		} else if ($isXHR) {
			$builder = XhrResponseBuilder::createFor($this);
			$builder->add("cause", ["btnclick" => $this->getId()]);
			$errorsAndWarnings = ob_get_clean();
			//$builder->add("errorsandwarnings", $errorsAndWarnings);
			echo $builder->toJson();
			die();
			//echo sprintf("Action button's '%s' (id: %d) onclick successfully executed action (id: %d)", $this->getName(), $this->getId(), $this->getActionId());
		}
	}

	public final function onclick($isXHR = false) {
		$this->beforeRunAction($isXHR);
		try {
			$action = $this->runAction();
			$this->afterRunAction($isXHR, $action);
		} catch (Exception $e){
			http_response_code(500);
			die(sprintf("Exception while executing onclick-method [button '%s', Id: $d]:\n%s", $this->getName(), $this->getId(), $e->getMessage()));
		}
	}

	public function setActionId($actionId) {
		$this->actionId = $actionId;
	}

	public function getActionId() {
		return $this->actionId;
	}

	public function setSortValue($sortValue) {
		$this->sortValue = $sortValue;
	}

	public function getSortValue() {
		return $this->sortValue;
	}

	public function setHidden($hidden) {
		$this->hidden = $hidden;
	}

	public function isHidden() {
		return $this->hidden;
	}

	public function setCssClasses($cssClasses = []) {
		if (is_array($cssClasses)) {
			$this->cssClasses = $cssClasses;
		}
	}

	public function getCssClasses() {
		return $this->cssClasses;
	}

}

?>