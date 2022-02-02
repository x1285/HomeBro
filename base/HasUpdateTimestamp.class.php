<?php

require_once("HasCreateTimestamp.class.php");

class HasUpdateTimestamp implements HasCreateTimestamp {

    private $createTimestamp;
	private $timestamp_last_update;

	function updateTimestamp(){
		$datetime = new DateTime();
		$this->timestamp_last_update = $datetime->format("Y-m-d H:i:s");
	}

	function getUpdateTimestamp(){
		return $this->timestamp_last_update;
	}

	function setUpdateTimestamp($timestamp_last_update){
		$this->timestamp_last_update = $timestamp_last_update;
	}

    public function getCreateTimestamp() {
        return $this->createTimestamp;
    }

    public function setCreateTimestamp($createTimestamp = null) {
        $this->createTimestamp = $createTimestamp;
    }
}

?>