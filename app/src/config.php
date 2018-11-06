<?php

use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env');

// Database Connection Constants
define('ENV', getenv('ENV'));
define('DB_HOST', getenv('DB_HOST'));
define('DB_USER', getenv('DB_USER'));
define('DB_PASS', getenv('DB_PASS'));
define('DB_NAME', getenv('DATABASENAME'));
define('BASE_URL', getenv('BASE_URL'));
define('BASE_URL_USER', getenv('BASE_URL_USER'));
define('BASE_URL_PASS', getenv('BASE_URL_PASS'));
define('SHORTURL_API', getenv('SHORTURL_API'));
define('FTP_LOCAL', getenv('FTP_LOCAL'));
define('VOUCHER_VALUE', getenv('VOUCHER_VALUE'));
define('VOUCHER_TRESHOLD', getenv('VOUCHER_TRESHOLD'));
define('TWILIO_SID', getenv('TWILIO_SID'));
define('TWILIO_TOKEN', getenv('TWILIO_TOKEN'));
define('TWILIO_FROM', getenv('TWILIO_FROM'));
define('LOYALTY_STATIC', getenv('LOYALTY_STATIC'));
define('EMAIL_API_KEY', getenv('EMAIL_API_KEY'));
define('PHONELENGTH', getenv('PHONELENGTH'));
define('PHONEREGEXBE', getenv('PHONEREGEXBE'));
define('PHONEREGEXLU', getenv('PHONEREGEXLU'));
define('GEOS', getenv('GEOS'));
define('SUBCATEGORY', getenv('SUBCATEGORY'));
define('USERTYPE', getenv('USERTYPE'));
define('AREACODEBE', getenv('AREACODEBE'));
define('AREACODELU', getenv('AREACODELU'));
?>