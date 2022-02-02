<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("../Mhz433Action.class.php");

$actionObj = new Mhz433Action(0, "name", "00001", "03", "1");

$actionObj->run();
  
file_put_contents($actionObj->getId().".action", serialize($actionObj));


?>