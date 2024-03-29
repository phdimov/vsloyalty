<?php

class Api
{

    protected $database;
    protected $body;

    function __construct(Database $db)
    {
        $this->database = $db;
        $this->body = isset($_GET['u']) ? $_GET['u'] : null;
    }

    public function findVoucherCount()
    {
        $userid = $this->database->escape_string($this->body);

        $sql = "SELECT vouchers.userid as 'userid',  count(vouchers.id) as 'voucher_count'  FROM vouchers WHERE userid =  '{$userid}' AND date_redeemed ='' AND DATE(vouchers.expires) > CURRENT_DATE";

        $result = $this->database->query($sql);

        return print(json_encode($result->fetch_all(MYSQLI_ASSOC)[0]));
    }


}