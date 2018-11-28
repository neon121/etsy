<?php
abstract class DBEntity extends DBUser implements EntityInterface {
    protected $id = null;
    
    /**
     * @param array $array
     * @return int
     * @throws Exception
     */
    public static function add($array) {
        foreach ($array as $name => $value)
            if (!self::_checkValue($name, $value))
                throw new Exception("$name = '$value' did not pass check");
        $table = get_called_class();
        $names = implode(', ', array_map(function($value) {return "`$value`";}, array_keys($array)));
        $values = implode(', ', array_map(function($value) {return "'$value'";}, array_keys($array)));
        self::query("INSERT INTO `$table` ($names) VALUES($values)");
        return self::lastInsertId();
    }
    
    /**
     * @param string $name
     * @param mixed $value
     * @throws Exception
     */
    public function change($name, $value) {
        if (!self::_checkValue($name, $value)) throw new Exception("$name = '$value' did not pass check");
        $table = get_called_class();
        self::query("UPDATE `$table` SET `$name` = '$value' WHERE id = " . $this->id);
    }
    
    /**
     * @param int $id
     * @param string $name
     * @param mixed $value
     * @throws Exception
     */
    public static function changeById($id, $name, $value) {
        $table = get_called_class();
        /** @var $Object DBEntity */
        $Object = new $table($id);
        $Object->change($name, $value);
    }
    
    /**
     * @throws Exception
     */
    public function delete() {
        $table = get_called_class();
        self::query("DELETE FROM `$table` WHERE id = " . $this->id);
    }
    
    /**
     * @param $id
     * @throws Exception
     */
    public static function deleteById($id) {
        $table = get_called_class();
        /** @var $Object DBEntity */
        $Object = new $table($id);
        $Object->delete();
    }
    
    /**
     * DBEntity constructor.
     * @param mixed $arg
     * @throws Exception
     */
    public function __construct($arg) {
        if (is_array($arg)) {
            foreach ($arg as $name => $value) {
                if (!self::_checkValue($name, $value)) throw new Exception("$name = '$value' did not pass check");
                $this->$name = $value;
            }
            return $this;
        }
        else {
            if (!self::_checkValue('id', $arg)) throw new Exception("id = $arg did not pass check");
            $table = get_called_class();
            $result = self::query("SELECT * FROM `$table` WHERE id = $arg");
            if ($result->num_rows) return new $table($result->fetch_row());
            else throw new Exception("Entity '$table' with id = $arg doesn't exists");
        }
    }
    
    /**
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public static function isExists($id) {
        $table = get_called_class();
        if (!self::_checkValue('id', $id)) throw new Exception("id = '$id' did not pass check");
        return self::query("SELECT id FROM `$table` WHERE id = $id")->num_rows === 1;
    }
    
    /**
     * @param string $name
     * @return mixed
     */
    public function get($name) {
        return $this->$name;
    }
}

interface EntityInterface {
    public static function add($array);
    public static function isExists($id);
    public function delete();
    public static function deleteById($id);
    public function change($name, $value);
    public static function changeById($id, $name, $value);
    public static function _checkValue($name, $value);
    public function get($name);
}