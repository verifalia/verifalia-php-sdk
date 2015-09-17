<?php
	namespace Verifalia;

	class WaitForCompletionOptions {
		const DONT_WAIT = 'DONT_WAIT';

		public $timeout = NULL;
		public $pollingInterval = NULL;
		
		public function __construct($timeout, $pollingInterval)
		{
			$this->timeout = $timeout;
			$this->pollingInterval = $pollingInterval;
		}	
	}
?>