<?php

namespace Verifalia\Security {

    use \GuzzleHttp\RequestOptions;

    class UsernamePasswordAuthenticator implements IAuthenticator
    {
        private $username;
        private $password;

        public function __construct(string $username, string $password)
        {
            $this->username = $username;
            $this->password = $password;
        }

        public function addAuthentication(&$requestOptions)
        {
            $requestOptions = array_merge($requestOptions, [
                RequestOptions::AUTH => [
                    $this->username,
                    $this->password
                ]
            ]);
        }
    }
}
