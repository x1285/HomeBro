<?php

require_once("HasId.class.php");

class Session extends HasId {

    private $secret;
    private $userId;

    private $startTime;
    private $endTime;
    private $lastTimeSeen;

    private $wrongAuthCounter;

    function __construct($id, $userId, $secret = null, $startTime = null, $endTime = null, $lastTimeSeen = null) {
        parent::__construct($id);
        $this->userId = $userId;
        $this->secret = $secret;
        if ($startTime === null) {
            $this->startTime = time();
        } else {
            $this->startTime = $startTime;
        }
        $this->endTime = $endTime;
        $this->lastTimeSeen = $lastTimeSeen;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function getStartTime() {
        return $this->startTime;
    }

    public function getEndTime() {
        return $this->endTime;
    }

    public function setEndTime($endTime) {
        $this->endTime = $endTime;
    }

    public function getLastTimeSeen() {
        return $this->lastTimeSeen;
    }

    public function refreshLastTimeSeen() {
        $this->lastTimeSeen = time();
    }

    public function kill() {
        $killTime = time();
        if ($this->endTime === null || $this->endTime > $killTime) {
            $this->endTime = time();
        } else {
            $text = sprintf("Die Session (%s) konnte nicht beenden werden, da sie bereits beendet wurde.", $this->getId());
            throw new Exception($text);
        }
    }

    public function isActive() {
        if ($this->endTime === null || $this->endTime > time()) {
            return true;
        }
        return false;
    }

    public function isValidSecret($secret) {
        if ($secret === $this->secret) {
            return true;
        }
        return false;
    }

    public function setSecret($secret) {
        $this->secret = $secret;
    }

}

?>