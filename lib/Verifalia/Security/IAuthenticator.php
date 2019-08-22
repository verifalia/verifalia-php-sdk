<?php

namespace Verifalia\Security {

    interface IAuthenticator
    {
        public function addAuthentication(&$requestOptions);
    }
}
