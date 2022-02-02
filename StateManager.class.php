<?php

//ALL STATE CLASS NAMES NEED TO BE LINKED: $ACTION_CLASSES
require_once("State.class.php");
require_once("UnboundState.class.php");

StateManager::getInstance()->initialize();

class StateManager {

	protected static $instance = null;

	protected $STATE_CLASSES = ["State","UnboundState"];

	private $STATE_LOCATION = "./states/";
	private $STATE_MIME_TYPE = "state";

	private $states = [];

	public static function getInstance(){
		if(self::$instance === null){
			self::$instance = new self;
		}
		return self::$instance;
	}

	public static function get(){
		return self::getInstance();
	}

	protected function __construct(){}

	protected function __clone(){}

	public function initialize() {
		$files = glob($this->STATE_LOCATION."*.{".$this->STATE_MIME_TYPE."}", GLOB_BRACE);
		foreach($files as $file){
			$state = unserialize(file_get_contents($file));
			$this->states[$state->getId()] = $state;
		}
	}

	public function copy($state) {
		if(is_numeric($state)) {
			$state = $this->getStateById($state);
		}
		if ($state !== null) {
			$copy = clone $state;
			$copy->setId( null );
			return $copy;
		}
		return null;
	}

	public function save($stateOrId) {
		$state = $stateOrId;
		if(is_numeric($stateOrId)) {
			$state = $this->getStateById($stateOrId);
		}
		if ($state !== null) {
			if ($state->getId() === null || $state->getId()<0) {
				$newId = $this->getNextId();
				$state->setId( $newId );
			}
			$bytes = serialize($state);
			$filepath = $this->STATE_LOCATION.$state->getId().".".$this->STATE_MIME_TYPE;
			$successBytes = file_put_contents($filepath, $bytes);

			if ($successBytes !== false && $successBytes > 0){
				$this->states[$state->getId()] = $state;
				return $state;
			}
		}
		return null;
	}

	public function delete($stateOrId) {
		if(is_numeric($stateOrId)) {
			$id = $stateOrId;
		} else {
			$id = $stateOrId->getId();
		}
		$deletionSuccess = unlink($this->STATE_LOCATION.$id.".".$this->STATE_MIME_TYPE);
		if ($deletionSuccess) {
			unset($this->states[$id]);
		}
	}

	public function getStateById($id = null) {
		if ($id !== null && array_key_exists($id, $this->states)) {
			return $this->states[$id];
		}
		return null;
	}

	public function getStates() {
		return $this->states;
	}

	public function getStateClassNames() {
		return $this->STATE_CLASSES;
	}

	public function getListOfAllStateIdsAndNames() {
		$result = [];
		foreach ($this->states as $state) {
			$result[$state->getId()] = $state->getName();
		}
		asort($result);
		return $result;
	}

	private function getNextId(){
		$nextId = 0;
		foreach ($this->states as $state) {
			if ($state->getId() >= $nextId) {
				$nextId = $state->getId()+1;
			}
		}
		return $nextId;
	}

}

?>