<?php
	namespace Verifalia\Rest;
	
	abstract class RestClient {
		const DEFAULT_API_VERSION = 'v1.2';
		const DEFAULT_BASE_URL = 'https://api.verifalia.com/';
		const USER_AGENT = 'verifalia-rest-client/php/1.2';
		
		// Supported HTTP status codes

		const HTTP_STATUS_OK = 200;
		const HTTP_STATUS_ACCEPTED = 202;
		const HTTP_STATUS_UNAUTHORIZED = 401;
		const HTTP_STATUS_PAYMENT_REQUIRED = 402;
		const HTTP_STATUS_NOT_FOUND = 404;
		const HTTP_STATUS_GONE = 410;

		// Supported HTTP methods

		const HTTP_METHOD_GET = 'GET';
		const HTTP_METHOD_POST = 'POST';
		const HTTP_METHOD_DELETE = 'DELETE';
		
		function __construct($username, $password) {
			$this->username = $username;
			$this->password = $password;
		}

		protected function sendRequest($relativePath, $jsonPostedData = NULL, $method = self::HTTP_METHOD_GET, $timeout = self::DEFAULT_REQUEST_TIMEOUT) {
			$url = self::DEFAULT_BASE_URL.self::DEFAULT_API_VERSION.$relativePath;
			
			// Configure curl
			
			$ch = curl_init();

			try {
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_USERPWD, $this->username . ":" . $this->password);
				
				curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
				
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:',
					'Content-Type: application/json',
					'User-Agent: '.self::USER_AGENT));

				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				// curl_setopt($ch, CURLOPT_VERBOSE, true);
				
				switch($method) {
					case self::HTTP_METHOD_GET:
						break;
					case self::HTTP_METHOD_POST:
						curl_setopt($ch, CURLOPT_POST, TRUE);
						curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPostedData);
						break;
				}
				
				// Execute the request through curl

				$body = curl_exec($ch);
				
				if (!$body) {
					return new RestError(curl_errno($ch), curl_error($ch));
				}
				
				$info = curl_getinfo($ch);
				
				return new RestResponse($info, $body);
			}
			finally {
				curl_close($ch);
			}
		}
	}
?>