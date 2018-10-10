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
        $this->phone = isset($_POST['phone']) ? $_POST['phone'] : null;
        $this->database = $db;
        $this->guzzle = new GuzzleHttp\Client();
    }


    public function sendSMS($from, $to, $message, $flag)
    {
        if ($flag === 'prod') {
            $message = $this->client->messages->create(
                $to,
                [
                    "body" => $message,
                    "from" => $from
                ]);

            return $message->sid;
        }

        if ($flag === 'dev') {
            echo "From:" . $from;
            echo "| To:" . $to;
            echo "| Message:" . $message;
        }

    }

    public function incoming_balance()
    {

        $sql = "SELECT users.userid as 'userid', users.phone as 'phone', count(vouchers.id) as 'voucher_count'  FROM users join vouchers on users.userid = vouchers.userid WHERE phone LIKE '%{$this->phone}%' AND vouchers.date_redeemed = ''";

        $result = $this->database->query($sql);

        $userBalance = $result->fetch_all(MYSQLI_ASSOC)[0];

        if ($userBalance['voucher_count'] === '0') {

            $message = "You don't have any vouchers at the moment. Keep spending :)";

        } elseif ($userBalance['voucher_count'] === '1') {

            $message = "BALANCE: You have " . $userBalance['voucher_count'] . " voucher with us. Call 222-222-2222 to redeem.";

        } else {

            $message = "BALANCE: You have " . $userBalance['voucher_count'] . " vouchers with us. Call 222-222-2222 to redeem.";

        }

        //override for testing
        $userBalance['phone'] = '+447493077820';

        $this->sendSMS('+32460209483', $userBalance['phone'], $message, 'dev');


    }

    public function incoming_redeem()
    {

        $sql = "SELECT users.userid as 'userid', users.phone as 'phone', count(vouchers.id) as 'voucher_count'  FROM users join vouchers on users.userid = vouchers.userid WHERE phone LIKE '%{$this->phone}%' AND vouchers.date_redeemed = ''";

        $result = $this->database->query($sql);

        $userBalance = $result->fetch_all(MYSQLI_ASSOC)[0];

        if ($userBalance['voucher_count'] === '0') {

            $message = "You don't have any vouchers at the moment. Keep spending :)";

        } elseif ($userBalance['voucher_count'] === '1') {

            $message = "REDEEEM: You have " . $userBalance['voucher_count'] . " voucher with us. Customer Service has been notified to contact you";

        } else {

            $message = "REDEEM: You have " . $userBalance['voucher_count'] . " vouchers with us. Customer Service has been notified to contact you.";

        }

        //override for testing
        $userBalance['phone'] = '+447493077820';;

        $this->sendSMS('+32460209483', $userBalance['phone'], $message, 'dev');

        $emailBody = "userid:" . $userBalance['userid'] . "<br>";
        $emailBody .= "phone:" . $userBalance['phone'] . "<br>";
        $emailBody .= "Count:" . $userBalance['voucher_count'] . "<br>";

        $this->sendEmail("petar@vivastreet.com", $emailBody);
    }

    public function sendTestSMS()
    {

        $message = $this->client->messages->create(
            '+32460202329',
            [
                "body" => 'REDEEM',
                "from" => '+32460209483'
            ]);

        echo $message->sid;

    }

    public function sendBalance($userBalance)
    {

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
                    $message = "phone " . $phone['0']['0'] . " has $v new vouchers.";
                } else {
                    $message = "phone " . $phone['0']['0'] . " has $v new voucher.";
                }

                $this->sendSMS('+32460209483', '+447493077820', $message, 'dev');

            }

        }
    }

    public function expiringVouchers()
    {
        $sql = "SELECT count(vouchers.id) as 'count', vouchers.userid as 'userid', users.phone as 'phone' FROM `vouchers` JOIN users ON vouchers.userid = users.userid WHERE expires BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY) GROUP BY userid";
        $result = $this->database->query($sql);
        foreach ($result->fetch_all(MYSQLI_ASSOC) as $row) {
            if ($row['count'] === '1') {
                $message = "You have " . $row['count'] . " expiring voucher in the next 7 days. Contact us today to redeem.";
            } else {
                $message = "You have " . $row['count'] . " expiring vouchers in the next 7 days. Contact us today to redeem.";
            }
            $this->sendSMS('+32460209483', $row['phone'], $message, 'dev');
        }
    }

    public function sendEmail($to, $emailBody)
    {

        try {
            $res = $this->guzzle->request('POST', 'https://vivastreet.msyscloud.com/api/v1/transmissions?num_rcpt_errors=3',
                [
                    'headers' => [
                        "accept" => "application/json",
                        "authorization" => "b6cc6e4e6f16414ca896a36b9e33928991ea1d53",
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