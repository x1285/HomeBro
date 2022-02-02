<?php

require_once("CreationFormBuilder.class.php");

//ALL ACTION CLASS NAMES NEED TO BE LINKED: $ACTION_CLASSES
require_once("HttpGetAction.class.php");
require_once("HttpGetStateQueryAction.class.php");
require_once("HttpPostAction.class.php");
require_once("OpenHabRESTAction.class.php");
require_once("MakerEventAction.class.php");
require_once("Mhz433Action.class.php");
require_once("ShellAction.class.php");
require_once("StateDependingAction.class.php");
require_once("StateChangeAction.class.php");
require_once("Sequence.class.php");
require_once("SleepAction.class.php");

ActionManager::getInstance()->initialize();

class ActionManager {

	protected static $instance = null;

	protected $ACTION_CLASSES = [
									"HttpGetAction",
									"HttpGetStateQueryAction",
									"HttpPostAction",
									"OpenHabRESTAction",
									"MakerEventAction",
									"Mhz433Action",
									"ShellAction",
									"StateDependingAction",
									"StateChangeAction",
									"SleepAction",
									"Sequence"
								];

	private $ACTION_LOCATION = "./actions/";
	private $ACTION_MIME_TYPE = "action";

	private $actions = [];

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
		CreationFormBuilder::printHeader();
	}

	protected function __construct(){}

	protected function __clone(){}

	public function initialize() {
		$files = glob($this->ACTION_LOCATION."*.{".$this->ACTION_MIME_TYPE."}", GLOB_BRACE);
		foreach($files as $file){
			$action = unserialize(file_get_contents($file));
			$this->actions[$action->getId()] = $action;
		}
	}

	public function run($actionOrId) {
		$action = $actionOrId;
		if(is_numeric($actionOrId)) {
			$action = $this->getActionById($actionOrId);
		}

		if ($action !== null) {
			$action->run();
		} else {
			die(sprintf("No action for id '%s' found.", $actionOrId));
		}
	}

	public function copy($action) {
		if(is_numeric($action)) {
			$action = $this->getActionById($action);
		}
		if ($action !== null) {
			$copy = clone $action;
			$copy->setId( null );
			return $copy;
		}
		return null;
	}

	public function save($action = null) {
		if ($action !== null) {
			if ($action->getId() === null || $action->getId()<0) {
				$newId = $this->getNextId();
				$action->setId( $newId );
			}
			$bytes = serialize($action);
			$filepath = $this->ACTION_LOCATION.$action->getId().".".$this->ACTION_MIME_TYPE;
			$successBytes = file_put_contents($filepath, $bytes);

			if ($successBytes !== false && $successBytes > 0){
				$this->actions[$action->getId()] = $action;
				return $action;
			}
		}
		return null;
	}

	public function delete($actionOrId) {
		if(is_numeric($actionOrId)) {
			$id = $actionOrId;
		} else {
			$id = $actionOrId->getId();
		}
		$deletionSuccess = unlink($this->ACTION_LOCATION.$id.".".$this->ACTION_MIME_TYPE);
		if ($deletionSuccess) {
			unset($this->actions[$id]);
		}
	}

	public function getActionById($id = null) {
		if ($id !== null && array_key_exists($id, $this->actions)) {
			return $this->actions[$id];
		}
		return null;
	}

	public function getActions() {
		return $this->actions;
	}

	public function getActionClassNames() {
		return $this->ACTION_CLASSES;
	}


	public function getListOfAllActionIdsAndNamesPrefixedByClass() {
		$result = [];
		foreach ($this->actions as $action) {
			$result[$action->getId()] = get_class($action).": ".$action->getName();
		}
		asort($result);
		return $result;
	}

	public function getListOfAllActionIdsAndNames() {
		$result = [];
		foreach ($this->actions as $action) {
			$result[$action->getId()] = $action->getName();
		}
		asort($result);
		return $result;
	}

	private function getNextId(){
		$highestId = 0;
		foreach ($this->actions as $action) {
			if ($action->getId() >= $highestId) {
				$highestId = $action->getId();
			}
		}
		return $highestId + 1;
	}

}

?>