<?php
/**
 * Created by PhpStorm.
 * User: zijian
 */

require __DIR__ . '/../lib/User.php';
require __DIR__ . '/../lib/Center.php';

$pdo = require __DIR__ . '/../lib/db.php';



class Restful {
    /**
     * @var User
     */
    private $_user;

    /**
     * @var Center
     */
    private $_center;

    /**
     * request method
     * @var
     */
    private $_requestMethod;

    /**
     * resource name
     * @var
     */
    private $_resourceName;

    /**
     * resource id
     * @var
     */
    private $_id;

    /**
     * resource list
     * @var array
     */
    private $_allowResources = array('users', 'centers');

    /**
     * request HTTP methods
     * @var array
     */
    private $_allowRequestMethods = array('GET', 'POST', 'PUT', 'DELETE', 'OPTIONS');

    /**
     * Status code
     * @var array
     */
    private $_statusCodes = array(
        200 => 'OK',
        204 => 'No Content',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        500 => 'Server Internal Error',
    );

    /**
     * Restful constructor.
     * @param $_user
     * @param $_center
     */
    public function __construct($_user, $_center) {
        $this->_user    = $_user;
        $this->_center = $_center;
    }

    public function run() {
        try {
            $this->_setupRequestMethod();
            $this->_setupResource();
            if ($this->_resourceName == 'users') {
                $this->_json($this->_handlerUser());
            } else {
                $this->_json($this->_handlerCenter());
            }
        } catch (Exception $e) {
            $this->_json(array('error' => $e->getMessage()), $e->getCode());
        }
    }

    /**
     * init request method
     */
    private function _setupRequestMethod() {
        $this->_requestMethod = $_SERVER['REQUEST_METHOD'];
        if (!in_array($this->_requestMethod, $this->_allowRequestMethods)) {
            throw new Exception('method not allowed', 405);
        }
    }

    /**
     * init resource
     */
    private function _setupResource() {
        $path = $_SERVER['PATH_INFO'];
        $params = explode('/', $path);
        $this->_resourceName = $params[1];
        if (!in_array($this->_resourceName, $this->_allowResources)) {
            throw new Exception('resource not allowed', 400);
        }
        if (!empty($params[2])) {
            $this->_id = $params[2];
        }
    }

    /**
     * JSON output
     * @param $array
     * @param $code
     */
    private function _json($array, $code = 0) {

        if (null === $array && 0 === $code) {
            $code = 204;
        }

        if (null !== $array && 0 === $code) {
            $code = 200;
        }

        if ($code > 0 && $code != 200 && $code != 204) {
            header("HTTP/1.1 {$code} {$this->_statusCodes[$code]}");
        }

        header('Content-Type: application/json; charset=utf-8');

        if (null !== $array) {
            echo json_encode($array, JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT);
        }
        exit;
    }

    /**
     * request user
     * @return bool|array
     * @throws Exception
     */
    private function _handlerUser() {
        if ('POST' != $this->_requestMethod) {
            throw new Exception('method not allowed', 405);
        }

        $body = $this->_getBodyParams();
        if (empty($body['username'])) {
            throw new Exception('username cannot be null', 400);
        }
        if (empty($body['password'])) {
            throw new Exception('password cannot be null', 400);
        }

        return $this->_user->register($body['username'], $body['password']);
    }

    /**
     * request center
     */
    private function _handlerCenter() {
        switch ($this->_requestMethod) {
            case 'POST':
                return $this->_handlerCenterCreate();
            case 'PUT':
                return $this->_handlerCenterEdit();
            case 'DELETE':
                return $this->_handlerCenterDelete();
            case 'GET':
                if (empty($this->_id)) {
                    return $this->_handlerCenterList();
                } else {
                    return $this->_handlerCenterView();
                }
            default:
                throw new Exception('method not allowed', 405);
        }
    }

    /**
     * get request body params
     * @return mixed
     * @throws Exception
     */
    private function _getBodyParams() {
        $raw = file_get_contents('php://input');
        if (empty($raw)) {
            throw new Exception('invalid params', 400);
        } else {
            return json_decode($raw, true);
        }
    }

    /**
     * created center
     * @return array
     * @throws Exception
     */
    private function _handlerCenterCreate() {
        $body = $this->_getBodyParams();
        if (empty($body['center_name'])) {
            throw new Exception('center_name cannot be null', 400);
        }
        if (empty($body['center_contact'])) {
            throw new Exception('center_contact cannot be null', 400);
        }

        $user = $this->_userLogin($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
        try {
            $center = $this->_center->create($body['center_name'], $body['center_contact'], $user['uid']);
            return $center;
        } catch (Exception $e) {
            if (!in_array($e->getCode(), array(
                ErrorCode::CENTER_TITLE_CANNOT_EMPTY,
                ErrorCode::CENTER_CONTENT_CANNOT_EMPTY
            ))) {
                throw new Exception($e->getMessage(),400);
            }
            throw new Exception($e->getMessage(),500);
        }
    }

    /**
     * edit center
     * @return array|mixed
     * @throws Exception
     */
    private function _handlerCenterEdit() {
        try {
            $center = $this->_center->view($this->_id);
            $user = $this->_userLogin($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
            if ($user['uid'] !== $center['uid']) {
                throw new Exception('permission denied', 403);
            }

            $body       = $this->_getBodyParams();
            $center_name      = empty($body['center_name']) ? $center['center_name'] : $body['center_name'];
            $center_contact    = empty($body['center_name']) ? $center['center_contact'] : $body['center_contact'];
            if ($center_name == $center['center_name'] && $center_contact == $center['center_contact']) {
                return $center;
            }
            return $this->_center->edit($center['center_id'], $center_name, $center_contact, $user['uid']);
        } catch (Exception $e) {
            if ($e->getCode() < 100) {
                if ($e->getCode() == ErrorCode::CENTER_NOT_FOUND) {
                    throw new Exception($e->getMessage(), 404);
                } else {
                    throw new Exception($e->getMessage(), 400);
                }
            } else {
                throw $e;
            }
        }
    }

    /**
     * delete center
     * @return null
     * @throws Exception
     */
    private function _handlerCenterDelete() {
        $user = $this->_userLogin($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
        try {
            $center = $this->_center->view($this->_id);
            $this->_center->delete($center['center_id'], $user['uid']);
            return null;
        } catch (Exception $e) {
            if ($e->getCode() < 100) {
                if ($e->getCode() == ErrorCode::CENTER_NOT_FOUND) {
                    throw new Exception($e->getMessage(), 404);
                } else {
                    throw new Exception($e->getMessage(), 400);
                }
            } else {
                throw $e;
            }
        }
    }

    /**
     *
     * @return array
     * @throws Exception
     */
    private function _handlerCenterList() {
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $size = isset($_GET['size']) ? $_GET['size'] : 10;

        try {
            $user = $this->_userLogin($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
            return $this->_center->getList($user['uid'], $page, $size);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 400);
        }
    }

    /**
     * view center
     * @return mixed
     * @throws Exception
     */
    private function _handlerCenterView() {
        try {
            return $this->_center->view($this->_id);
        } catch (Exception $e) {
            if ($e->getCode() == ErrorCode::CENTER_NOT_FOUND) {
                throw new Exception($e->getMessage(), 404);
            } else {
                throw new Exception($e->getMessage(), 500);
            }
        }
    }

    private function _userLogin($PHP_AUTH_USER, $PHP_AUTH_PW) {
        try {
            return $this->_user->login($PHP_AUTH_USER, $PHP_AUTH_PW);
        } catch (Exception $e) {
            if (in_array($e->getCode(), array(
                ErrorCode::USERNAME_CANNOT_EMPTY,
                ErrorCode::PASSWORD_CANNOT_EMPTY,
                ErrorCode::USERNAME_OR_PASSWORD_INVALID))) {
                throw new Exception($e->getMessage(), 401);
            }
            throw new Exception($e->getMessage(), 500);
        }
    }

}

$user       = new User($pdo);
$center    = new Center($pdo);
$restful = new Restful($user, $center);
$restful->run();