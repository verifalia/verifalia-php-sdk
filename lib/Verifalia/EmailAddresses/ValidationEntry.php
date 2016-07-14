<?php
	namespace Verifalia\EmailAddresses;

	class ValidationEntry {
		public $inputData = NULL;
		public $custom = NULL;
		
		public function __construct($inputData, $custom = NULL)
		{
			$this->inputData = $inputData;
			$this->custom = $custom;
		}	
	}
?>