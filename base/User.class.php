<?php

require_once("HasIdAndName.class.php");

class User extends HasIdAndName {

    private $lastTimeSeen;

    function __construct($id = -1, $name = "unbenannt") {
        parent::__construct($id, $name);
    }

    public function getLastTimeSeen() {
        return $this->lastTimeSeen;
    }

    public function refreshLastTimeSeen() {
        $this->lastTimeSeen = time();
    }

}

?>