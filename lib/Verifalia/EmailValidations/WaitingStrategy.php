<?php

namespace Verifalia\EmailValidations {

	class WaitingStrategy
	{
		public $waitForCompletion = false;
		public $progress = null;

		public function __construct($waitForCompletion, callable $progress = null)
		{
			$this->waitForCompletion = $waitForCompletion;
			$this->progress = $progress;
		}

		function waitForNextPoll($validationOverview)
		{
			// Observe the ETA if we have one, otherwise a delay given the formula: max(0.5, min(30, 2^(log(noOfEntries, 10) - 1)))

			$delay = max(0.5, min(30, pow(2, log10($validationOverview->noOfEntries) - 1)));

			if (property_exists($validationOverview, 'progress') && property_exists($validationOverview->progress, 'estimatedTimeRemaining')) {
				preg_match("/^(?:(\d*?)\.)?(\d{2})\:(\d{2})\:(\d{2})(?:\.(\d*?))?$/", $validationOverview->progress->estimatedTimeRemaining, $timespanMatch);

				if (!empty($timespanMatch)) {
					$hours = $timespanMatch[2];
					$minutes = $timespanMatch[3];
					$seconds = $timespanMatch[4];
	
					// Calculate the delay (in seconds)
	
					$delay = $seconds;
					$delay += $minutes * 60;
					$delay += $hours * 3600;

					// TODO: Follow the ETA more precisely: as a safenet, we are constraining it to a maximum of 30s for now.
	
					$delay = max(0.5, min(30, $delay));
				}
			}
	
			sleep($delay);
		}
	}
}
