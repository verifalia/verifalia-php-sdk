<?php

namespace Verifalia\Rest {

    use \GuzzleHttp\Client;
    use \GuzzleHttp\RequestOptions;
    use \Verifalia\Exceptions\VerifaliaException;
    use \Verifalia\Security\IAuthenticator;

    class MultiplexedRestClient
    {
        const PACKAGE_VERSION = '2.1';
        const DEFAULT_API_VERSION = 'v2.1';
        const USER_AGENT = 'verifalia-rest-client/php/2.1';

        // Supported HTTP status codes

        const HTTP_STATUS_OK = 200;
        const HTTP_STATUS_ACCEPTED = 202;
        const HTTP_STATUS_UNAUTHORIZED = 401;
        const HTTP_STATUS_PAYMENT_REQUIRED = 402;
        const HTTP_STATUS_NOT_FOUND = 404;
        const HTTP_STATUS_GONE = 410;

        // Supported HTTP methods

        const HTTP_METHOD_GET = 'GET';
        const HTTP_METHOD_POST = 'POST';
        const HTTP_METHOD_PUT = 'PUT';
        const HTTP_METHOD_DELETE = 'DELETE';

        private $authenticator;
        private $shuffledBaseUris;

        public function __construct(array $baseUris, IAuthenticator $authenticator)
        {
            $this->shuffledBaseUris = $baseUris;
            shuffle($this->shuffledBaseUris);

            $this->authenticator = $authenticator;
        }

        public function sendRequest($method = self::HTTP_METHOD_GET, $relativePath, $query = null, $data = null)
        {
            $errors = [];

            // Cycle among the base URIs

            foreach ($this->shuffledBaseUris as $baseUri) {
                $underlyingClient = new Client([
                    'base_uri' => $baseUri . self::DEFAULT_API_VERSION . '/',
                ]);

                $requestOptions = [
                    RequestOptions::ALLOW_REDIRECTS => false,
                    RequestOptions::HEADERS => [
                        'Accept' => 'application/json',
                        'Accept-Encoding' => 'gzip',
                        'User-Agent' => 'verifalia-rest-client/php/' . self::PACKAGE_VERSION . '/' . phpversion()
                    ]
                ];

                $this->authenticator->addAuthentication($requestOptions);

                if ($query !== null) {
                    $requestOptions[RequestOptions::QUERY] = $query;
                }

                if ($method === self::HTTP_METHOD_POST || $method === self::HTTP_METHOD_PUT) {
                    if ($data !== null) {
                        $requestOptions[RequestOptions::JSON] = $data;
                    }
                }

                // Execute the request against the current API endpoint

                $response = null;

                try {
                    $response = $underlyingClient->request(
                        $method,
                        $relativePath,
                        $requestOptions
                    );
                } catch (\Exception $e) {
                    // Records the error and continue cycling, hoping the next endpoint will handle the request

                    array_push($errors, $e);
                    continue;
                }

                // Records an error (and continue cycling) in the event the status code is a 5xx

                if ($response->getStatusCode() >= 500 && $response->getStatusCode() <= 599) {
                    array_push($errors, new VerifaliaException('Status code is ' . $response->getStatusCode()));
                    continue;
                }

                if ($response->getStatusCode() === 401 || $response->getStatusCode() === 403) {
                    throw new VerifaliaException('Authentication error (HTTP ' . $response->getStatusCode() . '): ' . $response->getBody());
                }

                return $response;
            }

            // We have iterated all of the base URIs at this point, so we should report the issue

            throw new VerifaliaException('All the endpoints are unreachable. ' . join(',', $errors));
        }
    }
}
