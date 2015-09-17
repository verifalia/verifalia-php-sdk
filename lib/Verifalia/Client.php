<?php
	namespace Verifalia;
	
	class Client {
		public $emailValidations = NULL;
		
		function __construct($accountSid, $authToken) {
			$this->emailValidations = new \Verifalia\EmailAddresses\ValidationRestClient($accountSid, $authToken);
		}
	}
?>