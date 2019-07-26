![Verifalia API](https://img.shields.io/badge/Verifalia%20API-v1.2-green)
[![Packagist](https://img.shields.io/packagist/v/verifalia/sdk.svg?maxAge=2592000)](http://packagist.org/packages/verifalia/sdk)

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

The example below shows how to have your PHP application to submit and validate a single email address using the Verifalia PHP helper library:

```php
<?php
	// Initializes Composer
	
	require_once 'vendor/autoload.php';
	
	// Configures the Verifalia SDK, using your sub-account SID and auth token.
	// Sub-accounts can be managed through the Verifalia dashboard, in the clients area.
	
	$verifalia = new Verifalia\Client('YOUR-ACCOUNT-SID', 'YOUR-AUTH-TOKEN');

	try {
		// Submits the email addresses to Verifalia and waits until the engine
		// complete its validation.
	
		$job = $verifalia
			->emailValidations
			->submit('alice@example.com', NULL);
		
		// Displays the validation status code

		echo('Validation status: ' . $job->entries[0]->status);
	}
	catch (Exception $ex) {
		echo "Code: " . $ex->getCode() . " Message: " . $ex->getMessage();
	}
?>
```

The `submit()` function allows to validate multiple addresses easily, in a single pass; for this, just pass an array of strings as shown below:

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
	
		$job = $verifalia
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
?>
```

Starting from version 1.2, the library also allows to specify custom data (your internal customer or record ID, for example) for each entry to be validated, which are then returned to the caller upon the end of the validation job. To use this feature, just pass a `ValidationEntry` instance (or more than one, by way of an array) to the `submit()` function, specifying your custom string:

```php
<?php
	// ...

	use Verifalia\EmailAddresses\ValidationEntry;

	// ...
	
	$job = $verifalia
		->emailValidations
		->submit(new ValidationEntry('alice@example.com', 'my custom data'), NULL);
	
	// Displays the results
	
	for ($x = 0; $x < count($job->entries); $x++) {
		$entry = $job->entries[$x];
		echo($entry->inputData . ' - ' . $entry->status . ' - custom: ' . $entry->custom . "\n");
	}

	// ...
?>
```

Also starting from version 1.2, this SDK allows to specify the desired results quality level for an email validation job. To do that, embed your entries - being them a `ValidationEntry` instance (or more than one, by way of an array) or the email address string (or the array of strings to validate) - inside a new `Validation` instance and pass it to the `submit()` function, specifying the desired level in its constructor, as shown below:

```php
<?php
	// ...

	use Verifalia\EmailAddresses\ValidationEntry;
	use Verifalia\EmailAddresses\Validation;

	// ...

	// Submits the validation job, using the "extreme" quality level
	
	$job = $verifalia
		->emailValidations
		->submit(new Validation(array('alice@example.com', 'test@@invalid.tld'), 'extreme'), NULL);
	
	// Displays the results
	
	for ($x = 0; $x < count($job->entries); $x++) {
		$entry = $job->entries[$x];
		echo($entry->inputData . ' - ' . $entry->status . "\n");
	}

	// ...
?>
```

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
	
		$job = $verifalia
			->emailValidations
			->submit(array('alice@example.com', 'bob@example.net'));
		
		// Polls the Verifalia service until the whole email validation job is completed
		
		while ($job->status != 'completed') {
			// Waits for 5 seconds before issuing a new poll request
		
			sleep(5000);
			
			$job = $verifalia
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
?>
```
