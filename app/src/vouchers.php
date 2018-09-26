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
        $sql = "SELECT * FROM vouchers WHERE userid='{$userid}' AND created < DATE(NOW() - INTERVAL 30 DAY)";
        $result = $this->database->query($sql);
        if($result->num_rows > 0) {
            print_r($result->fetch_all(MYSQLI_ASSOC));
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function addVoucher($userid) {
        print($userid." deserves a voucher<br>");
        $date = new DateTime();
        $created = $date->format("d/m/Y");
        $expires = $date->modify("+ 30 day")->format("d/m/Y");
        $sql = "INSERT into vouchers (`id`,`userid`, `created`, `expires`) VALUES('', '{$userid}','{$created}', '{$expires}')";
        $this->database->query($sql);
    }

}