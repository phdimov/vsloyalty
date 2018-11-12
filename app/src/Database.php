<?php


class Database
{

    public $connection;

    function __construct()
    {
        $this->open_db_connection();
    }

    public function open_db_connection()
    {

        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($this->connection->connect_errno) {
            die("DB Err:" . $this->connection->connect_error);
        }

    }

    public function query($sql)
    {

        $sql = $this->connection->real_escape_string($sql);

        $result = $this->connection->query($sql);
        $this->confirm_query($result);
        return $result;
    }

    private function confirm_query($result)
    {
        if (!$result) {
            die("Query Failed" . $this->connection->error);
        }
    }

    public function escape_string($string)
    {
        $escaped_string = $this->connection->real_escape_string($string);
        return $escaped_string;
    }

    public function insertArr($Arr, $table)
    {
        $fields = array_keys($Arr);
        $values = array_values($Arr);
        $fieldsList = implode('`,`', $fields);
        $fieldValues = implode('","', $values);
        $fieldValues = '"' . $fieldValues . '"';
        $sql = "INSERT INTO `{$table}` (`$fieldsList`) VALUES ($fieldValues)";
        if (isset($fieldValues)) {
            $result = $this->database->query($sql);
        }

    }

}


