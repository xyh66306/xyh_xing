<?php
/*
 * @Description: 
 * @Version: 1.0
 * @Autor: Xyh
 * @Date: 2025-09-14 14:15:59
 */

namespace app\common\model\order;

use think\Model;


class Chujin extends Model
{

    

    

    // 表名
    protected $name = 'order_chujin';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'pay_type_text',
        'status_text',
        'pay_status_text'
    ];
    

    
    public function getPayTypeList()
    {
        return ['qtpay' => __('Pay_type qtpay'), 'wxpay' => __('Pay_type wxpay'), 'alipay' => __('Pay_type alipay'), 'bank' => __('Pay_type bank')];
    }

    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }

    public function getPayStatusList()
    {
       return ['0' => __('Paystatus 0'), '1' => __('Paystatus 1'),'2' => __('Paystatus 2'),'3' => __('Paystatus 3'),'3' => __('Paystatus 3'),'3' => __('Paystatus 3'),'4' => __('Paystatus 4'),'5' => __('Paystatus 5'),'6' => __('Paystatus 6')];
    }





    public function getPayTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['pay_type']) ? $data['pay_type'] : '');
        $list = $this->getPayTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getPayStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['pay_status']) ? $data['pay_status'] : '');
        $list = $this->getPayStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function user()
    {
        return $this->belongsTo('app\common\model\User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


}
