<?php

namespace app\admin\model\user\usdt;

use think\Model;
use app\admin\model\user\User as UserModel;


class Log extends Model
{

    

    

    // 表名
    protected $name = 'user_usdt_log';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'type_text',
        'flow_type_text'
    ];
    

    
    public function getTypeList()
    {
        return ['1' => __('Type 1'), '2' => __('Type 2'), '3' => __('Type 3'), '4' => __('Type 4'), '5' => __('Type 5'), '6' => __('Type 6')];
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


    public function getFlowTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['flow_type']) ? $data['flow_type'] : '');
        $list = $this->getFlowTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }




    public function user()
    {
        return $this->belongsTo('app\admin\model\User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }



    public function addLog($user_id,$type,$flow_type,$usdt,$memo=''){

        $userModel = new UserModel();
        $user = $userModel->where('id',$user_id)->find();
        $before = $user['usdt'];
        $order_type = '';
        if($flow_type==2){
            $after = $before-$usdt;
            $order_type = 'uzhuanchu';
        } else {
            $order_type = 'uchongzhi';
            $after = $before+$usdt;
        }

        $data = [
            'bianhao'   => getOrderNo($order_type),
            'user_id'   => $user_id,
            'type'      => $type,
            'flow_type' => $flow_type,
            'usdt'      => $usdt,
            'before'    => $before,
            'after'     => $after,
            'memo'      => $memo,
            'createtime' => time(),
        ];
        $this->save($data);
        $userModel->update(['usdt'=>$after],['id'=>$user_id]);
        return true;
    }

}
