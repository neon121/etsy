<?php
require '_/_.php';
header ('access-control-allow-origin: chrome-extension://pbncfplklgiaglkhinpiohnhlmkiaefi');
ob_start();
$return = ['result' => ''];
try {
    if (isset($_POST['hash'])) Session::load($_POST['hash']);
    switch ($_POST['action']) {
        case 'getGlobals':
            $return['result'] = [
                'user' => ['regex' => User::regex],
                'debug' => DEBUG
            ];
            break;
        case 'checkLogin':
            try {
                Session::load($_POST['login'], $_POST['password']);
                if (Session::hasAuth()) {
                    $return['result'] = ['hash' => Session::hash(), 'role' => Session::role()];
                }
                else $return['result'] = false;
            }
            catch (NoSuchUserException $e) {$return['result'] = 'NO_SUCH_USER';}
            catch (WrongPasswordException $e) {$return['result'] = 'WRONG_PASSWORD';}
            break;
        case 'destroySession':
            Session::destroy();
            break;
        case 'addUser':
            $return['result'] = User::add($_POST['data']);
            break;
        case 'changeUser':
            User::changeById($_POST['id'], $_POST['name'], $_POST['value']);
            break;
        case 'removeUser':
            User::deleteById($_POST['id']);
            break;
        case 'addShop':
            $return['result'] = Shop::add($_POST['data']);
            break;
        case 'changeShop':
            Shop::changeById($_POST['id'], $_POST['name'], $_POST['value']);
            break;
        case 'removeShop':
            Shop::deleteById($_POST['id']);
            break;
        case 'setAssignment':
            $return['result'] = Assignment::add($_POST['data']);
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
        default:
            throw new Exception("Wrong action '" . $_POST['action']."'");
    }
    if (!isset($return['result'])) $return['result'] = true;
}
catch (Exception $e) {
    $return['error'] = 'Exception: '.$e->getMessage().' in '.$e->getFile().'('.$e->getLine().')'
        ."\nTrace:\n"
        .$e->getTraceAsString()
        .' '.$e->getFile().'('.$e->getLine().')';
    print_r($_POST);
}
catch (Error $e) {
    $return['error'] = 'Error: '.$e->getMessage().'in '.$e->getFile().'('.$e->getLine().')'
        ."\nTrace:\n"
        .$e->getTraceAsString()
        .' '.$e->getFile().'('.$e->getLine().')';
    print_r($_POST);
}
finally {
    $output = ob_get_contents();
    if ($output) $return['output'] = $output;
    ob_end_clean();
    echo json_encode($return);
}