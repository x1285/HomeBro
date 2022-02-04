<?php

require_once("ActionButton.class.php");

class ActionButtonWithSubButtons extends ActionButton {

	private $primaryButtonId;
	private $subButtonIds = [];

	public function __construct($id, $name, $primaryButtonId, $subButtonIds = []) {
		$primaryButtonActionId = ActionButtonManager::get()->getActionButtonById($primaryButtonId)->getActionId();
		parent::__construct($id, $name, $primaryButtonActionId);
		$this->setPrimaryButtonId($primaryButtonId);
		$this->setSubButtonIds($subButtonIds);
	}

	static public function createActionButtonCreationFormBuilder($clazz, $button = null){
		$builder = CreationFormBuilder::createForActionButton($clazz, $button);
		$builder->addClass("actionButton");

		$builder->addLabel("actionButtonName", "Name");
		$builder->addInput("actionButtonName", "text", is_null($button) ? null : $button->getName());

		$builder->addLabel("sortValue", "Sortierungswert");
		$builder->addInput("sortValue", "text", is_null($button) ? null : $button->getSortValue());

		$builder->addCheckbox("hidden", "Verstecken", is_null($button) ? null : $button->isHidden());

		$buttonOptions = ActionButtonManager::get()->getListOfAllActionButtonIdsAndNames();
		if($button !== null){
			unset($buttonOptions[$button->getId()]);
		}

		$builder->addLabel("primaryButtonId", "Primärer Button");
		$builder->addSelection("primaryButtonId", $buttonOptions, is_null($button) ? [] : [$button->getPrimaryButtonId() => $buttonOptions[$button->getPrimaryButtonId()]]);

		if (is_null($button) || sizeof($button->getSubButtonIds()) == 0) {
			$builder->startMultipleFirst();

			$builder->addLabel("subButtonIds[]", "Sub-Button");
			$builder->addSelection("subButtonIds[]", $buttonOptions, []);

			$builder->endMultipleFirst();
		} else {
			$first = true;
			foreach ($button->getSubButtonIds() as $subButtonId) {
				$builder->startMultipleElement($first);

				$builder->addLabel("subButtonIds[]", "Sub-Button");
				$builder->addSelection("subButtonIds[]", $buttonOptions, [$subButtonId => $buttonOptions[$subButtonId]]);

				$builder->endMultipleElement($first);
				$first = false;
			}
		}
		$builder->endMultiple();

		$builder->addSubmit(is_null($button) || is_null($button->getId()) ? "Erzeugen" : "Änderung speichern");
		return $builder;
	}

	static public function createByFormResponse($response) {
		$actionButtonId = -1;
		if (isset($response["actionButtonId"]) && is_numeric($response["actionButtonId"])) {
			$actionButtonId = $response["actionButtonId"];
		}
		$actionButtonName = $response["actionButtonName"];
		$sortValue = $response["sortValue"];
		$hidden = isset($response["hidden"]) && $response["hidden"] === "on";
		$primaryButtonId = $response["primaryButtonId"];
		$subButtonIds = $response["subButtonIds"];

		$actionButton = new ActionButtonWithSubButtons($actionButtonId, $actionButtonName, $primaryButtonId, $subButtonIds);
		$actionButton->setSortValue($sortValue);
		$actionButton->setHidden($hidden);

		return $actionButton;
	}

	//OVERRIDE
	public function getCssClasses() {
		$activeCssClasses = parent::getCssClasses();
		if (in_array("hide", $this->getPrimaryButton()->getCssClasses())) {
			$activeCssClasses[] = "hide";
		}
		return $activeCssClasses;
	}

	public function setPrimaryButtonId($primaryButtonId) {
		$this->primaryButtonId = $primaryButtonId;
	}

	public function getPrimaryButtonId() {
		return $this->primaryButtonId;
	}

	public function getPrimaryButton() {
		return ActionButtonManager::get()->getActionButtonById($this->primaryButtonId);
	}

	public function setSubButtonIds($subButtonIds = []) {
		$this->subButtonIds = $subButtonIds;
	}

	public function getSubButtonIds() {
		return $this->subButtonIds;
	}

}

?>