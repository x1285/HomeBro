# HomeBro
HomeBro is a PHP web application I developed for my Raspberry Pi to manage and control my smart home on my mobile, laptop and on wall mounted tablets and to connect several systems like [openHAB](https://www.openhab.org/) and smart devices like [Shelly](https://shelly.cloud/) or 443 MHz power sockets.
It comes as a simple standalone application and does not have any dependencies to other frameworks or systems and does not even need a database.
## Set up
Just put all files on your webserver. To persist configurations, the application needs write acces into following subfolders:
- /actionButtons
- /actions
- /sessions
- /states
- /users

All HTTP requests will initially be rejected, because the mandatory authentification data is missing. To initially access the application, empty the body of the method `handleMissingSessionData` inside `/base/UserSystem.class.php`:
```php
private function handleMissingSessionData() {
    //http_response_code(401);
    //die("Your device is missing permissions to access the requested target.\nContact the administrator for an invite.");
}
```
After those changes everybody can access the application. You should access the web page, click on the menu > "User verwalten" > "Einen neuen User einladen", type in a name for your first user and click on "Neuen User einladen" to create an access-URL. 

:warning: Do not forget to reset the method after you created your first User.

Use the created access-URL to login to HomeBro. Your session data is then stored in your browsers cookies. You can invite more users or create more sessions for a user and set up Actions, ActionButtons, States and more.
