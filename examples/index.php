<?php
require_once "../src/Rewards.php";
$endpoint = 'http://marketplace.alldigitalrewards.com';
$apiUser = 'demouser';
$apiKey = 'examplekey';

$rewards = new \ADR\Rewards($endpoint, $apiUser, $apiKey);

/* Redemptions

var_dump(json_decode($rewards->getRedemptionCampaigns(1, 30)));
var_dump(json_decode($rewards->getRedemptionCampaign(62)));
var_dump(json_decode($rewards->getRedemptionCampaignPins(62)));

*/

/* Rewards

var_dump(json_decode($rewards->getRewards(1, 30, [
    'hotpick' => false,
    'featured' => false,
    'title' => null, // reward card
    'priceMin' => null, // 0
    'priceMax' => null, // 10000
    'categoryId' => null, // 1
    'categoryIds' => [] // [43, 1]
])));
var_dump(json_decode($rewards->getReward(4)));
var_dump(json_decode($rewards->getRewardCategories()));
var_dump(json_decode($rewards->getRewardCategory(1)));

*/

/* Users

var_dump(json_decode($rewards->getUsers()));
var_dump(json_decode($rewards->getUser(177158)));

var_dump(json_decode($rewards->createUserTransaction(123777, [
    'rewards' => [4, 8],
    'shipping_address' => [
        'firstname' => 'John',
        'lastname' => 'Doe',
        'address' => '123 Acme Blvd',
        'city' => 'Hollywood',
        'state' => 'CA',
        'zip' => 90210
    ]
])));

var_dump(json_decode($rewards->getUserTransactions(123777)));
var_dump(json_decode($rewards->getUserTransaction(123777, 29361)));

var_dump(json_decode($rewards->getUserSSOToken(177158)));
var_dump(json_decode($rewards->createUser([
    'firstname' => 'John',
    'lastname' => 'Doe',
    'email_address' => 'johndoe@example.com',
    'unique_id' => 123777,
    'password' => 'password',
    'credit' => 1
])));

var_dump(json_decode($rewards->updateUser(123777, [
    'firstname' => 'John',
    'lastname' => 'Doe',
    'email_address' => 'johndoe@example.com',
    'credit' => 50000,
    'password' => 'password'
])));

var_dump(json_decode($rewards->createUserCart(123777, [8])));
var_dump(json_decode($rewards->updateUserCart(123777, 2, [4, 8, 9])));
var_dump(json_decode($rewards->deleteUserCart(123777, 2)));
var_dump(json_decode($rewards->getUserCart(123777, 2)));

*/
