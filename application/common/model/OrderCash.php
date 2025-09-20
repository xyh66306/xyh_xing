<?php

namespace app\common\model;

use think\Model;

/**
 * 用户返佣设置
 */
class OrderCash extends Model
{

    protected $name = 'order_cash';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'ctime';
    protected $updateTime = 'utime';
}
