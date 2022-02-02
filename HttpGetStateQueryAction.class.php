<?php

class HttpGetStateQueryAction extends HttpGetAction {

	const POST_PARAMETER_SET_STATE = "setState";
	const POST_PARAMETER_RAW_VALUE = "rawValue";

	private $stateId;
	private $queryRegex;
	private $queryRegexMatchingGroup;

	public function __construct($id, $name, $stateId = null, $queryRegex = null, $queryRegexMatchingGroup = null,
								$unformattedUrl, $httpHeaderArr = [], $variablesArr = []){
		parent::__construct($id, $name, $unformattedUrl, $httpHeaderArr, $variablesArr);

		$this->setStateId($stateId);
		$this->setQueryRegex($queryRegex);
		$this->setQueryRegexMatchingGroup($queryRegexMatchingGroup);
	}

	protected function main() {
		$stateManager = StateManager::get();
		$stateObj = $stateManager->getStateById($this->stateId);
		if (isset($_POST[self::POST_PARAMETER_SET_STATE])) {
			if ($stateObj->setState($_POST[self::POST_PARAMETER_SET_STATE])) {
				$stateManager->save($stateObj);
			}
		} else {
			if (isset($_POST[self::POST_PARAMETER_RAW_VALUE])) {
				$rawValue = $_POST[self::POST_PARAMETER_RAW_VALUE];
			} else {
				parent::main();
				$rawValue = $this->getResponse();
			}
			$matches = [];
			$queryRegex = $this->getQueryRegex();
			$queryRegexMatchingGroup = $this->getQueryRegexMatchingGroup();
			if ($queryRegex !== null && strlen($queryRegex) > 0 && $queryRegexMatchingGroup !== null) {
				preg_match('/'.$queryRegex.'/', $rawValue, $matches);
				if ($matches !== null && array_key_exists($queryRegexMatchingGroup, $matches)) {
					$newState = $matches[$queryRegexMatchingGroup];
					if ($stateObj->setState($newState)) {
						$stateManager->save($stateObj);
					}
				}
			} else {
				if ($stateObj->setState($rawValue)) {
					$stateManager->save($stateObj);
				}
			}
		}
	}

	protected function before() {
		if (isset($_POST[self::POST_PARAMETER_SET_STATE])) {
			return;
		}
		$this->addHttpHeader("Content-Type: text/plain");
		parent::before();
	}

	static public function createActionCreationFormBuilder($clazz, $action = null) {
		$builder = CreationFormBuilder::createForAction($clazz, $action);
		$builder->addClass("httpGetStateQueryAction");

		$builder->addLabel("actionName", "Name");
		$builder->addInput("actionName", "text", is_null($action) ? null : $action->getName());

		$moreAttributes = ["tooltip" => "Variablenform: '%name'"];
		$builder->addLabel("unformattedUrl", "URL (inkl. Variablen)", $moreAttributes);
		$builder->addInput("unformattedUrl", "url", is_null($action) ? null : $action->getUnformattedUrl());

		$states = StateManager::get()->getListOfAllStateIdsAndNames();
		$builder->addLabel("stateId", "Zu aktualisierender Zustand");
		$builder->addSelection("stateId", $states, is_null($action) ? [] : [$action->getStateId() => $states[$action->getStateId()]]);

		$builder->addLabel("queryRegex", "Response query regex for new value");
		$builder->addInput("queryRegex", "text", is_null($action) ? null : $action->getQueryRegex());

		$builder->addLabel("queryRegexMatchingGroup", "Regex matching group");
		$builder->addInput("queryRegexMatchingGroup", "number", is_null($action) ? null : $action->getQueryRegexMatchingGroup());

		$builder->addSubmit(is_null($action) || is_null($action->getId()) ? "Erzeugen" : "Änderung speichern");
		return $builder;
	}

	static public function createByFormResponse($response) {
		$actionId = -1;
		if (isset($response["actionId"]) && is_numeric($response["actionId"])) {
			$actionId = $response["actionId"];
		}
		$name = $response["actionName"];
		$unformattedUrl = $response["unformattedUrl"];
		$stateId = $response["stateId"];
		$queryRegex = $response["queryRegex"];
		$queryRegexMatchingGroup = $response["queryRegexMatchingGroup"];

		$action = new HttpGetStateQueryAction($actionId, $name, $stateId, $queryRegex, $queryRegexMatchingGroup, $unformattedUrl, [], []);
		return $action;
	}

	public function setStateId($stateId) {
		$this->stateId = $stateId;
	}

	public function getStateId() {
		return $this->stateId;
	}

	public function setQueryRegex($queryRegex) {
		$this->queryRegex = $queryRegex;
	}

	public function getQueryRegex() {
		return $this->queryRegex;
	}

	public function setQueryRegexMatchingGroup($queryRegexMatchingGroup) {
		if (is_numeric($queryRegexMatchingGroup)) {
			$this->queryRegexMatchingGroup = $queryRegexMatchingGroup;
		}
	}

	public function getQueryRegexMatchingGroup() {
		return $this->queryRegexMatchingGroup;
	}

}

?>