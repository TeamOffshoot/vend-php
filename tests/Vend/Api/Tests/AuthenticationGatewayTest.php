<?php

namespace Vend\Api\Tests;

class AuthenticationGatewayTest extends \PHPUnit_Framework_TestCase
{

    protected $authenticate;

    protected $httpClient;

    protected $redirector;

    public function setUp()
    {

        $this->httpClient = $this->getMock('Offshoot\HttpClient');
        $this->redirector = $this->getMock('Offshoot\Redirector');

        $this->authenticate = new \Vend\Api\AuthenticationGateway(
            $this->httpClient, $this->redirector
        );

    }

    public function testInitiatingLogin()
    {

        $storeName = 'store-name';
        $clientId = 'XXX1234567890';
        $redirectUri = 'http://example.com/myapp';

        $authorizeUrl = "https://secure.vendhq.com/connect"
            . "?" . http_build_query(array(
                'response_type' => 'code',
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri
            ));

        $this->redirector->expects($this->once())
                         ->method('redirect')
                         ->with($authorizeUrl)
                         ->will($this->returnValue($redirectUri));

        $this->authenticate->forStoreName($storeName)
                           ->usingClientId($clientId)
                           ->andReturningTo($redirectUri)
                           ->initiateLogin();

        $this->assertEquals(
            $authorizeUrl, $this->authenticate->getAuthenticationUri()
        );

    }

    public function testExchangingToken()
    {

        $storeName = 'store-name';
        $clientId = 'XXX1234567890';
        $clientSecret = 'ABC123XYZ';
        $temporaryToken = 'TEMP_TOKEN';
        $permanentAccessToken = 'ACCESS_TOKEN';
        $refreshToken = 'REFRESH_TOKEN';
        $redirectUri = 'http://example.com/myapp';

        $accessUri = "https://{$storeName}.vendhq.com/api/1.0/token";
        $params = array(
            'code' => $temporaryToken,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirectUri
        );

        $response = array(
            'access_token' => $permanentAccessToken,
            'token_type' => 'Bearer',
            'expires' => 1387145621,
            'expires_in' => 604800,
            'refresh_token' => $refreshToken
        );

        $this->httpClient->expects($this->once())
                         ->method('post')
                         ->with($accessUri, $params)
                         ->will($this->returnValue(json_encode($response)));

        $object = $this->authenticate->forStoreName($storeName)
                                    ->usingClientId($clientId)
                                    ->usingClientSecret($clientSecret)
                                    ->andReturningTo($redirectUri)
                                    ->toExchange($temporaryToken);

        $this->assertEquals(
            $accessUri, $this->authenticate->getAccessUri()
        );

        $this->assertEquals($permanentAccessToken, $object['access_token']);
        $this->assertEquals($permanentAccessToken, $object['refresh_token']);

    }

}
