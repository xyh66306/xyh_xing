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

    public function __construct(Request $request)
    {
        parent::__construct(); // 确保调用父类构造函数

        $this->request = $request;

        // 使用 input() 方法获取请求参数
        if(empty($this->request->param('access_key'))) {
            $this->error('access_key错误');
        }
        $supplyModel = new Supply();
        $info = $supplyModel->where('access_key',$this->request->param('access_key'))->find();
        // if(empty($info)){
        //     $this->error('商户不存在');
        // }
        $this->supplyInfo = $info;
        $this->access_key = $info['access_key'];
        $this->secret = $info['access_secret'];
    }



    public function ceshi(){

        // $mobile = "18919660526";
        // $event = "resetpwd";
        // $code = rand(1111,9999);
        // $ret = Smslib::notice($mobile, $code, $event);

        // $orderid = "57595";
        // $rujinModel = new Rujin();
        // $info = $rujinModel->where(['orderid'=>$orderid])->find();
        // $this->commission($info['user_id'],$info['merchantOrderNo'],$orderid,$info['user_usdt']);

        $orderid = "20251030101926";


        $rujinModel = new Rujin();
        $info = $rujinModel->where(['orderid'=>$orderid])->find();        
        $commissionModel = new Commission();
        $comlist = $commissionModel->where("p4b_orderid",$orderid)->select();
        $comSum  = $commissionModel->where("p4b_orderid",$orderid)->sum('money');
        if($comSum>0){
            
            foreach ($comlist as $vo) {
                $userModel = new User();
                $userModel->usdt($vo['money'],$vo['p_userid'],5,1,$orderid);
            }

            $companyProfit3 = new companyProfit();
            $res5 = $companyProfit3->addLog($info['user_usdt'],$comSum,10,2,2,$orderid); 
            $commissionModel->update(['status'=>1,'chaoshi'=>1],['p4b_orderid'=>$orderid]);
        }          


    }


    public function cjceshi(){

        $orderid = "f261926881";
        $chujinModel = new Chujin();
        $info = $chujinModel->where('orderid',$orderid)->find();
        $this->cj_commission($info['user_id'],$orderid,$info['access_key'],$info['merchantOrderNo'],$info['user_usdt']);
    }



    /***
     * 分佣
     */
    public function commission($user_id,$fy_orderid,$p4b_orderid,$number)
    {
		$fanyong = config("site.fanyong");
		
		if($fanyong==0){
			return;
		}



        $Commission = new Commission();
        $userModel  = new User();

        $uinfo = $userModel->where("id", $user_id)->find();
        $invite = $uinfo['invite'];

        $userRebate = new UserRebate();

        // $rateInfo = $userRebate->where(['user_id' => $user_id,'pid'=>$invite,'churu'=>'duiru','type'=>'bank'])->find();

        // dump($rateInfo);
        // die;
        // if(!$rateInfo){
        //     return true;
        // }

        $rateLst =  $this->getrate($uinfo);

        $result = [];
        foreach ($rateLst as $key => $value) { 

            $money = truncateDecimal($number * $value['rate'] / 100);
            if($money<=0){
                continue;
            }

            $rebateData = [
                'user_id' =>$user_id,
                'p_userid' => $value['user_id'],
                'fy_orderid' => $fy_orderid,
                'p4b_orderid' => $p4b_orderid,
                'number' => $number,
                'rate'  => $value['rate'],
                'money' => $money,
                'type' => 1,
                'source' => 1,
                'level' => $key+1,
                'status' => 2,
                'chaoshi' => 1,
                'ctime' => time(),
                'utime' => time(),
            ];

            $result[] = $rebateData;
        }

        if(count($result)==0){
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






    public function cj_commission($user_id,$fy_orderid,$access_key,$p4b_orderid,$number)
    {
		$fanyong = config("site.fanyong");
		
		if($fanyong==0){
			return;
		}
        $Commission = new Commission();
        $userModel  = new User();

        $uinfo = $userModel->where("id", $user_id)->find();
        // $invite = $uinfo['invite'];

        // $userRebate = new UserRebate();

        // $rateInfo = $userRebate->where(['user_id' => $user_id,'pid'=>$invite,'churu'=>'duichu','type'=>'bank'])->find();

        // dump($rateInfo);
        // if(!$rateInfo){
        //     return true;
        // }

        $supplyModel = new Supply();
        $supply_info = $supplyModel->where('access_key',$access_key)->find();


        if($supply_info['duichu_fanyong']==0){
            return;
        }

        $rateLst =  $this->cj_getrate($uinfo,$supply_info['duichu_fanyong']);


        $result = [];
        foreach ($rateLst as $key => $value) { 

            $money = truncateDecimal($number * $value['rate'] / 100);
            if($money<=0){
                continue;
            }

            $rebateData = [
                'user_id' =>$user_id,
                'p_userid' => $value['user_id'],
                'fy_orderid' => $fy_orderid,
                'p4b_orderid' => $p4b_orderid,
                'number' => $number,
                'rate'  => $value['rate'],
                'money' => $money,
                'type' => 1,
                'source' => 2,
                'level' => $key+1,
                'status' => 2,
                'chaoshi' => 1,
                'ctime' => time(),
                'utime' => time(),
            ];

            $result[] = $rebateData;
        }

        if(count($result)==0){
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



    /**
     * 包含自身
     */
    public function cj_getrate($uinfo,$duichu_fanyong){

        $sparent_str = str_replace("A", "", $uinfo['sparent']);
        $sparent_arr = explode(",", $sparent_str);

        $result = [];
        $max = 0;

        foreach ($sparent_arr as $key => $value) { 
            $res = [];
            $userRebate = new UserRebate();
            $rateInfo = $userRebate->where(['user_id' => $value,'churu'=>'duichu','type'=>'bank'])->find();

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
        $res['rate'] = $duichu_fanyong -$max;
        $result[] = $res;        
        return $result;
    }      

}
