<?php
/*
 * @Author: 提莫队长 =
 * @Date: 2025-11-10 17:01:45
 * @LastEditors: Please set LastEditors
 * @LastEditTime: 2026-02-02 10:46:39
 * @FilePath: \xyh_xing\application\job\Notice.php
 */

namespace app\job;

use think\queue\Job;
use app\common\model\Task;
use app\common\model\order\Rujin;
use app\common\model\User as UserModel;
use app\common\library\Sms as Smslib;
use app\common\library\Ems as Emslib;
use think\Log;

class Notice
{

const NOTICE_TEMPLATE = 'resetpwd';

public function fire(Job $job, $params)
{
    
    try {
        // 验证必要参数存在
        if (!isset($params['type'])) {
            throw new \InvalidArgumentException('Missing required parameter: type');
        }
        recordLogs("Notice",$params);

        switch ($params['type']) {
            case "sendEmsNotice":
                if (!isset($params['supplyinfoName'])) {
                    recordLogs("Notice",'Missing required parameter for sendEmsNotice: supplyinfoName');
                    continue;
                }                
                $this->sendEmsNotice($params['supplyinfoName']);
                break;
            case "sendEmsCdsNotice":
                // 验证 sendEmsCdsNotice 所需参数
                if (!isset($params['email'], $params['orderid'])) {
                     $job->delete();
                    recordLogs("Notice",'Missing required parameters for sendEmsCdsNotice: email, orderid');
                    continue;
                }
                if (isset($params['user_id']) && isset($params['orderid'])) {
                    $this->sendEmsCdsNotice($params['email'], $params['orderid']);
                    $this->sendNotice($params['user_id'], $params['orderid']);
                } else {
                     $job->delete();
                    recordLogs("Notice",'Missing required parameters for sendNotice: user_id, orderid');
                    continue;
                }
                break;
            default:
                // 可选：记录未知类型的操作
                break;
        }

        $job->delete();
    } catch (\Exception $e) {
        // 记录异常信息用于调试
        // Log::error('Job execution failed: ' . $e->getMessage(), [
        //     'job_attempts' => $job->attempts(),
        //     'exception' => $e
        // ]);
        recordLogs("Notice", $e->getMessage());
        
        // 如果已达到最大重试次数，则删除任务
        if ($job->attempts() >= 3) { // 假设最大重试次数为3，可根据实际配置调整
            $job->delete();
        } else {
            // 否则重新抛出异常让队列系统处理重试
            throw $e;
        }
    }
}

    public function failed($data)
    {
        // ...任务达到最大重试次数后，失败了
    }


    public function sendEmsNotice($name){

        $email = "870416982@qq.com";
        $msg = "当前商户".$name."有一笔新的兑入订单，请准备。<a href='https://bingocn.wobeis.com/otc/#/pages/buy/buy'>点击查看</a>";
        $result = Emslib::notice($email, $msg, self::NOTICE_TEMPLATE);
        
        if (!$result) {
            Log::warning('Failed to send EMS notice', ['email' => $email]);
        }
        
        return (bool)$result;
    }
    
    
    //兑入承兑商
    public function sendEmsCdsNotice($email,$orderid){

        $msg = "您好，订单号".$orderid.",请查看是否收到款，麻烦尽快确认。温馨提醒一定务必核实姓名，金额，订单号是否吻合，避免不必要的损失";
        $result = Emslib::notice($email, $msg, self::NOTICE_TEMPLATE);
        
        if (!$result) {
            Log::warning('Failed to send EMS CDS notice', ['email' => $email, 'orderid' => $orderid]);
        }
        
        return (bool)$result;
    }   
    
    public function sendNotice($userid,$orderid){

        $mobile = "18919660526";
        $event = "resetpwd";
        $code = random_int(3333,9999);
        $ret = Smslib::notice($mobile, $code, $event);

        $email = "870416982@qq.com";
        // $msg = "用户ID".$userInfo['id']."当前有一笔新的兑出订单".$info['orderid']."，金额：".$info['amount']."您可以登录抢单查看。<a href='https://bingocn.wobeis.com/otc/#/pages/buy/buy'>点击查看</a>";
        $msg = $userid."您好，订单号".$orderid.",请查看是否收到款，麻烦尽快确认。温馨提醒一定务必核实姓名，金额，订单号是否吻合，避免不必要的损失";
        $emailResult = Emslib::notice($email, $msg, self::NOTICE_TEMPLATE);
        
        if (!$emailResult) {
            Log::warning('Failed to send notice email', ['email' => $email, 'userid' => $userid, 'orderid' => $orderid]);
        }
        
        return (bool)$emailResult;
    }    

}
