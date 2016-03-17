<?php
	namespace Verifalia\EmailAddresses;
	
	use Verifalia;
	use Verifalia\Rest;
	use Verifalia\Exceptions;

	class ValidationRestClient extends \Verifalia\Rest\RestClient {
		// Validation statuses
		
		const VALIDATION_STATUS_PENDING = 'pending';
		const VALIDATION_STATUS_COMPLETED = 'completed';
		
		// Default timeout values, in seconds
		
		const DEFAULT_REQUEST_TIMEOUT = 15;
		const DEFAULT_QUERY_POLLING_INTERVAL = 5;
		
		/** 
		* Submits email addresses to the Verifalia email validation engine.
		*
		* @param string|array $emailAddresses The email address(es) to validate.
		* @param \Verifalia\WaitForCompletionOptions $waitForCompletionOptions The waiting option for the completion of the email validation job.
		* @return object An object describing the validation job (which may have been already completed upon returning).
		*/		
		function submit($emailAddresses, $waitForCompletionOptions = \Verifalia\WaitForCompletionOptions::DONT_WAIT) {
			// Builds the input json structure
		
			$entries = array();
			
			if (is_array($emailAddresses)) {
				for($x = 0; $x < count($emailAddresses); $x++) {
					array_push($entries, array('inputData' => (string)$emailAddresses[$x]));
				}
			}
			else if (is_string($emailAddresses)) {
				array_push($entries, array('inputData' => $emailAddresses));
			}
			else {
				throw new InvalidArgumentException('submit() only accepts strings or array of strings.');
			}
			
			$data = array('entries' => $entries);
			
			// Sends the request to the Verifalia servers
			
			if ($waitForCompletionOptions === NULL)
				$waitForCompletionOptions = new \Verifalia\WaitForCompletionOptions(self::DEFAULT_REQUEST_TIMEOUT, self::DEFAULT_QUERY_POLLING_INTERVAL);
			
			if ($waitForCompletionOptions === \Verifalia\WaitForCompletionOptions::DONT_WAIT) {
				$timeout = self::DEFAULT_REQUEST_TIMEOUT;
			}
			else {
				$timeout = $waitForCompletionOptions->timeout;
			}

			$result = $this->sendRequest("/email-validations",
				json_encode($data),
				self::HTTP_METHOD_POST,
				$timeout);

			if ($result instanceof \Verifalia\Rest\RestError) {
				// Unexpected CURL error
					
				throw new \Verifalia\Exceptions\VerifaliaException($result->error);
			}
			
			$httpStatusCode = $result->info['http_code'];
			
			switch ($httpStatusCode) {
				case self::HTTP_STATUS_OK:
				case self::HTTP_STATUS_ACCEPTED: {
					$validation = json_decode($result->body);
					$validation->status = ($httpStatusCode == self::HTTP_STATUS_OK) ?
						self::VALIDATION_STATUS_COMPLETED : self::VALIDATION_STATUS_PENDING;

					if ($httpStatusCode == self::HTTP_STATUS_ACCEPTED) {
						if (!($waitForCompletionOptions === \Verifalia\WaitForCompletionOptions::DONT_WAIT)) {
							return $this->query($validation->uniqueID, $waitForCompletionOptions);
						}
					}
						
					return $validation;
				}

				case self::HTTP_STATUS_PAYMENT_REQUIRED:
					throw new \Verifalia\Exceptions\VerifaliaException("Verifalia was unable to accept your request because of low account credit. Body: {$result->body}");

				default:
					throw new \Verifalia\Exceptions\VerifaliaException("Unexpected HTTP status code {$httpStatusCode}. Body: {$result->body}");
			}
		}

		/** 
		* Queries about a specific email validation job, submitted by way of the submit() function.
		*
		* @param string|array $emailAddresses The email address(es) to validate.
		* @param \Verifalia\WaitForCompletionOptions $waitForCompletionOptions The waiting option for the completion of the email validation job.
		* @return object An object describing the validation job.
		*/		
		function query($uniqueID, $waitForCompletionOptions = \Verifalia\WaitForCompletionOptions::DONT_WAIT) {
			// Special treatment for DONT_WAIT
		
			if ($waitForCompletionOptions === \Verifalia\WaitForCompletionOptions::DONT_WAIT)
				return $this->queryOnce($uniqueID, self::DEFAULT_REQUEST_TIMEOUT);
		
			if ($waitForCompletionOptions === NULL)
				$waitForCompletionOptions = new \Verifalia\WaitForCompletionOptions(self::DEFAULT_REQUEST_TIMEOUT, self::DEFAULT_QUERY_POLLING_INTERVAL);

			while (TRUE) {
				$result = $this->queryOnce($uniqueID, $waitForCompletionOptions->timeout);
				
				if (!($result === NULL)) {
					if ($result->status === self::VALIDATION_STATUS_COMPLETED) {
						return $result;
					}
				}
				
				sleep($waitForCompletionOptions->pollingInterval);
			}
		}
		
		private function queryOnce($uniqueID, $timeout) {
			// Sends the request to the Verifalia servers
			
			$result = $this->sendRequest("/email-validations/{$uniqueID}",
				array(),
				self::HTTP_METHOD_GET,
				$timeout);
			
			if ($result instanceof \Verifalia\Rest) {
				// Returns NULL for timeouts
			
				if ($result->errno == \Verifalia\Rest\RestError::OPERATION_TIMEDOUT)
					return NULL;
			
				// Unexpected CURL error
				
				throw new \Verifalia\Exceptions\VerifaliaException($result->error);
			}
			
			$httpStatusCode = $result->info['http_code'];
			
			switch ($httpStatusCode) {
				case self::HTTP_STATUS_OK:
				case self::HTTP_STATUS_ACCEPTED: {
					$validation = json_decode($result->body);
					$validation->status = ($httpStatusCode == self::HTTP_STATUS_OK) ?
						self::VALIDATION_STATUS_COMPLETED : self::VALIDATION_STATUS_PENDING;
					
					return $validation;
				}

				case self::HTTP_STATUS_NOT_FOUND: // Not found
				case self::HTTP_STATUS_GONE: // Gone
					return NULL;
				
				default:
					throw new \Verifalia\Exceptions\VerifaliaException("Unexpected HTTP status code {$httpStatusCode}. Body: {$result->body}");
			}
		}

		/** 
		* Deletes a specific email validation job, submitted by way of the submit() function.
		*
		* @param string $uniqueID The unique identifier of the validation job to delete.
		*/		
		function delete($uniqueID) {
			// Sends the request to the Verifalia servers
			
			$result = $this->sendRequest("/email-validations/{$uniqueID}",
				array(),
				self::HTTP_METHOD_DELETE);
			
			$httpStatusCode = $result->info['http_code'];
			
			if ($httpStatusCode != self::HTTP_STATUS_OK) {
				throw new \Verifalia\Exceptions\VerifaliaException("Unexpected HTTP status code {$httpStatusCode}. Body: {$result->body}");
			}
		}
	}
?>