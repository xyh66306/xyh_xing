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
use app\common\library\Sms as Smslib;
use app\common\library\Ems as Emslib;
use think\Queue;
use think\Db;

class Demo extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';

    

    public function ceshi(){

        // $mobile = "18919660526";
        // $event = "resetpwd";
        // $code = rand(1111,9999);
        // $ret = Smslib::notice($mobile, $code, $event);

        $email = "870416982@qq.com";
        $msg = "当前有一笔新的兑出订单，您可以登录抢单查看。<a href='https://bingocn.wobeis.com/otc/#/pages/buy/buy'>点击查看</a>";
        Emslib::notice($email, $msg);


    }

    /*
    public function index()
    {
        // $model = new UsdtModel();

        // $user_czLst = $model->order("id desc")->select();

        // foreach($user_czLst as $k=>$row){
        //     $usdtLog = new UsdtLogModel();
        //     $usdtLog->addLog($row['user_id'],2,1,$row['act_num']);
        //     $model = new UsdtModel();
        //     $model->update(['status'=>'normal'],['id'=>$row['id']]);
        // }


        
        $list= Db::name("supply_usdt_log2")->order("createtime asc")->limit(7,50)->select();

        foreach($list as $k=>$vo){ 
        
            if($vo['type']==1){
                $Rujin = new Rujin();
                $list = $Rujin->where("orderid",$vo['memo'])->where("pay_status",4)->order("id desc")->select();
                foreach($list as $k=>$v){

                    // //承兑商确认模拟
                    // $userModel = new User();
                    // //添加usdt_dj 冻结金额
                    // $userModel->usdt_dj($v['user_usdt'],$v['user_id'], 8, 1);
                    // //扣除usdt 金额
                    // $userModel->usdt($v['user_usdt'], $v['user_id'], 8, 2);

                    // $Rujin = new Rujin();
                    // $Rujin->update(['pay_status' => 3], ['id' => $v['id']]);

                    // //模拟后台确认
                    // //增加商户USDT
                    $SpullyUsdtLog = new Usdtlog();
                $res =  $SpullyUsdtLog->addLog($v['pintai_id'], $v['supply_usdt'], 1, 1, $v['orderid']);

                dump($res);
                    // //减少用户usdt_dj 冻结金额
                    // $userModel = new User();
                    // $userModel->usdt_dj($v['user_usdt'],$v['user_id'], 6, 2);

                    // //添加公司金额
                    // $companyProfit1 = new companyProfit();
                    // $companyProfit1->addLog($v['usdt'],$v['supply_fee'],1,1,1,$v['orderid']);   

                    // $companyProfit2 = new companyProfit();
                    // $companyProfit2->addLog($v['usdt'],$v['user_fee'],1,3,1,$v['orderid']);             

                    // $Rujin = new Rujin();
                    // $Rujin->update(['pay_status' => 4], ['id' => $v['id']]);

                    //更新时间
                    // $info = Db::name("company_profit")->where(['type'=>1,'user_type'=>1,'order_usdt'=>$v['usdt']])->find();
                    // if($info){
                    //     Db::name("company_profit")->where(['type'=>1,'user_type'=>1,'order_usdt'=>$v['usdt']])->update(['createtime'=>$v['ctime']+3600*4]);
                    //     Db::name("company_profit")->where(['type'=>1,'user_type'=>3,'order_usdt'=>$v['usdt']])->update(['createtime'=>$v['ctime']+3600*4]);
                    // } else {
                    //     echo Db::name("company_profit")->getlastsql();
                    //     dump($info);
                    // }

                    // $info2 = Db::name("user_usdt_log")->where(['type'=>8,'usdt'=>$v['user_usdt']])->find();
                    // if($info2){
                    //     Db::name("user_usdt_log")->where(['type'=>8,'usdt'=>$v['user_usdt']])->update(['createtime'=>$v['ctime']+3600*2]);
                    //     Db::name("supply_usdt_log")->where(['type'=>1,'usdt'=>$v['supply_usdt']])->update(['createtime'=>$v['ctime']+3600*2]);
                    // }else {
                    //     echo Db::name("user_usdt_log")->getlastsql();
                    //     dump($info2);
                    // }
                    Db::name("supply_usdt_log")->where(['type'=>1,'usdt'=>$v['supply_usdt'],'memo'=>$v['orderid']])->update(['createtime'=>$v['ctime']+3600*2]);


                }
            }



         if($vo['type']==2){
        $Chujin = new Chujin();
        $list = $Chujin->where("orderid",$vo['memo'])->where("pay_status",5)->order("id desc")->select();
        foreach($list as $k=>$v){


            //扣除商户冻结金额
            $Usdtlog = new Usdtlog();
            $Usdtlog->addtxLog($v['access_key'],$v['supply_usdt'],2,$v['orderid'],2);
            // $data = [
            //     'pay_status' =>3
            // ];
            // $Chujin = new Chujin();
            // $res = $Chujin->update($data,['orderid'=>$v['orderid']]);

            // $userModel = new User();
            // //添加用户冻结金额
            // $res2 =  $userModel->usdt_dj($v['user_usdt'],$v['user_id'],6,1);
            // //添加用户金额
            // $res3 =  $userModel->usdt($v['user_usdt'],$v['user_id'],7,1);

            // if($v['id']>=6){
            //      $this->commission($v['user_id'],$v['orderid'],$v['merchantOrderNo'],$v['user_usdt']);
            // } 
           
            //  //模拟后台确认
            $Usdtlog = new Usdtlog();
            // //扣除商户冻结金额
            $res = $Usdtlog->authtxLog($v['access_key'],$v['supply_usdt'],$v['orderid']);                 
            // $userModel = new User();
            // $res2 = $userModel->usdt_dj($v['user_usdt'],$v['user_id'],7,2);
            // $companyProfit1 = new companyProfit();
            // $res3 =  $companyProfit1->addLog($v['usdt'],$v['supply_fee'],2,3,1,$v['orderid']);                   
            // $companyProfit2 = new companyProfit();
            // $res4 = $companyProfit2->addLog($v['usdt'],$v['user_fee'],2,1,1,$v['orderid']); 

            // //添加代理商佣金
            // $commissionModel = new Commission();

            // $comlist = $commissionModel->where("fy_orderid",$v['orderid'])->select();
            // $comSum  = $commissionModel->where("fy_orderid",$v['orderid'])->sum('money');
            // if($comSum>0){
            //     foreach ($comlist as $vo) {
            //         $userModel = new User();
            //         $userModel->usdt($vo['money'],$vo['p_userid'],5,1,$v['orderid']);
            //     }

            //     $companyProfit3 = new companyProfit();
            //     $res5 = $companyProfit3->addLog($v['usdt'],$comSum,10,2,2,$v['orderid']); 
            //     $commissionModel->update(['status'=>1,'chaoshi'=>1],['fy_orderid'=>$v['orderid']]);
            // }    
               
            // $data = [
            //     'pay_status' =>5
            // ];
            // $Chujin = new Chujin();
            // $res = $Chujin->update($data,['orderid'=>$v['orderid']]);
            


            // Db::name("user_usdt_log")->where(['user_id'=>$v['user_id'],'usdt'=>$v['user_usdt'],'type'=>7])->update(['createtime'=>$v['createtime']+3600*4]);
            Db::name("supply_usdt_log")->where(['type'=>2,'usdt'=>$v['supply_usdt'],'memo'=>$v['orderid']])->update(['createtime'=>$v['createtime']]);
            // $info = Db::name("company_profit")->where(['type'=>2,'user_type'=>1,'usdt'=>$v['user_fee']])->find();
            // var_dump($info);


            // Db::name("company_profit")->where(['type'=>2,'user_type'=>1,'usdt'=>$v['user_fee']])->update(['createtime'=>$v['createtime']+3600*4]);
            // Db::name("company_profit")->where(['type'=>2,'user_type'=>3,'usdt'=>$v['supply_fee']])->update(['createtime'=>$v['createtime']+3600*4]);

        }
    }
                }



        //充值修改时间
        // $list = Db::name("user_usdt")->select();
        // foreach($list as $k=>$v){
        //     Db::name("user_usdt_log")->where("type",2)->where('usdt',$v['num'])->update(['createtime'=>$v['createtime']+3600*3]);
        // }


        // $userModel = new User();

        // $usdt = 3309.1667;
        // $remark = "";
        // // //转账扣除
        // $ret = $userModel->usdt($usdt,168031,1,2,$remark,'转出:1171746715@qq.com');
        // // //转账对象增加
        // $ret = $userModel->usdt($usdt,168025,1,1,$remark,'转入:604647740@qq.com');

        
        // $usdtlogModel = new Usdtlog();
        // $res =  $usdtlogModel->addtxLog("1250803358",5000, '2', '提现申请',3);

    }*/

    public function addLog($supply_id, $usdt, $type, $flow_type = 1, $memo = '')
    {

        $supply = new Supply();
        $info = $supply->where('access_key', $supply_id)->find();
        if(!$info){
            return false;
        }
        $bhtype = '';
        if ($flow_type == 2) {
            $after = $info['usdt'] - $usdt;
            $bhtype = 'uzhuanchu';
        } else {
            $bhtype = 'uchongzhi';
            $after = $info['usdt'] + $usdt;
        }

        Db::startTrans();
        try {
            $data = [
                'supply_id'  => $supply_id,
                'bianhao'   => getOrderNo($bhtype),
                'type'       => $type,
                'flow_type'  => $flow_type,
                'usdt'      => $usdt,
                'before'     => $info['usdt'],
                'after'      => $after,
                'memo'       => $memo,
                'createtime' => time(),
            ];

            $this->save($data);

            $supply->update(['usdt' => $after], ['access_key' => $supply_id]);

            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            echo $e->getMessage();
            Db::rollback();
            $this->error($e->getMessage());
        }

        return true;
    }

    /***
     * 分佣
     */
    public function commission($user_id,$fy_orderid,$p4b_orderid,$number)
    {
		// $fanyong = config("site.fanyong");
		
		// if($fanyong==0){
		// 	return;
		// }
        $Commission = new Commission();
        $userModel  = new User();

        $uinfo = $userModel->where("id", $user_id)->find();
        $invite = $uinfo['invite'];

        $userRebate = new UserRebate();

        $rateInfo = $userRebate->where(['user_id' => $user_id,'pid'=>$invite,'churu'=>'duichu','type'=>'bank'])->find();
        if(!$rateInfo){
            return true;
        }

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


    public function getrate($uinfo){

        $sparent_str = str_replace("A", "", $uinfo['sparent']);
        $sparent_arr = explode(",", $sparent_str);
        $sparent_arr = array_diff($sparent_arr, [$uinfo['id']]); //删除自身

        $result = [];
        $max = 0;
        $user_id = $uinfo['id'];

        foreach ($sparent_arr as $key => $value) { 
            $res = [];
            $userRebate = new UserRebate();
            $rateInfo = $userRebate->where(['user_id' => $user_id,'pid'=>$value,'churu'=>'duichu','type'=>'bank'])->find();

            $user_id = $value;
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



    public function makeSign($params = [], $secret = '')
    {

        if (empty($params) || !is_array($params)) {
            $this->error('签名错误');
        }

        foreach ($params as $key => $v) {
            if (empty($v)) {
                unset($params[$key]);
            }
        }
        $ascii_str = $this->ascii($params);
        if ($ascii_str == false) {
            $this->error('签名错误');
        }

        $stringSignTemp = $ascii_str . "&key=" . $secret;
        return strtoupper(MD5($stringSignTemp));
    }



    /**
     * 入参参数名ASCII码从小到大排序（字典序）
     *
     * @param array $params
     * @return void
     */
    public function ascii($params = [])
    {
        if (!empty($params) && is_array($params)) {
            $p =  ksort($params);
            if ($p) {
                foreach ($params as $k => $v) {
                    if (is_array($v)) {
                        $params[$k] = json_encode($v);
                    }
                }
                $strs = urldecode(http_build_query($params));
                $strs = str_replace('\\', '', $strs);
                return $strs;
            }
        }
        return false;
    }



    /**
     * postCurl 京东的 helper 类拷贝过来的，可以正常使用
     *
     * @param $url
     * @param array $params
     * @param bool $decode
     * @return mixed
     * @throws \Exception
     */
    public static function postCurl($url, $params = [], $method = 'POST')
    {
        if (!in_array($method, ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD', 'OPTIONS'])) {
            return false;
        }

        $opts = [
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_URL            => $url,
            CURLOPT_FAILONERROR    => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_CONNECTTIMEOUT => 120,
            CURLOPT_HEADER         => false
        ];

        $headers = [];
        array_push($headers, "Content-Type" . ":" . "application/json; charset=UTF-8");
        if ($method == 'POST' && !is_null($params)) {
            $opts[CURLOPT_POSTFIELDS] = json_encode($params);
        }

        if (strlen($url) > 5 && strtolower(substr($url, 0, 5)) == 'https') {
            $opts[CURLOPT_SSL_VERIFYPEER] = false;
            $opts[CURLOPT_SSL_VERIFYHOST] = false;
        }

        if (!empty($headers) && is_array($headers)) {
            $opts[CURLOPT_HTTPHEADER] = $headers;
        }
        $curl = curl_init();
        curl_setopt_array($curl, $opts);
        $data = curl_exec($curl);
        $err  = curl_errno($curl);
        $error = curl_error($curl);
        curl_close($curl);
        if ($err > 0) {
            throw new \Exception($error);
            return false;
        } else {
            return $data;
        }
    }
}
