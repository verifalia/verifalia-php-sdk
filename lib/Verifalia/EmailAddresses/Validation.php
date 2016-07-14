<?php
	namespace Verifalia\EmailAddresses;

	class Validation {
		public $entries = NULL;
		public $quality = NULL;
		
		public function __construct($entries, $quality = NULL)
		{
			$this->entries = array();
			$this->quality = $quality;
		
			if (is_array($entries)) {
				for($x = 0; $x < count($entries); $x++) {
					$this->addEntry($entries[$x]);
				}
			}
			else {
				$this->addEntry($entries);
			}
		}
		
		private function addEntry($entry)
		{
			if (is_string($entry)) {
				array_push($this->entries, new ValidationEntry($entry));
			}
			else if ($entry instanceof ValidationEntry) {
				array_push($this->entries, $entry);
			}
			else {
				throw new InvalidArgumentException('Invalid input entries, please review the data you are about to submit to Verifalia.');
			}
		}
	}
?>