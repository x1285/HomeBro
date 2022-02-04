<?php

require_once("StateManager.class.php");

class PercentageStateActionButton extends StateDependingActionButton {

	public function __construct($id, $name, $actionId = null, $stateId = null) {
		parent::__construct($id, $name, $actionId, $stateId);
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

		$actionButton = new PercentageStateActionButton($actionButtonId, $actionButtonName, $actionId, $stateId);
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
					$builder = self::createActionButtonCreationFormBuilder("PercentageStateActionButton", $actionButton);
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

}

?>