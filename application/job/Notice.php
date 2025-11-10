<?php

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

    public function fire(Job $job, $params)
    {
        
        try {

            recordLogs("Notice_data",json_encode($params));

            // 检查参数格式是否正确
            if (!isset($params['params'])) {
                $job->delete();
                return;
            }

            $data['orderid'] = $params['params']['orderid'];
            $rujinModel = new Rujin();
            $info = $rujinModel->where(['orderid'=>$data['orderid']])->find();

            $this->sendNotice($info);
            $this->sendEmsNotice($info['user_id']);            

            $job->delete();
        } catch (\Exception $e) {
            // 异常处理
            if ($job->attempts() >= 1) {
                $job->delete();
            }
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
