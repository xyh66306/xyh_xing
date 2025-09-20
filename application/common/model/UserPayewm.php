<?php

namespace app\common\model;

use think\Model;

/**
 * 收款二维码
 */
class UserPayewm extends Model
{

    protected $name = 'user_payewm';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'ctime';
    protected $updateTime = false;
}
