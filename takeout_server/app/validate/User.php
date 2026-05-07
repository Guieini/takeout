<?php

namespace app\validate;

use think\Validate;

class User extends Validate
{
    protected $rule = [
        'id' => 'require|number',
        'currentPage' =>  'require|number',
        'size' =>  'require|number',
        'username' => 'require|max:25|min:5',
        'password' => 'max:25|min:6',
        'gender' => 'in:0,1,2',
        'phone' => 'mobile',
    ];

    protected $message = [
        // 'currentPage.require' => 'currentPage is required',
    ];

    protected $scene = [
        'get'  =>  ['currentPage', 'size'],
        'add' =>  ['username', 'password', 'gender', 'phone'],
        'update' =>  ['id', 'username', 'password', 'gender', 'phone'],
        'login' =>  ['username', 'password'],
    ];
}
