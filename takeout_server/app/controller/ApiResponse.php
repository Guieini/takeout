<?php

namespace app\controller;

class ApiResponse
{
    /**
     * Define the returned API data code information
     */
    const CODE_INFO = [
        // Common success code
        0 => 'success',
        // database error code
        801 => 'Database error',
        802 => 'Model not found',
        803 => 'Invalid url',

        // Unknown error code
        1000 => 'Unknown error',
        // Common error code
        1001 => 'No permission',
        1002 => 'Parameter error',
        1003 => 'Data does not exist',
        1004 => 'Data already exists',
        1005 => 'Data save failed',

        1100 => 'Login failed',
        1101 => 'Token is required',
        1102 => 'Token is invalid',
        1103 => 'Token has expired',
        // Wechat error code
        2000 => 'Wechat error',
    ];

    public static function success(array $data = [], string $message = '')
    {
        $message = empty($message) ? self::getErrorMsg(0) : $message;

        // Define the returned Json data format
        $data = array(
            'code' => 0,
            'data' => $data,
            'message' => $message,
        );

        self::output($data);
    }

    public static function fail(array $data = [], $code = 1000, string $message = '')
    {
        $message = empty($message) ? self::getErrorMsg($code) : $message;

        // Define the returned Json data format
        $data = array(
            'code' => $code,
            'data' => $data,
            'message' => $message,
        );

        self::output($data);
    }

    public static function output($data)
    {
        header('Content-Type:application/json; charset=utf-8');
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        echo $data;
        exit;
    }

    public static function getErrorMsg($code)
    {
        return self::CODE_INFO[$code];
    }
}
