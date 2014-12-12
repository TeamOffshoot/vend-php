# vend-php

A simple [Vend API](https://developers.vendhq.com/) client in PHP.

The canoncial repository for this stream of development is
[https://github.com/TeamOffshoot/vend-php](https://github.com/TeamOffshoot/vend-php)

This API Client is still in a pre-1.0 state, so you can expect:
* some bugs (feel free to submit a pull request with bug fixes and test coverage)
* possibly some breaking API changes between v0.9 and v1.0

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
        "offshoot/vend-php": "0.9.x"
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
        "offshoot/vend-php": "0.9.x",
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

### Interacting with the Vend API

```php
require 'vendapi.php';

$request = new VendAPI\VendRequest(
  'https://shopname.vendhq.com',
  'username',
  'password',
  array(
    'CURLOPT_CAINFO' => 'path/to/your/cacert.pem'
  )
);

$vend = new VendAPI\VendAPI($request);
$products = $vend->getProducts();
```

*NB* this will only grab the first 20 or so results. To grab all results set `$vend->automatic_depage` to `true`

```php
$vend->automatic_depage = true;
$products = $vend->getProducts();
```
### Add a Product

```php
$donut = new \VendAPI\VendProduct(null, $vend);
$donut->handle = 'donut01';
$donut->sku = '343434343';
$donut->retail_price = 2.99;
$donut->name = 'Donut w/ Sprinkles';
$donut->save();
echo 'Donut product id is '.$donut->id;
```

### Add a Sale

```php
$sale = new \VendAPI\VendSale(null, $vend);
$sale->register_id = $register_id;
$sale->customer_id = $customer_id;
$sale->status = 'OPEN';
$products = array();
foreach ($items as $item) {
    $products[] = array(
        'product_id' => $item->product_id,
        'quantity' => $item->quantity,
        'price' => $item->price
    );
}
$sale->register_sale_products = $products;
$sale->save();

echo "Created new order with id: ".$sale->id;
```

### Other cool stuff

```php
$vend->getProducts(array('active' => '1', 'since' => '2012-09-15 20:55:00'));
```
*NB* Check the vend api docs for supported search fields. If a search field isn't supported all results will be returned rather than the zero I was expecting

```php
$coffee = $vend->getProduct('42c2ccc4-fbf4-11e1-b195-4040782fde00');
echo $coffee->name; // outputs "Hot Coffee"
if ($product->getInventory() == 0) {
  $coffee->setInventory(10);
  $coffee->name = 'Iced Coffee';
  $coffee->save();
}
```

### Debugging

To debug make a call to the ```debug()``` function.
eg:
```php
$vend->debug(true);
```

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
