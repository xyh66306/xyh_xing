<?php

namespace app\admin\model\supply;

use think\Model;
use think\Db;


class Money extends Model
{

    

    

    // 表名
    protected $name = 'supply_money_log';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];
    

    



    /**
     * 添加
     */
    public function addLog($supply_id,$money,$before,$after,$memo='')
    { 

        $data = [
            'supply_id'  => $supply_id,
            'money'      => $money,
            'before'     => $before,
            'after'      => $after,
            'memo'       => $memo,
            'createtime' => time(),
        ];
        $this->save($data);

        $supply = new Supply();
        $supply->update(['money'=>$after],['id'=>$supply_id]);
        return true;

    }

    /**
     * 添加提现记录
     */
    public function addtxLog($supply_id,$money,$memo='')
    { 

        $supply = new Supply();
        $info = $supply->where('id',$supply_id)->find();
        $after = $info['money']-$money;
        if($after<0){
            return false;
        }
        $data = [
            'supply_id'  => $supply_id,
            'money'      => $money,
            'before'     => $info['money'],
            'after'      => $after,
            'memo'       => $memo,
            'createtime' => time(),
        ];
        $this->save($data);

        //减少金额，添加冻结金额
        $freeze_money = $info['freeze_money']+$money;
        $supply->update(['money'=>$after,'freeze_money'=>$freeze_money],['id'=>$supply_id]);
        return true;

    }

    /**
     * 审核提现记录
     */
    public function authtxLog($supply_id,$money,$memo='')
    { 

        $supply = new Supply();
        $info = $supply->where('id',$supply_id)->find();
        $after = $info['freeze_money']-$money;
        if($after<0){
            return false;
        }
        $data = [
            'supply_id'  => $supply_id,
            'money'      => $money,
            'before'     => $info['money'],
            'after'      => $after,
            'memo'       => $memo,
            'createtime' => time(),
        ];
        $this->save($data);
        $supply->update(['freeze_money'=>$after],['id'=>$supply_id]);
        return true;

    }





    public function supply()
    {
        return $this->belongsTo('app\admin\model\Supply', 'supply_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
