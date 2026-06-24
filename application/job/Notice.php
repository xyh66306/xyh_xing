<?php
/*
 * @Author: 提莫队长 =
 * @Date: 2025-11-10 17:01:45
 * @LastEditors: Please set LastEditors
 * @LastEditTime: 2026-06-23 17:36:55
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
                     $job->delete();
                    throw new \InvalidArgumentException('Missing required parameter for sendEmsNotice: supplyinfoName');
                }                
                $this->sendEmsNotice($params['supplyinfoName']);
                break;
            case "sendEmsCdsNotice":
                // 验证 sendEmsCdsNotice 所需参数
                if (!isset($params['email'], $params['orderid'])) {
                     $job->delete();
                    recordLogs('Missing required parameters for sendEmsCdsNotice: email, orderid');
                    throw new \InvalidArgumentException('Missing required parameters for sendEmsCdsNotice: email, orderid');
                }
                if (isset($params['user_id']) && isset($params['orderid'])) {
                    $this->sendEmsCdsNotice($params['email'], $params['orderid'], $params['amount']);
                    $this->sendNotice($params['user_id'], $params['orderid'], $params['amount']);
                } else {
                     $job->delete();
                    recordLogs('Missing required parameters for sendNotice: user_id, orderid');
                    throw new \InvalidArgumentException('Missing required parameters for sendNotice: user_id, orderid');
                }
                break;
            case "sendIdcardNotice":
                // 验证 sendIdcardNotice 所需参数
                if (!isset($params['email'])) {
                    $job->delete();
                    recordLogs('Missing required parameter for sendIdcardNotice: email');
                    return; 
                }
                
                $username = isset($params['username']) ? $params['username'] : '';
                $this->sendIdcardNotice($params['email'], $username);
                break;  
            case "sendAccountNotice":
                if (!isset($params['email'])) {
                    $job->delete();
                    recordLogs('Missing required parameter for sendIdcardNotice: email');
                    return; 
                }
                
                $username = isset($params['username']) ? $params['username'] : '';
                $this->sendAccountNotice($params['email'], $username);
                break;                                 
            // case "sendEmsNotice":
            //     // 验证 sendEmsNotice 所需参数
            //     if (!isset($params['email'], $params['orderid'])) {
            //          $job->delete();
            //         recordLogs('Missing required parameters for sendEmsCdsNotice: email, orderid');
            //         throw new \InvalidArgumentException('Missing required parameters for sendEmsCdsNotice: email, orderid');
            //     }
            //     if (isset($params['user_id']) && isset($params['orderid'])) {
            //         $this->sendEmsCdsNotice($params['email'], $params['orderid']);
            //     } else {
            //          $job->delete();
            //         recordLogs('Missing required parameters for sendNotice: user_id, orderid');
            //         throw new \InvalidArgumentException('Missing required parameters for sendNotice: user_id, orderid');
            //     }
            //     break;                
            default:
                // 可选：记录未知类型的操作
                break;
        }

        $job->delete();
    } catch (\Exception $e) {
        // 记录异常信息用于调试
        Log::error('Job execution failed: ' . $e->getMessage(), [
            'job_attempts' => $job->attempts(),
            'exception' => $e
        ]);
        
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
    public function sendEmsCdsNotice($email,$orderid,$amount=''){

        $msg = "您好，订单号".$orderid.",请查看是否收到款".$amount."，麻烦尽快确认。温馨提醒一定务必核实姓名，金额，订单号是否吻合，避免不必要的损失";
        $result = Emslib::notice($email, $msg, self::NOTICE_TEMPLATE);
        
        if (!$result) {
            Log::warning('Failed to send EMS CDS notice', ['email' => $email, 'orderid' => $orderid]);
        }
        
        return (bool)$result;
    }   
    
    public function sendNotice($userid,$orderid,$amount=''){

        $email = "870416982@qq.com";
        $msg = $userid."您好，订单号".$orderid.",请查看是否收到款".$amount."，麻烦尽快确认。温馨提醒一定务必核实姓名，金额，订单号是否吻合，避免不必要的损失";
        $emailResult = Emslib::notice($email, $msg, self::NOTICE_TEMPLATE);
        
        if (!$emailResult) {
            Log::warning('Failed to send notice email', ['email' => $email, 'userid' => $userid, 'orderid' => $orderid]);
        }
        
        return (bool)$emailResult;
    }    

    /**
     * 身份证实名认证通过通知
     * @param string $email 用户邮箱
     * @param string $username 用户名 (可选，用于个性化消息)
     * @return bool
     */
    public function sendIdcardNotice($email, $username = ''){
        if (empty($email)) {
            return false;
        }

        // 构建消息内容
        $userNameStr = $username ? $username : '用户';
        $msg = "尊敬的{$userNameStr}，您的身份证实名认证已审核通过。现在您可以享受平台更多服务。";
        
        // 发送邮件
        Emslib::notice($email, $msg, self::NOTICE_TEMPLATE);
        
        return true;
    }

    /**
     * 账户审核通过通知
     * @param string $email 用户邮箱
     * @param string $username 用户名 (可选，用于个性化消息)
     * @return bool
     */
    public function sendAccountNotice($email, $username = ''){
        if (empty($email)) {
            return false;
        }

        // 构建消息内容
        $userNameStr = $username ? $username : '用户';
        $msg = "尊敬的{$userNameStr}，您的账户认证已审核通过,请登录平台完善账户信息。";
        
        // 发送邮件
        Emslib::notice($email, $msg, self::NOTICE_TEMPLATE);
        
        return true;
    }    
}
