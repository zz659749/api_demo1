<?php
/**
 * Created by PhpStorm.
 * User: zijian
 */

require_once __DIR__ . '/ErrorCode.php';

class Center {

    private $_db;

    /**
     * Center constructor.
     * @param PDO $_db
     */
    public function __construct($_db) {
        $this->_db = $_db;
    }


    /**
     * create center
     * @param $center_name
     * @param $center_contact
     * @param $uid
     * @return array
     * @throws Exception
     */
    public function create($center_name, $center_contact, $uid) {
        if (empty($center_name)) {
            throw new Exception('center_name cannot be null', ErrorCode::CENTER_TITLE_CANNOT_EMPTY);
        }
        if (empty($center_contact)) {
            throw new Exception('center_contact cannot be null', ErrorCode::CENTER_CONTENT_CANNOT_EMPTY);
        }

        $sql = 'INSERT INTO `center`(`center_name`, `center_contact`, `create_time`, `uid`) VALUES(:center_name, :center_contact, :create_time, :uid)';
        $createTime = date('Y-m-d H:i:s');

        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':center_name', $center_name);
        $stmt->bindParam(':center_contact', $center_contact);
        $stmt->bindParam(':create_time', $createTime);
        $stmt->bindParam(':uid', $uid);
        if (!$stmt->execute()) {
            throw new Exception('create center fails', ErrorCode::CENTER_CREATE_FAIL);
        }

        return array(
            'center_id'    => $this->_db->lastInsertId(),
            'center_name'         => $center_name,
            'center_contact'       => $center_contact,
            'create_time'   => $createTime,
            'uid'           => $uid,
        );
    }

    /**
     * view
     * @param $center_id
     * @return mixed
     * @throws Exception
     */
    public function view($center_id) {
        if (empty($center_id)) {
            throw new Exception('center id cannot be null', ErrorCode::CENTER_ID_CANNOT_EMPTY);
        }

        $sql = 'SELECT * FROM `center` WHERE `center_id` = :center_id';
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':center_id', $center_id);
        $stmt->execute();
        $center = $stmt->fetch(PDO::FETCH_ASSOC);
        if (empty($center)) {
            throw new Exception('center does not exist', ErrorCode::CENTER_NOT_FOUND);
        }
        return $center;
    }

    /**
     * edit
     * @param $center_id
     * @param $center_name
     * @param $center_contact
     * @param $uid
     * @return array|mixed
     * @throws Exception
     */
    public function edit($center_id, $center_name, $center_contact, $uid) {
        $center = $this->view($center_id);
        if ($uid !== $center['uid']) {
            throw new Exception('no permission to edit center', ErrorCode::PERMISSION_DENIED);
        }
        $center_name      = empty($center_name) ? $center['center_name'] : $center_name;
        $center_contact    = empty($center_contact) ? $center['center_contact'] : $center_contact;
        if ($center_name == $center['center_name'] && $center_contact == $center['center_contact']) {
            return $center;
        }

        $sql = 'UPDATE `center` SET `center_name` = :center_name, `center_contact` = :center_contact  WHERE `center_id` = :center_id';
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':center_name', $center_name);
        $stmt->bindParam(':center_contact', $center_contact);
        $stmt->bindParam(':center_id', $center_id);
        if (!$stmt->execute()) {
            throw new Exception('center edit fails', ErrorCode::CENTER_EDIT_FAIL);
        }
        return array(
            'center_id'    => $center_id,
            'center_name'         => $center_name,
            'center_contact'       => $center_contact,
            'create_time'   => $center['create_time'],
        );
    }

    /**
     * delete
     * @param $center_id
     * @param $uid
     * @return bool
     * @throws Exception
     */
    public function delete($center_id, $uid) {
        $center = $this->view($center_id);
        if ($uid !== $center['uid']) {
            throw new Exception('no permission to delete center', ErrorCode::PERMISSION_DENIED);
        }

        $sql = 'DELETE FROM `center` WHERE `center_id` = :center_id AND `uid` = :uid';
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':center_id', $center_id);
        $stmt->bindParam(':uid', $uid);
        if (false === $stmt->execute()) {
            throw new Exception('delete center fails', ErrorCode::CENTER_DELETE_FAIL);
        }
        return true;

    }

    /**
     * get a list
     * @param $uid
     * @param int $page
     * @param int $size
     * @return array
     * @throws Exception
     */
    public function getList($uid, $page = 1, $size = 10) {
        if ($size > 100) {
            throw new Exception('page limit 100', ErrorCode::PERMISSION_DENIED);
        }

        $sql = 'SELECT * FROM `center` WHERE `uid` = :uid LIMIT :limit,:offset';
        $limit = ($page - 1) * $size;
        $limit = $limit < 0 ? 0 : $limit;
        $stmt = $this->_db->prepare($sql);
        $stmt->bindParam(':uid', $uid);
        $stmt->bindParam(':limit', $limit);
        $stmt->bindParam(':offset', $size);
        $stmt->execute();
        $list = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $list;
    }
}