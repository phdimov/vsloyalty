<?php
class Users {

    protected $database;

    public function __construct(Database $db)
    {
        $this->database = $db;
    }

    public function updateUserBalance($user) {
       $sql = "UPDATE users SET  balance = `balance` + {$user['sum']} where userid = '{$user['userid']}'";
       echo $sql;
       $result =  $this->database->query($sql);
       var_dump($result);

    }

}