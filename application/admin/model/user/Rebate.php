<?php

namespace app\admin\model\user;

use think\Model;


class Rebate extends Model
{

    

    

    // 表名
    protected $name = 'user_rebate';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'type_text',
        'churu_text',
        'bi_text',
        'ctime_text',
        'utime_text'
    ];
    

    
    public function getTypeList()
    {
        return ['ewm' => __('Ewm'), 'bank' => __('Bank')];
    }

    public function getChuruList()
    {
        return ['duiru' => __('Duiru'), 'duichu' => __('Duichu')];
    }

    public function getBiList()
    {
        return ['INR' => __('Inr'), 'THB' => __('Thb'), 'CNY' => __('Cny')];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getChuruTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['churu']) ? $data['churu'] : '');
        $list = $this->getChuruList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getBiTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['bi']) ? $data['bi'] : '');
        $list = $this->getBiList();
        return isset($list[$value]) ? $list[$value] : '';
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

    protected function setCtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setUtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function user()
    {
        return $this->belongsTo('app\admin\model\User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
