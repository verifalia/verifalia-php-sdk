<?php
	namespace Verifalia\Rest;

	class RestResponse {
		public $info = NULL;
		public $body = NULL;
		
		public function __construct($info, $body)
		{
			$this->info = $info;
			$this->body = $body;
		}	
	}
?>