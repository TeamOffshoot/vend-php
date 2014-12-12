<?php

namespace Vend\Api;

use Offshoot\HttpClient;
use Offshoot\Redirector;

class AuthenticationGateway
{

    const AUTHORIZATION_URI = 'https://secure.vendhq.com/connect?response_type=code&client_id=%s&redirect_uri=%s';
    const ACCESS_URI = 'https://%s.vendhq.com/api/1.0/token';

    /** @var string */
    protected $shopName;

    /** @var string */
    protected $clientId;

    /** @var string */
    protected $clientSecret;

    /** @var string */
    protected $redirectUri;

    /** @var HttpClient */
    protected $httpClient;

    /** @var Redirector */
    protected $redirector;

    /**
     * initialize the authentication gateway
     * @param HttpClient $httpClient
     * @param Redirector $redirector
     */
    public function __construct(HttpClient $httpClient, Redirector $redirector)
    {
        $this->httpClient = $httpClient;
        $this->redirector = $redirector;
    }

}
