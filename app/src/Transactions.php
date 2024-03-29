<?php

Class Transactions
{
    protected $database;
    protected $logger;

    public function __construct(Database $db)
    {
        $this->database = $db;
        $this->logger = new Logger($db);

    }

    public function destroySomeJobLogs ()
    {
        // we are killing some jobs to redo the monitoring here so we can test
        $sql = "DELETE FROM monitor where time > DATE_SUB(NOW(), INTERVAL 1 DAY)";
        $this->database->query($sql);

    }

    public function truncateDb ()
    {
        $sql = "TRUNCATE `logger`";
        $this->database->query($sql);
        $sql = "TRUNCATE `monitor`";
        $this->database->query($sql);
        $sql = "TRUNCATE `smslog`";
        $this->database->query($sql);
        $sql = "TRUNCATE `users`";
        $this->database->query($sql);
        $sql = "TRUNCATE `vouchers`";
        $this->database->query($sql);
        echo "DB truncated<br>";
        $this->logger->add("DB Cleaned", "Monitor");
    }

    public function monitor()
    {
        $last_monitor_date = ($this->getLastJobTime()) ? $this->getLastJobTime() : '2018-01-01';
        $sql = "SELECT user_id as 'userid', user_phone_num as 'phone', clad_phone as `clad_phone`, post_code as `post_code`, sum(price) as 'sum' FROM `transactions` WHERE date > '{$last_monitor_date}' GROUP BY userid";
        $result = $this->database->query($sql);
        $this->logJob();
        $this->logger->add( 'Moritoring job ran - '.$result->num_rows.' rows', 'Monitor');
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


