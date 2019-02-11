<?php
$arr = [$_GET, $_POST];
echo json_encode($arr);
require '_/_.php';
if (!DEBUG) exit;
exit;
Session::debugMode();
if (isset($_GET['action'])) switch ($_GET['action']) {
    case 'createSuperAdminUser':
        DBUser::query("DELETE FROM `User` WHERE id = 1");
        $result = User::add([
            'id' => 1,
            'role' => 'SUPER',
            'login' => 'superadmin',
            'password' => 'qqqqqq',
            'mustChangePassword' => false
        ]);
        print_r($result);
        break;
}
?>
<a href="?action=createSuperAdminUser">Create super admin (pwd qqqqqq)</a>