<?php

class Vouchers
{
    protected $database;

    public function __construct(Database $db, Users $user)
    {
        $this->database = $db;
        $this->user = $user;
    }

    public function balance($userid)
    {
        $sql = "SELECT count(vouchers.id) as 'count', users.phone as 'phone' FROM vouchers join users on vouchers.userid = users.userid WHERE vouchers.userid='{$userid}' AND date_redeemed =''";
        $result = $this->database->query($sql);
        if ($result->num_rows > 0) {
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            return FALSE;
        }
    }

    public function determineVoucherCount($transactionsArray)
    {
        $voucherCount = [];

        foreach ($transactionsArray as $transactionData) {
            $sql = "SELECT userid,balance FROM users where userid = {$transactionData['userid']}";
            $result = $this->database->query($sql);
            $balance = $result->fetch_all(MYSQLI_ASSOC);
            $cnt = (int)($balance[0]['balance'] / VOUCHER_TRESHOLD);
            if ($cnt > 0) {
                $voucherCount[$transactionData['userid']] = $cnt;
            }
        }

        return $voucherCount;
    }

    public function addVoucher($voucherUserCount)
    {
        $date = new DateTime();
        $created = $date->format("Y-m-d");
        $expires = $date->modify("+ 30 day")->format("Y-m-d");

        foreach ($voucherUserCount as $u => $v) {

            // we need to reset the user balances after we have added all the vouchers.
            $this->user->updateBalance($u, $v);

            for ($i = 0; $i < $v; $i++) {
                $sql = "INSERT INTO vouchers (`id`,`userid`, `created`, `expires`, `value`) VALUES('', '{$u}','{$created}', '{$expires}', " . VOUCHER_VALUE . ")";
                $this->database->query($sql);
                $logmessage =  "New voucher added for " . $u;
               // $logger->add($logmessage, 'Vouchers');
            }

        }


    }

}