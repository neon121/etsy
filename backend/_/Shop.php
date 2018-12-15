<?php
class Shop extends DBEntity {
    protected const columns = ['id', 'created', 'name'];
    protected const arrayValues = ['id', 'name', 'created'];
    protected $id, $created, $name;
    
    public const regex = [
        'name' => '/^[-_\s\d\wа-яА-Я]+$/'
    ];
    
    /**
     * @param array $array
     * @return int
     * @throws Exception
     * @throws InputException
     */
    public static function add($array) {
        //todo check rights
        if (!self::isNameUnique($array['name'])) throw new NameNotUniqueException();
        return parent::add($array);
    }
    
    /**
     * @param $array
     * @return bool
     * @throws Exception
     * @throws NameNotUniqueException
     */
    public function change($array) {
        //todo check rights
        if (!self::isNameUnique($array['name'], $this->id)) throw new NameNotUniqueException();
        return parent::change($array);
    }
    /**
     * @return bool
     * @throws Exception
     */
    public function delete() {
        //todo check rights
        return parent::delete();
    }
    
    /**
     * @param string $name
     * @param int|null $id
     * @return bool
     * @throws Exception
     */
    public static function isNameUnique($name, $id = null) {
        if (self::_checkValue('name', $name)) {
            $result = self::query("SELECT id FROM `Shop` WHERE BINARY `name` = '$name' LIMIT 1");
            if ($result->num_rows === 0) return true;
            elseif ($id !== null && $id == $result->fetch_row()[0]) return true;
            else return false;
        }
        else throw new Exception("name = '$name' not passed check");
    }
    
    public static function _checkValue($name, $value) {
        if (array_search($name, self::columns) === false) return false;
        else {
            switch ($name) {
                case 'id':
                    return preg_match('/^\d+$/', $value) == 1;
                case 'created':
                    return preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $value) == 1;
                case 'name':
                    return preg_match(self::regex['name'].'u', $value) == 1;
                default:
                    return true;
            }
        }
    }
}

class NameNotUniqueException extends InputException {public $txt = "Неуникальное имя магазина", $input = 'name';}