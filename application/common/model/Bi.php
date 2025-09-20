<?php

namespace app\common\model;

use think\Model;


class Bi extends Model
{

    

    

    // 表名
    protected $name = 'bi';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'default_text',
        'status_text'
    ];
    

    
    public function getDefaultList()
    {
        return ['1' => __('Default 1'), '2' => __('Default 2')];
    }

    public function getStatusList()
    {
        return ['1' => __('Status 1'), '2' => __('Status 2')];
    }


    public function getDefaultTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['default']) ? $data['default'] : '');
        $list = $this->getDefaultList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}
