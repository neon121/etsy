<?php
class User extends DBEntity {
    private const salt = "TPixAs4Z";
    private const roles = ['SUPER', 'ADMIN', 'MANAGER'];
    public const regex = [
        'login' => '/^[-_\w\d]{6,32}$/',
        'password' => '/^[-_\w\d]{6,}$/'
    ];
    private const columns = ['id', 'role', 'created', 'login', 'passwordHash', 'salt',
        'reservationRule', 'mustChangePassword'];
    protected $id, $role, $created, $login, $mustChangePassword, $reservationRule;
    
    /**
     * @param $password
     * @param $salt
     * @return mixed
     */
    private static function hashPassword($password, $salt) {
        return explode('$', crypt($password, '$6$'.$salt.self::salt))[3];
    }
    
    /**
     * @return string
     * @throws Exception
     */
    private static function generateSalt() {
        $symbols = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $max = strlen($symbols) - 1;
        $return = '';
        while (strlen($return) < 8) $return .= $symbols[random_int(0, $max)];
        return $return;
    }
    
    /**
     * @param $login
     * @param $password
     * @return User
     * @throws Exception
     */
    public static function byAuth($login, $password) {
        if (!self::_checkValue('login', $login))
            throw new Exception("login = '$login' not passed check");
        elseif (!self::_checkValue('password', $password))
            throw new Exception("password = '$password' not passed check");
        else {
            $result = self::query("SELECT * FROM `User` WHERE login = '$login'");
            if ($result->num_rows == 0) throw new Exception("No such user");
            else {
                $array = $result->fetch_assoc();
                if (hash_equals($array['passwordHash'], self::hashPassword($password, $array['salt'])))
                    return new User($array);
                else throw new Exception("Wrong password");
            }
        }
    }
    
    /**
     * User constructor.
     * @param $arg
     * @throws Exception
     */
    public function __construct($arg) {
        parent::__construct($arg);
        $this->reservationRule = json_decode($this->reservationRule);
    }
    
    /**
     * @param string $name
     * @param mixed $value
     * @throws Exception
     */
    public function change($name, $value) {
        //todo check rights
        parent::change($name, $value);
    }
    
    /**
     * @param array $array
     * @return int
     * @throws Exception
     */
    public static function add($array) {
        //todo check rights
        return parent::add($array);
    }
    
    /**
     * @throws Exception
     */
    public function delete() {
        //todo check rights
        parent::delete();
    }
    
    /**
     * @param string $name
     * @param mixed $value
     * @return bool
     */
    public static function _checkValue($name, $value) {
        if ($name === 'password') return preg_match(self::regex['password'], $value) == 1;
        elseif (array_search($name, self::columns) === false) return false;
        else {
            switch ($name) {
                case 'id':
                    return preg_match('/^\d+$/', $value) == 1;
                case 'role':
                    return array_search($value, self::roles) !== false;
                case 'passwordHash':
                    return strlen($value) == 86;
                case 'salt':
                    return strlen($value) == 8;
                case 'created':
                    return preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $value) == 1;
                case 'login':
                    return preg_match(self::regex['login'], $value) == 1;
                case 'reservationRule':
                    return is_array(json_decode($value));
                case 'mustChangePassword':
                    return is_bool($value);
            }
        }
    }
}