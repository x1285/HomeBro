<?php

class DynamicThemeChanger {

	public static function printHeader() {
		echo file_get_contents(dirname(__FILE__)."/DynamicThemeChangerHeader.html");
	}

	public static function printBody() {
		echo '<div id="bg"></div>';
	}

}
?>