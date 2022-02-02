<?php

require_once("User.class.php");
require_once("Session.class.php");

UserSystem::getInstance()->initialize();

class UserSystem {

    protected static $instance = null;

    private $USER_LOCATION = "./users/";
    private $USER_MIME_TYPE = "user";

    private $SESSION_LOCATION = "./sessions/";
    private $SESSION_MIME_TYPE = "session";

    private $SESSION_ID_COOKIENAME = "SID";
    private $SESSION_SECRET_COOKIENAME = "SS";

    private $GET_AUTH_PARAM = "G_AUTH";

    private $users = [];
    private $sessions = [];

    private $authUser;
    private $authSession;
    private $authSessionJustStarted = false;
    private $authenticatedByGetParam = false;

    public static function getInstance() {
        if(self::$instance === null){
            self::$instance = new self;
        }
        return self::$instance;
    }

    public static function get() {
        return self::getInstance();
    }

    protected function __construct(){}

    protected function __clone(){}

    public function initialize() {
        $userFiles = glob($this->USER_LOCATION."*.{".$this->USER_MIME_TYPE."}", GLOB_BRACE);
        foreach($userFiles as $userFile){
            $user = unserialize(file_get_contents($userFile));
            $this->users[$user->getId()] = $user;
        }
        $sessionFiles = glob($this->SESSION_LOCATION."*.{".$this->SESSION_MIME_TYPE."}", GLOB_BRACE);
        foreach($sessionFiles as $sessionFile){
            $i = 3;
            $session = null;
            do {
                $i--;
                $session = unserialize(file_get_contents($sessionFile));
                if ($session === null || empty($session)) {
                    usleep(400);
                } else {
                    $i = 0;
                }
            } while ($i > 0);
            $this->sessions[$session->getId()] = $session;//das ist manchmal kaputt !?
        }
        $this->reauthenticate();
    }

    private function reauthenticate() {
        if (isset($_GET[$this->SESSION_ID_COOKIENAME], $_GET[$this->SESSION_SECRET_COOKIENAME])) {
            $sessionId = $_GET[$this->SESSION_ID_COOKIENAME];
            $sessionSecret = $_GET[$this->SESSION_SECRET_COOKIENAME];

            $isGetAuthentificationProcess = isset($_GET[$this->GET_AUTH_PARAM]);
            $this->authenticate($sessionId, $sessionSecret, !$isGetAuthentificationProcess);
            if ($isGetAuthentificationProcess) {
                $this->authenticatedByGetParam = true;
            } else {
                $this->authSessionJustStarted = true;
            }
        } else if (isset($_COOKIE[$this->SESSION_ID_COOKIENAME], $_COOKIE[$this->SESSION_SECRET_COOKIENAME])) {
            $sessionId = $_COOKIE[$this->SESSION_ID_COOKIENAME];
            $sessionSecret = $_COOKIE[$this->SESSION_SECRET_COOKIENAME];
            $this->authenticate($sessionId, $sessionSecret, false);
        } else if (isset($_POST[$this->SESSION_ID_COOKIENAME], $_POST[$this->SESSION_SECRET_COOKIENAME])) {
            $sessionId = $_POST[$this->SESSION_ID_COOKIENAME];
            $sessionSecret = $_POST[$this->SESSION_SECRET_COOKIENAME];
            $this->authenticate($sessionId, $sessionSecret, false);
        } else {
            $this->handleMissingSessionData();
        }
    }

    public function authenticate($sessionId, $secret, $regenerateSecret = false) {
        $session = $this->getSessionById($sessionId);
        if ($session !== null && $session->isActive() && $session->isValidSecret($secret)) {
            $this->authSession = $session;
            $this->authUser = $this->getUserById($this->authSession->getUserId());
            $this->authSession->refreshLastTimeSeen();
            $this->authUser->refreshLastTimeSeen();
            $this->saveSession($this->authSession);
            $this->saveUser($this->authUser);
            $this->cookies($regenerateSecret);
        } else {
            $this->authSession = null;
            $this->authUser = null;
            $this->cookies();
            $this->handleInvalidSessionData();
        }
    }

    public function getAuthUser() {
        return $this->authUser;
    }

    public function getAuthSession() {
        return $this->authSession;
    }

    public function isAuthSessionJustStarted() {
        return $this->authSessionJustStarted;
    }

    public function isAuthenticatedByGetParam() {
        return $this->authenticatedByGetParam;
    }

    public function getSessionById($id) {
        if ($id !== null && array_key_exists($id, $this->sessions)) {
            return $this->sessions[$id];
        }
        return null;
    }

    public function getAllActiveSessions() {
        $activeSessions = [];
        foreach($this->sessions as $session) {
            if ($session->getLastTimeSeen() !== null && $session->isActive()) {
                $activeSessions[] = $session;
            }
        }
        return $activeSessions;
    }

    public function getAllCurrentlyOpenSessions() {
        $now = time();
        $openSessions = [];
        foreach($this->sessions as $session) {
            if ($session->getLastTimeSeen() !== null && $session->isActive()) {
                $secondsDiff = $now - $session->getLastTimeSeen();
                if ($secondsDiff < 6) {
                    // Sessions die in den letzten 6 Sekunden ein Request gefeuert haben, nehmen wir als current an
                    $openSessions[] = $session;
                }
            }
        }
        return $openSessions;
    }

    public function getAllOpenInvitations() {
        $openSessionInvitations = [];
        foreach($this->sessions as $session) {
            if ($session->getLastTimeSeen() === null && $session->isActive()) {
                $openSessionInvitations[] = $session;
            }
        }
        return $openSessionInvitations;
    }

    public function getUserById($id) {
        if ($id !== null && array_key_exists($id, $this->users)) {
            return $this->users[$id];
        }
        return null;
    }

    public function getAllUsers() {
        return $this->users;
    }

    public function getUserByName($name) {
        if ($name !== null) {
            foreach($this->users as $user) {
                if ($user->getName() === $name) {
                    return $user;
                }
            }
        }
        return null;
    }

    public function getUserBySessionId($sessionId) {
        if ($sessionId !== null) {
            $session = $this->getSessionById($sessionId);
            return $this->getUserById($session->getUserId());
        }
        return null;
    }

    private function cookies($regenerateSecret = false) {
        if ($this->authSession === null) {
            setcookie($this->SESSION_ID_COOKIENAME, "", time()-60);
            setcookie($this->SESSION_SECRET_COOKIENAME, "", time()-60);
        } else if ($regenerateSecret === true) {
            $expires = time() + 365*24*60*60;
            setcookie($this->SESSION_ID_COOKIENAME, $this->authSession->getId(), $expires);
            setcookie($this->SESSION_SECRET_COOKIENAME, $this->regenerateSecret($this->authSession), $expires);
        }
    }

    public function recreateInvitationUrlForSessionId($sessionId = null) {
        $session = $this->getSessionById($sessionId);
        $invitationSecret = $this->regenerateSecret($session);
        return $this->createInvitationUrl($session->getId(), $invitationSecret);
    }

    public function createInvitationUrlForNewUser($username) {
        $newUser = $this->saveUser(new User(null, $username));
        $newUsersFirstSession = $this->saveSession(new Session(null, $newUser->getId()));
        $invitationSecret = $this->regenerateSecret($newUsersFirstSession);
        return $this->createInvitationUrl($newUsersFirstSession->getId(), $invitationSecret);
    }

    public static function createInvitationUrl($sessionId, $secret) {
        $invitationUrl = sprintf("/remote/?SID=%s&SS=%s\"", $sessionId, $secret);
        return $invitationUrl;
    }

    public function regenerateSecret($session) {
        $new = md5(uniqid($session->getId() . rand(), true));
        $session->setSecret($new);
        $this->saveSession($session);
        return $new;
    }

    public function saveUser($user) {
        if ($user !== null && $user instanceof User) {
            if ($user->getId() === null || $user->getId()<0) {
                $newId = $this->getNextUserId();
                $user->setId( $newId );
            }
            $bytes = serialize($user);
            $filepath = $this->USER_LOCATION.$user->getId().".".$this->USER_MIME_TYPE;
            $successBytes = file_put_contents($filepath, $bytes);
            if ($successBytes !== false && $successBytes > 0){
                $this->users[$user->getId()] = $user;
                return $user;
            }
        }
        return null;
    }

    public function saveSession($session) {
        if ($session !== null && $session instanceof Session) {
            if ($session->getId() === null || $session->getId() < 0) {
                $newId = $this->getNextSessionId();
                $session->setId($newId);
            }
            $bytes = serialize($session);
            $filepath = $this->SESSION_LOCATION.$session->getId().".".$this->SESSION_MIME_TYPE;
            $successBytes = file_put_contents($filepath, $bytes);
            if ($successBytes !== false && $successBytes > 0) {
                $this->sessions[$session->getId()] = $session;
                return $session;
            }
        }
        return null;
    }

    private function getNextSessionId(){
        $highestId = 0;
        foreach ($this->sessions as $session) {
            if ($session->getId() >= $highestId) {
                $highestId = $session->getId();
            }
        }
        return $highestId + 1;
    }

    private function getNextUserId(){
        $highestId = 0;
        foreach ($this->users as $user) {
            if ($user->getId() >= $highestId) {
                $highestId = $user->getId();
            }
        }
        return $highestId + 1;
    }

    public function killSession($id = null) {
        if ($id !== null && is_numeric($id)) {
            $sessionToKill = $this->getSessionById($id);
            $sessionToKill->kill();
            $this->saveSession($sessionToKill);
        }
    }

    private function handleInvalidSessionData() {
        http_response_code(401);
        die("Invalid session data. Secret maybe too old.");
    }

    private function handleMissingSessionData() {
        http_response_code(401);
        die("Your device is missing permissions to access the requested target.\nContact the administrator for an invite.");
    }

}