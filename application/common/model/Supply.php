<?php

namespace app\common\model;

use think\Model;

/**
 * 供应链表
 */
class Supply extends Model
{

    // 表名
    protected $name = 'supply';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

}
