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
use app\common\library\Sms as Smslib;
use app\common\library\Ems as Emslib;
use think\Db;
use think\Cache;
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

        if(preg_match('/^[a-zA-Z0-9\s]+$/', $params['payername'])) {
            $this->error('付款人姓名只支持中文');
        }

        if($params['amount']<3500) {
            $this->error('不能小于最低金额3500');
        }     
        
        if($params['amount']>=200000) {
            $this->error('不能大于最低金额20万');
        }           

        $userModel = new UserModel();
        $rujinModel = new Rujin();


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

            $pinyinname = \fast\Pinyin::get($params['payername']);
            $name = $this->access_key."-".$params['amount']."-".$pinyinname;
            
            $cacheData = Cache::get($name);

            $userInfo = [];
            if($cacheData){
                $userInfo = $cacheData;
            }else{

                $where['diqu'] = $params['diqu'];
                $where['status'] = "normal";
                $where['sfz_status'] = 1;
                $where['pay_status'] = "normal";
                $order = 'pay_sort desc,id desc';

                // $ulist = $userModel->where($where)->where('usdt',">",100)->where('id','<>','168017')->order($order)->column('id');
                
                if(!$userInfo){
                    $ulist = $userModel->where($where)->where('usdt',">",100)->cache(3600)->order($order)->column('id');

                    $count = count($ulist);
                    if($count<=1){
                        return;
                    }

                    $limit = $count-1;
                    $rjLst = $rujinModel->where("pay_status",">=","1")->order("id desc")->limit($limit)->column('user_id');
                    $diff = array_diff($ulist,$rjLst);

                    if (!empty($diff)) {
                        $randomIndex = array_rand($diff);
                        $rj_user_id = $diff[$randomIndex];
                        $userInfo = $userModel->where($where)->where('id',$rj_user_id)->order($order)->find();
                    } else {
                        $rj_user_id = config('site.rj_user_id');
                        if($rj_user_id && $rj_user_id>0){
                            $userInfo = $userModel->where($where)->where('id',$rj_user_id)->order($order)->find();
                        } else {
                            // $userInfo = $userModel->where($where)->where('usdt',">",100)->where('id','<>','168017')->order($order)->find();
                            $userInfo = $userModel->where($where)->where('usdt',">",100)->order($order)->find();
                        }
                    }  
                }
                Cache::set($name,$userInfo,60*2);
            }




        }


        $supplyModel = new Supply();
        $supplyinfo = $supplyModel->where('access_key',$this->access_key)->find();

        if(!$userInfo) {           
          return  $this->error('收银员不存在');
        }
        $UserBankcard = new UserBankcard();
        $count = $UserBankcard->where(['user_id'=>$userInfo['id'],'status'=>'normal','sys_status'=>'normal'])->count();

        if($count==0){
           return $this->error('收款账户不存在');
        }
        $bankInfo = $UserBankcard->where(['user_id'=>$userInfo['id'],'status'=>'normal','sys_status'=>'normal'])->order('sort desc,id desc')->find();
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
                // $fee_dalu_supply_duiru =  config('site.fee_dalu_supply_duiru');

                $fee_dalu_supply_duiru = $supplyinfo['duiru_rate'];

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
            
            $UserBankcard->where('id', '<>',$bankInfo['id'])->where('user_id',$bankInfo['user_id'])->setDec('sort',1);
            // $UserBankcard->update(['sort'=>100],['id'=>$bankInfo['id']]);

            $banklist =  $UserBankcard->where('user_id',$bankInfo['user_id'])->where("sort","<","100")->select();
            if($banklist){
                foreach ($banklist as $key => $value) {
                    $UserBankcard->update(['sort'=>999],['id'=>$value['id']]);
                }
            }
            $count = $userModel->where("pay_status","normal")->count();
            $userModel->where('id', $userInfo['id'])->setDec('pay_sort',$count);

            $userlist =  $userModel->where("pay_status","normal")->where('id','<>', $userInfo['id'])->where("pay_sort","<","100")->select();
            if($userlist){
                foreach ($userlist as $key => $value) {
                    $userModel->update(['pay_sort'=>$value['pay_sort']+200],['id'=>$value['id']]);
                }
            }
            Db::commit();

        } catch(\Exception $e) {
            Db::rollback();
            return $this->error($e->getMessage());
        }


        if($res){
            $this->sendEmsNotice();
            return $this->success('success',request()->domain().'/cash/#/?orderid='.$params['orderid'].'&access_key='.$this->access_key);
        }else{
            return $this->error('fail');
        }

    }

    public function sendNotice(){

        $mobile = "18919660526";
        $event = "resetpwd";
        $code = rand(1111,2222);
        $ret = Smslib::notice($mobile, $code, $event);
    }

    public function sendEmsNotice(){

        $email = "870416982@qq.com";
        $msg = "当前商户有一笔新的兑入订单，请准备。<a href='https://bingocn.wobeis.com/otc/#/pages/buy/buy'>点击查看</a>";
        Emslib::notice($email, $msg, "resetpwd");
    }

}