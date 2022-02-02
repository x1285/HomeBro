<?php

class CreationFormBuilder {

	private $multipleButtonTextAdd = "";
	private $multipleButtonTextRemove = "";

	private $formObjs = [];
	private $formId = null;
	private $formClasses = ["creationForm"];
	private $formMethod = "POST";
	private $formActionPath = null;
	private $autoBreak = true;
	private $clazz = null;

	static public function printHeader() {
		echo file_get_contents(dirname(__FILE__)."/CreationFormBuilder.html");
	}

	static public function createForAction($clazz = null, $action = null){
		$builder = new CreationFormBuilder($clazz);
		$builder->addInput("actionType", "hidden", $clazz);
		if ($action !== null && $action->getId() !== null) {
			$builder->addInput("actionId", "hidden", $action->getId());
		}
		return $builder;
	}

	static public function createForActionButton($clazz = null, $actionButton = null){
		$builder = new CreationFormBuilder($clazz);
		$builder->addInput("actionButtonType", "hidden", $clazz);
		if ($actionButton !== null && $actionButton->getId() !== null) {
			$builder->addInput("actionButtonId", "hidden", $actionButton->getId());
		}
		return $builder;
	}

	static public function createForState($clazz = null, $state = null){
		$builder = new CreationFormBuilder($clazz);
		$builder->addInput("stateType", "hidden", $clazz);
		if ($state !== null && $state->getId() !== null) {
			$builder->addInput("stateId", "hidden", $state->getId());
		}
		return $builder;
	}

	private function __construct($clazz = null){
		if($clazz == null){
			die("CreationFormBuilder needs a referencing 'clazz' String.");
		}
		$this->clazz = $clazz;
	}

	public function withId($id) {
		$this->id = $id;
	}

	public function withClass($classes) {
		if(is_array($classes)) {
			foreach($classes as $class){
				$this->addClass($class);
			}
		}
	}

	public function withAutobreak($autoBreak = true) {
		$this->autoBreak = $autoBreak;
	}

	public function addClass($class){
		if(is_array($class)) {
			$this->withClass($class);
		} else {
			$lastFirstDotPosition = 0;
			while(substr($class, $lastFirstDotPosition, 1) === ".") {
				$lastFirstDotPosition;
			}
			$this->formClasses[] = substr($class, $lastFirstDotPosition);
		}
	}

	public function add($html = ""){
		$this->addHtml($html);
	}

	public function addHtml($html = ""){
		$this->formObjs[] = $html;
		$this->addAutoBreak();
	}

	public function withMethod($method) {
		$this->formMethod = $method;
	}

	public function withActionPath($actionPath) {
		$this->formActionPath = $actionPath;
	}

	public function startMultipleFirst($title = null) {
		if ($title === null  || strlen($title) === 0) {
			$title = $this->multipleButtonTextAdd;
		}
		$this->formObjs[] = "<div class=\"multiple\">";
		$this->formObjs[] = "\t<div class=\"multipleElement firstElement\">";
		$this->formObjs[] = sprintf("\t<button class=\"withIcon add green\" onclick=\"return addMultiple(this);\">%s</button>", $title);
	}

	public function endMultipleFirst() {
		$this->formObjs[] = "\t</div>";
	}

	public function startMultipleElement($first = false, $buttonText = null) {
		if ($first === true) {
			$this->startMultipleFirst($buttonText);
			return;
		}
		$this->formObjs[] = "\t<div class=\"multipleElement\">";
		$btnText = (is_null($buttonText)  || strlen($buttonText) === 0) ? $this->multipleButtonTextRemove : $buttonText;
		$this->formObjs[] = sprintf("\t<button class=\"withIcon remove red\" onclick=\"return removeMultiple(this);\">%s</button>", $btnText);
	}

	public function endMultipleElement($first = false) {
		if ($first === true) {
			$this->endMultipleFirst();
			return;
		}
		$this->formObjs[] = "</div>";
	}

	public function endMultiple() {
		$this->formObjs[] = "</div>";
		$this->addAutoBreak();
	}

	public function addCheckbox($name, $title, $checked = false) {
		$checkboxId = sprintf("%s", $name);
		$this->formObjs[] = sprintf("<input type=\"checkbox\" name=\"%s\" id=\"%s\"%s>", $name, $checkboxId, ($checked ? " checked" : ""));
		$this->addLabel($checkboxId, $title);
		$this->addAutoBreak();
	}

	public function addRadio($name, $value, $title, $selected = false) {
		$radioId = sprintf("%s_%s_radio", $name, $value);
		$this->formObjs[] = sprintf("<input type=\"radio\" name=\"%s\" value=\"%s\" id=\"%s\"%s>", $name, $value, $radioId, ($selected ? " checked" : ""));
		$this->addLabel($radioId, $title);
		$this->addAutoBreak();
	}

	public function addSelection($name, $values = [], $selected = [], $moreAttributes = []) {
		$moreAttributesString = $this->buildMoreAttributesString($moreAttributes);
		$this->formObjs[] = sprintf("<select name=\"%s\"%s>", $name, $moreAttributesString);
		foreach ($values as $value => $title) {
			if($selected !== null 
				&& is_array($selected) 
				&& sizeof($selected) > 0 
				&& array_key_exists($value, $selected)) {
					$value = array_keys($selected)[0];
					$title = array_values($selected)[0];
					$this->formObjs[] = sprintf("\t<option value=\"%s\" selected=\"selected\">%s</option>", $value, $title);
			} else {
				$this->formObjs[] = sprintf("\t<option value=\"%s\">%s</option>", $value, $title);
			}
		}
		$this->formObjs[] = "</select>";
		$this->addAutoBreak();
	}

	public function addLabel($for, $htmlTitle, $moreAttributes = []) {
		$moreAttributesString = $this->buildMoreAttributesString($moreAttributes);
		$this->formObjs[] = sprintf("<label for=\"%s\">%s</label>", $for, $htmlTitle, $moreAttributesString);
	}

	public function addInput($name, $type = "text", $value = null, $moreAttributes = []) {
		$moreAttributesString = (is_null($value) ? "" : sprintf(" value=\"%s\"", htmlentities($value)));
		$moreAttributesString .= $this->buildMoreAttributesString($moreAttributes);
		$this->formObjs[] = sprintf("<input type=\"%s\" name=\"%s\"%s>", $type, $name, $moreAttributesString);
		$this->addAutoBreak();
	}

	public function addSubmit($value, $moreAttributes = []) {
		$moreAttributesString = $this->buildMoreAttributesString($moreAttributes);
		$this->formObjs[] = sprintf("<input type=\"submit\" value=\"%s\"%s>", $value, $moreAttributesString);
		$this->addAutoBreak();
	}

	public function addBreak(){
		$this->formObjs[] = "<br />";
	}

	private function buildMoreAttributesString($moreAttributes = []) {
		$moreAttributesString = "";	
		if(is_array($moreAttributes)) {
			foreach($moreAttributes as $key => $value) {
				if(is_int($key)) {
					$moreAttributesString .= " ".$value;
				} else {
					$moreAttributesString .= " ".sprintf("%s=\"%s\"", $key, $value);
				}
			}
		}
		return $moreAttributesString;
	}

	public function buildHtml() {
		$html = "<form";

		if($this->formMethod !== null) {
			$html .= sprintf(" method=\"%s\"", $this->formMethod);
		}

		if($this->formActionPath !== null) {
			$html .= sprintf(" action=\"%s\"", $this->formActionPath);
		}

		if($this->formId !== null){
			$html .= sprintf(" id=\"%s\"", $this->formId);
		}

		$classes = "";
		foreach($this->formClasses as $class){
			$classes .= sprintf("%s ", $class);
		}
		$html .= sprintf(" class=\"%s\"", substr($classes, 0, -1));

		$html .= ">\n";

		foreach($this->formObjs as $formObj) {
			$html .= sprintf("\t%s\n", $formObj);
		}
		
		$html .= "</form>\n";
		return $html;
	}

	public function getHtml() {
		return $this->buildHtml();
	}

	public function build() {
		return $this->buildHtml();
	}

	public function addAutoBreak() {
		if ($this->autoBreak) {
			$this->formObjs[] = "<br />";
		}
	}

	public function setMultipleButtonTextAdd($text = "") {
		if (strlen($text) > 0) {
			$this->multipleButtonTextAdd = $text;
		}
	}

	public function setMultipleButtonTextRemove($text = "") {
		if (strlen($text) > 0) {
			$this->multipleButtonTextRemove = $text;
		}
	}

}

?>