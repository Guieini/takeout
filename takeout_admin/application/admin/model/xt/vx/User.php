<?php

namespace app\admin\model\xt\vx;

use think\Model;


class User extends Model
{

    

    

    // 表名
    protected $table = 'xt_vx_user';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = true;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'black_time_text'
    ];


    public function getBlackTimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['black_time'] ?? '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setBlackTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
