![Verifalia API](https://img.shields.io/badge/Verifalia%20API-v2.1-green)
[![Packagist](https://img.shields.io/packagist/v/verifalia/sdk.svg?maxAge=2592000)](http://packagist.org/packages/verifalia/sdk)

Verifalia REST API - PHP SDK and helper library
================================================

[Verifalia][0] provides a simple HTTPS-based API for validating email addresses in real-time and checking whether they are deliverable or not; this SDK library integrates with Verifalia and allows to [verify email addresses][0] on PHP quickly and easily.

## Adding Verifalia REST API support to your PHP project ##

The best and easiest way to add the Verifalia email verification library to your PHP project is to use [composer](https://getcomposer.org), which will automatically download and install the required files [from Packagist](http://packagist.org/packages/verifalia/sdk). With composer installed, run the following from your project root:

```bash
php composer.phar require verifalia/sdk
```

Windows users can instead run the following:

```batch
composer require verifalia/sdk
```

### Naming conventions ###

> This package follows the `PSR-0` convention names for its classes, meaning you can even load them easily with your own autoloader.

### Authentication ###

Authentication to the Verifalia API is performed by way of either the credentials of your root Verifalia account or of one of its users (previously known as sub-accounts): if you don't have a Verifalia account, just [register for a free one][4]. For security reasons, it is always advisable to [create and use a dedicated user][3] for accessing the API, as doing so will allow to assign only the specific needed permissions to it.

Learn more about authenticating to the Verifalia API at [https://verifalia.com/developers#authentication][2]

Once you have your Verifalia credentials at hand, use them while creating a new instance of the `VerifaliaRestClient` class, which will be the starting point to every other operation against the Verifalia API:

```php
<?php
	// Initializes Composer
	
	require_once 'vendor/autoload.php';

	// Set up the Verifalia client with your credentials

	use \Verifalia\VerifaliaRestClient;

	$verifalia = new VerifaliaRestClient([
		'username' => 'your-username-here',
		'password' => 'your-password-here'
	]);
```

## Validating email addresses ##

Every operation related to verifying / validating email addresses is performed through the `emailValidations` property exposed by the `VerifaliaRestClient` instance you created above. The property is filled with useful methods: in the next few paragraphs we are looking at the most used ones, so it is strongly advisable to explore the library and look for other integration opportunities.

### How to validate an email address ###

To validate an email address from a PHP application you can invoke the `submit()` method: it accepts one or more email addresses and any eventual verification options you wish to pass to Verifalia, including the expected results quality, deduplication preferences and processing priority.

In the next example, we are showing how to verify a single email address using this library and automatically wait for the job completion by passing a `true` value. For more advanced waiting scenarios and progress notifications, you can also pass an instance of the `WaitingStrategy` class.

```php
$validation = $verifalia
	->emailValidations
	->submit('batman@gmail.com', true);

// At this point the address has been validated: let's output its email validation result

$entry = $validation->entries[0];
echo("{$entry->inputData}: {$entry->classification} ({$entry->status})");

// Prints out something like:
// batman@gmail.com: Deliverable (Success)
```

### How to validate a list of email addresses ###

As an alternative to method above, you can avoid automatically waiting and retrieve the email validation results at a later time; this is preferred in the event you are verifying a list of email addresses, which could take minutes or even hours to complete.

Here is how to do that:

```php
$validation = $verifalia
    ->emailValidations
    ->submit([{
		'batman@gmail.com',
		'steve.vai@best.music',
		'samantha42@yahoo.de'
	}]);

echo("Job Id: {$validation->overview->id}");
echo("Status: {$validation->overview->status}");

// Prints out something like:
// Job Id: 290b5146-eeac-4a2b-a9c1-61c7e715f2e9
// Status: InProgress
```

Once you have an email validation job Id, which is always returned by `submit()` as part of the validation's `overview` property, you can retrieve the job data using the `get()` method. Similarly to the submission process, you can either wait for the completion of the job or just retrieve the current job snapshot to get its progress. Only completed jobs have their `entries` filled with the email validation results, however.

In the following example, we are requesting the current snapshot of a given email validation job back from Verifalia:

```php
use \Verifalia\EmailValidations\ValidationStatus;

$validation = $verifalia
    ->emailValidations
    ->get('290b5146-eeac-4a2b-a9c1-61c7e715f2e9');

if ($validation->overview->status === ValidationStatus::COMPLETED)
{
	// $validation->entries will have the validation results!
}
else
{
	// What about having a coffee?
}
```

And here is how to request the same job, asking the SDK to automatically wait for us until the job is completed (that is, _joining_ the job):

```php
$validation = $verifalia
    ->emailValidations
    ->get('290b5146-eeac-4a2b-a9c1-61c7e715f2e9', true);
```

### Don't forget to clean up, when you are done ###

Verifalia automatically deletes completed jobs after 30 days since their completion: deleting completed jobs is a best practice, for privacy and security reasons. To do that, you can invoke the `delete()` method passing the job Id you wish to get rid of:

```php
$verifalia
    ->emailValidations
    ->delete($validation->id);
```

Once deleted, a job is gone and there is no way to retrieve its email validation(s).

## Managing credits ##

To manage the Verifalia credits for your account you can use the `credits` property exposed by the `VerifaliaRestClient` instance created above. Like for the previous topic, in the next few paragraphs we are looking at the most used operations, so it is strongly advisable to explore the library and look for other opportunities.

### Getting the credits balance ###

One of the most common tasks you may need to perform on your account is retrieving the available number of free daily credits and credit packs. To do that, you can use the `getBalance()` method, as shown below:

```php
$balance = $verifalia
    ->credits
    ->getBalance();

echo("Credit packs: {$balance->creditPacks}");
echo("Free daily credits: {$balance->freeCredits}");
echo("Free daily credits will reset in {$balance->freeCreditsResetIn}");

// Prints out something like:
// Credit packs: 956.332
// Free daily credits: 128.66
// Free daily credits will reset in 09:08:23
```

To add credit packs to your Verifalia account visit [https://verifalia.com/client-area#/credits/add][5].

[0]: https://verifalia.com
[2]: https://verifalia.com/developers#authentication
[3]: https://verifalia.com/client-area#/users/new
[4]: https://verifalia.com/sign-up
[5]: https://verifalia.com/client-area#/credits/add