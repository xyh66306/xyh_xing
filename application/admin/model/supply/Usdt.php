<?php
/*
 * @Description: 
 * @Version: 1.0
 * @Autor: Xyh
 * @Date: 2025-09-07 10:55:24
 */

namespace app\admin\model\supply;

use think\Model;


class Usdt extends Model
{

    

    

    // 表名
    protected $name = 'supply_usdt';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'pay_status_text',
        'pay_time_text',
        'diqu_text',
        'status_text'
    ];
    

    
    public function getPayStatusList()
    {
        return ['1' => __('Pay_status 1'), '2' => __('Pay_status 2'), '3' => __('Pay_status 3'), '4' => __('Pay_status 4')];
    }

    public function getDiquList()
    {
        return ['1' => __('Diqu 1'), '2' => __('Diqu 2'), '3' => __('Diqu 3')];
    }

    public function getStatusList()
    {
        return ['1' => __('Status 1'), '2' => __('Status 2')];
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


    public function getDiquTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['diqu']) ? $data['diqu'] : '');
        $list = $this->getDiquList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setPayTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function supply()
    {
        return $this->belongsTo('app\admin\model\Supply', 'supply_id', 'access_key', [], 'LEFT')->setEagerlyType(0);
    }
}
