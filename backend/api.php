<?php
require '_/_.php';

ob_start();
$return = [];
try {
    if (is_array($_POST['session'])) Session::load($_POST['session']['login'], $_POST['session']['password']);
    switch ($_POST['action']) {
        case 'addUser':
            $return['id'] = User::add($_POST['data']);
            break;
        case 'changeUser':
            User::changeById($_POST['id'], $_POST['name'], $_POST['value']);
            break;
        case 'removeUser':
            User::deleteById($_POST['id']);
            break;
        case 'addShop':
            $return['id'] = Shop::add($_POST['data']);
            break;
        case 'changeShop':
            Shop::changeById($_POST['id'], $_POST['name'], $_POST['value']);
            break;
        case 'removeShop':
            Shop::deleteById($_POST['id']);
            break;
        case 'setAssignment':
            $return['id'] = Assignment::add($_POST['data']);
            break;
        case 'removeAssignment':
            Assignment::deleteById($_POST['id']);
            break;
        case 'reserveOrder':
            //todo
            break;
        case 'getList':
            //todo
            break;
        case 'loadGlobals':
            //todo
            break;
    }
    if (!isset($return['result'])) $return['result'] = true;
}
catch (Exception $e) {
    $return['error'] = $e;
}
finally {
    $return['output'] = ob_get_contents();
    ob_end_clean();
    echo json_encode($return);
}