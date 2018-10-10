<?php
include('../src/init.php');

$report->get('BETransactions.csv');
$report->import('BETransactions.csv');
