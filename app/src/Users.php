<?php

class Users
{

    protected $database;
    protected $logger;
    protected $message;

    public function __construct(Database $db, $client)
    {
        $this->database = $db;
        $this->logger = new Logger($db);
        $this->message = new Messages($db, $client);
    }

    public function process($userArr)
    {

        foreach ($userArr as $user) {

            if (!$this->checkGeo($user)) {
                $this->logger->add("User geo  " . $user['post_code'] . " not in geo list: " . $user['userid'], 'Users');
                continue;
            };

            if ($this->check($user)) {

                $this->addBalance($user);

            } else {

                $this->addUser($user);

            }

        }

    }

    private function checkGeo($user)
    {
        $geoArr = explode(",", GEOS);

        if (in_array($user['post_code'], $geoArr)) {

            return true;

        }


    }

    public function addBalance($user)
    {

        $sql = "UPDATE users SET  balance = `balance` + {$user['sum']}, total = `total` +  {$user['sum']} WHERE userid = '{$user['userid']}'";
        $this->database->query($sql);
        $this->logger->add("Updated user balance " . $user['userid'], 'Users');
    }

    public function updateBalance($userid, $cnt)
    {
        $deduct = $cnt * VOUCHER_TRESHOLD;
        $sql = "UPDATE users SET balance = `balance` - $deduct WHERE userid = '{$userid}'";
        $this->database->query($sql);
        $this->logger->add("Updated user balance " . $user['userid'], 'Users');

    }

    public function check($user)
    {
        $sql = "SELECT * from users WHERE userid = '{$user['userid']}'";
        $result = $this->database->query($sql);
        if ($result->num_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    private function addUser($user)
    {
        $aphone = $this->filterPhone($user);
        if ($aphone) {
            $sql = "INSERT INTO users (`userid`, `phone`, `balance`, `total`) VALUES('{$user['userid']}',  '{$aphone}', '0','0')";
            $this->database->query($sql);
            $this->logger->add("Added new user " . $user['userid'], 'Users');
            $this->message->sendSMS($aphone, $this->message->getMessageBody('welcome', '1'), ENV);
            $this->addBalance($user);

            return true;

        } else {
            $this->logger->add("User not allowed (bad phone number): " . $user['userid'], 'Users');

            return false;
        }

    }


    private function filterPhone($user)
    {
        if (preg_match(PHONEREGEXBE, $user['clad_phone'], $matches)) {
            return AREACODEBE.substr($matches[0],-9);
        }

        if (preg_match(PHONEREGEXLU, $user['clad_phone'], $matches)) {
            return AREACODELU.substr($matches[0],-9);
        }

    }


}