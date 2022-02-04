<?php
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("base/SessionPersistentNotificationSystem.class.php");
require_once("ActionManager.class.php");
require_once("ActionButtonManager.class.php");
require_once("StateManager.class.php");
require_once("DynamicThemeChanger.class.php");

$notificationSystem = SessionPersistentNotificationSystem::get("main");
$userSystem = UserSystem::get();

if (isset($_POST["actionButtonClick"]) && is_numeric($_POST["actionButtonClick"])) {
	$actionButtonManager = ActionButtonManager::get();
	$actionButton = $actionButtonManager->getActionButtonById($_POST["actionButtonClick"]);
	$actionButtonManager->onclick($actionButton, true);
	die();
}


$aIdsToRun = [];
$aIdsToRun[] = $_POST["runAction"];
$aIdsToRun[] = $_GET["runAction"];
foreach($aIdsToRun as $actionIds) {
	if (isset($actionIds)) {
		$actionIdsToRun = [];
		if (is_array($actionIds)) {
			$actionIdsToRun = $actionIds;
		} else if (is_numeric($actionIds)) {
			$actionIdsToRun[] = $actionIds;
		}
		$actionManager = ActionManager::get();
		foreach ($actionIdsToRun as $actionId) {
			$action = $actionManager->getActionById($actionId);
			$actionManager->run($action);

			$text = sprintf("Action [%d] wurde ausgeführt", $actionId);
			$title = sprintf("'%s' ausgeführt", $action->getName());
			$notificationSystem->addNewNotification($text, $title, "success");
		}
	}
}


if (isset($_POST["deleteAction"]) && is_numeric($_POST["deleteAction"])) {
	$actionManager = ActionManager::get();
	$actionManager->delete($_POST["deleteAction"]);

	$text = sprintf("Action [%d] wurde gelöscht", $_POST["deleteAction"]);
	$title = "Gelöscht";
	$notificationSystem->addNewNotification($text, $title, "success");
}

if (isset($_POST["deleteState"]) && is_numeric($_POST["deleteState"])) {
	$stateManager = StateManager::get();
	$stateManager->delete($_POST["deleteState"]);

	$text = sprintf("State [%d] wurde gelöscht", $_POST["deleteState"]);
	$title = "Gelöscht";
	$notificationSystem->addNewNotification($text, $title, "success");
}

if (isset($_POST["deleteActionButton"]) && is_numeric($_POST["deleteActionButton"])) {
	$actionButtonManager = ActionButtonManager::get();
	$actionButtonManager->delete($_POST["deleteActionButton"]);

	$text = sprintf("Action-Button [%d] wurde gelöscht", $_POST["deleteActionButton"]);
	$title = "Gelöscht";
	$notificationSystem->addNewNotification($text, $title, "success");
}

if ($userSystem->isAuthSessionJustStarted()) {
    $text = "Viel Spaß bei der Nutzung von HomeBro";
    $title = sprintf("Hi %s", $userSystem->getAuthUser()->getName());
    $notificationSystem->addNewNotification($text, $title, "success");
}

$errorsAndWarnings = ob_get_clean();
?>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<title>HomeBro - Die Heimsteuerung</title>
		<?php 
			$notificationSystem->printHeader(); 
			ActionManager::printHeader();
			if (!$userSystem->isAuthenticatedByGetParam()) {
				ActionButtonManager::printHeader();
				DynamicThemeChanger::printHeader();
			}
		?>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="google" value="notranslate">
		<meta name="theme-color" content="#000">
		<link rel="icon" type="image/png" href="img/favicon.ico">
		<link rel="manifest" href="/manifest.json">
		<style>
		<?php 
			include "style.css"; 
		?>
		</style>
		<link rel="stylesheet" type="text/css" href="icons/iconfont.css">
		<?php if ($userSystem->isAuthSessionJustStarted()) { ?>
			<script>window.onload = function() { window.history.pushState({}, document.title, "."); };</script>
		<?php } ?>
	</head>
	<body>
		<header>
			<a href="./">
				<div id="homeBroHeader">
					<h1>HomeBro</h1>
					<h4>Die Heimsteuerung</h4>
					<p>by Maik Wosnitzka</p>
				</div>
			</a>
		</header>
<?php
		DynamicThemeChanger::printBody();
?>
		<div id="overlay"></div>
		<input id="menustate" type="checkbox"/>
		<div id="menubutton">
			<span></span>
			<span></span>
			<span></span>
		</div>
		<div id="menu">
			<details><summary>Buttons verwalten</summary>
				<details><summary>Neuen Button anlegen</summary>
					<form action="?" method="POST">
						<label for="actionButtonCreationForm">Button-Typ:</label><br />
						<select name="actionButtonCreationForm">
						<?php
							$actionManager = ActionButtonManager::get();
							foreach ($actionManager->getActionButtonClassNames() as $clazz) {
								echo sprintf("<option value=\"%s\">%s</option>", $clazz, $clazz);
							}
						?>
						</select><br />
						<input type="submit" value="Neuen Aktions-Button konfigurieren">
					</form>
				</details>
				<form action="?" method="POST">
<?php
					$actionButtons = ActionButtonManager::get()->getActionButtons();
					usort($actionButtons, function($a, $b) {
						return strcmp($a->getName(), $b->getName());
					});
					foreach ($actionButtons as $actionButton) {
						echo sprintf("<p>\n\t<b>%s</b><br />", $actionButton->getName());
						echo sprintf("<i>%s</i><br />", get_class($actionButton));
						echo sprintf("\n\t<button class=\"withIcon edit\" type=\"submit\" name=\"actionButtonEditForm\" value=\"%d\">Bearbeiten</button>", $actionButton->getId());
						echo sprintf("\n\t<button class=\"withIcon copy\" type=\"submit\" name=\"copyActionButton\" value=\"%d\">Kopieren</button>", $actionButton->getId());
						$jsDeleteConfirm = "return confirm('Action-Button löschen?')";
						echo sprintf("\n\t<button class=\"withIcon delete\" type=\"submit\" name=\"deleteActionButton\" value=\"%d\" onclick=\"%s\">Löschen</button>", $actionButton->getId(), $jsDeleteConfirm);
						echo sprintf("</p>");
					}
?>
				</form>
			</details>
<?php
			/***********************
			 ** ERRORS & WARNINGS **
			 ***********************/
			if(strlen($errorsAndWarnings) > 0) {
				echo sprintf("<details><summary>Errors and Warnings</summary><div>%s</div></details>", $errorsAndWarnings);
			}
?>
			<details><summary>Actions verwalten</summary>
				<details><summary>Neue Action</summary>
				<form action="?" method="POST">
					<label for="actionCreationForm">Action-Typ:</label><br />
					<select name="actionCreationForm">
					<?php
						$actionManager = ActionManager::get();
						foreach ($actionManager->getActionClassNames() as $clazz) {
							echo sprintf("<option value=\"%s\">%s</option>", $clazz, $clazz);
						}
					?>
					</select><br />
					<input type="submit" value="Neue Action konfigurieren">
				</form>
				</details>
				<form action="?" method="POST">
<?php
					$actions = ActionManager::get()->getActions();
					usort($actions, function($a, $b) {
						return strcmp($a->getName(), $b->getName());
					});
					foreach ($actions as $id => $action) {
						echo sprintf("<p>\n\t<b>%s</b><br />", $action->getName());
						echo sprintf("<i>%s</i><br />", get_class($action));
						echo sprintf("\n\t<button class=\"withIcon execute\" type=\"submit\" name=\"runAction\" value=\"%d\">Ausführen</button>", $action->getId());
						echo sprintf("\n\t<button class=\"withIcon edit\" type=\"submit\" name=\"actionEditForm\" value=\"%d\">Bearbeiten</button>", $action->getId());
						echo sprintf("\n\t<button class=\"withIcon copy\" type=\"submit\" name=\"copyAction\" value=\"%d\">Kopieren</button>", $action->getId());
						$jsDeleteConfirm = "return confirm('Action löschen?')";
						echo sprintf("\n\t<button class=\"withIcon delete\" type=\"submit\" name=\"deleteAction\" value=\"%d\" onclick=\"%s\">Löschen</button>", $action->getId(), $jsDeleteConfirm);
						echo sprintf("</p>");
					}
?>
				</form>
			</details>
			<details><summary>Zustände verwalten</summary>
				<details><summary>Neuen Zustand anlegen</summary>
					<form action="?" method="POST">
						<label for="stateCreationForm">State-Typ:</label><br />
						<select name="stateCreationForm">
<?php
							$stateManager = StateManager::get();
							foreach ($stateManager->getStateClassNames() as $clazz) {
								echo sprintf("<option value=\"%s\">%s</option>", $clazz, $clazz);
							}
?>
						</select><br />
						<input type="submit" value="Neuen State konfigureren">
					</form>
				</details>
				<form action="?" method="POST">
<?php
					$states = StateManager::get()->getStates();
					foreach ($states as $id => $state) {
						echo sprintf("<p>\n\t<b>%s</b><br />", $state->getName());
						echo sprintf("<i>%s - %s</i><br />", get_class($state), $state->getState());;
						echo sprintf("\n\t<button class=\"withIcon edit\" type=\"submit\" name=\"stateEditForm\" value=\"%d\">Bearbeiten</button>", $state->getId());
						echo sprintf("\n\t<button class=\"withIcon copy\" type=\"submit\" name=\"copyState\" value=\"%d\">Kopieren</button>", $state->getId());
						$jsDeleteConfirm = "onclick=\"return confirm('State löschen?')\"";
						echo sprintf("\n\t<button class=\"withIcon delete\" type=\"submit\" name=\"deleteState\" value=\"%d\" onclick=\"%s\">Löschen</button>", $state->getId(), $jsDeleteConfirm);
						echo sprintf("</p>");
					}
?>
				</form>
			</details>
<?php
			if ($userSystem->getAuthUser() != null) { // Needs user management right TODO
?>
			<details><summary>User verwalten</summary>
	            <details><summary>Neuen User einladen</summary>
	                <form action="?" method="POST">
	                    <label for="username">Name*:</label>
	                    <input type="text" name="username" value="unbenannt" required/><br/>
	                    <button class="withIcon add" type="submit" name="inviteNewUser" value="true">Neuen User einladen</button>
	                </form>
	            </details>
	            <details><summary>Eigenen User verwalten</summary>
	                <form action="?" method="POST">
	                	<button class="withIcon add" type="submit" name="manageUser" value="createNewSession">URL für neue Session anlegen</button>
	                </form>
	            </details>
	            <details><summary>Offene Einladungen</summary>
<?php
                    $openSessionInvitations = $userSystem->getAllOpenInvitations();
                    $users = $userSystem->getAllUsers();
                    foreach ($openSessionInvitations as $openSessionInvitation) {
                        echo "<form action=\"?\" method=\"POST\">";
                        echo sprintf("<span>Offene Einladung an: %s</span>",
                            $users[$openSessionInvitation->getUserId()]->getName());
                        echo sprintf("<input type=\"hidden\" name=\"session\" value=\"%s\" readonly/>",
                            $openSessionInvitation->getId());
                        echo sprintf("<button class=\"withIcon remove\" type=\"submit\" name=\"manageSession\" value=\"kill\">Einladung zurücknehmen</button>");
                        echo sprintf("<button class=\"withIcon magic\" type=\"submit\" name=\"manageInvitation\" value=\"regenerateInvitationUrl\">Neuen Einladungslink erzeugen</button>");
                        echo "</form>";
                    }
?>
				</details>
				<details><summary>Aktive Sessions verwalten</summary>
					<form action="?" method="POST">
						<label for="session">Session*:</label>
						<select name="session" required>
<?php
	                    $users = $userSystem->getAllUsers();
	                    //current session
	                    $authSession = $userSystem->getAuthSession();
	                    if ($authSession !== null) {
	                        $readableSessionName = sprintf("Own: (User: %s [%s], Session: [%s])",
	                            $users[$authSession->getUserId()]->getName(),
	                            $authSession->getUserId(),
	                            $authSession->getId());
	                        echo sprintf("<option value=\"%s\">%s</option>",
	                            $authSession->getId(), $readableSessionName);
	                    }
	                    //all other, active sessions
	                    $activeSessions = $userSystem->getAllActiveSessions();
	                    usort($activeSessions, function($a, $b) {
	                        return strcmp($a->getUserId()."#".$a->getId(), $b->getUserId()."#".$b->getId());
	                    });
	                    foreach ($activeSessions as $activeSession) {
	                        if ($authSession !== null && $activeSession->getId() === $authSession->getId()) {
	                            //skip current session
	                            continue;
	                        }
	                        $readableSessionName = sprintf("User: %s [%s], Session: [%s])",
	                            $users[$activeSession->getUserId()]->getName(),
	                            $activeSession->getUserId(),
	                            $activeSession->getId());
	                        echo sprintf("<option value=\"%s\">%s</option>",
	                            $activeSession->getId(), $readableSessionName);
	                    }
?>
						</select>
						<br>
						<button class="withIcon close" type="submit" name="manageSession" value="kill">Session beenden</button>
						<button class="withIcon select" type="submit" name="manageSession" value="showDetails">Session anzeigen</button>
			        </form>
			    </details>
			</details>
<?php
			} // Needs user management right
?>
		</div>
		<div id="main">
<?php

		$notificationSystem->printBody();

		$actionButtons = ActionButtonManager::get()->getActionButtons();
		usort($actionButtons, function($a, $b) {
			return strcmp($a->getSortValue() . $a->getId(), $b->getSortValue() . $b->getId());
		});
		foreach ($actionButtons as $actionButton) {
			if ($actionButton->isHidden()) {
				continue;
			}
			$actionButton->createButtonPrinter()->printHtml();
		}


		/********************
		 ** ACTION BUTTONS **
		 ********************/

		if (isset($_POST["actionButtonType"])) {
			$clazz = $_POST["actionButtonType"];
			if (class_exists($clazz) && is_callable([$clazz, "createByFormResponse"])) {
				$actionButtonToSave = $clazz::createByFormResponse($_POST);

				$actionButtonManager = ActionButtonManager::get();
				$newSavedActionButton = $actionButtonManager->save( $actionButtonToSave );
				if ($newSavedActionButton !== null) {
					$message = "Neuer Action-Button gespeichert.";
					if ($actionButtonToSave->getId() > -1) {
						$message = "Änderungen gespeichert";
					}
					$message = sprintf("%s [id:\"%s\"]", $message, $newSavedActionButton->getId());
					$notificationSystem->addNewNotification($message, null, "success");
				} else {
					ob_start();
					var_dump($actionButtonToSave, $_POST);
					$vardump = ob_get_clean();
					die(sprintf("Fehler beim speichern des neuen Action-Buttons [name:\"%s\"].\n<details><summary>VARDUMP:</summary>%s</details>", 
						$actionButtonToSave->getName(), 
						$vardump)
					);
				}
			} else {
				echo sprintf("\"%s\" is no known action type.", $clazz);
			}

		} elseif (isset($_POST["actionButtonCreationForm"])) {
			$clazz = $_POST["actionButtonCreationForm"];
			if (class_exists($clazz) && is_callable([$clazz, "createActionButtonCreationFormBuilder"])) {
				$builder = $clazz::createActionButtonCreationFormBuilder($clazz);
				echo $builder->build();
			} else {
				echo sprintf("\"%s\" is no known action button type.", $clazz);
			}

		} elseif (isset($_POST["actionButtonEditForm"])) {
			$actionButtonId = $_POST["actionButtonEditForm"];
			$actionButtonManager = ActionButtonManager::get();
			if (is_numeric($actionButtonId)) {
				$actionButton = $actionButtonManager->getActionButtonById($actionButtonId);
				$clazz = get_class($actionButton);
				if ($actionButton !== null && is_callable([$clazz, "createActionButtonCreationFormBuilder"])) {
					$builder = $clazz::createActionButtonCreationFormBuilder($clazz, $actionButton);
					echo $builder->build();
					return;
				}
			}
			echo sprintf("\"%s\" is no valid action button id.", $actionButtonId);

		} elseif (isset($_POST["copyActionButton"]) && is_numeric($_POST["copyActionButton"])) {
			$actionButtonManager = ActionButtonManager::get();
			$copy = $actionButtonManager->copy($_POST["copyActionButton"]);
			$copy->setName(sprintf("Kopie von '%s'", $copy->getName()));
			if($copy !== null) {
				$clazz = get_class($copy);
				if ($clazz !== null && is_callable([$clazz, "createActionButtonCreationFormBuilder"])) {
					$builder = $clazz::createActionButtonCreationFormBuilder($clazz, $copy);
					$builder->withActionPath("");
					echo $builder->build();
				}
			}

		}

		/*************
		 ** ACTIONS **
		 *************/

		function printActionEditPanel($action) {
			$clazz = get_class($action);
			if ($action !== null && is_callable([$clazz, "createActionCreationFormBuilder"])) {
				$builder = $clazz::createActionCreationFormBuilder($clazz, $action);
				echo $builder->build();
				return;
			}
		}

		if (isset($_POST["actionType"])) {
			$clazz = $_POST["actionType"];
			if (class_exists($clazz) && is_callable([$clazz, "createByFormResponse"])) {
				$actionToSave = $clazz::createByFormResponse($_POST);

				$actionManager = ActionManager::get();
				$newSavedAction = $actionManager->save( $actionToSave );
				if ($newSavedAction !== null) {
					$message = "Neue Action gespeichert.";
					if ($actionToSave->getId() > -1) {
						$message = "Änderungen gespeichert";
					}
					$message = sprintf("%s [id:\"%s\"]", $message, $newSavedAction->getId());
					$notificationSystem->addNewNotification($message, null, "success");
					printActionEditPanel($newSavedAction);
				} else {
					ob_start();
					var_dump($actionToSave, $_POST);
					$vardump = ob_get_clean();
					echo sprintf("Fehler beim speichern der neuen Action [name:\"%s\"].\n<details><summary>VARDUMP:</summary>%s</details>", $actionToSave->getName(), $vardump);
					printActionEditPanel($actionToSave);
				}
			} else {
				echo sprintf("\"%s\" is no known action type.", $clazz);
			}

		} elseif (isset($_POST["actionCreationForm"])) {
			$clazz = $_POST["actionCreationForm"];
			if (class_exists($clazz) && is_callable([$clazz, "createActionCreationFormBuilder"])) {
				$builder = $clazz::createActionCreationFormBuilder($clazz);
				echo $builder->build();
			} else {
				echo sprintf("\"%s\" is no known action type.", $clazz);
			}

		} elseif (isset($_POST["actionEditForm"])) {
			$actionId = $_POST["actionEditForm"];
			$actionManager = ActionManager::get();
			if (is_numeric($actionId)) {
				$action = $actionManager->getActionById($actionId);
				printActionEditPanel($action);
			} else {
				echo sprintf("\"%s\" is no valid action id.", $actionId);
			}

		} elseif (isset($_POST["copyAction"])) {
			if (is_numeric($_POST["copyAction"])) {
				$actionManager = ActionManager::get();
				$copy = $actionManager->copy($_POST["copyAction"]);
				$copy->setName(sprintf("Kopie von '%s'", $copy->getName()));
				if($copy !== null) {
					$clazz = get_class($copy);
					if ($clazz !== null && is_callable([$clazz, "createActionCreationFormBuilder"])) {
						$builder = $clazz::createActionCreationFormBuilder($clazz, $copy);
						$builder->withActionPath("");
						echo $builder->build();
					}
				}
			} else {
				echo sprintf("\"%s\" is no valid action id.", $actionId);
			}

		} 

		/**************
		 **  STATES  **
		 **************/

		if (isset($_POST["stateType"])) {
			$clazz = $_POST["stateType"];
			if (class_exists($clazz) && is_callable([$clazz, "createByFormResponse"])) {
				$stateToSave = $clazz::createByFormResponse($_POST);

				$stateManager = StateManager::get();
				$newSavedState = $stateManager->save( $stateToSave );
				if ($newSavedState !== null) {
					$message = "Neuer State gespeichert.";
					if ($stateToSave->getId() > -1) {
						$message = "Änderungen gespeichert";
					}
					$message = sprintf("%s [id:\"%s\"]", $message, $newSavedState->getId());
					$notificationSystem->addNewNotification($message, null, "success");
				} else {
					ob_start();
					var_dump($stateToSave, $_POST);
					$vardump = ob_get_clean();
					die(sprintf("Fehler beim speichern des neuen State [name:\"%s\"].\n<details><summary>VARDUMP:</summary>%s</details>", $stateToSave->getName(), $vardump));
				}
			} else {
				$text = sprintf("\"%s\" ist kein bekannter State Typ.", $clazz);
				$title = "Fehler!";
				$notificationSystem->addNewNotification($text, $title, "error");
			}

		} elseif (isset($_POST["stateCreationForm"])) {
			$clazz = $_POST["stateCreationForm"];
			if (class_exists($clazz) && is_callable([$clazz, "createStateCreationFormBuilder"])) {
				$builder = $clazz::createStateCreationFormBuilder($clazz);
				echo $builder->build();
			} else {
				$text = sprintf("\"%s\" ist kein gültiger State Typ.", $clazz);
				$title = "Fehler!";
				$notificationSystem->addNewNotification($text, $title, "error");
			}

		} elseif (isset($_POST["stateEditForm"])) {
			$stateId = $_POST["stateEditForm"];
			$stateManager = StateManager::get();
			if (is_numeric($stateId)) {
				$state = $stateManager->getStateById($stateId);
				$clazz = get_class($state);
				if ($state !== null && is_callable([$clazz, "createStateCreationFormBuilder"])) {
					$builder = $clazz::createStateCreationFormBuilder($clazz, $state);
					echo $builder->build();
				}
			} else {
				$text = sprintf("\"%s\" ist keine gültige State Id.", $stateId);
				$title = "Fehler!";
				$notificationSystem->addNewNotification($text, $title, "error");
			}

		} elseif (isset($_POST["copyState"]) && is_numeric($_POST["copyState"])) {
			$stateManager = StateManager::get();
			$copy = $stateManager->copy($_POST["copyState"]);
			$copy->setName(sprintf("Kopie von '%s'", $copy->getName()));
			if($copy !== null) {
				$clazz = get_class($copy);
				if ($clazz !== null && is_callable([$clazz, "createStateCreationFormBuilder"])) {
					$builder = $clazz::createStateCreationFormBuilder($clazz, $copy);
					$builder->withActionPath("");
					echo $builder->build();
				}
			}

		}

	    /********************
	    **      USERS      **
	    ********************/
		if ($userSystem->getAuthUser() != null) { // Needs user management right TODO

		    if (isset($_POST["inviteNewUser"]) && $_POST["inviteNewUser"] === "true") {
		        $username = isset($_POST["username"]) && strlen($_POST["username"])>0 ? $_POST["username"] : "unbenannt";
		        $invitationUrl = $userSystem->createInvitationUrlForNewUser($username);
		        echo sprintf("<form>\n<h4>Use this invitation URL for the new user \"%s\":</h4>\n<br>\n", $username);
		        echo sprintf("<input class=\"user-select-all\" type=\"text\" value=\"%s\" readonly/>", $invitationUrl);
		        echo "</form>";
		    }
		    if (isset($_POST["manageUser"]) && $_POST["manageUser"] === "createNewSession") {
				$user = $userSystem->getAuthUser();
		        $usersNewSession = $userSystem->saveSession(new Session(null, $user->getId()));
		        $invitationSecret = $userSystem->regenerateSecret($usersNewSession);
				$invitationUrl = $userSystem->createInvitationUrl($usersNewSession->getId(), $invitationSecret);
				echo sprintf("<form>\n<h4>Hi %s, use this invitation URL for your new session:</h4>\n<br>\n", $user->getName());
				echo sprintf("<label for=\"invitationUrl\">Invitation URL:</label><br />");
				echo sprintf("<input name=\"invitationUrl\" class=\"user-select-all\" type=\"text\" value=\"%s\" readonly/><br />", $invitationUrl);
				echo sprintf("<label for=\"invitationId\">Invitation id:</label><br />");
				echo sprintf("<input name=\"invitationId\" class=\"user-select-all\" type=\"text\" value=\"%s\" readonly/><br />", $usersNewSession->getId());
				echo sprintf("<label for=\"invitationSecret\">1st secret:</label><br />");
				echo sprintf("<input name=\"invitationSecret\" class=\"user-select-all\" type=\"text\" value=\"%s\" readonly/>", $invitationSecret);
				echo "</form>";
		    }
		    if (isset($_POST["manageInvitation"]) && isset($_POST["session"]) && is_numeric($_POST["session"])) {
		        if ($_POST["manageInvitation"] === "regenerateInvitationUrl") {
		            $invitationUrl = $userSystem->recreateInvitationUrlForSessionId($_POST["session"]);
		            $username = $userSystem->getUserBySessionId($_POST["session"])->getName();
		            echo sprintf("<form>\n<h4>Use this new invitation URL for the user \"%s\":</h4>\n<br>\n", $username);
		            echo sprintf("<input type=\"text\" class=\"user-select-all\" value=\"%s\" readonly/>", $invitationUrl);
		            echo "</form>";
		        }
		    }
		    if (isset($_POST["manageSession"]) && isset($_POST["session"]) && is_numeric($_POST["session"])) {
		        if ($_POST["manageSession"] === "kill") {
		            $userSystem->killSession($_POST["session"]);
		            $text = sprintf("Session [%d] wurde beendet", $_POST["session"]);
		            $title = "Session beendet";
		            $notificationSystem->addNewNotification($text, $title, "success");
		        } else if ($_POST["manageSession"] === "showDetails") {
		            $session = $userSystem->getSessionById($_POST["session"]);
		            if ($userSystem->getAuthSession()->getId() === $session->getId()) {
		            	echo "<p><b>This is your own, currently used session!</b></p>";
		            }
		            $user = $userSystem->getUserById($session->getUserId());
		            $dateFormat = "Y/m/d H:i:s";
		            echo "<table>";
		            echo sprintf("<tr><th>Session-Id</th><td>%s</td></tr>", $session->getId());
		            echo sprintf("<tr><th>Start-Time</th><td>%s</td></tr>", date($dateFormat, $session->getStartTime()));
		            echo sprintf("<tr><th>End-Time</th><td>%s</td></tr>", date($dateFormat, $session->getEndTime()));
		            echo sprintf("<tr><th>Last-Time-Seen</th><td>%s</td></tr>", date($dateFormat, $session->getLastTimeSeen()));
		            echo sprintf("<tr><th>User-Id</th><td>%s</td></tr>", $session->getUserId());
		            echo sprintf("<tr><th>User-Name</th><td>%s</td></tr>", $user->getName());
		            echo sprintf("<tr><th>User-Last-Time-Seen</th><td>%s</td></tr>", date($dateFormat, $user->getLastTimeSeen()));
		            echo "</table>";
		        }
		    }

		} // Needs user management right
?>
		</div>
	</body>
</html>