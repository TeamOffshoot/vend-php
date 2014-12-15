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
     * a simple DSL on top of setting the client secret
     * @param string $clientSecret
     * @return AuthenticationGateway
     */
    public function usingClientSecret($clientSecret)
    {
        $this->setClientSecret($clientSecret);
        return $this;
    }

    /**
     * exchange the temporary token for a permanent access token
     * @param string $temporaryToken
     * @return string
     */
    public function toExchange($temporaryToken)
    {

        if (!$this->canAuthenticateUser($temporaryToken)) {
            throw new \RuntimeException(
                'Cannot authenticate user, dependencies are missing'
            );
        }

        if (!$this->codeIsValid($temporaryToken)) {
            throw new \InvalidArgumentException('Vend code is invalid');
        }

        $request = array(
            'client_id' => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
            'code' => $temporaryToken,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->getRedirectUri()
        );

        $response = json_decode($this->httpClient->post(
            $this->getAccessUri(),
            $request
        ));

        if (isset($response->error)) {
            throw new \RuntimeException($response->error);
        }

        return $response;

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
     * build the Vend access uri that users are forwarded to for exchanging
     * the temporary token with the permanent access token
     * @return string
     */
    public function getAccessUri()
    {
        return sprintf(self::ACCESS_URI, $this->getStoreName());
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
     * assert that it is possible to proceed with authenticating the user
     * @param string $temporaryToken
     * @return boolean
     */
    protected function canAuthenticateUser($temporaryToken)
    {
        return $this->getClientId()
            && $this->getClientSecret()
            && $temporaryToken;
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
     * assert that the shopify code is valid for use
     * @param string $code
     * @return boolean
     */
    protected function codeIsValid($code)
    {
        return !is_null($code);
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
     * set the client secret
     * @param string $clientSecret
     */
    protected function setClientSecret($clientSecret)
    {
        $this->clientSecret = $clientSecret;
    }

    /**
     * get the client secret
     * @return string
     */
    protected function getClientSecret()
    {
        return $this->clientSecret;
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
