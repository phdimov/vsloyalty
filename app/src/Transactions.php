<?php

Class Transactions
{
    protected $database;

    protected $users;

    public function __construct(Database $db)
    {
        $this->database = $db;
        $this->users = new Users($db);
        $this->vouchers = new Vouchers($db);
    }

    public function monitor()
    {
        $last_monitor_date = ($this->getLastJobTime()) ? $this->getLastJobTime() : '2018-01-01';
        $sql = "SELECT user_id as 'userid', user_phone_num as 'phone', sum(price) as 'sum' FROM `transactions` WHERE date > '{$last_monitor_date}' GROUP BY DATE(date), userid";
        $result = $this->database->query($sql);
        foreach ($result->fetch_all(MYSQLI_ASSOC) as $res) {
            $this->users->updateBalance($res);
            $cnt = $this->vouchers->determineVoucherCount($res['userid'];
            $this->vouchers->addVoucher($res['userid'], $cnt ); // may be 0 and that is fine.
            $this->logJob();
        }
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

    public function import($remotefile)
    {
        $result = $this->database->query("SELECT date FROM transactions ORDER BY date DESC LIMIT 0,1");
        $t_check_date = $result->fetch_object();
        if ($t_check_date) {
            $transactionsLastEntry = new DateTime($t_check_date->date);
        } else {
            $transactionsLastEntry = new DateTime('1982');
        }
        $counter = 0;
        $header = NULL;
        $data = array();
        $delimiter = ',';
        if (($handle = fopen(FTP_LOCAL . $remotefile, 'r')) !== FALSE) {
            while (($row = fgetcsv($handle, $delimiter)) !== FALSE) {

                if (!$header)
                    $header = $row;
                else
                    $data = array_combine($header, $row);


                if (empty($data)) {
                    continue;
                }
                $counter++;
                $date2 = new DateTime($data['Date']);


                if ($transactionsLastEntry >= $date2) {
                    continue;
                }
                $tableMapping =
                    array('order_id' => 'Order ID',
                        'date' => 'Date',
                        'user_id' => 'User ID',
                        'user_phone_num' => 'User Phone Number',
                        'user_account_create_date' => 'Account Creation Date',
                        'name' => 'Name',
                        'existing_new' => 'Existing/New',
                        'clad_id' => 'Classified ID',
                        'teaser_interval' => 'Received Teaser on Day',
                        'discount_interval' => 'Received Discount on Day',
                        'posting_modify' => 'Posting/Modify',
                        'category' => 'Category',
                        'umbrella' => 'Umbrella',
                        'subcategory' => 'Subcategory',
                        'source' => 'Source',
                        'price' => 'Price',
                        'plans' => 'Plans',
                        'p2v_length' => 'P2V Length',
                        'p2v_price' => 'P2v Price',
                        'p2vip_length' => 'P2VIP Length',
                        'p2vip_price' => 'P2VIP Price',
                        'premium_price' => 'P2P Price',
                        'premium_length' => 'P2P Length',
                        'featured_price' => 'FA Price',
                        'featured_length' => 'FA Length',
                        'highlight_price' => 'H Price',
                        'highlight_length' => 'H Length',
                        'repost_price' => 'P2R Price',
                        'repost_length' => 'P2R Length',
                        'single_repost_price' => 'P2R1 Price',
                        'single_repost_length' => 'P2R1 Length',
                        'p2label_price' => 'P2L Price',
                        'p2label_length' => 'P2L Length',
                        'p2url_length' => 'P2URL Length',
                        'p2url_price' => 'P2URL Price',
                        'repost_unlimited_length' => 'P2RU Length',
                        'repost_unlimited_price' => 'P2RU Price',
                        'country' => 'Country',
                        'geo1' => 'Geo1',
                        'geo2' => 'Geo2',
                        'geo3' => 'Geo3',
                        'post_code' => 'Post Code',
                        'discount' => 'Discount',
                        'type' => 'Type',
                        'social_status' => 'Social Status',
                        'init_by' => 'Page Type',
                        'platform' => 'Platform');

                //fix the name

                $patterns = array();
                $patterns[0] = '/[0-9]*/';
                $patterns[1] = '/\@.*$/';
                $patterns[2] = '/(\.|\_|\,)/';
                $replacements = array();
                $replacements[0] = '';
                $replacements[1] = '';
                $replacements[2] = '';
                $data['Name'] = ucfirst(preg_replace($patterns, $replacements, $data['Name']));

                $fields = array_keys($tableMapping);
                count($tableMapping);
                $values = array_values($data);
                // sort the plans start
                $planSort = explode(',', $values['18']);
                sort($planSort);
                $values['18'] = implode(',', $planSort);
                // sort the plans end
                $fieldsList = implode('`,`', $fields);
                $fieldValues = implode('","', $values);
                $fieldValues = '"' . $fieldValues . '"';
                $sql = "INSERT INTO transactions (`$fieldsList`) VALUES ($fieldValues)";
                if (isset($fieldValues)) {
                    $result = $this->database->query($sql);
                }
            }

        }
    }

}


