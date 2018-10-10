<?php
include('../src/init.php');
include('../public/cron.php');
/*
$voucherUserCount = array(
    "41429184" => 1,
    "41965850" => 1,
    "42028073" => 1,
    "41629094" => 3,
    "9828094" => 1,
    "41739917" => 1,
    "39789894" => 1,
    "28979624" => 1
);
*/

//for Testing
//$transactions->destroySomeJobLogs();


$messages->expiringVouchers();

// Load new transactions
$newTransactions = $transactions->monitor();

// Add the new balances to the users balances
$users->addBalance($newTransactions);

// Check how many vouchers to give out
$voucherUserCount = $vouchers->determineVoucherCount($newTransactions);

// Add the vouchers if there are any
$vouchers->addVoucher($voucherUserCount);

$messages->notifyUsers($voucherUserCount);