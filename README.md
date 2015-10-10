# Installation

Package is available on [Packagist](https://packagist.org/packages/alldigitalrewards/rewards),
you can install it using [Composer](http://getcomposer.org).

```shell
composer require alldigitalrewards/rewards
```

[PHP](https://php.net) 5.5+

# Usage
```
$endpoint = 'http://marketplace.alldigitalrewards.com';
$apiUser = 'demouser';
$apiKey = 'examplekey';

$reward = new \ADR\Rewards($endpoint, $apiUser, $apiKey);

var_dump(json_decode($reward->getRedemptionCampaigns()));
```
