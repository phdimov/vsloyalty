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
        $sql = "INSERT INTO smslog (`userid`, `numberfrom`, `numberto`, `message`, `smsid`) VALUES('{$userid}','{$from}', '{$to}', '{$message}',  '000000000000')";
        $this->database->query($sql);
    }


    public function sendSMS($to, $message, $flag)
    {
        if ($flag === 'prod') {
            $message = $this->client->messages->create(
                $to,
                [
                    "body" => $message,
                    "from" => TWILIO_FROM
                ]);

            $this->addSMSLog($from, $to, $message, $message->sid);

            return $message->sid;
        }

        if ($flag === 'dev') {
            echo "SMS Data - From:" . $from;
            echo "| To:" . $to;
            echo "| Message:" . $message;

            $this->addSMSLog($from, $to, $message, $messageid);

        }

    }

    public function incoming($type)
    {

        $sql = "SELECT users.userid as 'userid', users.phone as 'phone', count(vouchers.id) as 'voucher_count'  FROM users join vouchers on users.userid = vouchers.userid WHERE RIGHT(phone, " . PHONELENGTH . ") = RIGHT('{$this->phone}'," . PHONELENGTH . ") AND vouchers.date_redeemed = '' AND DATE(vouchers.expires) > CURRENT_DATE ";

        echo $sql;

        $result = $this->database->query($sql);

        $userBalance = $result->fetch_all(MYSQLI_ASSOC)[0];

        if ($userBalance['voucher_count'] === '0') {

            $message = $this->getMessageBody($type, $userBalance, 'novouchers');

        } elseif ($userBalance['voucher_count'] === '1') {

            $message = $this->getMessageBody($type, $userBalance, 'single');

        } else {

            $message = $this->getMessageBody($type, $userBalance, 'plural');

        }


        $this->sendSMS($userBalance['phone'], $message, 'dev');

        if (($type === 'redeem') && ($userBalance['voucher_count'] != '0')) {

            $emailBody = $this->getMessageBody($type, $userBalance, 'email');
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

    private function getMessageBody($type, $optionArr, $misc)
    {

        $messageBody['redeem']['single'] ="Le montant de votre/vos voucher(s) Vivastreet est : " . $optionArr['voucher_count'] . " . Si vous souhaitez utiliser votre/vos voucher(s), veuillez cliquer sur le lien ci-dessous: ";
        $messageBody['redeem']['plural'] = "Le montant de votre/vos voucher(s) Vivastreet est : " . $optionArr['voucher_count'] . " . Si vous souhaitez utiliser votre/vos voucher(s), veuillez cliquer sur le lien ci-dessous: ";
        $messageBody['redeem']['novouchers'] = "You don't have any vouchers at the moment. Keep spending :)";
        $messageBody['balance']['single'] = "Le montant de votre/vos voucher(s) Vivastreet est : " . $optionArr['voucher_count'] . " . Si vous souhaitez utiliser votre/vos voucher(s), veuillez cliquer sur le lien ci-dessous: ";
        $messageBody['balance']['plural'] = "Le montant de votre/vos voucher(s) Vivastreet est : " . $optionArr['voucher_count'] . " . Si vous souhaitez utiliser votre/vos voucher(s), veuillez cliquer sur le lien ci-dessous: ";
        $messageBody['balance']['novouchers'] = "You don't have any vouchers at the moment. Keep spending :)";
        $messageBody['redeem']['email'] = "userid:" . $optionArr['userid'] . "<br>" . "phone:" . $optionArr['phone'] . "<br>" . "Count:" . $optionArr['voucher_count'] . "<br>";

        return $messageBody[$type][$misc];

    }


    public function notifyUsers($voucherUserCount)
    {

        foreach ($voucherUserCount as $u => $v) {

            $sql = "SELECT phone FROM users where userid ='{$u}' and phone !=''";

            //echo $sql;

            $result = $this->database->query($sql);

            if ($result->num_rows > 0) {

                $phone = $result->fetch_all();

                if ($u > 1) {
                    $message = "Phone " . $phone['0']['0'] . " has $v new vouchers.";
                } else {
                    $message = "Phone " . $phone['0']['0'] . " has $v new voucher.";
                }

                $this->sendSMS('+447493077820', $message, 'dev');
                $this->logger->add($message, 'UserNotification');

            }

        }
    }

    public function expiringVouchers()
    {
        $sql = "SELECT count(vouchers.id) as 'count', vouchers.userid as 'userid', users.phone as 'phone' FROM `vouchers` JOIN users ON vouchers.userid = users.userid WHERE DATE(expires) = DATE_ADD(CURRENT_DATE, INTERVAL 3 DAY) GROUP BY userid";
        $result = $this->database->query($sql);
        foreach ($result->fetch_all(MYSQLI_ASSOC) as $row) {
            if ($row['count'] === '1') {
                $message = " You have " . $row['count'] . " expiring voucher in the next 7 days. Contact us today to redeem.";
            } else {
                $message = " You have " . $row['count'] . " expiring vouchers in the next 7 days. Contact us today to redeem.";
            }
            $this->sendSMS( $row['phone'], $message, 'dev');

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