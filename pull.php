<?php
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("base/UserSystem.class.php");
require_once("base/SessionPersistentNotificationSystem.class.php");
require_once("ActionButtonManager.class.php");

$pullRequestHandler = new PullRequestHandler();

$errorsAndWarnings = ob_get_clean();

if (isset($_GET["actionButtonUpdateRequest"])) {
	$pullRequestHandler->addActionButtonUpdateResponse();
}
if (isset($_GET["createNotification"])) {
    /*TEST*/
    $n = createNotificationTargetingAllCurrentlyOpenSessions("Text for Test notification", "TITLE", "success");
    $ns = SessionPersistentNotificationSystem::get("main");
    $ns->save($n);
    /*TEST ENDE*/
}
if (isset($_GET["notificationsPull"])) {
    $pullRequestHandler->addNotificationResponse();
}

if ($pullRequestHandler->hasResponse()) {
    header('Content-Type: application/json');
    echo $pullRequestHandler->createJson();
    die();
}

function createNotificationTargetingAllCurrentlyOpenSessions($text = "", $title = "", $classification = "") {
    $toIdMapper = function($session) { return $session->getId(); };
    $currentSessionIds = array_map($toIdMapper, UserSystem::get()->getAllCurrentlyOpenSessions());
    return new SessionTargetingNotification($currentSessionIds, $text, $title, $classification);
}

class PullRequestHandler {

    protected $response = [];

	public function __construct(){}

	public function addNotificationResponse() {
        $notificationSystem = SessionPersistentNotificationSystem::get("main");
        $notifications = $notificationSystem->getNotificationsForAuthSession();

        foreach ($notifications as $notification) {
            $builder = XhrResponseBuilder::createFor($notification);
            $notificationSystem->delete($notification); //todo löschen von Notifications für einmaliges abholen.
            $builder->addArrayValue("cause", "notificationsPull");
            $this->response[] = $builder->getResponse();
        }
        return $this->response;
    }

	public function addActionButtonUpdateResponse() {
		//alternativ könnte man auch den gesamten Button neu printen...
		$actionButtons = ActionButtonManager::get()->getActionButtons();

		foreach ($actionButtons as $actionButton) {
			$builder = XhrResponseBuilder::createFor($actionButton);
			$builder->addArrayValue("cause", "actionButtonUpdateRequest");
			$this->response[] = $builder->getResponse();
		}
		return $this->response;
	}

	public function hasResponse() {
	    return sizeof($this->response) > 0;
    }

	public function createJson() {
	    return json_encode($this->response);
    }

}

?>