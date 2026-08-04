<?php
/*
 * @Author: 提莫队长 =
 * @Date: 2025-11-10 17:01:45
 * @LastEditors: Please set LastEditors
 * @LastEditTime: 2026-07-09 17:07:28
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
    
    // 先删除任务，防止异常导致任务残留循环重试
    $job->delete();

    try {
        // 验证必要参数存在
        if (!isset($params['type'])) {
            throw new \InvalidArgumentException('Missing required parameter: type');
        }
        recordLogs("Notice",$params);

        switch ($params['type']) {
            case "sendEmsNotice":
                if (!isset($params['supplyinfoName'])) {
                    recordLogs('Missing required parameter for sendEmsNotice: supplyinfoName');
                    return;
                }                
                $this->sendEmsNotice($params['supplyinfoName']);
                break;
            case "sendEmsCdsNotice":
                // 验证 sendEmsCdsNotice 所需参数
                if (!isset($params['email'])) {
                    recordLogs('Missing required parameter for sendEmsCdsNotice: email');
                    return; 
                }                
                $this->sendEmsCdsNotice($params['email'], $params['orderid'], $params['amount']);
                $this->sendNotice($params['user_id'], $params['orderid'], $params['amount']);                
                break;
            case "sendIdcardNotice":
                // 验证 sendIdcardNotice 所需参数
                if (!isset($params['email'])) {
                    recordLogs('Missing required parameter for sendIdcardNotice: email');
                    return; 
                }
                
                $username = isset($params['username']) ? $params['username'] : '';
                $this->sendIdcardNotice($params['email'], $username);
                break;  
            case "sendAccountNotice":
                if (!isset($params['email'])) {
                    recordLogs('Missing required parameter for sendAccountNotice: email');
                    return; 
                }
                
                $username = isset($params['username']) ? $params['username'] : '';
                $this->sendAccountNotice($params['email'], $username);
                break;     
            case "sendSellChujinNotice":
                if (!isset($params['email']) || !isset($params['amount']) ) {
                    recordLogs('Missing required parameter for sendSellChujinNotice: email or amount');
                    return; 
                }
                
                $this->sendSellChujinNotice($params['email'], $params['amount'],$params['count']);
                break;                                                           
            default:
                // 可选：记录未知类型的操作
                break;
        }
    } catch (\Exception $e) {
        // 记录异常信息用于调试，任务已删除不会再重试
        Log::error('Job execution failed: ' . $e->getMessage(), [
            'exception' => $e
        ]);
    }
}

    public function failed($data)
    {
        // ...任务达到最大重试次数后，失败了
    }


    public function sendEmsNotice($name){
        try {
            $email = "870416982@qq.com";
            $msg = "当前商户".$name."有一笔新的兑入订单，请准备。<a href='https://bingocn.wobeis.com/otc/#/pages/buy/buy'>点击查看</a>";
            $result = Emslib::notice($email, $msg, self::NOTICE_TEMPLATE);
            
            if (!$result) {
                Log::warning('Failed to send EMS notice', ['email' => $email]);
            }
        } catch (\Exception $e) {
            Log::error('sendEmsNotice exception: ' . $e->getMessage());
        }
        
        return true;
    }
    
    
    //兑入承兑商
    public function sendEmsCdsNotice($email,$orderid,$amount=''){
        try {
            $msg = "您好，订单号".$orderid.",请查看是否收到款，麻烦尽快确认";
            $result = Emslib::notice($email, $msg, self::NOTICE_TEMPLATE);
            
            if (!$result) {
                Log::warning('Failed to send EMS CDS notice', ['email' => $email, 'orderid' => $orderid]);
            }
        } catch (\Exception $e) {
            Log::error('sendEmsCdsNotice exception: ' . $e->getMessage(), ['email' => $email, 'orderid' => $orderid]);
        }
        
        return true;
    }   
    
    public function sendNotice($userid,$orderid,$amount=''){
        try {
            $email = "870416982@qq.com";
            $msg = $userid."您好，订单号".$orderid.",请查看是否收到款，麻烦尽快确认";
            $emailResult = Emslib::notice($email, $msg, self::NOTICE_TEMPLATE);
            
            if (!$emailResult) {
                Log::warning('Failed to send notice email', ['email' => $email, 'userid' => $userid, 'orderid' => $orderid]);
            }
        } catch (\Exception $e) {
            Log::error('sendNotice exception: ' . $e->getMessage(), ['userid' => $userid, 'orderid' => $orderid]);
        }
        
        return true;
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

        try {
            $userNameStr = $username ? $username : '用户';
            $msg = "尊敬的{$userNameStr}，您的身份证实名认证已审核通过。现在您可以享受平台更多服务。";
            Emslib::notice($email, $msg, self::NOTICE_TEMPLATE);
        } catch (\Exception $e) {
            Log::error('sendIdcardNotice exception: ' . $e->getMessage(), ['email' => $email]);
        }
        
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

        try {
            $userNameStr = $username ? $username : '用户';
            $msg = "尊敬的{$userNameStr}，您的账户认证已审核通过,请登录平台完善账户信息。";
            Emslib::notice($email, $msg, self::NOTICE_TEMPLATE);
        } catch (\Exception $e) {
            Log::error('sendAccountNotice exception: ' . $e->getMessage(), ['email' => $email]);
        }
        
        return true;
    }   
    
    /**
     * 出金邮件通知
     */
    public function sendSellChujinNotice($email,$amounts_str,$count)
    {
        try {
            $msg = "您好，商户已分配" . $count . "笔订单兑出订单,金额：" . $amounts_str . "，麻烦请处理，谢谢。温馨提醒交易员需麻烦确认好金额才打款，不要多付/重复打款，避免不必要的损失";
            Emslib::notice($email, $msg, "resetpwd");
        } catch (\Exception $e) {
            Log::error('sendSellChujinNotice exception: ' . $e->getMessage(), ['email' => $email]);
        }
        return true;
    }    

}
