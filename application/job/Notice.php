<?php
/*
 * @Description: 
 * @Version: 1.0
 * @Autor: Xyh
 * @Date: 2025-08-01 17:22:40
 */
namespace app\job;

use think\queue\Job;
use app\common\model\Task;
use think\Log;
use app\common\model\User as UserModel;
use app\common\library\Sms as Smslib;
use app\common\library\Ems as Emslib;

class Notice
{

    public function fire(Job $job, $params)
    {
        
        try {
            
            recordLogs("jobNotice",$params);

            $res1 = $this->sendNotice($params);
            // $res2 = $this->sendEmsNotice($params['user_id']);
            $res2 = $this->sendEmsNotice(168005);
            

            if ($res1 && $res2) {
                $job->delete(); // 成功后删除任务
            } else {
                if ($job->attempts() >= 3) {
                    $job->delete();
                }
            }
        } catch (\Exception $e) {
            // 异常处理
            recordLogs("jobNotice",$e->getMessage());
            $job->delete();
        }
    }

    public function failed($data)
    {
        // ...任务达到最大重试次数后，失败了
    }


    public function sendNotice($info){

        $mobile = "18919660526";
        $event = "resetpwd";
        $code = rand(3333,9999);
        $ret = Smslib::notice($mobile, $code, $event);

        $email = "870416982@qq.com";
        $msg = "用户ID".$info['user_id']."姓名：".$info['username']."当前有一笔新的兑出订单".$info['orderid']."，金额：".$info['amount']."您可以登录抢单查看。<a href='https://bingocn.wobeis.com/otc/#/pages/buy/buy'>点击查看</a>";
        Emslib::notice($email, $msg, "resetpwd");
        return true;
    }


    public function sendEmsNotice($user_id){

        $userModel = new UserModel();
        $userInfo = $userModel->where(['id'=>$user_id])->find();
        $msg = "当前有一笔新的兑出订单，您可以登录抢单查看。<a href='https://bingocn.wobeis.com/otc/#/pages/buy/buy'>点击查看</a>";
        Emslib::notice($userInfo['email'], $msg, "resetpwd");
        return true;
    }
  

}
