<?php
/**
 * Created by PhpStorm.
 * User: zijian
 */

//header('HTTP/1.1 404 Not Found');
header('Content-Type: text/html; Charset=UTF-8');

require __DIR__ . '/config.php';
require __DIR__ . '/lib/User.php';
require __DIR__ . '/lib/Center.php';

$pdo        = require __DIR__ . '/lib/db.php';
$user       = new User($pdo);
$center    = new Center($pdo);

try {
//    print_r($user->register('admin', '123'));
//    print_r($user->login('admin', '123'));

//    $result = $center->create('center_name', 'center_contact1', 1);
//    $result = $center->edit(6, '$center_name', '$center_contact2', 1);
//    $result = $center->delete(6, 1);
    $result = $center->getList(1, 1);
//    $result = $center->view(6);
    var_dump($result);
} catch (Exception $e) {
    echo $e->getMessage();
//    var_dump($e);
}


