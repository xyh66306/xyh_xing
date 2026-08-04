<?php

namespace app\openapi\controller;

use app\common\controller\Api;
use app\common\model\Supply;
use app\common\model\pay\Address as PayAddress;
use app\common\model\order\Shrj as OrderShrjModel;
use app\common\model\User as UserModel;
use app\common\model\Task;
use app\common\library\Sms as Smslib;
use app\common\library\Ems as Emslib;
use think\Db;
use think\Cache;
use think\Request;


/**
 * 充值接口
 */
class Recharge extends Api
{

    use Send;
    protected $noNeedRight = ['index','details'];
    protected $noNeedLogin = ['index','details'];

    protected $access_key = "";
    public function __construct(Request $request)
    {
        parent::__construct(); // 确保调用父类构造函数

    }


    /**
     * 收银台接口
     */
    public function index()
    { 
        
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



        $params = [
            'accesskey'    => $header['accesskey'],
            'gmtrequest'    => $header['gmtrequest'],
            'randomstr'     => $header['randomstr'],
            'signature'     => $header['signature'],
        ];
        #先鉴权
        $this->Authentication($params, $info['access_secret']);        
        
        
        $params = [
            'orderid'    => trim($this->request->param('orderid','')),
            'usdt'    => $this->request->param('usdt',''),
            'backurl' => trim($this->request->param('backurl','')),
        ];
        if(empty($params['orderid'])) {
            $this->error('订单号错误');
        }
        if(empty($params['usdt'])) {
            $this->error('金额错误');
        }
        if(empty($params['backurl'])) {
            $this->error('回调地址错误');
        }  
        
        $rate = 0;
        $fee = truncateDecimal($params['usdt']*$rate,4);

        $payAddress = new PayAddress();
        $payLsts = $payAddress->where('status',"normal")->select();
        if(empty($payLsts)){
            $this->error('充值地址错误');
        }
        $randomIndex = array_rand($payLsts);
        $pay_address_info = $payLsts[$randomIndex];        //随机获取一条收款地址
        
        $data = [
            "access_key" => $header['accesskey'],
            "order_id" => $params['orderid'],
            "usdt" => $params['usdt'],
            "fee"   => $fee,
            "type"=>$pay_address_info['type'],
            "address"=>$pay_address_info['address'],
            "callback" => $params['backurl'],
            "pay_status"=>1,
            "status"=>1,
            "createtime"=>  time(),
            "updatetime"=>time(),
        ];
        $OrderShrjModel = new OrderShrjModel();
        $res = $OrderShrjModel->save($data);


        if($res){
            $result['type'] = $pay_address_info['type'];
            $result['address'] = $pay_address_info['address'];
            $result['cashier'] = request()->domain().'/cash/#/pages/recharge/index?orderid='.$params['orderid'].'&access_key='.$header['accesskey'];
            return $this->success('success',$result);
        }else{
            return $this->error('fail');
        }

    }

    /**
     * 订单详情接口
     */
    public function details(){
        $params = [
            'orderid'    => trim($this->request->param('orderid','')),
        ];
        if(empty($params['orderid'])) {
            $this->error('订单号错误');
        }
        $OrderShrjModel = new OrderShrjModel();
        $info = $OrderShrjModel->where('order_id',$params['orderid'])->find();
        if(empty($info)){
            $this->error('订单不存在');
        }
        return $this->success($info);
    }


    public function sendNotice(){

        $mobile = "18919660526";
        $event = "resetpwd";
        $code = rand(1111,2222);
        $ret = Smslib::notice($mobile, $code, $event);
    }

    public function sendEmsNotice(){

        $email = "870416982@qq.com";
        $msg = "当前商户有一笔新的充值订单，请准备。<a href='https://bingocn.wobeis.com/otc/#/pages/buy/buy'>点击查看</a>";
        Emslib::notice($email, $msg, "resetpwd");
    }

}