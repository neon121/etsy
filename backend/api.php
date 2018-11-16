<?php
require '_/_.php';

ob_start();
$return = [];
try {
    switch ($_POST['action']) {
        //todo
        case 'addUser':
            break;
        case 'changeUser':
            break;
        case 'removeUser':
            break;
        case 'addShop':
            break;
        case 'changeShop':
            break;
        case 'removeShop':
            break;
        case 'setAssignment':
            break;
        case 'removeAssignment':
            break;
        case 'reserveOrder':
            break;
        case 'getList':
            break;
    }
}
catch (Exception $e) {
    $return['error'] = $e;
}
finally {
    $return['output'] = ob_get_contents();
    ob_end_clean();
    echo json_encode($return);
}