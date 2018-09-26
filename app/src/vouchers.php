<?php

class Vouchers
{
    protected $database;

    public function __construct(Database $db)
    {
        $this->database = $db;
    }

    public function checkVoucher($userid)
    {
        $sql = "SELECT * FROM vouchers WHERE userid='{$userid}'";
        $result = $this->database->query($sql);
        if($result->num_rows > 0) {
            print_r($result->fetch_all(MYSQLI_ASSOC));
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function determineVoucherCount($userid) {
        $sql = "SELECT balance FROM users where userid = {$userid}";
        $result = $this->database->query($sql);
        $balance = $resul->fetch_all(MYSQLI_ASSOC);
        return round($balance/ VOUCHER_TRESHOLD);

    }

    public function addVoucher($userid, $count) {
        $date = new DateTime();
        $created = $date->format("d/m/Y");
        $expires = $date->modify("+ 30 day")->format("d/m/Y");
        for ($i = 0; $i =< $cnt; $i++) {
            $sql = "INSERT into vouchers (`id`,`userid`, `created`, `expires`) VALUES('', '{$userid}','{$created}', '{$expires}')";
        }
        $this->database->query($sql);
    }

}