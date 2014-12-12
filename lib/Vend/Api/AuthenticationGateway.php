<?php

namespace Vend\Api;

use Offshoot\HttpClient;
use Offshoot\Redirector;

class AuthenticationGateway
{

    const AUTHORIZATION_URI = 'https://secure.vendhq.com/connect';
    const ACCESS_URI = 'https://%s.vendhq.com/api/1.0/token';

    /** @var string */
    protected $storeName;

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

    /**
     * a simple DSL on top of setting the store name
     * @param string $storeName
     * @return AuthenticationGateway
     */
    public function forStoreName($storeName)
    {
        $this->setStoreName($storeName);
        return $this;
    }

    /**
     * a simple DSL on top of setting the client ID
     * @param string $clientId
     * @return AuthenticationGateway
     */
    public function usingClientId($clientId)
    {
        $this->setClientId($clientId);
        return $this;
    }

    /**
     * a simple DSL on top of setting the redirect URI
     * @param string $redirectUri
     * @return AuthenticationGateway
     */
    public function andReturningTo($redirectUri)
    {
        $this->setRedirectUri($redirectUri);
        return $this;
    }

    /**
     * initiate the login process
     */
    public function initiateLogin()
    {

        if (!$this->canInitiateLogin()) {
            throw new \RuntimeException(
                'Unable to initiate login'
            );
        }

        $uri = $this->getAuthenticationUri();
        return $this->redirector->redirect($uri);

    }

    /**
     * build the uri that users are forwarded to for authentication
     * @return string
     */
    public function getAuthenticationUri()
    {

        $authorizeUri = self::AUTHORIZATION_URI;

        $uriParams = array(
            'response_type' => 'code',
            'client_id' => $this->getClientId()
        );

        if ($this->getRedirectUri()) {
            $uriParams['redirect_uri'] = $this->getRedirectUri();
        }

        return $authorizeUri . '?' . http_build_query($uriParams);

    }

    /**
     * assert that it is possible to proceed with initiating the login
     * @return boolean
     */
    protected function canInitiateLogin()
    {

        if (!$this->canBuildAuthenticationUri()) {
            throw new \RuntimeException(
                'Cannot build authentication uri, dependencies are missing'
            );
        }

        return true;

    }

    /**
     * assert that it is possible to build the authentication uri
     * @return boolean
     */
    protected function canBuildAuthenticationUri()
    {
        return $this->getClientId()
            && $this->getStoreName();
    }

    /**
     * set the store name
     * @param string $storeName
     */
    protected function setStoreName($storeName)
    {
        $this->storeName = $storeName;
    }

    /**
     * get the store name
     * @return string
     */
    protected function getStoreName()
    {
        return $this->storeName;
    }

    /**
     * set the client ID
     * @param string $clientId
     */
    protected function setClientId($clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * get the client ID
     * @return string
     */
    protected function getClientId()
    {
        return $this->clientId;
    }

    /**
     * set the redirect URI
     * @param string $redirectUri
     */
    protected function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri;
    }

    /**
     * get the redirect uri
     * @return string
     */
    protected function getRedirectUri()
    {
        return $this->redirectUri;
    }

}
