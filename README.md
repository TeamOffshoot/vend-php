# vend-php

A simple [Vend API](https://developers.vendhq.com/) client in PHP.

The canoncial repository for this stream of development is
[https://github.com/TeamOffshoot/vend-php](https://github.com/TeamOffshoot/vend-php)

## Requirements

* PHP 5.3 (or higher)
* ext-curl, ext-json
* offshoot/http

## Development Requirements

* phpunit/phpunit 3.7

## Getting Started

Install vend-php via [Composer](http://getcomposer.org/)

Create a `composer.json` file if you don't already have one in your projects
root directory and require vend-php:

    {
      "require": {
        "offshoot/vend-php": "~1.0"
      }
    }

To learn more about Composer, including the complete installation process,
visit http://getcomposer.org/

### Using cURL

If you're using a cURL based HttpClient like the `CurlHttpClient`, you will want
to include the cacert.pem file that can be found at
[http://curl.haxx.se/docs/caextract.html](http://curl.haxx.se/docs/caextract.html)

You can add this as a dependency in your composer file. Your `composer.json`
might look something like this:

    {
      "require": {
        "offshoot/vend-php": "~1.0",
        "haxx-se/curl": "1.0.0"
      },
      "repositories": [
        {
          "type": "package",
          "package": {
            "name": "haxx-se/curl",
            "version": "1.0.0",
            "dist": {
              "url": "http://curl.haxx.se/ca/cacert.pem",
              "type": "file"
            }
          }
        }
      ]
    }

You will be able to find the cacert.pem file in `vendor/haxx-se/curl/cacert.pem`

## Usage

### Authentication

#### Getting an Access Token

If you do not already have a Vend API Permanent Access Token, you will need
you authenticate with the Vend API first

    $pathToCertificateFile = 'vendor/haxx-se/curl/cacert.pem';
    $httpClient = new \Offshoot\HttpClient\CurlHttpClient($pathToCertificateFile);
    $redirector = new \Offshoot\Redirector\HeaderRedirector();

    $authenticate = new \Vend\Api\AuthenticationGateway($httpClient, $redirector);

    $authenticate->forStoreName('mycoolstore')
        ->usingClientId('XXX1234567890') // get this from your Vend Account
        ->andReturningTo('http://wherever.you/like')
        ->initiateLogin();

This will redirect your user to a Vend login screen where they will need
to authenticate with their Vend credentials. After doing that, Vend will
perform a GET request to your redirect URI, that will look like:

    GET http://wherever.you/like?code=TEMP_TOKEN&domain_prefix=YOUR_STORE_NAME

Your application will need to capture the `code` query param from the request
and use that to get the access token from Vend

    // validate the Vend Request
    if ($client->isValidRequest($_GET)) {

        // exchange the token
        $accessToken = $authenticate->forStoreName('mycoolstore')
            ->usingClientId('XXX1234567890') // get this from your Vend Account
            ->usingClientSecret('ABC123XYZ') // get this from your Vend Account
            ->andReturningTo('http://wherever.you/like')
            ->toExchange($_GET['code']);

    }

#### Refreshing an Access Token

TBD

### Interacting with the Vend API

#### Set up the API Client

    $vend = new \Vend\Api\Client($httpClient);
    $vend->setStoreName('mycoolstore');
    $vend->setAccessToken($accessToken);



## Contributing

Contributions are welcome. Just fork the repository and send a pull request.
Please be sure to include test coverage with your pull request. You can learn
more about Pull Requests
[here](https://help.github.com/articles/creating-a-pull-request)

In order to run the test suite, ensure that the development dependencies have
been installed via composer. Then from your command line, simple run:

    vendor/bin/phpunit --bootstrap tests/bootstrap.php tests/

## License

This library is released under the
[GPL 3.0 License](https://github.com/TeamOffshoot/vend-php/blob/master/LICENSE)

## Acknowledgements

Thanks to [Bruce Aldridge](https://github.com/brucealdridge/VendAPI) for
his development of the initial code base.
