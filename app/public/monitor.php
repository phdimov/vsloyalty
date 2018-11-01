<?php
include('../src/init.php');
include('../public/cron.php');

// for testing
$transactions->truncateDb();

// Load new transactions
$newTransactions = $transactions->monitor();


// Add the new balances to the users balances
$users->process($newTransactions);

// Check how many vouchers to give out
$voucherUserCount = $vouchers->determineVoucherCount($newTransactions);


// Add the vouchers if there are any
$vouchers->addVoucher($voucherUserCount);

// send notification to users who got new vouchers
$messages->notifyUsers($voucherUserCount);