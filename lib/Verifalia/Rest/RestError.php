<?php
	namespace Verifalia\Rest;

	class RestError {
		// Same value of CURLE_OPERATION_TIMEDOUT
		const OPERATION_TIMEDOUT = 28;

		public $errno = NULL;
		public $error = NULL;
		
		public function __construct($errno, $error)
		{
			$this->errno = $errno;
			$this->error = $error;
		}
	}
?>