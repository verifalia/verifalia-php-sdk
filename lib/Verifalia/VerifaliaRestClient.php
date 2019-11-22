<?php

namespace Verifalia {

	use \Verifalia\Credits\CreditsRestClient;
	use \Verifalia\EmailValidations\EmailValidationsRestClient;
	use \Verifalia\Rest\MultiplexedRestClient;
	use \Verifalia\Security\UsernamePasswordAuthenticator;

	/**
	 * HTTPS-based REST client for Verifalia.
	 */
	class VerifaliaRestClient
	{
	    const DEFAULT_BASE_URIS = [
			'https://api-1.verifalia.com/',
			'https://api-2.verifalia.com/',
			'https://api-3.verifalia.com/'
		];

		/**
		 * Allows to manage the credits for the Verifalia account.
		 */
		public $credits;

		/**
		 * Allows to submit and manage email validations using the Verifalia service.
		 */
		public $emailValidations;

		/**
		 * VerifaliaRestClient accepts an array of constructor parameters.
		 *
		 * Here's an example of creating a VerifaliaRestClient using the username/password credentials
		 * for a user:
		 *
		 *     $verifalia = new VerifaliaRestClient([
		 *         'username' => 'your-username-here',
		 *         'password' => 'your-password-here'
		 *     ]);
		 *
		 * Configuration settings include the following options:
		 *
		 * - username: The username of the Verifalia user to authenticate with.
		 * - password: The password of the Verifalia user to authenticate with.
		 *
		 * @param array $options VerifaliaRestClient configuration settings.
		 *
		 * @see \Verifalia\VerifaliaRestClientOptions for a list of available options.
		 */
		public function __construct($options)
		{
			// Check the provided options

			if ($options === null) {
				throw new \InvalidArgumentException('options is null');
			}

			// Base URIs

			if (!array_key_exists(VerifaliaRestClientOptions::BASE_URIS, $options)) {
				$options[VerifaliaRestClientOptions::BASE_URIS] = self::DEFAULT_BASE_URIS;
			}

			// Authentication settings

			if (!array_key_exists(VerifaliaRestClientOptions::USERNAME, $options)) {
				throw new \InvalidArgumentException("username is null or empty: please visit https://verifalia.com/client-area to set up a new user, if you don't have one.");
			}

			if (!array_key_exists(VerifaliaRestClientOptions::PASSWORD, $options)) {
				throw new \InvalidArgumentException("password is null or empty: please visit https://verifalia.com/client-area to set up a new user, if you don't have one.");
			}

			$authenticator = new UsernamePasswordAuthenticator(
				$options[VerifaliaRestClientOptions::USERNAME],
				$options[VerifaliaRestClientOptions::PASSWORD]
			);

			$restClient = new MultiplexedRestClient(
				$options[VerifaliaRestClientOptions::BASE_URIS],
				$authenticator
			);

			$this->credits = new CreditsRestClient($restClient);
			$this->emailValidations = new EmailValidationsRestClient($restClient);
		}
	}
}
