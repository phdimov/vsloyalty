<?php
class Users {

    protected $database;

    public function __construct(Database $db)
    {
        $this->database = $db;
    }

    public function updateBalance($user) {
       $sql = "UPDATE users SET  balance = `balance` + {$user['sum']}, total = `total` +  {$user['sum']} WHERE userid = '{$user['userid']}'";
       if($this->check($user)) {
           $result = $this->database->query($sql);
       } else {
           $this->add($user);
       }
    }

    public function check($user) {
        $sql = "SELECT * from users WHERE userid = '{$user['userid']}'";
        $result =  $this->database->query($sql);
        if($result->num_rows > 0) {
            return true;
        }  else {
            return false;
        }
    }

    public function add($user) {
        $sql = "INSERT INTO users (`userid`, `phone`, `balance`, `total`) VALUES('{$user['userid']}',  '{$user['phone']}', '{$user['sum']}','{$user['sum']}')";
        print $sql;
        $this->database->query($sql);
    }

}