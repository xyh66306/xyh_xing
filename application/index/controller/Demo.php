<?php
/*
 * @Author: 提莫队长 =
 * @Date: 2025-10-10 09:14:35
 * @LastEditors: 提莫队长 =
 * @LastEditTime: 2025-12-31 16:10:09
 */

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\model\Task;
use app\common\model\User;
use app\common\model\user\Bankcard;
use app\common\model\user\Payewm;
use app\common\model\UserRebate;
use app\common\model\order\Rujin;
use app\common\model\UserBankcard;
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


    public function updateList(){

        $list = Db::name("commission")->where("order_status",1)->select();

        foreach ($list as $key => $row) {
            // $info = Db::name("order_rujin")->where("orderid",$row['p4b_orderid'])->find();
            // if($info['pay_status']==4){
            //     Db::name("commission")->where("id",$row['id'])->update(['order_status'=>2]);
            // }

            // $info2 = Db::name("order_chujin")->where("merchantOrderNo",$row['p4b_orderid'])->find();
            // if($info2['pay_status']==5){
            //     Db::name("commission")->where("id",$row['id'])->update(['order_status'=>2]);
            // }            

        }



    }


    public function ceshi(){


        
        //从8月20日开始，到今天
        $start = strtotime("2025-08-20");
        $end = time();
        for ($i = $start; $i <= $end; $i = $i + 86400) {
            $date = date("Y-m-d", $i);
            $this->get_rujin_profit($date);
            $this->get_chujin_profit($date);
        }
        // $remark ="";
        // $email = "875586838@qq.com";
        // $usdt = "1672.3104";
        // $userModel = new User();
        // $fuserInfo = $userModel->where("email",$email)->find();        
        // //转账扣除
        // $ret = $userModel->usdt($usdt,168035,1,2,$remark,'转出:'.$fuserInfo->email);
        // //转账对象增加
        // $uemail = "2067127331@qq.com";
        // $ret = $userModel->usdt($usdt,168034,1,1,$remark,'转入:'. $uemail);


    }

    public function get_rujin_profit($date){ 

        $rjlist = Rujin::field("id,user_id,orderid,amount,usdt,huilv,user_fee,supply_fee,user_usdt,supply_usdt,pay_status,order_status,pay_time,ctime,utime")->where('ctime', '>', strtotime($date))
            ->where('ctime', '<', strtotime($date) + 86400)
            ->where(['pay_status'=>4,'status'=>1])
            ->select();

        if(count($rjlist)>0){ 
            
            foreach ($rjlist as $key => $row) { 

                $profitModel = new companyProfit();
                //收益归公司
                $profitModel->addLog2($row['user_usdt'],$row['user_fee'],1,1,1,$row['orderid'],$row['utime']);  
                $profitModel->addLog2($row['user_usdt'],$row['supply_fee'],1,2,1,$row['orderid'],$row['utime']);  

                if($row['order_status']==1){
                    //订单未超时  
                    // $profit = $row['user_fee'] + $row['supply_fee'];                   
                    $profit =  Db::name("commission")->where(['p4b_orderid'=>$row['orderid']])->sum("money");
                    $profitModel->addLog2($row['user_usdt'],$profit,9,1,2,$row['orderid'],$row['utime']);   
                }

            }

        }


    }

    public function get_chujin_profit($date){ 

        $cjlist = Chujin::field("id,user_id,merchantOrderNo,withdrawAmount,usdt,user_fee,supply_fee,user_usdt,supply_usdt,pay_status,createtime,updatetime")->where('createtime', '>', strtotime($date))
            ->where('createtime', '<', strtotime($date) + 86400)
            ->where(['pay_status'=>5,'status'=>'normal'])
            ->select();



        if(count($cjlist)>0){ 
            
            foreach ($cjlist as $key => $row) { 

                $profitModel = new companyProfit();
                //收益归公司
                $profitModel->addLog2($row['user_usdt'],$row['user_fee'],2,1,1,$row['merchantOrderNo'],$row['updatetime']);  
                $profitModel->addLog2($row['user_usdt'],$row['supply_fee'],2,2,1,$row['merchantOrderNo'],$row['updatetime']);  

            }

        }


    }    



}
