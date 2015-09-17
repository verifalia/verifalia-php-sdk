Verifalia REST API - PHP SDK and helper library
================================================

Verifalia provides a simple HTTPS-based API for validating email addresses and checking whether they are deliverable or not. Learn more at http://verifalia.com

## Adding Verifalia REST API support to your PHP project ##

The easiest way to add support for the Verifalia REST API to your PHP project is to use [composer](http://getcomposer.org), which will automatically download and install the required files [from Packagist](http://packagist.org/packages/verifalia/sdk). With composer installed, run the following from your project root:

```bash
$ php composer.phar require verifalia/sdk
```

### Naming conventions ###

> This package follows the `PSR-0` convention names for its classes, meaning you can even load them easily with your own autoloader.

### Sample usage ###

The example below shows how to have your PHP application to submit and validate a couple of email addresses using the Verifalia PHP helper library:

```php
<?php
	// Initializes Composer
	
	require_once 'vendor/autoload.php';

	// Configures the Verifalia SDK, using your sub-account SID and auth token.
	// Sub-accounts can be managed through the Verifalia dashboard, in the clients area.
	
	$verifalia = new Verifalia\Client('YOUR-ACCOUNT-SID', 'YOUR-AUTH-TOKEN');

	try {
		// Submits the email addresses to Verifalia and waits until the engine
		// complete their validation.
	
		$job = $client
			->emailValidations
			->submit(array('alice@example.com', 'bob@example.net'), NULL);
		
		// Displays the results
		
		for ($x = 0; $x < count($job->entries); $x++) {
			$entry = $job->entries[$x];
			echo($entry->inputData . ' - ' . $entry->status . "\n");
		}
	}
	catch (Exception $ex) {
		echo "Code: " . $ex->getCode() . " Message: " . $ex->getMessage();
	}
```php

Internally, the `submit()` function sends the email addresses to the Verifalia servers and then polls them until the validations complete.
Instead of relying on this automatic polling behavior, you may even manually query the Verifalia servers by way of the `query()` function, as shown below:

```php
<?php
	// Initializes Composer
	
	require_once 'vendor/autoload.php';

	// Configures the Verifalia SDK, using your sub-account SID and auth token.
	// Sub-accounts can be managed through the Verifalia dashboard, in the clients area.
	
	$verifalia = new Verifalia\Client('YOUR-ACCOUNT-SID', 'YOUR-AUTH-TOKEN');

	try {
		// Submits the email addresses to Verifalia, *without* waiting for their validation
	
		$job = $client
			->emailValidations
			->submit(array('alice@example.com', 'bob@example.net'));
		
		// Waits until the whole email validation job is completed
		
		while ($job->status != 'completed') {
			$client
				->emailValidations
				->query($job->uniqueID);
		}
		
		// Displays the results
		
		for ($x = 0; $x < count($job->entries); $x++) {
			$entry = $job->entries[$x];
			echo($entry->inputData . ' - ' . $entry->status . "\n");
		}
	}
	catch (Exception $ex) {
		echo "Code: " . $ex->getCode() . " Message: " . $ex->getMessage();
	}
```php
