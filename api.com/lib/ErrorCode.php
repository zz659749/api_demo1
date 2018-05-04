<?php
/**
 * Created by PhpStorm.
 * User: zijian
 */

class ErrorCode {
    const USERNAME_EXISTS               = 1;
    const PASSWORD_CANNOT_EMPTY         = 2;
    const USERNAME_CANNOT_EMPTY         = 3;
    const REGISTER_FAIL                 = 4;
    const USERNAME_OR_PASSWORD_INVALID  = 5;
    const CENTER_TITLE_CANNOT_EMPTY    = 6;
    const CENTER_CONTENT_CANNOT_EMPTY  = 7;
    const CENTER_CREATE_FAIL           = 8;
    const CENTER_ID_CANNOT_EMPTY       = 9;
    const CENTER_NOT_FOUND             = 10;
    const PERMISSION_DENIED             = 11;
    const CENTER_EDIT_FAIL             = 12;
    const CENTER_DELETE_FAIL           = 13;
    const PAGE_SIZE_TO_BIG              = 14;
    const SERVER_INTERNAL_ERROR         = 15;
}