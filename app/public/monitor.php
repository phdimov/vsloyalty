<?php
include('../src/init.php');

Report::get('BETransactions.csv');
$transactions->import('BETransactions.csv');
$transactions->monitor();