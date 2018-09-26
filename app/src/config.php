<?php

// Database Connection Constants
define('ENV','development');
define('DB_HOST','chilepayments-aurora-instance-0.czozouolf5xj.eu-west-2.rds.amazonaws.com');
define('DB_USER','vsloyalty');
define('DB_PASS','Z96Yd94o3ZdBYVsW');
define('DB_NAME','vsloyalty');
define('BASE_URL', 'https://vivastreet.egnyte.com/webdav/shared/vs.order.retrievals/');
define('BASE_URL_USER', 'vs.order.retrievals');
define('BASE_URL_PASS', 'OkGd1Mjh907Uw1SXqmI=');
define('SHORTURL_API', 'AIzaSyA-7hVhbqpWzrSsqqL5YUZmhmbowqxAYbE');
define('FTP_LOCAL', '../temp/');

$countryConfig = [
    'BE' => [
        'voucherValue' => '10.00'
    ],
    'GB' => [
        'voucherValue' => '10.00'
    ]
];
?>