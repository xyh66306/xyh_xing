<?php
/*
 * @Author: 提莫队长 =
 * @Date: 2025-10-10 09:14:35
 * @LastEditors: 提莫队长 =
 * @LastEditTime: 2026-01-15 14:50:24
 * @FilePath: \xyh_xing\application\index\controller\Demo.php
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
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

            $pintai_id = "1241209564";
            $supplyModel = new Supply();
            $info = $supplyModel->where('access_key', $pintai_id)->find();

            $taskModel = new Task();
            $data = [
                'access_key'    => $info['access_key'],
                'access_secret' => $info['access_secret'],
                'name' => 'cash',
                'message' => '',
                'params' => [
                    'orderid' => "DP590662236689948672",
                    'url'  => "https://spontaneous-trina-cadential.ngrok-free.dev/checkout-callback/starfire",
                    'pay_status' => 3
                ]
            ];
            $taskModel->addTask($data, "Cash");            
        
    }

}
