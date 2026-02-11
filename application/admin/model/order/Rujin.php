<?php

namespace app\admin\model\order;

use think\Model;


class Rujin extends Model
{

    

    

    // 表名
    protected $name = 'order_rujin';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'pay_type_text',
        'yx_time_text',
        'pay_status_text',
        'pay_time_text',
        'ctime_text',
        'utime_text',
        'status_text',
        'diqu_text'
    ];
    

    
    public function getPayTypeList()
    {
        return ['none' => __('None'), 'bank' => __('Bank'), 'alipay' => __('Alipay'), 'wxpay' => __('Wxpay')];
    }

    public function getPayStatusList()
    {
        return ['0' => __('Pay_status 0'), '1' => __('Pay_status 1'), '2' => __('Pay_status 2'), '3' => __('Pay_status 3'), '4' => __('Pay_status 4'), '5' => __('Pay_status 5')];
    }

    public function getStatusList()
    {
        return ['1' => __('Status 1'), '2' => __('Status 2')];
    }

    public function getDiquList()
    {
        return ['1' => __('Diqu 1'), '2' => __('Diqu 2'), '3' => __('Diqu 3')];
    }


    public function getPayTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['pay_type']) ? $data['pay_type'] : '');
        $list = $this->getPayTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getYxTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['yx_time']) ? $data['yx_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getPayStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['pay_status']) ? $data['pay_status'] : '');
        $list = $this->getPayStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getPayTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['pay_time']) ? $data['pay_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getCtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['ctime']) ? $data['ctime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getUtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['utime']) ? $data['utime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getDiquTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['diqu']) ? $data['diqu'] : '');
        $list = $this->getDiquList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setYxTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setPayTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setCtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setUtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function supply()
    {
        return $this->belongsTo('app\admin\model\Supply', 'pintai_id', 'access_key', [], 'LEFT')->setEagerlyType(0);
    }
}
