<?php

namespace Verifalia\EmailValidations {

	use \Verifalia\Exceptions\VerifaliaException;
	use \Verifalia\Rest\MultiplexedRestClient;
	use \Verifalia\EmailValidations\ValidationStatus;
	use \Verifalia\Common\Cursor;
	use \Verifalia\Common\Direction;

	/**
	 * Allows to submit and manage email validations using the Verifalia service.
	 */
	class EmailValidationsRestClient
	{
		private $client;

		public function __construct(MultiplexedRestClient $client)
		{
			$this->client = $client;
		}

		/** 
		 * Submits a new email validation for processing. By default, this method does not wait for the completion
		 * of the email validation job: specify a waitingStrategy to request a different waiting behavior.
		 *
		 * @param string|array|Validation|ValidationEntry $entries One or more email addresses to validate.
		 * @param bool|WaitingStrategy $waitingStrategy The strategy which rules out how to wait for the completion of the email validation.
		 * Can be true to wait for the completion or an instance of WaitingStrategy for advanced scenarios and progress tracking.
		 * @return object An object describing the validation job.
		 */
		public function submit($entries, $waitingStrategy = false)
		{
			// Builds the input json structure

			$validation = NULL;

			if ($entries instanceof Validation) {
				$validation = $entries;
			} else {
				$validation = new ValidationRequest($entries);
			}

			$data = array(
				'entries' => $validation->entries
			);

			if ($validation->deduplication !== null) {
				$data['deduplication'] = $validation->deduplication;
			}

			if ($validation->quality !== null) {
				$data['quality'] = $validation->quality;
			}

			if ($validation->priority !== null) {
				$data['priority'] = $validation->priority;
			}

			if ($validation->retention !== null) {
				$data['retention'] = $validation->retention;
			}

			// Sends the request to the Verifalia servers

			$response = $this->client->sendRequest(
				MultiplexedRestClient::HTTP_METHOD_POST,
				"email-validations",
				null,
				$data
			);

			$statusCode = $response->getStatusCode();
			$body = $response->getBody();

			switch ($statusCode) {
				case MultiplexedRestClient::HTTP_STATUS_OK:
				case MultiplexedRestClient::HTTP_STATUS_ACCEPTED: {
						$partialValidation = json_decode($body);

						// Returns immediately if the validation has been completed or if we should not wait for it

						if (is_bool($waitingStrategy)) {
							$waitingStrategy = new WaitingStrategy($waitingStrategy);
						}

						if ($waitingStrategy->waitForCompletion === false || $partialValidation->overview->status === ValidationStatus::COMPLETED) {
							return $this->retrieveValidationFromPartialValidation($partialValidation);
						}

						return $this->waitValidationForCompletion($partialValidation->overview, $waitingStrategy);
					}

				case MultiplexedRestClient::HTTP_STATUS_PAYMENT_REQUIRED:
					throw new VerifaliaException("Verifalia was unable to accept your request because of low account credit. Body: {$body}");

				default:
					throw new VerifaliaException("Unexpected HTTP status code {$statusCode}. Body: {$body}");
			}
		}

		/** 
		 * Returns an email validation job previously submitted for processing. By default, this method does not wait
		 * for the eventual completion of the email validation job: pass a waitingStrategy to request a different
		 * waiting behavior.
		 *
		 * @param string $id The ID of the email validation job to retrieve.
		 * @param bool|WaitingStrategy $waitingStrategy The strategy which rules out how to wait for the completion of
		 * the email validation.
		 * @return object An object describing the validation job.
		 */
		public function get($id, $waitingStrategy = false)
		{
			$response = $this->client->sendRequest(MultiplexedRestClient::HTTP_METHOD_GET, "email-validations/{$id}");
			$statusCode = $response->getStatusCode();
			$body = $response->getBody();

			switch ($statusCode) {
				case MultiplexedRestClient::HTTP_STATUS_OK:
				case MultiplexedRestClient::HTTP_STATUS_ACCEPTED: {
						$partialValidation = json_decode($body);

						// Returns immediately if the validation has been completed or if we should not wait for it

						if (is_bool($waitingStrategy)) {
							$waitingStrategy = new WaitingStrategy($waitingStrategy);
						}

						if ($waitingStrategy->waitForCompletion === false || $partialValidation->overview->status === ValidationStatus::COMPLETED) {
							return $this->retrieveValidationFromPartialValidation($partialValidation);
						}

						return $this->waitValidationForCompletion($partialValidation->overview, $waitingStrategy);
					}

				case MultiplexedRestClient::HTTP_STATUS_NOT_FOUND:
				case MultiplexedRestClient::HTTP_STATUS_GONE:
					return null;

				default:
					throw new VerifaliaException("Unexpected HTTP status code {$statusCode}. Body: {$body}");
			}
		}

		private function waitValidationForCompletion($validationOverview, $waitingStrategy)
		{
			$resultOverview = $validationOverview;

			do {
				// Fires a progress, since we are not yet completed

				if ($waitingStrategy->progress !== null) {
					call_user_func($waitingStrategy->progress, $resultOverview);
				}

				// Wait for the next polling schedule

				$waitingStrategy->waitForNextPoll($resultOverview);

				// Fetch the job from the API

				$result = $this->get($validationOverview->id);

				if ($result === null) {
					// A null result means the validation has been deleted (or is expired) between a poll and the next one

					return null;
				}

				$resultOverview = $result->overview;

				// var_dump($resultOverview);

				// Returns immediately if the validation has been completed

				if ($resultOverview->status === ValidationStatus::COMPLETED) {
					return $result;
				}
			} while (true);
		}

		private function retrieveValidationFromPartialValidation($partialValidation)
		{
			$allEntries = [];

			if (property_exists($partialValidation, 'entries')) {
				$currentSegment = $partialValidation->entries;

				while ($currentSegment !== null && $currentSegment->data !== null) {
					$allEntries = array_merge($allEntries, $currentSegment->data);

					if (!property_exists($currentSegment, 'meta') || !property_exists($currentSegment->meta, 'isTruncated') || $currentSegment->meta->isTruncated === false) {
						break;
					}

					$currentSegment = $this->listEntriesSegmented(
						$partialValidation->overview->id,
						new Cursor($currentSegment->meta->cursor)
					);
				}
			}

			return new Validation($partialValidation->overview, $allEntries);
		}

		private function listEntriesSegmented($id, Cursor $cursor)
		{
			// Generate the additional parameters, where needed

			$cursorParamName = $cursor->direction === Direction::BACKWARD
				? "cursor:prev"
				: "cursor";

			$query = [
				$cursorParamName => $cursor->cursor
			];

			if ($cursor->limit > 0) {
				$query["limit"] = $cursor->limit;
			}

			$response = $this->client->sendRequest(
				MultiplexedRestClient::HTTP_METHOD_GET,
				"/email-validations/{$id}/entries",
				$query
			);

			$statusCode = $response->getStatusCode();
			$body = $response->getBody();

			if ($statusCode === MultiplexedRestClient::HTTP_STATUS_OK) {
				return json_decode($body)->data;
			}

			throw new VerifaliaError("Unexpected HTTP response: ${statusCode} ${body}");
		}

		/** 
		 * Deletes an email validation job previously submitted for processing.
		 *
		 * @param string $id The ID of the email validation job to delete.
		 * @return void
		 */
		public function delete($id)
		{
			// Sends the request to the Verifalia servers

			$response = $this->client->sendRequest(MultiplexedRestClient::HTTP_METHOD_DELETE, "email-validations/{$id}");
			$statusCode = $response->getStatusCode();

			if ($statusCode !== MultiplexedRestClient::HTTP_STATUS_OK) {
				$body = $response->getBody();

				throw new VerifaliaException("Unexpected HTTP status code {$statusCode}. Body: {$body}");
			}
		}
	}
}
