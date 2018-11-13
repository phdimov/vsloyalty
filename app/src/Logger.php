<?php

class Logger
{

    protected $database;

    public function __construct(Database $db)
    {
        $this->database = $db;
    }

    public function add($message, $module)
    {
        $message = addslashes($message);
        $sql = "INSERT INTO logger (`id`,`action`, `module`) VALUES('','{$message}', '$module')";
        $this->database->query($sql);

    }

}