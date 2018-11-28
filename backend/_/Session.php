<?php
class Session {
    /** @var User $User */
    protected static $User = null;
    
    /**
     * @param null|string $login
     * @param null|string $password
     * @throws Exception
     */
    public static function load($login = null, $password = null) {
        if (!is_null($login)) {
            self::$User = User::byAuth($login, $password);
            if (self::$User->get('mustChangePassword')) throw new Exception("Change password first");
        }
    }
    
    public static function role() {
        if (is_object(self::$User)) return self::$User->get('role');
        else return false;
    }
    
    public static function id() {
        if (is_object(self::$User)) return self::$User->get('id');
        else return false;
    }
}