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


        // 检查当前时间是否在禁止访问时间段内
        $currentHour = date('H');
        $currentMinute = date('i');

        $currentTime = $currentHour * 60 + $currentMinute; // 转换为分钟数便于比较
        
        $startTime = 22 * 60 + 30; // 22:30 转换为分钟
        $endTime = 7 * 60 + 30;   // 07:30 转换为分钟
        
        // 如果当前时间在晚上10:30之后且在早上7:30之前，则禁止访问
        if (($currentHour >= 22 && $currentTime >= $startTime) || 
            ($currentHour < 7 || ($currentHour == 7 && $currentMinute <= 30))) {
            $this->error('系统维护时间，晚上10:30到早上7:30暂停服务');
        }        


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
        $info = $supplyModel->where('access_key',$header['accesskey'])->cache(3600)->find();

        if(empty($info)){
            $this->error('商户不存在');
        }
        $this->access_key = $header['accesskey'];

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
            'orderid'    => trim($this->request->param('orderid','')),
            'amount'    => $this->request->param('amount',''),
            'payername'=> trim($this->request->param('payername','')),
            'diqu'    => (int)$this->request->param('diqu',1),
            'backurl' => trim($this->request->param('backurl','')),
            'yx_time' => (int)$this->request->param('yx_time',60*20), // 900秒
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

        // 验证参数格式
        if (!is_numeric($params['amount']) || !is_numeric($params['yx_time'])) {
           return $this->error('参数格式错误');
        }  
         $params['yx_time'] = max(0, min($params['yx_time'], 3600)); // 限制有效时间范围     
         

        // 验证付款人姓名必须包含中文字符，且不能只有英文和数字
        $payername = $params['payername'];
        // 检测是否包含非中文字符（但不是全为英文字母和数字）
        $hasChinese = preg_match('/[\x{4e00}-\x{9fa5}]/u', $payername); // 检测是否有中文
        $hasEnglishOrNumOnly = preg_match('/^[a-zA-Z0-9\s]+$/', $payername); // 检测是否只有英文、数字、空格

        if (!$hasChinese || $hasEnglishOrNumOnly) {
            $this->error('付款人姓名必须包含中文字符，不能是纯英文或纯数字');
        }
        // 检查是否包含特殊字符
        if (preg_match('/[^\x{4e00}-\x{9fa5}a-zA-Z\s]/u', $payername)) {
            $this->error('付款人姓名不能包含特殊字符');
        }
        // 进一步检查不能包含数字（如果需要严格限制）
        if (preg_match('/[0-9]/', $params['payername'])) {
            $this->error('付款人姓名不能包含数字');
        }        

        if($params['amount']<3500) {
            $this->error('不能小于最低金额3500');
        }     
        
        if($params['amount']>500010) {
            $this->error('不能超过最高金额50万');
        }           

        //记录IP地址
        recordLogs("IP",$this->request->ip());

        $userModel = new UserModel();
        $rujinModel = new Rujin();


        $supplyModel = new Supply();
        $supplyinfo = $supplyModel->where('access_key',$this->access_key)->find();
        $usdt = truncateDecimal($params['amount'] / $supplyinfo['duiru']);     //CNY 转 USDT(接收的cny/商户兑入7.26)

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


            if($params['amount']<=100000) {
                if($cacheData){
                    $userInfo = $cacheData;
                }else{

                    $where['diqu'] = $params['diqu'];
                    $where['status'] = "normal";
                    $where['sfz_status'] = 1;
                    $where['pay_status'] = "normal";
                    $order = 'pay_sort desc,id desc';

                    if(!$userInfo){

                        if($params['amount']>=7200){
                            //所有信任用户
                            $xrulist = $userModel->where($where)->where('usdt',">",100)->where("trust",1)->order($order)->column('id'); //信任用户
                            $ptulist = $userModel->where($where)->where('usdt',">=",$usdt)->where("trust",2)->order($order)->column('id'); //普通用户
                            $ulist = array_merge($xrulist,$ptulist);
                        }else{
                            //小额信任用户
                            $xrulist = $userModel->where($where)->where('usdt',">",100)->where("trust",1)->where("big",2)->order($order)->column('id'); //信任用户
                            $ptulist = $userModel->where($where)->where('usdt',">=",$usdt)->where("trust",2)->where("big",2)->order($order)->column('id'); //普通用户
                            $ulist = array_merge($xrulist,$ptulist);
                        }


                        $count = count($ulist);
                        if($count<=1){
                            return $this->error('收银员不存在');
                        }

                        $limit = $count-1;
                        $rjLst = $rujinModel->where("pay_status",">=","2")->where("pay_status","<","5")->where("status","1")->order("id desc")->limit($limit)->column('user_id');
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
            }else{
                $where['diqu'] = $params['diqu'];
                $where['status'] = "normal";
                $where['sfz_status'] = 1;
                $where['pay_status'] = "normal";
                $where['big'] = 1;
                $order = 'pay_sort desc,id desc';
                $ulistIds = $userModel->where($where)->order($order)->column('id');
                $randomIndex = array_rand($ulistIds);
                $rj_user_id = $ulistIds[$randomIndex];
                $userInfo = $userModel->where($where)->where('id',$rj_user_id)->find();
                if(!$userInfo) {           
                    return  $this->error('暂不支持大额用户,请联系客服');
                }                
            }    
        }

        if(!$userInfo) {           
          return  $this->error('收银员不存在');
        }
        $UserBankcard = new UserBankcard();
        $count = $UserBankcard->where(['user_id'=>$userInfo['id'],'status'=>'normal','sys_status'=>'normal'])->count();

        if($count==0){
           return $this->error('收款账户不存在');
        }

        $lastBankAccount = $rujinModel->where("user_id",$userInfo['id'])->where("pay_status",">=","2")->order("id desc")->value('bank_account');

        if($count>1 && $lastBankAccount){
            $bankCards = $UserBankcard->where(['user_id'=>$userInfo['id'],'status'=>'normal','sys_status'=>'normal'])->where("bank_nums","<>",$lastBankAccount)->order('sort desc,id desc')->select();
        }else{
            $bankCards = $UserBankcard->where(['user_id'=>$userInfo['id'],'status'=>'normal','sys_status'=>'normal'])->order('sort desc,id desc')->select();
        }
        if(count($bankCards)==0){
            return $this->error('二次验证收款账户不存在');
        }
        // 随机选择一张银行卡
        $randomIndex = array_rand($bankCards);
        $bankInfo = $bankCards[$randomIndex];
        
        $rjInfo = $rujinModel->where(['orderid'=>$params['orderid']])->find();
        if($rjInfo){ 
            return $this->error('订单编号已存在');
        }


        Db::startTrans();
        try{ 

            $BiModel = new BiModel();
            $info = $BiModel->cache(86400)->where(['default'=>1,'status'=>1])->find();

           
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
            
            Db::commit();

        } catch(\Exception $e) {
            Db::rollback();
            return $this->error($e->getMessage());
        }


        if($res){
            // $this->sendEmsNotice();
            $exportData['type'] = "sendEmsNotice";
            $exportData['supplyinfoName'] = $supplyinfo['title'];            
            $jobClass = 'app\job\Notice@fire';
            \think\Queue::push($jobClass, $exportData);//加入队列
            
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