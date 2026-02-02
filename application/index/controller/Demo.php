<?php
/*
 * @Author: Xyhao
 * @Date: 2025-10-10 09:14:35
 * @Description: 安徽爱喜网络科技有限公司
 */

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\model\Task;
use app\common\model\User;
use app\common\model\user\Bankcard;
use app\common\model\user\Payewm;
use app\common\model\UserRebate;
use app\common\model\order\Rujin;
use app\common\model\Bank;
use app\common\model\order\Chujin;
use app\common\model\Bi as BiModel;
use app\common\model\company\Profit as companyProfit;
use app\common\model\Commission;
use app\admin\model\supply\Usdtlog;
use app\admin\model\user\usdt\Log as UsdtLogModel;
use app\admin\model\user\Usdt as UsdtModel;
use app\common\model\Supply;
use app\common\library\Sms as Smslib;
use app\common\library\Ems as Emslib;
use think\Queue;
use think\Db;
use think\Request;

class Demo extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';

    
    protected $access_key = "";
    protected $secret = "";
    protected $supplyInfo = [];


    public function ceshi(){
        // $list = Db::name("order_chujin")->select();
        // foreach ($list as $item){ 
        //     $username = Db::name("user")->where("id",$item["user_id"])->value("username");
        //     Db::name("order_chujin")->where("id",$item["id"])->update(["payername"=>$username]);
        // }
        
        //         $remark ="";
        // $email = "875586838@qq.com";
        // $usdt = "1672.3104";
        // $userModel = new User();
        // $fuserInfo = $userModel->where("email",$email)->find();        
        // //转账扣除
        // $ret = $userModel->usdt($usdt,168035,1,2,$remark,'转出:'.$fuserInfo->email);
        // //转账对象增加
        // $uemail = "2067127331@qq.com";
        // $ret = $userModel->usdt($usdt,168034,1,1,$remark,'转入:'. $uemail);




            // $userModel = new User();
            // //添加用户冻结金额
            // $res2 =  $userModel->usdt_dj("1471.0972",168038,6,1);

            // //添加用户金额
            // $res3 =  $userModel->usdt("1471.0972",168038,7,1);
            
            $order_id = "87011";
            $order_info = Db::name("order_rujin")->where("orderid",$order_id)->find();

            $pintai_id = "1250803358";
            $supplyModel = new Supply();
            $info = $supplyModel->where('access_key', $pintai_id)->find();

            $taskModel = new Task();
            $data = [
                'access_key'    => $info['access_key'],
                'access_secret' => $info['access_secret'],
                'name' => 'cash',
                'message' => '',
                'params' => [
                    'orderid' => $order_id,
                    'url'  => $order_info['callback'],
                    'pay_status' => 3
                ]
            ];
            $taskModel->addTask($data, "Cash");             
        
    }

}
