<?php

class Messages {

    private $client;
    private $phone;
    private $database;

    function __construct(Database $db, $client )
    {
        $this->client = $client;
        $this->phone = isset($_POST['phone']) ? $_POST['phone'] : null;
        $this->database = $db;
    }


    public function sendSMS($from, $to, $message) {

        $message = $this->client->messages->create(
            $to,
            [
            "body" => $message,
            "from" => $from
            ]);

        return $message->sid;

    }

    public function incoming_balance()
    {

        $sql = "SELECT users.userid as 'userid', users.phone as 'phone', count(vouchers.id) as 'voucher_count'  FROM users join vouchers on users.userid = vouchers.userid WHERE phone = '{$this->phone}' AND vouchers.date_redeemed = ''";

        $result = $this->database->query($sql);

        print(json_encode($result->fetch_all(MYSQLI_ASSOC)[0]));

    }

    public function incoming_redeem()
    {

        $sql = "SELECT * FROM vouchers JOIN users on vouchers.userid = users.userid WHERE users.phone =  '{$this->phone}' AND vouchers.date_redeemed = ''";

        $result = $this->database->query($sql);
        if ($result->num_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function sendTestSMS() {

        $message = $this->client->messages->create(
            '+32460202329',
            [
                "body" => 'BALANCE',
                "from" => '+32460209483'
            ]);

        echo $message->sid;

    }

}