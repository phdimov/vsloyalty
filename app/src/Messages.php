<?php

class Messages
{

    private $client;
    private $phone;
    private $database;
    private $guzzle;

    function __construct(Database $db, $client)
    {
        $this->client = $client;
        $this->phone = isset($_POST['body']) ? $_POST['body'] : null;
        $this->database = $db;
        $this->guzzle = new GuzzleHttp\Client();
        $this->logger = new Logger($db);

    }

    private function addSMSLog($from, $to, $message, $messagesid)
    {
        $sql = "SELECT userid from users where phone = '{$to}'";
        $result = $this->database->query($sql);
        $userid = $result->fetch_all()[0][0];
        $sql = "INSERT INTO smslog (`userid`, `numberfrom`, `numberto`, `message`, `smsid`) VALUES('{$userid}','{$from}', '{$to}', '{$message}', '{$messagesid}' )";
        $this->database->query($sql);
    }


    public function sendSMS($to, $message, $flag)
    {
        if ($flag === 'PRODUCTION') {

            $sms = $this->client->messages->create(
                $to,
                [
                    "body" => $message,
                    "from" => TWILIO_FROM
                ]);

            $this->addSMSLog(TWILIO_FROM, $to, $message, $sms->sid);

            return $sms->sid;
        }

        if ($flag === 'DEVELOPMENT') {

            echo "SMS Data - From:" . TWILIO_FROM;
            echo "| To:" . $to;
            echo "| Message:" . $message;

            $this->addSMSLog(TWILIO_FROM, $to, $message, '0000');

        }

    }

    public function incoming($type)
    {

        $sql = "SELECT users.userid as 'userid', users.phone as 'phone', count(vouchers.id) as 'voucher_count'  FROM users join vouchers on users.userid = vouchers.userid WHERE RIGHT(phone, " . PHONELENGTH . ") = RIGHT('{$this->phone}'," . PHONELENGTH . ") AND vouchers.date_redeemed = '' AND DATE(vouchers.expires) > CURRENT_DATE ";


        $result = $this->database->query($sql);

        $userBalance = $result->fetch_all(MYSQLI_ASSOC)[0];

        if ($userBalance['voucher_count'] > '0') {

            $message = $this->getMessageBody('voucher', $userBalance['voucher_count']);

        } else {

            $message = $this->getMessageBody('novoucher', $userBalance['voucher_count']);

        }


        $this->sendSMS($userBalance['phone'], $message, ENV);

        if (($type === 'redeem') && ($userBalance['voucher_count'] != '0')) {

            $emailBody = $this->getMessageBody('email', $userBalance['voucher_count'], );
            $this->sendEmail("petar@vivastreet.com", $emailBody);
        }

    }

    public function sendTestSMS($message)
    {

        $message = $this->client->messages->create(
            '+32460202329',
            [
                "body" => $message,
                "from" => '+32460209483'
            ]);

        echo $message->sid;

    }

    public function getMessageBody($type, $misc)
    {

        $messageBody['welcome'] = "Welcome to the Vivastreet loyalty program! For every €200 you spend we give you ".VOUCHER_VALUE." credit.";
        $messageBody['voucher'] = "Congratulations, you received a voucher worth ".$misc * VOUCHER_VALUE." ! Click here to redeem now. http://www.vivastreet.be/s/loyaltyprogram";
        $messageBody['expire'] = "Your Vivastreet voucher worth ".$misc * VOUCHER_VALUE." expires in the next 3 days, click to redeem your voucher. http://www.vivastreet.be/s/loyaltyprogram";
        $messageBody['novouchers'] = "Your don't have any active vouchers at the moment. Learn more on: http://www.vivastreet.be/s/loyaltyprogram";
        $messageBody['email'] =  "userid:" . $misc['userid'] . "<br>" . "phone:" . $misc['phone'] . "<br>" . "Count:" . $misc['voucher_count'] . "<br>";


        return $messageBody[$type];

    }


    public function notifyUsers($voucherUserCount)
    {

        foreach ($voucherUserCount as $u => $v) {

            $sql = "SELECT phone FROM users where userid ='{$u}' and phone !=''";

            //echo $sql;

            $result = $this->database->query($sql);

            if ($result->num_rows > 0) {

                $phone = $result->fetch_all();


                    $message = $this->getMessageBody('voucher', $v);


                $this->sendSMS( $phone['0']['0'] , $message, ENV);
                $this->logger->add($message, 'UserNotification');

            }

        }
    }

    public function expiringVouchers()
    {
        $sql = "SELECT count(vouchers.id) as 'count', vouchers.userid as 'userid', users.phone as 'phone' FROM `vouchers` JOIN users ON vouchers.userid = users.userid WHERE DATE(expires) = DATE_ADD(CURRENT_DATE, INTERVAL 3 DAY) GROUP BY userid";
        $result = $this->database->query($sql);
        foreach ($result->fetch_all(MYSQLI_ASSOC) as $row) {
            if ($row['count'] > 0) {
                $message = $this->getMessageBody('expire', $row['count'] );
            }
            $this->sendSMS( $row['phone'], $message, ENV);

            $this->logger->add($row['userid'] . $message, 'VoucherExpiration');
        }
    }

    public function sendEmail($to, $emailBody)
    {

        try {
            $res = $this->guzzle->request('POST', 'https://vivastreet.msyscloud.com/api/v1/transmissions?num_rcpt_errors=3',
                [
                    'headers' => [
                        "accept" => "application/json",
                        "authorization" => EMAIL_API_KEY,
                        "content-type" => "application/json"
                    ],
                    'body' => '{
          "campaign_id": "uk_blog_payment_page",
          "recipients": [
            {
              "address": "' . $to . '",
            } 
          ],
          "return_path": "support-be@sitemail.vivastreet.com",
          "metadata" : 
            {
            "binding": "vivastreet"
            },
          "content": {
            "from": {
              "email": "support-be@sitemail.vivastreet.com",
              "name": "VS BE Loyalty Program"
            },
        
            "subject": "User wants to be contacted to redeem a voucher",
            "html": "' . $emailBody . '"
          }
        }'
                ]);
        } catch (RequestException $e) {
            print(Psr7\str($e->getRequest()));
            if ($e->hasResponse()) {
                print(Psr7\str($e->getResponse()));
            }
        }


    }

}