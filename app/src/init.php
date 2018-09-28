<?php
require('../vendor/autoload.php');
spl_autoload_register(function($className) {
    include_once $className . '.php';
});


$db = new Database();
$transactions = new Transactions($db);
$users = new Users($db);
$vouchers = new Vouchers($db);

