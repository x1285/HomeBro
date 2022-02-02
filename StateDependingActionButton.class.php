<?php

require_once("StateManager.class.php");

class StateDependingActionButton extends ActionButton {

	private $stateId;
	private $stateDependingCssClasses = [];
	private $asRegex = false;

	public function __construct($id, $name, $actionId = null, $stateId = null) {
		parent::__construct($id, $name, $actionId);
		if (is_null($actionId) || !is_numeric($actionId)) {
			die("StateDependingActionButton needs a numeric actionId as constructor parameter.");
		}
		$this->setStateId($stateId);
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

		$stateOptions = StateManager::get()->getListOfAllStateIdsAndNames();
		$builder->addLabel("stateId", "Zustand");
		$builder->addSelection("stateId", $stateOptions, is_null($button) ? [] : [$button->getStateId() => $stateOptions[$button->getStateId()]]);

		$builder->addHtml("<a href=\"icons/showcase.html\" target=\"_blank\" class=\"button fr\">Icons</a>");

		$builder->addLabel("cssClasses", "Allgemeine CSS Klassen");
		$builder->addInput("cssClasses", "text", is_null($button) ? null : implode(" ", $button->getCssClasses(false)));

		if (is_null($button) || sizeof($button->getStateDependingCssClasses()) == 0) {
			$builder->startMultipleFirst();

			$builder->addLabel("states[]", "Wenn Zustand:");
			$builder->addInput("states[]", "text");

			$builder->addLabel("stateDependingCssClasses[]", "dann CSS Klasse:");
			$builder->addInput("stateDependingCssClasses[]", "text");
			
			$builder->endMultipleFirst();
		} else {
			$first = true;
			foreach ($button->getStateDependingCssClasses() as $state => $cssClasses) {
				$builder->startMultipleElement($first);

				$builder->addLabel(sprintf("states[]", $state), "Wenn Zustand:");
				$builder->addInput(sprintf("states[]", $state), "text", $state);

				$builder->addLabel(sprintf("stateDependingCssClasses[]", $state), "dann CSS Klasse:");
				$builder->addInput(sprintf("stateDependingCssClasses[]", $state), "text", $cssClasses);
				
				$builder->endMultipleElement($first);
				$first = false;
			}
		}
		$builder->endMultiple();

		$builder->addCheckbox("asRegex", "Zustand mit Regex testen", is_null($button) ? null : $button->isAsRegex());

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
		$stateId = $response["stateId"];
		$sortValue = $response["sortValue"];
		$hidden = isset($response["hidden"]) && $response["hidden"] === "on";
		$asRegex = isset($response["asRegex"]) && $response["asRegex"] === "on";
		$cssClasses = explode(" ", trim(preg_replace('!\s+!', ' ', $response["cssClasses"])));

		$actionButton = new StateDependingActionButton($actionButtonId, $actionButtonName, $actionId, $stateId);
		$actionButton->setSortValue($sortValue);
		$actionButton->setHidden($hidden);
		$actionButton->setCssClasses($cssClasses);
		$actionButton->setAsRegex($asRegex);

		$stateDependingCssClasses = [];
		if (isset($response["states"])) {
			$state = StateManager::get()->getStateById($stateId);
			//new StateDependingActionButton: check if state is valid! -> no: print creationFormAgain!
			$responseStates = $response["states"];
			foreach ($responseStates as $responseState) {
				if ($asRegex === false && get_class($state) === 'State' && !in_array($responseState, $state->getStates())) {
					echo sprintf("Invalid state '%s' given for state '%s' (id: %d). Valid states: %s", $responseState, $state->getName(), $state->getId(), implode(", ", $state->getStates()));
					$builder = self::createActionButtonCreationFormBuilder("StateDependingActionButton", $actionButton);
					echo $builder->getHtml();
					return null;
				}
				$stateDependingCssClasses = array_combine($response["states"], $response["stateDependingCssClasses"]);
			}
		} else {
			$stateDependingCssClasses = $response["stateDependingCssClasses"];
		}
		$actionButton->setStateDependingCssClasses($stateDependingCssClasses);

		return $actionButton;
	}

	//OVERRIDE
	public function getCssClasses($withStateDepedingCssClasses = true) {
		$activeCssClasses = parent::getCssClasses();
		if ($withStateDepedingCssClasses === true) {
			$currentState = $this->getCurrentState();
			$stateDependingCssClasses = $this->getStateDependingCssClasses();
			if ($this->isAsRegex() === true) {
				foreach ($stateDependingCssClasses as $regexState => $cssClasses) {
					if (preg_match('/'.$regexState.'/', $currentState)) {
						$activeCssClasses[] = $cssClasses;
					}
				}
			} else if (array_key_exists($currentState, $stateDependingCssClasses)) {
				$activeCssClasses[] = $stateDependingCssClasses[$currentState];
			}
		}
		return $activeCssClasses;
	}

	public function getCurrentState() {
		return StateManager::get()->getStateById($this->getStateId())->getState();
	}

	public function setStateDependingCssClasses($stateDependingCssClasses) {
		return $this->stateDependingCssClasses = $stateDependingCssClasses;
	}

	public function getStateDependingCssClasses() {
		return $this->stateDependingCssClasses;
	}

	public function setStateId($stateId = null) {
		$state = StateManager::get()->getStateById($stateId);
		if (is_null($stateId) || !is_numeric($stateId)) {
			die(sprintf("StateDependingActionButton needs a numeric stateId, but '%s' given.", $stateId));
		}
		$this->stateId = $stateId;
		
		if (get_class($state) === 'State') {
			$validStateDependingCssClasses = [];
			$stateOptions = $state->getStates();
			foreach ($stateOptions as $stateOption) {
				if ($state instanceof UnboundState || $this->isAsRegex() || array_key_exists($stateOption, $this->stateDependingCssClasses)) {
					$validStateDependingCssClasses[$stateOption] = $this->stateDependingCssClasses[$stateOption];
				} else {
					$validStateDependingCssClasses[$stateOption] = "";
				}
			}
			$this->stateDependingCssClasses = $validStateDependingCssClasses;
		}	
	}

	public function getStateId() {
		return $this->stateId;
	}

	public function setAsRegex($asRegex = true) {
		$this->asRegex = $asRegex;
	}

	public function isAsRegex() {
		return $this->asRegex;
	}

}

?>