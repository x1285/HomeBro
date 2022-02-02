<?php
require_once("Notification.class.php");
require_once("HasCreateTimestamp.class.php");

class SerializableNotification extends Notification implements HasCreateTimestamp {

    protected $createTimestamp;
    protected $firstPrintTimestamp;

    public function __construct($text, $title, $classification, $id = null, $createTimestamp = null, $firstPrintTimestamp = null){
        parent::__construct($text, $title, $classification, $id);
        $this->setCreateTimestamp($createTimestamp);
        $this->setFirstPrintTimestamp($firstPrintTimestamp);
    }

    function getCreateTimestamp(){
        return $this->createTimestamp;
    }

    function setCreateTimestamp($createTimestamp = null) {
        if ($createTimestamp == null) {
            $datetime = new DateTime();
            $this->createTimestamp = $datetime;
        } else {
            $this->createTimestamp = $createTimestamp;
        }
    }

    function getFirstPrintTimestamp(){
        return $this->firstPrintTimestamp;
    }

    function setFirstPrintTimestamp($firstPrintTimestamp = null) {
        if ($firstPrintTimestamp == null) {
            $datetime = new DateTime();
            $this->firstPrintTimestamp = $datetime;
        } else {
            $this->firstPrintTimestamp = $firstPrintTimestamp;
        }
    }

    public function createHTML() {
        if ($this->getFirstPrintTimestamp() === null) {
            $this->setFirstPrintTimestamp(time());
        }
        return parent::createHTML();
    }

    public function createJsonArray() {
        if ($this->getFirstPrintTimestamp() === null) {
            $this->setFirstPrintTimestamp(time());
        }
        return parent::createJsonArray();
    }
}