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
        if ($result->num_rows > 0) {
            print_r($result->fetch_all(MYSQLI_ASSOC));
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function determineVoucherCount($transactionsArray)
    {
        $voucherCount = [];
        $voucherCount['totalcount'] = 0;

        foreach ($transactionsArray as $transactionData) {
            $sql = "SELECT userid,balance FROM users where userid = {$transactionData['userid']}";
            $result = $this->database->query($sql);
            $balance = $result->fetch_all(MYSQLI_ASSOC);
            $cnt = round($balance[0]['balance'] / VOUCHER_TRESHOLD);
            if ($cnt > 0) {
                $voucherCount['totalcount'] = $voucherCount['totalcount'] + $cnt;
                $voucherCount[$transactionData['userid']] = $cnt;
            }
        }

        return $voucherCount;
    }

    public function addVoucher($voucherUserCount)
    {

        echo "Voucher counts: <br>";
        echo "<pre>";
        print_r($voucherUserCount);
        echo "</pre>";

        $date = new DateTime();
        $created = $date->format("d/m/Y");
        $expires = $date->modify("+ 30 day")->format("d/m/Y");

        foreach ($voucherUserCount as $u => $v) {

            for ($i = 0; $i <= $v; $i++) {
                $sql = "INSERT INTO vouchers (`id`,`userid`, `created`, `expires`, `value`) VALUES('', '{$u}','{$created}', '{$expires}', ".VOUCHER_VALUE.")";
                $this->database->query($sql);
            }

        }


    }

}