<?php

namespace app\index\controller;

use app\common\controller\Api;
use app\common\model\Supply;
use app\common\model\OrderCash as OrderCashModel;
use app\common\model\User as UserModel;
use app\common\model\UserBankcard;
use app\common\model\Bi as BiModel;
use app\common\model\UserPayewm;
use app\common\model\UserRebate;
use app\common\model\order\Rujin;
use app\common\model\Task;
use app\common\model\Commission;
use think\Db;
use think\Request;


/**
 * 兑入接口
 */
class Cash extends Api
{

    protected $noNeedRight = ['index'];
    protected $noNeedLogin = ['index'];

    protected $access_key = "";


    /**
     * 收银台接口
     */
    public function index()
    { 


        $params = [
            'orderid'    => date("YmsHis").rand(1000,9999),
            'amount'    => 43560,
            'payername'=> "测试",
            'diqu'    => 1,
            'backurl' => 'https://bingocn.wobeis.com/index/index/ceshi',
            'yx_time' => 900, // 900秒
        ];
        if(empty($params['orderid'])) {
            $this->error('订单号错误');
        }
        if(empty($params['amount'])) {
            $this->error('金额错误');
        }
        if(empty($params['backurl'])) {
            $this->error('回调地址错误');
        }        
        if(empty($params['payername'])) {
            $this->error('付款人姓名不能为空');
        }         

        $userModel = new UserModel();


        $where = [];
        $order = 'pay_sort desc,id desc';
        $ispay = config('site.ispay');

        // if($ispay && $ispay != '1'){
        //     $this->error('系统维护中');
        // }
        $access_key = "1250803358";

        $where['diqu'] = $params['diqu'];
        $where['status'] = "normal";
        $where['sfz_status'] = 1;
        $where['pay_status'] = "normal";
        $order = 'pay_sort desc,id desc';

        $rj_user_id = config('site.rj_user_id');
        if($rj_user_id && $rj_user_id>0){
            $userInfo = $userModel->where($where)->where('id',$rj_user_id)->order($order)->find();
        } else {
            $userInfo = $userModel->where($where)->where('id','<>','168017')->where("min_cny",'>=',0)->where("max_cny",'>=',$params['amount'])->order($order)->find();
        }
        if(!$userInfo) {           
          return  $this->error('收银员不存在');
        }

        $supplyModel = new Supply();
        $supplyinfo = $supplyModel->where('access_key',$access_key)->find();


        $UserBankcard = new UserBankcard();
        $count = $UserBankcard->where(['user_id'=>$userInfo['id'],'status'=>'normal','sys_status'=>'normal'])->where("min_cny",'>=',0)->where("max_cny",'>=',$params['amount'])->count();


        if($count==0){
           return $this->error('收款账户不存在');
        }
        $bankInfo = $UserBankcard->where(['user_id'=>$userInfo['id'],'status'=>'normal','sys_status'=>'normal'])->where("min_cny",'>=',0)->where("max_cny",'>=',$params['amount'])->order('sort desc,id desc')->find();
        $rujinModel = new Rujin();
        $rjInfo = $rujinModel->where(['orderid'=>$params['orderid']])->find();
        if($rjInfo){ 
            return $this->error('订单编号已存在');
        }

        Db::startTrans();
        try{ 

            $BiModel = new BiModel();
            $info = $BiModel->where(['default'=>1,'status'=>1])->find();

            $usdt = truncateDecimal($params['amount'] / $supplyinfo['duiru']);     //CNY 转 USDT(接收的cny/商户兑入7.26)
            if($params['diqu']==1){
                $fee_dalu_supply_duiru =  config('site.fee_dalu_supply_duiru');

                $supply_fee = truncateDecimal($usdt * $fee_dalu_supply_duiru/100);
                $supply_usdt = $usdt - $supply_fee;

                $user_usdt = truncateDecimal($params['amount'] / $info['duiru']);
                $user_fee = truncateDecimal($user_usdt - $usdt);

            } elseif($params['diqu']==2){

                $fee_jc_supply_duiru =  config('site.fee_jc_supply_duiru');
                $supply_fee = truncateDecimal($usdt * $fee_jc_supply_duiru/100);
                $supply_usdt = $usdt - $supply_fee;

                $user_usdt = truncateDecimal($params['amount'] / $info['duiru']);
                $user_fee = truncateDecimal($user_usdt - $usdt);

            }

            $time = time();
            $merchantOrderNo = 'o'.date("YmdHis",time()).rand(100000,999999);

             $data = [
                'merchantOrderNo'=> $merchantOrderNo,
                'user_id' => $userInfo['id'],
                'orderid' => $params['orderid'],
                'amount' => $params['amount'],
                'diqu'  => $params['diqu'],
                'username'=> $bankInfo['username'],
                'bank_name'=>$bankInfo['bank_name'],
                'bank_account'=>$bankInfo['bank_nums'],
                'bank_zhihang'=>$bankInfo['bank_zhmc'],
                'payername'=>$params['payername'],
                'bi_type'  => 'USDT',
                'usdt'=>$usdt,
                'huilv'=>$info['duiru'],
                'supply_fee'=>$supply_fee,
                'supply_usdt'=>$supply_usdt,
                'user_usdt'=>$user_usdt,
                'user_fee'=>$user_fee,
                'status' => 2,
                'pay_status'=>1,
                'pintai_id'=>$this->access_key,
                'callback' =>$params['backurl'],
                'yx_time' => $params['yx_time'] + $time,
                'ctime' => $time,
                'utime' => $time,
            ];

            dump($data);
            return;

            $res = $rujinModel->insert($data);
            
            $UserBankcard->where('id', '<>',$bankInfo['id'])->where('user_id',$bankInfo['user_id'])->setDec('sort',1);
            $UserBankcard->update(['sort'=>1],['id'=>$bankInfo['id']]);


            $userModel->where('id','<>', $userInfo['id'])->setDec('pay_sort',1);
            $userModel->update(['pay_sort'=>1],['id'=>$userInfo['id']]);
            Db::commit();

        } catch(\Exception $e) {
            Db::rollback();
            return $this->error($e->getMessage());
        }


        if($res){
            return $this->success('success',request()->domain().'/cash/#/?orderid='.$params['orderid'].'&access_key='.$this->access_key);
        }else{
            return $this->error('fail');
        }

    }



}