<?php
include('../src/init.php');
include('../public/cron.php');
//for Testing
$transactions->destroySomeJobLogs();

// Load new transactions
$newTransactions = $transactions->monitor();

// Add the new balances to the users balances
$users->addBalance($newTransactions);

// Check how many vouchers to give out
$voucherUserCount = $vouchers->determineVoucherCount($newTransactions);

var_dump($voucherUserCount);

// Add the vouchers if there are any
$vouchers->addVoucher($voucherUserCount);