<?php
/**
 * Class DBUser
 */
abstract class DBUser {
    /**
     * @var mysqli
     */
    private static $DB;
    private static $used = 0;
    private static $requests = [];
    
    /**
     * @throws Exception
     */
    protected static function checkConnection() {
        if (!self::$DB) {
            self::$DB = DB::start();
            if (self::$DB) self::query("SET @@session.time_zone = '+00:00'");
        }
        if (!self::$DB) throw new Exception('Cant connect to DB');
    }
    
    /**
     * @param $query string
     * @throws Exception
     * @return bool|mysqli_result
     */
    public static function query($query) {
        self::$used++;
        $backtrace = debug_backtrace()[0];
        preg_match('/\/([^\/]+)$/',$backtrace['file'], $file);
        $file = $file[1];
        self::$requests[] = ["$file:{$backtrace['line']}", $query];
        self::checkConnection();
        return self::$DB->query($query);
    }
    
    /**
     * @return int|bool
     */
    public static function lastInsertId() {
        return self::$DB->insert_id;
    }
    
    public static function escapeString($str) {
        return self::$DB->escape_string($str);
    }
    
    public static function printLog($pre = false) {
        if ($pre) echo '<pre>';
        echo 'REQUESTS: ' . self::$used . "\n";
        foreach (self::$requests as $request) echo $request[0] . "\n" . $request[1] . "\n\n";
        if ($pre) echo '</pre>';
    }
}

/**
 * Class DB
 */
class DB extends mysqli {
    /**
     * @param string $query
     * @param int $resultmode
     * @return bool|mysqli_result
     * @throws Exception
     */
    public function query($query, $resultmode = MYSQLI_STORE_RESULT) {
        $result = parent::query($query, $resultmode);
        if ($this->errno != 0) {
            throw new Exception($query . ': (' . $this->errno . ') ' . $this->error);
        }
        else return $result;
    }
    
    /**
     * @return mysqli
     */
    public static function start() {
        return new self(MYSQL_HOST, MYSQL_USER, MYSQL_PWD, MYSQL_DB);
    }
}