<?php

namespace app\common\model;

use think\Model;

/**
 * 收款二维码
 */
class UserBankcard extends Model
{

    protected $name = 'user_bankcard';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'ctime';
    protected $updateTime = false;

    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }


}
