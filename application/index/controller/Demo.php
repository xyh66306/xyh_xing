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


    public function index()
    {
        $order_id = "f503525704";
        // $row = Db::name("order_chujin")->where("orderid",$order_id)->find();

        // dump($row);

        //添加商户冻结金额
        $Usdtlog = new Usdtlog();
        $Usdtlog->quxiaotxLog(1241209564,1978.1946, 1, $order_id, 2); 
    }


    public function ceshi()
    {
        // $list = Db::name("order_chujin")->select();
        // foreach ($list as $item){ 
        //     $username = Db::name("user")->where("id",$item["user_id"])->value("username");
        //     Db::name("order_chujin")->where("id",$item["id"])->update(["payername"=>$username]);
        // }

        // $remark ="";
        // $email = "515256802@qq.com";
        // $usdt = "23244.3579";
        // $userModel = new User();
        // $fuserInfo = $userModel->where("email",$email)->find();        
        // // //转账扣除
        // $ret = $userModel->usdt($usdt,168041,1,2,$remark,'转出:'.$fuserInfo->email);
        // //转账对象增加
        // $uemail = "2067127331@qq.com";
        // $ret = $userModel->usdt($usdt,168034,1,1,$remark,'转入:'. $uemail);




        // $userModel = new User();
        // //添加用户冻结金额
        // $res2 =  $userModel->usdt_dj("1471.0972",168038,6,1);

        //添加用户金额
        // $userModel->usdt("986.6064", 168041, 8, 2,91592);

        // $order_id = "20260515122036646";
        // $order_info = Db::name("order_rujin")->where("orderid",$order_id)->find();

        // $pintai_id = "1341568728";
        // $supplyModel = new Supply();
        // $info = $supplyModel->where('access_key', $pintai_id)->find();

        // $taskModel = new Task();
        // $data = [
        //     'access_key'    => $info['access_key'],
        //     'access_secret' => $info['access_secret'],
        //     'name' => 'cash',
        //     'message' => '',
        //     'params' => [
        //         'orderid' => $order_id,
        //         'url'  => $order_info['callback'],
        //         'pay_status' => 3
        //     ]
        // ];
        // $taskModel->addTask($data, "Cash");    


        // $userModel = new User();
        // $userModel->usdt(497.8843,168033, 6, 2,92402);
        // $userModel->usdt(497.8843, 168033, 8, 2,92402);


        // $order_id = "202604020526500225";
        // $order_info = Db::name("order_rujin")->where("orderid",$order_id)->find();

        // $pintai_id = "1525364505";
        // $supplyModel = new Supply();
        // $info = $supplyModel->where('access_key', $pintai_id)->find();

        // $taskModel = new Task();
        // $data = [
        //     'access_key'    => $info['access_key'],
        //     'access_secret' => $info['access_secret'],
        //     'name' => 'cash',
        //     'message' => '',
        //     'params' => [
        //         'orderid' => $order_id,
        //         'url'  => "https://api-test.logtec.dev/fapi/payment/psp/public/inlandxjpay/withdraw/back",
        //         'pay_status' => 3
        //     ]
        // ];
        // $taskModel->addTask($data, "Sell");    
        //添加公司金额
        // $companyProfit1 = new companyProfit();
        // $res3 =  $companyProfit1->addLog(70921.9858,70.9219,9,1,1,"DP634087051180093440");  

        $order_id = "DP644325440944730112";
        $rujinModel = new Rujin();
        $info = $rujinModel->where('orderid', $order_id)->find();
        if (empty($info)) {
            $this->error("订单不存在");
        }
        $fenyong = truncateDecimal($info['user_fee'] + $info['supply_fee']);
        $this->commission($info['user_id'], $info['merchantOrderNo'], $order_id, $info['user_usdt'], $fenyong);
    }




    /***
     * 分佣
     */
    public function commission($user_id, $fy_orderid, $p4b_orderid, $number, $total)
    {
        $fanyong = config("site.fanyong");

        if ($fanyong == 0) {
            return;
        }

        // if ($this->supplyInfo['duiru_fanyong'] == 0) {
        //     return;
        // }

        $Commission = new Commission();
        $userModel  = new User();

        $uinfo = $userModel->where("id", $user_id)->find();

        $rateLst =  $this->getrate($uinfo);

        $result = [];
        $team_total = 0;
        foreach ($rateLst as $key => $value) {

            $money = truncateDecimal($number * $value['rate'] / 100);
            if ($money <= 0) {
                continue;
            }
            $team_total += $money;
            $rebateData = [
                'user_id' => $user_id,
                'p_userid' => $value['user_id'],
                'fy_orderid' => $fy_orderid,
                'p4b_orderid' => $p4b_orderid,
                'number' => $number,
                'rate'  => $value['rate'],
                'money' => $money,
                'type' => 1,
                'source' => 1,
                'level' => $key + 1,
                'status' => 2,
                'chaoshi' => 1,
                'order_status' => 1,
                'remarks' => $number . "*" . $value['rate'],
                'ctime' => time(),
                'utime' => time(),
            ];

            $result[] = $rebateData;
        }
        $diff = $total - $team_total;
        $rebateData = [
            'user_id' => $user_id,
            'p_userid' => 168022,
            'fy_orderid' => $fy_orderid,
            'p4b_orderid' => $p4b_orderid,
            'number' => $number,
            'rate'  => 0,
            'money' => $diff,
            'type' => 1,
            'source' => 1,
            'level' => 0,
            'status' => 2,
            'chaoshi' => 1,
            'order_status' => 1,
            'remarks' => $total . "-" . $team_total,
            'ctime' => time(),
            'utime' => time(),
        ];
        $result[] = $rebateData;

        if (count($result) == 0) {
            return true;
        }

        Db::startTrans();
        try {
            $Commission->saveAll($result);
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            $this->error('操作失败' . $e->getMessage());
        }
        return true;
    }


    public function getrate($uinfo){

        $sparent_str = str_replace("A", "", $uinfo['sparent']);
        $sparent_arr = explode(",", $sparent_str);

        $result = [];
        $max = 0;
        foreach ($sparent_arr as $key => $value) { 
            $res = [];
            $userRebate = new UserRebate();
            $rateInfo = $userRebate->where(['user_id' => $value,'churu'=>'duiru','type'=>'bank'])->find();

            if(!$rateInfo || $rateInfo['rate']<=0){
                continue;
            }
            $res['user_id'] = $value;
            $res['rate'] = $rateInfo['rate'] -$max;
            if($rateInfo['rate']>0){
                $max = $rateInfo['rate'];
            }
            $result[] = $res;
            
        }
        return $result;
    }    

}
