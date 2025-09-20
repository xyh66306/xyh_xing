<?php

namespace app\admin\model\user;

use think\Model;


class Payewm extends Model
{

    

    

    // 表名
    protected $name = 'user_payewm';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'pay_skpt_text',
        'type_text',
        'status_text',
        'sys_status_text',
        'ctime_text'
    ];
    

    
    public function getPaySkptList()
    {
        return ['wxpay' => __('Wxpay'), 'alipay' => __('Alipay')];
    }

    public function getTypeList()
    {
        return ['INR' => __('Inr'), 'THB' => __('Thb'), 'CNY' => __('Cny')];
    }

    public function getStatusList()
    {
        return ['hidden' => __('Hidden'), 'normal' => __('Normal')];
    }

    public function getSysStatusList()
    {
        return ['normal' => __('Sys_status normal'), 'hidden' => __('Sys_status hidden')];
    }


    public function getPaySkptTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['pay_skpt']) ? $data['pay_skpt'] : '');
        $list = $this->getPaySkptList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getSysStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['sys_status']) ? $data['sys_status'] : '');
        $list = $this->getSysStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getCtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['ctime']) ? $data['ctime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setCtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function user()
    {
        return $this->belongsTo('app\admin\model\User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
