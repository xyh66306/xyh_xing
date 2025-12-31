<?php

namespace app\common\model\company;

use think\Model;


class Profit extends Model
{





    // 表名
    protected $name = 'company_profit';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'type_text',
        'user_type_text',
        'flow_type_text'
    ];


    public function getTypeList()
    {
        return ['1' => __('Type 1'), '2' => __('Type 2'), '3' => __('Type 3'), '4' => __('Type 4'), '5' => __('Type 5'), '6' => __('Type 6'), '7' => __('Type 7'), '8' => __('Type 8'), '9' => __('Type 9'), '10' => __('Type 10')];
    }

    public function getUserTypeList()
    {
        return ['1' => __('User_type 1'), '2' => __('User_type 2'), '3' => __('User_type 3')];
    }

    public function getFlowTypeList()
    {
        return ['1' => __('Flow_type 1'), '2' => __('Flow_type 2')];
    }



    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getUserTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['user_type']) ? $data['user_type'] : '');
        $list = $this->getUserTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getFlowTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['flow_type']) ? $data['flow_type'] : '');
        $list = $this->getFlowTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }



    public function addLog($total,$usdt,$type, $user_type,$flow_type,$source_id='')
    {

        if($usdt==0){
            return;
        }
        $companyModel = new Company();
        $company = $companyModel->where('id', 1)->find();


        $before = $company['usdt'];
        if ($flow_type == 2) {
            $after = $before - $usdt;
        } else {
            $after = $before + $usdt;
        }

        $data = [
            'type'      => $type,
            'user_type' => $user_type,
            'flow_type' => $flow_type,
            'order_usdt'=>$total,
            'usdt'      => $usdt,
            'before'    => $before,
            'after'     => $after,
            'source_id' => $source_id,
            'createtime' => time(),
        ];
        $this->save($data);
        $companyModel->update(['usdt' => $after], ['id' => 1]);
        return true;
    }



    public function addLog2($total,$usdt,$type, $user_type,$flow_type,$source_id='',$time='')
    {

        if($usdt==0){
            return;
        }
        $companyModel = new Company();
        $company = $companyModel->where('id', 1)->find();


        $before = $company['usdt'];
        if ($flow_type == 2) {
            $after = $before - $usdt;
        } else {
            $after = $before + $usdt;
        }

        $data = [
            'type'      => $type,
            'user_type' => $user_type,
            'flow_type' => $flow_type,
            'order_usdt'=>$total,
            'usdt'      => $usdt,
            'before'    => $before,
            'after'     => $after,
            'source_id' => $source_id,
            'createtime' => $time,
        ];
        $this->save($data);
        $companyModel->update(['usdt' => $after], ['id' => 1]);
        return true;
    }    
}
