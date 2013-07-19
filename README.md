Magento Installer
=================

Composer's script handler to install Magento after a `composer install` / `composer update`.

Running tests
-------------
Tests must run in process isolation due to instance mocking of Mockery.

	$ git clone …
	$ composer install
	$ phpunit --process-isolation