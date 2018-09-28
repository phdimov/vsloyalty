<?php
include('../src/init.php');

Report::get('BETransactions.csv');
Report::import('BETransactions.csv');