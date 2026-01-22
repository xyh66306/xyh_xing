<?php
/*
 * @Author: 提莫队长 =
 * @Date: 2025-11-10 17:01:45
 * @LastEditors: Please set LastEditors
 * @LastEditTime: 2026-01-19 17:06:25
 * @FilePath: \xyh_xing\application\job\Notice.php
//  * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
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

    public function fire(Job $job, $params)
    {
        
        try {

            if($params['type'] == "sendEmsNotice"){
                $this->sendEmsNotice();
            }
            if($params['type'] == "sendEmsCdsNotice"){
                $this->sendEmsCdsNotice($params['email'],$params['orderid']);
                $this->sendNotice($params['user_id'],$params['orderid']);
            }

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



    public function sendEmsNotice(){

        $email = "870416982@qq.com";
        $msg = "当前商户有一笔新的兑入订单，请准备。<a href='https://bingocn.wobeis.com/otc/#/pages/buy/buy'>点击查看</a>";
        Emslib::notice($email, $msg, "resetpwd");
    }
    
    
    //兑入承兑商
    public function sendEmsCdsNotice($email,$orderid){

        $msg = "您好，订单号".$orderid.",请查看是否收到款，麻烦尽快确认。温馨提醒一定务必核实姓名，金额，订单号是否吻合，避免不必要的损失";
        Emslib::notice($email, $msg, "resetpwd");
    }   
    
    public function sendNotice($userid,$orderid){

        $mobile = "18919660526";
        $event = "resetpwd";
        $code = rand(3333,9999);
        $ret = Smslib::notice($mobile, $code, $event);

        $email = "870416982@qq.com";
        // $msg = "用户ID".$userInfo['id']."当前有一笔新的兑出订单".$info['orderid']."，金额：".$info['amount']."您可以登录抢单查看。<a href='https://bingocn.wobeis.com/otc/#/pages/buy/buy'>点击查看</a>";
        $msg = $userid."您好，订单号".$orderid.",请查看是否收到款，麻烦尽快确认。温馨提醒一定务必核实姓名，金额，订单号是否吻合，避免不必要的损失";
        Emslib::notice($email, $msg, "resetpwd");

    }    

}
