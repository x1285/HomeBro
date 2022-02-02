<?php
require_once('Notification.class.php');

class NotificationSystem {

    protected static $instances = [];
    protected $identifier;

	protected $notifications = [];

	public static function get($identifier) {
        if (array_key_exists($identifier, self::$instances)) {
        	return self::$instances[$identifier];
        } else {
        	return new self($identifier);
        }
    }

	protected function __construct($identifier){
		if (array_key_exists($identifier, self::$instances)) {
			$text = "The identifier (".$identifier.") was used to register another notification system, but it already was registered.";
			self::$instances[$identifier]->addNewNotification($text, "NotificationSystem failure", "error", "");
			return null;
		} else {
            $this->identifier = $identifier;
			$this->notifications = array();
			self::$instances[$identifier] = $this;
		}
	}

	function addNewNotification($text = "", $title = "", $classification = ""){
		$this->notifications[] = new Notification($text, $title, $classification);
	}

	function addNotification($notification){
		$this->notifications[] = $notification;
	}

	function printHeader(){
		echo file_get_contents(dirname(__FILE__)."/NotificationSystem.html");
	}

	function printBody(){
		echo "<div class=\"notificationcenter\">";
		foreach($this->notifications as $notification){
            $notification->printHTML();
		}
		echo "</div>\n";
	}

}

?>