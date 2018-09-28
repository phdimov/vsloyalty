<?php

Class Transactions
{
    protected $database;

    protected $users;

    public function __construct(Database $db)
    {
        $this->database = $db;
    }

    public function destroySomeJobLogs ()
    {
        // we are killing some jobs to redo the monitoring here so we can test
        $sql = "DELETE FROM monitor where time > DATE_SUB(NOW(), INTERVAL 1 DAY)";
        $this->database->query($sql);

    }

    public function monitor()
    {
        $last_monitor_date = ($this->getLastJobTime()) ? $this->getLastJobTime() : '2018-01-01';
        $sql = "SELECT user_id as 'userid', user_phone_num as 'phone', sum(price) as 'sum' FROM `transactions` WHERE date > '{$last_monitor_date}' GROUP BY DATE(date), userid";
        $result = $this->database->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    private function logJob() {
        $sql = "INSERT INTO monitor (`id`,`job`) VALUES('','job ran')";
        $this->database->query($sql);
    }

    private function getLastJobTime() {
        $sql = "SELECT time FROM monitor ORDER BY time DESC LIMIT 0,1";
        $result = $this->database->query($sql);
        $time = $result->fetch_assoc();
        return $time['time'];
    }



}


