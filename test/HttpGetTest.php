<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("../HttpGetAction.class.php");

$actionObj = new HttpGetAction(1, "name", "http://localhost:8080/api/notify/?title=%title&text=%text");
$actionObj->setUrlVariable("%title", "Test %20 Titel");
$actionObj->setUrlVariable("%text", "An awesome test text 123-_? :-)");

//echo $actionObj->run();

echo "<hr/>";
  
echo serialize($actionObj);

file_put_contents($actionObj->getId().".action", serialize($actionObj));


?>