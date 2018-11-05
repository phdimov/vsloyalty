<?php
require('../vendor/autoload.php');
require('config.php');

use Twilio\Rest\Client;

spl_autoload_register(function($className) {
    include_once $className . '.php';
});

$client = new Client(TWILIO_SID, TWILIO_TOKEN);
$db = new Database();
$transactions = new Transactions($db);
$users = new Users($db, $client);
$vouchers = new Vouchers($db, $users);
$messages = new Messages($db, $client, $logger);
$report = new Report($db);
$api = new Api($db);
