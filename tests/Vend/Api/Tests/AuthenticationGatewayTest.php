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

}
