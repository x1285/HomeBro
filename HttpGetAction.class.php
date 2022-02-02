<?php

require_once("HttpAction.class.php");

class HttpGetAction extends HttpAction {

	public function __construct($id, $name, $unformattedUrl, $httpHeaderArr = [], $variablesArr = []){
		parent::__construct($id, $name, $unformattedUrl, $httpHeaderArr);

		$this->setVariables($variablesArr);
	}

	static public function createActionCreationFormBuilder($clazz, $action = null){
		$builder = CreationFormBuilder::createForAction($clazz, $action);
		$builder->addClass("httpGet");

		$builder->addLabel("actionName", "Name");
		$builder->addInput("actionName", "text", is_null($action) ? null : $action->getName());

		$moreAttributes = ["tooltip" => "Variablenform: '%name'"];
		$builder->addLabel("unformattedUrl", "URL (inkl. Variablen)", $moreAttributes);
		$builder->addInput("unformattedUrl", "url", is_null($action) ? null : $action->getUnformattedUrl());

		if (is_null($action) || sizeof($action->getVariables()) == 0) {
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
			foreach ($action->getVariables() as $varName => $varValue) {

				$builder->startMultipleElement($first);

				$builder->addLabel("varname[]", "Variablenname");
				$moreAttributes = ["placeholder" => "%name"];
				$builder->addInput("varname[]", "text", $varName, $moreAttributes);

				$builder->addLabel("varvalue[]", "Variablenwert");
				$moreAttributes = ["placeholder" => "Ein beliebiger Wert"];
				$builder->addInput("varvalue[]", "text", $varValue, $moreAttributes);

				$builder->endMultipleElement($first);
				$first = false;
			}
		}
		$builder->endMultiple();

		if (is_null($action) || sizeof($action->getHttpHeaders()) == 0) {
				$builder->startMultipleFirst();
				$builder->addLabel("httpheader[]", "HTTP-Header");
				$moreAttributes = ["placeholder" => "Content-Type: text/plain"];
				$builder->addInput("httpheader[]", "text", null, $moreAttributes);
				$builder->endMultipleFirst();
		} else {
			$first = true;
			foreach ($action->getHttpHeaders() as $httpHeader) {

				$builder->startMultipleElement($first);

				$builder->addLabel("httpheader[]", "HTTP-Header");
				$moreAttributes = ["placeholder" => "Content-Type: text/plain"];
				$builder->addInput("httpheader[]", "text", $httpHeader, $moreAttributes);

				$builder->endMultipleElement($first);
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
		$unformattedUrl = $response["unformattedUrl"];
		$variablesArr = array_combine($response["varname"], $response["varvalue"]);
		$httpHeaderArr = $response["httpheader"];

		$action = new HttpGetAction($actionId, $name, $unformattedUrl, $httpHeaderArr, $variablesArr);
		return $action;
	}

}

?>