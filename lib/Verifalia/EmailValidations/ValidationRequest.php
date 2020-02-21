<?php

namespace Verifalia\EmailValidations {

	class ValidationRequest
	{
		public $entries = null;
		public $quality = null;
		public $deduplication = null;
		public $priority = null;
		public $retention = null;

		public function __construct($entries)
		{
			$this->entries = array();

			if (is_array($entries)) {
				for ($x = 0; $x < count($entries); $x++) {
					$this->addEntry($entries[$x]);
				}
			} else {
				$this->addEntry($entries);
			}
		}

		private function addEntry($entry)
		{
			if (is_string($entry)) {
				array_push($this->entries, new ValidationRequestEntry($entry));
			} else if ($entry instanceof ValidationEntry) {
				array_push($this->entries, $entry);
			} else {
				throw new \InvalidArgumentException('Invalid input entries, please review the data you are about to submit to Verifalia.');
			}
		}
	}
}

?>