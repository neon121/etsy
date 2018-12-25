<?php
class Customer extends DBEntity {
    protected const columns = ['id', 'created', 'name'];
    protected const arrayValues = ['id', 'name', 'created'];
    protected $id, $created, $name;
    
    public const regex = [
        'name' => '/^[-_\s\d\wа-яА-Я\.]+$/'
    ];
    
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