<?php

require_once("ActionButton.class.php");

class ActionButtonPrinter {

	private $cssClasses = [];
	private $htmlAttributes = [];
	private $buttonText = "undefined_button_text";
	private $autoBreak = true;
	private $actionButton;

	public function __construct($actionButton = null, $asXhr = false) {
		if (is_null($actionButton)) {
			die("ActionButtonPrinter needs an actionButton as parameter.");
		}
		$this->actionButton = $actionButton;
		$this->addCssClass(get_class($actionButton));
		$this->addHtmlAttribute("data-actionButtonId", $actionButton->getId());
		if (!is_a($this->actionButton, 'ActionButtonWithSubButtons')) {
			$this->addHtmlAttribute("onclick", ActionButtonManager::createJsForOnclick($asXhr));
		}
		$this->setButtonText($actionButton->getName());
	}

	public function withAutobreak($autoBreak = true) {
		$this->autoBreak = $autoBreak;
	}

	public function printButton() {
		$this->printHtml();
	}

	private function createHtml() {
		$element = "button";
		$innerHtml = "";
		if (is_a($this->actionButton, 'ActionButtonWithSubButtons')) {
			$element = "div";
			$primaryButton = $this->actionButton->getPrimaryButton();
			$printer = new ActionButtonPrinter($primaryButton);
			$innerHtml .= $printer->createHtml();
			$innerHtml .= $this->createHtmlForSubButtons($this->actionButton->getSubButtonIds());
		} else {
			$innerHtml .= $this->createInnerHtml();
		}

		$innerPart = $innerHtml;
		if ($this->actionButton instanceof PercentageStateActionButton) {
			$state = $this->actionButton->getCurrentState();
			$innerPart = sprintf("<progress class=\"%s\" max=\"100\" value=\"%s\"></progress>%s", "", $state, $innerHtml);
		}

		$html = sprintf("<%s class=\"%s\"%s>%s</%s>\n", 
			$element,
			$this->createCssClasses(), 
			$this->createHtmlAttributes(), 
			$innerPart,
			$element);
		return $html;
	}

	public function printHtml() {
		echo $this->createHtml();
		//$this->printAutoBreak();
	}

	public function printAutoBreak() {
		if ($this->autoBreak) {
			echo "<br />\n";
		}
	}

	public function setButtonText($text = null) {
		if (is_null($text) || strlen($text) === 0) {
			return;
		}
		$this->buttonText = $text;
	}

	public function addCssClass($cssClass = null) {
		if (is_null($cssClass) || strlen($cssClass) === 0) {
			return;
		}
		$this->cssClasses[] = $cssClass;
	}

	public function addHtmlAttribute($attribute = null, $value = null) {
		if (is_null($attribute) || strlen($attribute) === 0) {
			return;
		}
		$this->htmlAttributes[$attribute] = $value;
	}

	public function getActiveCssClasses() {
		$classes = ["gridItem"];
		if (is_a($this->actionButton, 'ActionButtonWithSubButtons')) {
			//$primaryButton = $this->actionButton->getPrimaryButton();
			//$printer = new ActionButtonPrinter($primaryButton);
			//todo
			//$classes = array_merge($classes, $primaryButton->getCssClasses());
		} else {
			$classes[] = "actionButton";
		}
		foreach ($this->cssClasses as $class) {
			$classes[] = $class;
		}
		foreach ($this->actionButton->getCssClasses() as $class) {
			$classes[] = $class;
		}
		return $classes;
	}

	public function createHtmlForSubButtons($subButtonIds) {
		$html = "";
		$subButtons = [];
		foreach ($subButtonIds as $subButtonId) {
			$subButtons[] = ActionButtonManager::get()->getActionButtonById($subButtonId);
		}
		usort($subButtons, function($a, $b) {
			return strcmp($a->getSortValue() . $a->getId(), $b->getSortValue() . $b->getId());
		});
		foreach ($subButtons as $subButton) {
			$printer = new ActionButtonPrinter($subButton);
			$html .= $printer->createHtml();
		}
		return $html;
	}

	public function createCssClasses() {
		$classes = "gridItem ";
		if (is_a($this->actionButton, 'ActionButtonWithSubButtons')) {
			//$primaryButton = $this->actionButton->getPrimaryButton();
			//$printer = new ActionButtonPrinter($primaryButton);
			//todo
			//foreach ($primaryButton->getCssClasses() as $class) {
			//	$classes .= " ".$class;
			//}
		} else {
			$classes .= "actionButton ";
		}
		foreach ($this->cssClasses as $class) {
			$classes .= " ".$class;
		}
		foreach ($this->actionButton->getCssClasses() as $class) {
			$classes .= " ".$class;
		}
		return $classes;
	}

	public function getActiveHtmlAttributes() {
		$htmlAttributes = [];
		foreach ($this->htmlAttributes as $attribute => $value) {
			if (is_null($value) || strlen($value) === 0) {
				$htmlAttributes[] = $attribute;
			} else {
				$htmlAttributes[$attribute] = $value;
			}
		}
		return $htmlAttributes;
	}

	public function createHtmlAttributes() {
		$htmlAttributes = "";
		foreach ($this->htmlAttributes as $attribute => $value) {
			if (is_null($value) || strlen($value) === 0) {
				$htmlAttributes .= sprintf(" %s", $attribute);
			} else {
				$htmlAttributes .= sprintf(" %s=\"%s\"", $attribute, $value);
			}
		}
		return $htmlAttributes;
	}

	public function createInnerHtml() {
		$state = '$state';
		if ($this->actionButton instanceof StateDependingActionButton) {
			$state = $this->actionButton->getCurrentState();
		}
		return str_replace('$state', $state, $this->buttonText);
	}

}

?>