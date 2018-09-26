<?php
require('../vendor/autoload.php');
require_once ('database.php');
require_once ('transactions.php');
require_once ('vouchers.php');
require_once ('users.php');
require_once ('report.php');

$db = new Database();
$transactions = new Transactions($db);

