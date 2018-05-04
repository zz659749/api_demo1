<?php
/**
 * Created by PhpStorm.
 * User: zijian
 */

require_once __DIR__ . '/ErrorCode.php';

class User {
    /**
     * database handle
     * @var
     */
    private $_db;

    /**
     * User constructor.
     * @param PDO $_db PDO connection handle
     */
    public function __construct($_db) {
        $this->_db = $_db;
    }


    /**
     * login
     * @param $username
     * @param $password
     * @return mixed
     * @throws Exception
     */
    public function login($username, $password) {
        if (empty($username)) {
            throw new Exception('username cannot be null', ErrorCode::USERNAME_CANNOT_EMPTY);
        }
        if (empty($password)) {
            throw new Exception('password cannot be null', ErrorCode::PASSWORD_CANNOT_EMPTY);
        }

        $sql = 'SELECT `uid`, `username` FROM `user` WHERE `username` = :username AND `password` = :password';
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', self::_md5($password));
        if (!$stmt->execute()) {
            throw new Exception('server internal error', ErrorCode::SERVER_INTERNAL_ERROR);
        }
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($user)) {
            throw new Exception('invalid username or password', ErrorCode::USERNAME_OR_PASSWORD_INVALID);
        }
        return $user;
    }

    /**
     * user login
     * @param $username
     * @param $password
     * @return array
     * @throws Exception
     */
    public function register($username, $password) {
        if (empty($username)) {
            throw new Exception('username cannot be null', ErrorCode::USERNAME_CANNOT_EMPTY);
        }
        if (empty($password)) {
            throw new Exception('password cannot be null', ErrorCode::PASSWORD_CANNOT_EMPTY);
        }
        if ($this->_isUsernameExists($username)) {
            throw new Exception('username exists', ErrorCode::USERNAME_EXISTS);
        }

        /* database */
        $sql = 'INSERT INTO `user`(`username`, `password`, `create_time`) VALUES(:username, :password, :create_time)';
        $password = self::_md5($password);
        $createTime = date('Y-m-d H:i:s');
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':create_time', $createTime);
        if (!$stmt->execute()) {
            throw new Exception('register fails', ErrorCode::REGISTER_FAIL);
        }
        return array(
            'uid'           => $this->_db->lastInsertId(),
            'username'      => $username,
            'create_time'   => $createTime,
        );
    }

    /**
     * MD5
     * @param $string
     * @param string $key
     * @return string
     */
    private static function _md5($string, $key = 'imooc') {
        return md5($string . $key);
    }

    /**
     * check username exists
     * @param $username
     * @return bool
     */
    private function _isUsernameExists($username) {
        $sql = 'SELECT * FROM `user` WHERE `username` = :username';
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return !empty($result);
    }
}