<?php
class User extends DBUser {
    private const salt = "TPixAs4Z";
    
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
     * User constructor.
     * @param array|null $data
     */
    private function __construct($data = null) {
        if ($data !== null) foreach ($data as $name => $value) $this->$name = $value;
    }
    
    /**
     * @param $login
     * @param $password
     * @return User|bool
     */
    public static function byAuth($login, $password) {
        //todo
        return false;
    }
    
}