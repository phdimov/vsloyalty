<?php

class Users
{

    protected $database;
    protected $logger;

    public function __construct(Database $db)
    {
        $this->database = $db;
        $this->logger = new Logger($db);
    }

    public function addBalance($userArr)
    {
        foreach ($userArr as $user) {
            if ($this->check($user)) {
                $sql = "UPDATE users SET  balance = `balance` + {$user['sum']}, total = `total` +  {$user['sum']} WHERE userid = '{$user['userid']}'";
                $this->database->query($sql);
                $this->logger->add("updated user balance ".$user['userid'], 'Users');
            } else {
                $this->addUser($user);
                $this->logger->add("added new user ".$user['userid'], 'Users');
                $sql = "UPDATE users SET  balance = `balance` + {$user['sum']}, total = `total` +  {$user['sum']} WHERE userid = '{$user['userid']}'";
                $this->database->query($sql);
                $this->logger->add("updated user balance ".$user['userid'], 'Users');
            }
        }
    }

    public function updateBalance($userid, $cnt)
    {
        $deduct = $cnt * VOUCHER_TRESHOLD;
        $sql = "UPDATE users SET balance = `balance` - $deduct WHERE userid = '{$userid}'";

        $this->database->query($sql);

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

    public function addUser($user)
    {
        $sql = "INSERT INTO users (`userid`, `phone`, `balance`, `total`) VALUES('{$user['userid']}',  '{$user['phone']}', '0','0')";
        $this->database->query($sql);
    }

}