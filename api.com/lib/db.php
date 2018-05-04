<?php
/**
 * database handle
 * Created by PhpStorm.
 * User: zijian
 */

try {

    /*$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_SCHEMA;
    $options = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    );
    if( version_compare(PHP_VERSION, '5.3.6', '<') ){
        if( defined('PDO::MYSQL_ATTR_INIT_COMMAND') ){
            $options[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES ' . DB_ENCODING;
        }
    }else{
        $dsn .= ';charset=' . DB_ENCODING;
    }

    $pdo = @new PDO($dsn, DB_USER, DB_PASSWORD, $options);
    if( version_compare(PHP_VERSION, '5.3.6', '<') && !defined('PDO::MYSQL_ATTR_INIT_COMMAND') ){
        $sql = 'SET NAMES ' . DB_ENCODING;
        $pdo->exec($sql);
    }*/

    $options = array(
        PDO::MYSQL_ATTR_INIT_COMMAND    => 'SET NAMES utf8'
    );
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=mydb', 'root', 'vagrant', $options);

    // no local simulation of prepare
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    var_dump($e->getMessage());
}
return $pdo;
