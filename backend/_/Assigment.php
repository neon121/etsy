<?php
class Assignment extends DBEntity {
    private const columns = ['id', 'userId', 'shopId'];
    
    /**
     * @param array $array
     * @return int
     * @throws Exception
     */
    public static function add($array) {
        //todo check rights
        $userId = (int)$array['userId'];
        $shopId = (int)$array['shopId'];
        $result = self::query(
            "SELECT id FROM `User` WHERE id = $userId UNION SELECT id FROM `Shop` WHERE id = $shopId");
        if ($result->num_rows < 2) throw new Exception("User($userId) OR shop($shopId) doesn't exist");
        else return parent::add($array);
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
        if (array_search($name, self::columns) === false) return false;
        else {
            switch ($name) {
                case 'id':
                case 'shopId':
                case 'userId':
                    return preg_match('/^\d+$/', $value) == 1;
            }
        }
    }
}