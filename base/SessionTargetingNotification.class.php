<?php
require_once("SerializableNotification.class.php");

class SessionTargetingNotification extends SerializableNotification {

    protected $targetSessionIds = [];

    public function __construct($targetSessionIds, $text, $title, $classification, $id = null, $createTimestamp = null, $firstPrintTimestamp = null){
        parent::__construct($text, $title, $classification, $id, $createTimestamp, $firstPrintTimestamp);
        $this->targetSessionIds = $targetSessionIds;
    }

    function isSessionIdTarget($sessionId){
        return $sessionId !== null && in_array($sessionId, $this->targetSessionIds);
    }

}