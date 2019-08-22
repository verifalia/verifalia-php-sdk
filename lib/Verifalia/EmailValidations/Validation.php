<?php

namespace Verifalia\EmailValidations {

	class Validation
	{
		public $overview;
		public $entries;

		public function __construct($overview, $entries = null)
		{
			$this->overview = $overview;
			$this->entries = $entries;
		}
	}
}
