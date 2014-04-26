<?php

include 'src/VendAPI/VendAPI.php';

$request = new VendAPI\VendRequest('https://shopname.vendhq.com','username','password');
$vend = new VendAPI\VendAPI($request);

$products = $vend->getProducts();

$donut = new \VendAPI\VendProduct(null, $vend);
$donut->handle = 'donut01';
$donut->sku = '343434343';
$donut->retail_price = 2.99;
$donut->name = 'Donut w/ Sprinkles';
$donut->save();
echo 'Donut product id is '.$donut->id;
