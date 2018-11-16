<?php
/**
 * Class DBUser
 */
abstract class DBUser {
    /**
     * @var mysqli
     */
    protected static $DB;
    public static $used = 0;
    
    /**
     * @throws Exception
     */
    protected static function checkConnection() {
        if (!self::$DB) self::$DB = DB::start();
        if (!self::$DB) throw new Exception('Cant connect to DB');
    }
    
    /**
     * @param $query string
     * @throws Exception
     * @return bool|mysqli_result
     */
    public static function query($query) {
        self::$used++;
        self::checkConnection();
        return self::$DB->query($query);
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