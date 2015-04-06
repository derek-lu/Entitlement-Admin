## Testing your Entitlement Server Setup

A set of tests has been provided to test the setup of the entitlement service. Please navigate to the setup_check.html from the hosting server.

Click on any one of the buttons on the left sidebar to perform the corresponding test. Each check will return an “ok” if success, an error message otherwise.

#### See below for the full list of tests

* _All_
	- This will perform the entire test below.
* _PHP Modules_
	- Check if the necessary PHP modules, utilized by the direct entitlement source code, are installed.
* _Configuration_
	- Check if the user has updated the settings.php file.
	-By default, the values are commented out, so the users will have to provide their real values.
* _Database_
	- Check if the direct entitlement database has been successfully created.
	- Check if the direct entitlement database can be accessed with the provided credentials (in settings.php).
* _HTTP Connection_
	- Check if the hosting server can access HTTP (unsecured) websites.
* _HTTPS Connection_
	- Check if the hosting server can access HTTPS (encrypted) websites.
* _Fulfillment URL_
	- Check if the fulfillment URL (hosted by Adobe) is available.
	- This URL is used for obtaining the list of folios within the given account credentials.
