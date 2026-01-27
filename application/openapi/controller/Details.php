<?php

namespace app\openapi\controller;

use app\common\controller\Api;
use app\common\model\Supply;
use app\common\model\User as UserModel;
use app\common\model\UserPayewm;
use app\common\model\UserBankcard;
use app\common\model\UserRebate;
use app\common\model\Commission;
use app\common\model\Task;
use app\common\model\order\Rujin;
use app\common\library\Sms as Smslib;
use app\common\library\Ems as Emslib;
use think\Cache;
use think\Db;
use think\Request;

/**
 * 详情接口
 */
class Details extends Api
{
    use Send;
    protected $noNeedRight = ['index'];
    protected $noNeedLogin = ['index','payOrder','ceshi'];

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
        if(empty($info)){
            $this->error('商户不存在');
        }
        $this->supplyInfo = $info;
        $this->access_key = $info['access_key'];
        $this->secret = $info['access_secret'];
    }



    /**
     * 预抢单
     * 订单详情
     */
    public function index(){ 


        $orderid = $this->request->param('orderid');
        if(!$orderid){
            return $this->error('订单id不能为空');
        }

        $rujinModel = new Rujin();
        $userPayewm = new UserPayewm();
        $UserBankcard = new UserBankcard();
        $userModel = new UserModel();


        $detailsInfo = $rujinModel->field("orderid,user_id,amount,yx_time,diqu,user_usdt,pay_time")->where(['orderid'=>$orderid])->find();

        if(!$detailsInfo){
            return $this->error('订单不存在');
        }

        
        if($detailsInfo['yx_time']<0){
            return $this->error('已超时');
        }


        $userInfo = $userModel->where(['id'=>$detailsInfo['user_id']])->find();
        if(!$userInfo) {
          return  $this->error('收银员不存在');
        }


        $bankInfo = $UserBankcard->field("username,bank_name,bank_nums,bank_zhmc")->where(['user_id'=>$userInfo['id'],'status'=>'normal','sys_status'=>'normal'])->find();
        $wxpayinfo = $userPayewm->field("username,pay_skpt,pay_ewm_image")->where(['user_id'=>$userInfo['id'],'pay_skpt'=>'wxpay','status'=>'normal','sys_status'=>'normal'])->find();
        $alipayinfo = $userPayewm->field("username,pay_skpt,pay_ewm_image")->where(['user_id'=>$userInfo['id'],'pay_skpt'=>'alipay','status'=>'normal','sys_status'=>'normal'])->find();
        if(!$bankInfo && !$wxpayinfo && !$alipayinfo){
            return $this->error('收款账户不存在');
        }

        // if($detailsInfo['yx_time']-time()<=0){
        //     return $this->error('已超时');
        // }

        $minutes = intdiv($detailsInfo['yx_time']-time(),60); 
        $seconds =  floatval(($detailsInfo['yx_time']-time())%60); 

        $info['yx_time_min'] = $minutes;
        $info['yx_time_sec'] = $seconds;
        $info['wxpay'] = $wxpayinfo;
        $info['alipay'] = $alipayinfo;
        $info['bankInfo'] = $bankInfo;
        $info['amount'] = $detailsInfo['amount'];
        $info['usdt'] = $detailsInfo['user_usdt'];
        $info['pay_time'] = $detailsInfo['pay_time'] ? date("Y-m-d H:i:s",$detailsInfo['pay_time']):'';
        $info['token'] = $this->getOrderToken($orderid);
        return $this->success('success',$info);

    }

    /**
    * 支付订单
    */   
    public function payOrder(){ 

        $orderid = $this->request->param('orderid');        // 订单id
        $pay_type = $this->request->param('pay_type','');  // 支付方式
        $pinzheng_image = $this->request->param('pinzheng_image');  // 凭证图片
        $authtoken = input("auth_token", '');

        if(!$orderid){ 
            return $this->error('订单id不能为空');
        }       
        if(!$pay_type){ 
            return $this->error('支付方式不能为空');
        }
        if(!$pinzheng_image){ 
            return $this->error('凭证图片不能为空');
        }
        if (!$this->checkOrderToken($orderid, $authtoken)) {
            $this->error('参数错误');
        }           

        $pay_type_arr = ['bank','alipay','wxpay'];
        if(!in_array($pay_type,$pay_type_arr)){
            return $this->error('pay_type参数错误');
        }

        $rujinModel = new Rujin();
        $info = $rujinModel->where(['orderid'=>$orderid])->find();
        if(!$info){
            return $this->error('订单不存在');
        }
      
        if($info['pay_status'] >= 2){
            return $this->error('订单已处理');
        }

        $userPayewm = new UserPayewm();
        $UserBankcard = new UserBankcard();

        $bankInfo = $UserBankcard->field("username,bank_name,bank_nums,bank_zhdz")->where(['user_id'=>$info['user_id'],'status'=>'normal','sys_status'=>'normal'])->find();
        $wxpayinfo = $userPayewm->field("username,pay_skpt,pay_nums,pay_ewm_image")->where(['user_id'=>$info['user_id'],'pay_skpt'=>'wxpay','status'=>'normal','sys_status'=>'normal'])->find();
        $alipayinfo = $userPayewm->field("username,pay_skpt,pay_nums,pay_ewm_image")->where(['user_id'=>$info['user_id'],'pay_skpt'=>'alipay','status'=>'normal','sys_status'=>'normal'])->find();        


        if($pay_type === 'bank' && !$bankInfo) {
            return $this->error('银行账户信息不存在或不可用');
        }
        if($pay_type === 'alipay' && !$alipayinfo) {
            return $this->error('支付宝信息不存在或不可用');
        }
        if($pay_type === 'wxpay' && !$wxpayinfo) {
            return $this->error('微信支付信息不存在或不可用');
        }        

        Db::startTrans();

        try {
            $data = [
                'status' => 1,
                'pay_status' => 2,
                'pay_time' => time(),
                'pay_type' => $pay_type,
                'pinzheng_image'=>$pinzheng_image,
            ];
            if($pay_type == 'bank'){
                $data['username'] = $bankInfo['username'];
                $data['bank_name'] = $bankInfo['bank_name'];
                $data['bank_account'] = $bankInfo['bank_nums'];
                $data['bank_zhihang'] = $bankInfo['bank_zhdz'];
            }
            if($pay_type == 'alipay'){
                $data['username'] = $alipayinfo['username'];
                $data['pay_account'] = $alipayinfo['pay_nums'];
                $data['pay_ewm_image'] = $alipayinfo['pay_ewm_image'];
            }   
            if($pay_type == 'wxpay'){
                $data['username'] = $wxpayinfo['username'];
                $data['pay_account'] = $wxpayinfo['pay_nums'];
                $data['pay_ewm_image'] = $wxpayinfo['pay_ewm_image'];
            }                   


            $res = $rujinModel->update($data,['id'=>$info['id']]);
            $fenyong = truncateDecimal($info['user_fee'] + $info['supply_fee']);
            $this->commission($info['user_id'],$info['merchantOrderNo'],$orderid,$info['user_usdt'],$fenyong);
            if($res){

                //通知买方待审核
                $taskModel = new Task();
                $data = [
                    'access_key'     => $this->access_key,
                    'access_secret'  => $this->secret,
                    'name' => 'cash',
                    'message'=>'',
                    'params' => [
                        'orderid'=> $orderid,
                        'url'  => $info['callback'],
                        'pay_status'=>2
                    ]
                ];
                $taskModel->addTask($data,"Cash");
                Db::commit();
            }

        } catch(\Exception $e) {
            Db::rollback();
            return $this->error($e->getMessage());
        }
        $userModel = new UserModel();
        $userInfo = $userModel->where(['id'=>$info['user_id']])->find();

        // $this->sendEmsNotice($userInfo['email'],$info);
        $exportData['type']     = "sendEmsCdsNotice";
        $exportData['email']    = $userInfo['email'];
        $exportData['orderid']  = $info['orderid'];
        $exportData['user_id']    = $info['user_id'];
        $jobClass = 'app\job\Notice@fire';
        \think\Queue::push($jobClass, $exportData);//加入队列


        $pinyinname = \fast\Pinyin::get($info['payername']);
        $name = $this->access_key."-".$info['amount']."-".$pinyinname;
        $cacheData = Cache::get($name);
        if ($cacheData !== false && $cacheData !== null) {
            Cache::rm($name);
        }

        // $this->sendNotice($userInfo,$info);
        $this->success('下单成功，等待确认');
    }    



    /***
     * 分佣
     */
    public function commission($user_id,$fy_orderid,$p4b_orderid,$number,$total)
    {
		$fanyong = config("site.fanyong");
		
		if($fanyong==0){
			return;
		}

        if($this->supplyInfo['duiru_fanyong']==0){
            return;
        }

        $Commission = new Commission();
        $userModel  = new UserModel();

        $uinfo = $userModel->where("id", $user_id)->find();

        $rateLst =  $this->getrate($uinfo);

        $result = [];
        $team_total = 0;
        foreach ($rateLst as $key => $value) { 

            $money = truncateDecimal($number * $value['rate'] / 100);
            if($money<=0){
                continue;
            }
            $team_total += $money;
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
                'order_status'=>1,
                'remarks'=> $number."*".$value['rate'],
                'ctime' => time(),
                'utime' => time(),
            ];

            $result[] = $rebateData;
        }
        $diff = $total - $team_total;
        $rebateData = [
            'user_id' =>$user_id,
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
            'order_status'=>1,
            'remarks'=>$total."-".$team_total,
            'ctime' => time(),
            'utime' => time(),
        ];
        $result[] = $rebateData;

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
        return $result;
    }    


    public function sendNotice($userInfo,$info){

        $mobile = "18919660526";
        $event = "resetpwd";
        $code = rand(3333,9999);
        $ret = Smslib::notice($mobile, $code, $event);

        $email = "870416982@qq.com";
        // $msg = "用户ID".$userInfo['id']."当前有一笔新的兑出订单".$info['orderid']."，金额：".$info['amount']."您可以登录抢单查看。<a href='https://bingocn.wobeis.com/otc/#/pages/buy/buy'>点击查看</a>";
        $msg = $userInfo['id']."您好，订单号".$info['orderid'].",请查看是否收到款，麻烦尽快确认。温馨提醒一定务必核实姓名，金额，订单号是否吻合，避免不必要的损失";
        Emslib::notice($email, $msg, "resetpwd");

    }


    public function sendEmsNotice($email,$info){

        $msg = "您好，订单号".$info['orderid'].",请查看是否收到款，麻烦尽快确认。温馨提醒一定务必核实姓名，金额，订单号是否吻合，避免不必要的损失";
        Emslib::notice($email, $msg, "resetpwd");
    }
  


    //结合orderid 生成一个一次性令牌
    public function getOrderToken($order_id)
    { 
        $token = md5($order_id . time() . uniqid());
        Cache::set('order_token_' . $order_id, $token, 60*30); // 15分钟有效期
        return $token;        
    }
    public function checkOrderToken($order_id, $token)
    {
        $cache_token = Cache::get('order_token_' . $order_id);
        if ($cache_token == $token) {
            // 验证成功 删除Token
            Cache::rm('order_token_' . $order_id);
            return true;
        }
        return false;
    }    


}