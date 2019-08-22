<?php

namespace Verifalia\EmailValidations {

	class ValidationRequestEntry
	{
		public $inputData = null;
		public $custom = null;

		public function __construct($inputData, $custom = null)
		{
			$this->inputData = $inputData;
			$this->custom = $custom;
		}
	}
}

?>