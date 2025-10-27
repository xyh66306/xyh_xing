<?php

namespace app\admin\model\supply;

use think\Model;
use think\Db;

use Exception;
use think\db\exception\BindParamException;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\response\Json;


class Usdtlog extends Model
{



    // 表名
    protected $name = 'supply_usdt_log';

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




    public function addLog($supply_id, $usdt, $type, $flow_type = 1, $memo = '')
    {

        $supply = new Supply();
        $info = $supply->where('access_key', $supply_id)->find();
        if(!$info){
            return false;
        }
        $bhtype = '';
        if ($flow_type == 2) {
            $after = $info['usdt'] - $usdt;
            $bhtype = 'uzhuanchu';
        } else {
            $bhtype = 'uchongzhi';
            $after = $info['usdt'] + $usdt;
        }

        Db::startTrans();
        try {
            $data = [
                'supply_id'  => $supply_id,
                'bianhao'   => getOrderNo($bhtype),
                'type'       => $type,
                'flow_type'  => $flow_type,
                'usdt'      => $usdt,
                'before'     => $info['usdt'],
                'after'      => $after,
                'memo'       => $memo,
                'createtime' => time(),
            ];

            $this->save($data);

            $supply->update(['usdt' => $after], ['access_key' => $supply_id]);

            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            echo $e->getMessage();
            Db::rollback();
            $this->error($e->getMessage());
        }

        return true;
    }


    /**
     * 
     * 添加提现记录
     */
    public function addtxLog($supply_id, $usdt, $flow_type = 2, $memo = '',$type=3)
    {


        $supply = new Supply();
        $info = $supply->where('access_key', $supply_id)->find();
        $after = $info['usdt'] - $usdt;
        // if ($after < 0) {
        //     return false;
        // }
        Db::startTrans();
        try {
            $data = [
                'supply_id'  => $supply_id,
                'bianhao'   => getOrderNo('uzhuanchu'),
                'usdt'      => $usdt,
                'before'     => $info['usdt'],
                'after'      => $after,
                'memo'       => $memo,
                'flow_type'  => $flow_type,
                'type'       => $type,
                'createtime' => time(),
            ];
            $this->save($data);

            //减少金额，添加冻结金额
            $freeze_usdt = $info['freeze_usdt'] + $usdt;
            $supply->update(['usdt' => $after, 'freeze_usdt' => $freeze_usdt], ['access_key' => $supply_id]);

            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

        return true;
    }

    /**
     * 取消兑出
     */
    public function quxiaotxLog($supply_id, $usdt, $flow_type = 1, $memo = '',$type=3)
    {


        $supply = new Supply();
        $info = $supply->where('access_key', $supply_id)->find();
        $after = $info['usdt'] + $usdt;
        if ($after < 0) {
            return false;
        }
        Db::startTrans();
        try {
            $data = [
                'supply_id'  => $supply_id,
                'bianhao'   => getOrderNo('uzhuanchu'),
                'usdt'      => $usdt,
                'before'     => $info['usdt'],
                'after'      => $after,
                'memo'       => $memo,
                'flow_type'  => $flow_type,
                'type'       => $type,
                'createtime' => time(),
            ];
            $this->save($data);

            //减少金额，添加冻结金额
            $freeze_usdt = $info['freeze_usdt'] - $usdt;
            $supply->update(['usdt' => $after, 'freeze_usdt' => $freeze_usdt], ['access_key' => $supply_id]);

            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

        return true;
    }


    /**
     * 审核提现记录
     */
    public function authtxLog($supply_id, $usdt, $memo = '')
    {
        Db::startTrans();
        try {

            $supply = new Supply();
            $info = $supply->where('access_key', $supply_id)->find();
            $after = $info['freeze_usdt'] - $usdt;
            if ($after < 0) {
                return false;
            }
            // $data = [
            //     'supply_id'  => $supply_id,
            //     'bianhao'   => getOrderNo(),
            //     'usdt'      => $usdt,
            //     'before'     => $info['freeze_usdt'],
            //     'after'      => $after,
            //     'memo'       => $memo,
            //     'type'       => '2',
            //     'createtime' => time(),
            // ];
            // $this->save($data);
            $supply->update(['freeze_usdt' => $after], ['access_key' => $supply_id]);

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }

        return true;
    }






    public function supply()
    {
        return $this->belongsTo('app\admin\model\Supply', 'supply_id', 'access_key', [], 'LEFT')->setEagerlyType(0);
    }
}
