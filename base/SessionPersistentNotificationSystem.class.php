<?php
require_once("UserSystem.class.php");
require_once("NotificationSystem.class.php");
require_once("UserTargetingNotification.class.php");
require_once("SessionTargetingNotification.class.php");

class SessionPersistentNotificationSystem extends NotificationSystem {

    protected static $instances = [];

    protected static $NOTIFICATIONS_LOCATION = "./notifications/";
    protected static $NOTIFICATIONS_MIME_TYPE = "notification";

	private $persistentNotifications = [];

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
            parent::__construct($identifier);
            $dirPath = $this->createNotificationLocation();
            $sessionNotificationFiles = glob($dirPath. "*.{" . self::$NOTIFICATIONS_MIME_TYPE . "}", GLOB_BRACE);
            foreach ($sessionNotificationFiles as $notificationFile) {
                $notification = unserialize(file_get_contents($notificationFile));
                    $this->persistentNotifications[$notification->getId()] = $notification;
            }
			self::$instances[$identifier] = $this;
		}
	}

	protected function createNotificationLocation() {
	    return self::$NOTIFICATIONS_LOCATION . $this->identifier . "/";
    }

    public function save($notification) {
        if ($notification !== null && $notification instanceof SerializableNotification) {
            if ($notification->getId() === null || $notification->getId() < 0) {
                $newId = $this->getNextNotificationId();
                $notification->setId($newId);
            }
            $bytes = serialize($notification);
            $dirPath = $this->createNotificationLocation();
            if (is_dir($dirPath) || mkdir($dirPath)) {
                $filepath = $dirPath.$notification->getId().".".self::$NOTIFICATIONS_MIME_TYPE;
                $successBytes = file_put_contents($filepath, $bytes);
                if ($successBytes !== false && $successBytes > 0) {
                    $this->persistentNotifications[$notification->getId()] = $notification;
                    return $notification;
                }
            } else {
                $text = "Could not manage notification system with identifier '$this->identifier' in path '$dirPath'.";
                self::$instances[$this->identifier]->addNewNotification($text, "NotificationSystem failure", "error", "");
                return null;
            }
        }
        return null;
    }

    public function delete($notificationOrId) {
        if(is_numeric($notificationOrId)) {
            $id = $notificationOrId;
        } else {
            $id = $notificationOrId->getId();
        }
        $deletionSuccess = unlink($this->createNotificationLocation().$id.".".self::$NOTIFICATIONS_MIME_TYPE);
        if ($deletionSuccess) {
            unset($this->persistentNotifications[$id]);
        }
    }

    private function getNextNotificationId(){
        $highestId = 0;
        foreach ($this->persistentNotifications as $notification) {
            if ($notification->getId() >= $highestId) {
                $highestId = $notification->getId();
            }
        }
        return $highestId + 1;
    }

    public function getNotificationsForAuthSession() {
        $session = UserSystem::get()->getAuthSession();
	    return $this->getNotificationsForSession($session);
    }

    private function getNotificationsForSession($session) {
	    $notificationsForSession = [];
        foreach ($this->persistentNotifications as $ntfctn) {
            $userTargeting = is_a($ntfctn, "UserTargetingNotification")
                && $ntfctn->isUserIdTarget($session !== null ? $session->getUserId() : null);

            $sessionTargeting = is_a($ntfctn, "SessionTargetingNotification")
                && $ntfctn->isSessionIdTarget($session !== null ? $session->getId() : null);

            if ($userTargeting || $sessionTargeting) {
                $notificationsForSession[] = $ntfctn;
            }
        }
        return $notificationsForSession;
    }

	function printHeader(){
        parent::printHeader();
		echo file_get_contents(dirname(__FILE__)."/SessionPersistentNotificationSystem.html");
	}

	function printBody(){
        echo "<div class=\"notificationcenter\">";

        $sessionNotifications = $this->getNotificationsForAuthSession();
        foreach ($sessionNotifications as $sessionNotification) {
            $sessionNotification->printHTML();
        }

        foreach($this->notifications as $notification){
            $notification->printHTML();
        }

        echo "</div>\n";
	}

}

?>