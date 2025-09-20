<?php
/*
 * @Description: 
 * @Version: 1.0
 * @Autor: Xyh
 * @Date: 2025-08-01 17:18:35
 */

namespace app\common\model;

use think\Model;
use think\Queue;
use think\queue\Job;
/**
 * 任务表
 */
class Task extends Model
{

    // 表名
    protected $name = 'task';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    CONST WAIT_STATUS = 0;//等待执行
    CONST EXPORT_PROCESS_STATUS = 1;//正在导出
    CONST EXPORT_SUCCESS_STATUS = 2;//导出成功
    CONST EXPORT_FAIL_STATUS = 3;//导出失败

    /**
     * 加入任务 app\job\Sell@fire
     * @param $data
     * @param string $model
     * @return bool
     */
    public function addTask($data, $job = 'Sell')
    {


        $signArr = [];
        // $signArr['access_key'] = $data['access_key'];
        $signArr['orderid']    = $data['params']['orderid'];
        $signArr['pay_status'] = $data['params']['pay_status'];
        $access_secret         = $data['access_secret'];
        $sign = $this->makeSign($signArr,$access_secret);


        $savedata['name'] = $data['name'];
        $savedata['orderid'] = $data['params']['orderid'];
        $savedata['message'] = $data['message'];
        $data['params']['sign'] = $sign;
        $savedata['params'] = json_encode($data['params']);

        if ($this->save($savedata)) {
            $exportData['task_id'] = $this->id;
            $exportData['params'] = $data['params'];
            $jobClass = 'app\job\\' . $job."@fire";
            $queueRes = \think\Queue::push($jobClass, $exportData);//加入队列
            return $queueRes;
        } else {
            return false;
        }

    }


    public function makeSign($params = [], $secret = '')
    {

        if(empty($params) || !is_array($params)) {
             $this->error('签名错误');
        }

        foreach($params as $key => $v) {
            if(empty($v)) {
                unset($params[$key]);
            }
        }
        $ascii_str = $this->ascii($params);
        if($ascii_str == false) {
            $this->error('签名错误');
        }

        $stringSignTemp = $ascii_str."&key=".$secret;
        return strtoupper(MD5($stringSignTemp));
        
    }



    /**
     * 入参参数名ASCII码从小到大排序（字典序）
     *
     * @param array $params
     * @return void
     */
    public function ascii($params = []){
        if(!empty($params) && is_array($params)){
            $p =  ksort($params);
            if($p){
                foreach ($params as $k => $v){
                    if(is_array($v)){
                        $params[$k] = json_encode($v);
                    }
                }
                $strs = urldecode(http_build_query($params));
                $strs = str_replace('\\','',$strs);
                return $strs;
            }
        }
        return false;
    }




}
