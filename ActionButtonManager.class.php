<?php

require_once("ActionButton.class.php");
require_once("StateDependingActionButton.class.php");
require_once("ActionButtonWithSubButtons.class.php");

require_once("ActionManager.class.php");

ActionButtonManager::getInstance()->initialize();

class ActionButtonManager {

	protected static $instance = null;

	protected $ACTION_BUTTON_CLASSES = [
									"ActionButton",
									"StateDependingActionButton",
									"ActionButtonWithSubButtons"
								];

	protected static $ACTION_BUTTON_ONCLICK_JS = "return handleActionButtonClick(this, %s);";

	private $ACTION_BUTTON_LOCATION = "./actionButtons/";
	private $ACTION_BUTTON_MIME_TYPE = "actionButton";

	private $buttons = [];

	public static function getInstance() {
		if(self::$instance === null){
			self::$instance = new self;
		}
		return self::$instance;
	}

	public static function get() {
		return self::getInstance();
	}

	public static function printHeader() {
		echo file_get_contents(dirname(__FILE__)."/ActionButtonManager.html");
	}

	public static function createJsForOnclick($asXhr = false) {
		return sprintf(self::$ACTION_BUTTON_ONCLICK_JS, $asXhr === true ? "true" : "false");
	}

	protected function __construct(){}

	protected function __clone(){}

	public function initialize() {
		$files = glob($this->ACTION_BUTTON_LOCATION."*.{".$this->ACTION_BUTTON_MIME_TYPE."}", GLOB_BRACE);
		foreach($files as $file){
			$button = unserialize(file_get_contents($file));
			$this->buttons[$button->getId()] = $button;
		}
	}

	public function onclick($actionButtonOrId, $isXHR = false) {
		$actionButton = $actionButtonOrId;
		if (is_numeric($actionButtonOrId)) {
			$actionButton = $this->getActionButtonById($actionButtonOrId);
		}

		if ($actionButton === null) {
			die(sprintf("No action button for id '%s' found.", $actionButtonOrId));
		}

		$actionButton->onclick($isXHR);
	}

	public function copy($actionButtonOrId) {
		$button = $actionButtonOrId;
		if (is_numeric($actionButtonOrId)) {
			$button = $this->getActionButtonById($actionButtonOrId);
		}
		if ($button !== null) {
			$copy = clone $button;
			$copy->setId( null );
			return $copy;
		}
		return null;
	}

	public function save($actionButton = null) {
		if ($actionButton !== null) {
			if ($actionButton->getId() === null || $actionButton->getId()<0) {
				$newId = $this->getNextId();
				$actionButton->setId( $newId );
			}
			$bytes = serialize($actionButton);
			$filepath = $this->ACTION_BUTTON_LOCATION.$actionButton->getId().".".$this->ACTION_BUTTON_MIME_TYPE;
			$successBytes = file_put_contents($filepath, $bytes);

			if ($successBytes !== false && $successBytes > 0){
				$this->buttons[$actionButton->getId()] = $actionButton;
				return $actionButton;
			}
		}
		return null;
	}

	public function delete($actionButtonOrId) {
		if(is_numeric($actionButtonOrId)) {
			$id = $actionButtonOrId;
		} else {
			$id = $actionButtonOrId->getId();
		}
		$deletionSuccess = unlink($this->ACTION_BUTTON_LOCATION.$id.".".$this->ACTION_BUTTON_MIME_TYPE);
		if ($deletionSuccess) {
			unset($this->buttons[$id]);
		}
	}

	public function getActionButtonById($id = null) {
		if ($id !== null && array_key_exists($id, $this->buttons)) {
			return $this->buttons[$id];
		}
		return null;
	}

	public function getActionButtons() {
		return $this->buttons;
	}

	public function getActionButtonClassNames() {
		return $this->ACTION_BUTTON_CLASSES;
	}

	public function getListOfAllActionButtonIdsAndNames() {
		$result = [];
		foreach ($this->buttons as $button) {
			$result[$button->getId()] = $button->getName();
		}
		asort($result);
		return $result;
	}

	private function getNextId(){
		$highestId = 0;
		foreach ($this->buttons as $button) {
			if ($button->getId() >= $highestId) {
				$highestId = $button->getId();
			}
		}
		return $highestId + 1;
	}

}

?>