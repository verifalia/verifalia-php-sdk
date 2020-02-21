<?php

namespace Verifalia\Credits {

	use \Verifalia\Exceptions\VerifaliaException;
	use \Verifalia\Rest\MultiplexedRestClient;

	/**
	 * Allows to submit and manage email validations using the Verifalia service.
	 */
	class CreditsRestClient
	{
		private $client = NULL;

		public function __construct($client)
		{
			$this->client = $client;
		}

		/** 
		 * Returns the current credits balance for the Verifalia account.
		 *
		 * @return object An object describing the balance.
		 */
		public function getBalance()
		{
			$response = $this->client->sendRequest(MultiplexedRestClient::HTTP_METHOD_GET, "credits/balance");
			$statusCode = $response->getStatusCode();
			$body = $response->getBody();

			switch ($statusCode) {
				case MultiplexedRestClient::HTTP_STATUS_OK:
					return json_decode($body);

				default:
					throw new VerifaliaException("Unexpected HTTP status code {$statusCode}. Body: {$body}");
			}
		}
	}
}
