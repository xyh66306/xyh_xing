<?php
/*
 * @Author: Xyhao
 * @Date: 2026-07-20 10:22:38
 * @Description: 安徽爱喜网络科技有限公司
 */

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\model\Task;
use think\Queue;
use think\Db;
use app\common\model\Supply;
use app\common\model\order\Chujin as chujinModel;
use app\common\library\Ems as Emslib;
use think\Cache;

class Chujin extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';    

    public function index(){

        $currentHour = date('H');
        $currentMinute = date('i');
        $currentTime = $currentHour * 60 + $currentMinute; // 转换为分钟数便于比较
        $startTime = 22 * 60 + 30; // 22:30 转换为分钟
        $endTime = 7 * 60 + 30;   // 07:30 转换为分钟        
        
        if (($currentHour >= 23 && $currentTime >= $startTime) || 
            ($currentHour < 7 || ($currentHour == 7 && $currentMinute <= 30))) {
            $this->error('系统维护时间，晚上11:30到早上7:30暂停服务');
        }        
        

        $chujinModel = new chujinModel();
        $lists = $chujinModel->where("pay_status",1)->column("withdrawAmount");

        if(count($lists)==0){
            return;
        }
        for($i=0;$i<count($lists);$i++){
            $lists[$i] = floatval($lists[$i]);
        }

        $str = implode("，",$lists);

        $email = "870416982@qq.com";
        // $msg = "用户ID".$userInfo['id']."当前有一笔新的兑出订单".$info['orderid']."，金额：".$info['amount']."您可以登录抢单查看。<a href='https://bingocn.wobeis.com/otc/#/pages/buy/buy'>点击查看</a>";
        $msg = $str."<br/>目前有这些金额的兑出订单，有需要请群里告知，谢谢";
        Emslib::notice($email, $msg, "resetpwd");        
        echo "已发送出金通知";
    }
}