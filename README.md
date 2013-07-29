Magento Installer
=================

Composer's script handler to install Magento after a `composer install` / `composer update`.

Installation
------------

Require this installer in your `composer.json` file:

	"require": {
		…
        "webgriffe/magento-installer": "dev-master",
        …
    }
    
And then execute the following command:

	$ composer update webgriffe/magento-installer

Usage
-----

Somewhere in your project create a YAML file like this:

	parameters:
	    locale: en_US
    	timezone: America/Los_Angeles
	    default_currency: USD
    	db_host: localhost
    	db_name: magento
    	db_user: root
    	db_pass: password
    	url: http://magento.local/
    	admin_firstname: John
    	admin_lastname: Doe
    	admin_email: john.doe@foo.it
    	admin_username: admin
    	admin_password: password

Then edit your `composer.json` to specify the path of the YAML file and to set the installer as a `post-install-cmd`/`post-update-cmd` script.

	"scripts": {
        "post-install-cmd": [
            "Webgriffe\\MagentoInstaller\\ScriptHandler::installMagento"
        ],
        "post-update-cmd": [
            "Webgriffe\\MagentoInstaller\\ScriptHandler::installMagento"
        ]
    }
    …
    "extra": {
    	…
        "install": "path/to/your/file.yml",
        …
    }

If something goes wrong during the installation you can fix your parameters in the YAML file and relaunch the installation through the command `composer run-script post-install-cmd`. The installation will be skipped automatically if the MySQL database already exists (it assumes that if there is the database, Magento is installed).

Running tests
-------------
Tests must run in process isolation due to instance mocking of Mockery.

	$ git clone …
	$ composer install
	$ phpunit --process-isolation