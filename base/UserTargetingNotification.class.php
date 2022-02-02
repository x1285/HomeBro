<?php
require_once("SerializableNotification.class.php");

class UserTargetingNotification extends SerializableNotification {

    protected $targetUserIds = [];

    public function __construct($targetUserIds, $text, $title, $classification, $id = null, $createTimestamp = null, $firstPrintTimestamp = null){
        parent::__construct($text, $title, $classification, $id, $createTimestamp, $firstPrintTimestamp);
        $this->targetUserIds = $targetUserIds;
    }

    function isUserIdTarget($userId){
        return $userId !== null && in_array($userId, $this->targetUserIds);
    }

}