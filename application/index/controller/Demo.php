<?php

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

        $orderid = "casher20251106174125";
        $rujinModel = new Rujin();
        $info = $rujinModel->where(['orderid'=>$orderid])->find();
        if(!$info){
            return $this->error('订单不存在');
        }
        $jobClass = "app\job\Notice\@fire";
        $res =  \think\Queue::push($jobClass,$info);//加入队列

        dump($res);

    }


    public function index()
    { 


        $user_id = "168033";
    }


    /**
     * 包含自身
     */
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
        $res['user_id'] = 168022;
        $res['rate'] = $this->supplyInfo['duiru_fanyong'] -$max;
        $result[] = $res;
        return $result;
    }  

}
