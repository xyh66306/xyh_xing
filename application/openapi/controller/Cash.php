<?php

namespace app\openapi\controller;

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

    use Send;
    protected $noNeedRight = ['index'];
    protected $noNeedLogin = ['index'];

    protected $access_key = "";
    public function __construct(Request $request)
    {
        parent::__construct(); // 确保调用父类构造函数

        $this->request = $request;
        $header = $this->request->header();

        // 使用 input() 方法获取请求参数
        if(empty($header['accesskey'])) {
            $this->error('accesskey错误');
        }
        if(empty($header['gmtrequest'])) {
            $this->error('请求时间错误');
        }

        if(empty($header['randomstr'])  && $header['randomstr'] != '32' ) {
            $this->error('获取随机字符串错误');
        }     
        $time = time();
        if($header['gmtrequest']+600<=$time){
            $this->error('时间过期');
        }

        $supplyModel = new Supply();
        $info = $supplyModel->where('access_key',$header['accesskey'])->find();

        $this->access_key = $header['accesskey'];

        if(empty($info)){
            $this->error('商户不存在');
        }

        $params = [
            'accesskey'    => $header['accesskey'],
            'gmtrequest'    => $header['gmtrequest'],
            'randomstr'     => $header['randomstr'],
            'signature'     => $header['signature'],
        ];
        #先鉴权
        $this->Authentication($params, $info['access_secret']);
    }


    /**
     * 收银台接口
     */
    public function index()
    { 


        $params = [
            'orderid'    => $this->request->param('orderid',''),
            'amount'    => $this->request->param('amount',''),
            'payername'=> $this->request->param('payername',''),
            'diqu'    => $this->request->param('diqu',1),
            'backurl' => $this->request->param('backurl',''),
            'yx_time' => $this->request->param('yx_time',60*20), // 900秒
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
        if($params['amount']<3500) {
            $this->error('不能小于最低金额3500');
        }     
        
        if($params['amount']>=200000) {
            $this->error('不能大于最低金额20万');
        }           

        $userModel = new UserModel();


        $where = [];
        $order = 'pay_sort desc,id desc';
        if($this->access_key == '1250730111'){
            // 测试账户
            // $rj_user_id = config('site.rj_user_id');
            
            $rj_user_id = 168017;
            $userInfo = $userModel->where($where)->where('id',$rj_user_id)->order($order)->find();


        } else{

            $ispay = config('site.ispay');

            if($ispay && $ispay != '1'){
                $this->error('系统维护中');
            }

            $where['diqu'] = $params['diqu'];
            $where['status'] = "normal";
            $where['sfz_status'] = 1;
            $where['pay_status'] = "normal";
            $order = 'pay_sort desc,id desc';

            $rj_user_id = config('site.rj_user_id');
            if($rj_user_id && $rj_user_id>0){
                $userInfo = $userModel->where($where)->where('id',$rj_user_id)->order($order)->find();
            } else {
                $userInfo = $userModel->where($where)->where('usdt',">",0)->where('id','<>','168017')->order($order)->find();
            }


        }

        $supplyModel = new Supply();
        $supplyinfo = $supplyModel->where('access_key',$this->access_key)->find();

        if(!$userInfo) {           
          return  $this->error('收银员不存在');
        }
        $UserBankcard = new UserBankcard();
        $count = $UserBankcard->where(['user_id'=>$userInfo['id'],'status'=>'normal','sys_status'=>'normal'])->where("max_cny",'>=',$params['amount'])->count();


        if($count==0){
           return $this->error('收款账户不存在');
        }
        $bankInfo = $UserBankcard->where(['user_id'=>$userInfo['id'],'status'=>'normal','sys_status'=>'normal'])->where("max_cny",'>=',$params['amount'])->order('sort desc,id desc')->find();
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
				'supply_huilv'=>$supplyinfo['duiru'],
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



            $res = $rujinModel->insert($data);
            
            $UserBankcard->where('id', '<>',$bankInfo['id'])->where('user_id',$bankInfo['user_id'])->setInc('sort',1);
            $UserBankcard->update(['sort'=>1],['id'=>$bankInfo['id']]);


            $userModel->where('id','<>', $userInfo['id'])->setInc('pay_sort',1);
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